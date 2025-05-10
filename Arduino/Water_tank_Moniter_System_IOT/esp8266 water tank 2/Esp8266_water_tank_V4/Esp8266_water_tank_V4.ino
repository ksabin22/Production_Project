/**********************************************************************************
 *  TITLE: IoT-based Water Level & Quality Monitor for a 20-cm Bottle using ESP8266,
 *         Ultrasonic Sensor, DS18B20, TDS Sensor, Blynk, OLED Display, Two Buttons & Buzzer
 *
 *  This sketch uses two separate buttons:
 *    - The Mode Button (on D7) toggles between AUTO and MANUAL operation.
 *    - The Manual Pump Button (on D4) toggles the pump in MANUAL mode.
 *
 *  A buzzer connected on D8 behaves as follows in AUTO mode:
 *    - When the water-level percentage is ≤ 10%, the pump is activated and the buzzer
 *      stays ON continuously for 15 seconds.
 *    - When the water level reaches or exceeds 90%, the pump is turned OFF and the buzzer
 *      beeps briefly for 100 ms.
 *
 *  Also, if the ultrasonic sensor is completely blocked (returns 0), the water level is
 *  forced to display 100%.
 *
 *  Hardware Pinout (Non‑Blynk):
 *    - DS18B20 (Water Temp):     Digital pin 0       (GPIO0)
 *    - Ultrasonic Sensor:        TRIG = pin 14, ECHO = pin 12
 *    - Pump Relay (Pump Control): D0                  (GPIO16)
 *    - TDS Sensor:               Analog A0
 *    - OLED Display:             I²C address 0x3C (default ESP8266 I²C pins)
 *    - Mode Button:              D7
 *    - Manual Pump Button:       D4
 *    - Buzzer:                   D8
 *
 *  For a 20-cm bottle the calibration is:
 *    - Empty: ~20 cm → 0% water level
 *    - Full: ~3 cm → 100% water level
 *
 *  AUTO mode: In AUTO mode, the pump turns ON when water level is ≤ 10%
 *             (with buzzer on continuously for 15 sec) and turns OFF when water level is
 *             ≥ 90% (with a brief 100 ms beep).
 *
 *  MANUAL mode: In MANUAL mode, the pump is toggled by the Manual Pump Button.
 *
 *  Blynk connectivity reports sensor values and statuses.
 *
 *  Configure WiFi and Blynk credentials below.
 **********************************************************************************/
// Blynk Template Definitions (must be at the very top)
#include <ESP8266WiFi.h>
#include <Firebase_ESP_Client.h>
#include <Wire.h>
#include <OneWire.h>
#include <DallasTemperature.h>
#include <Adafruit_SSD1306.h>
#include <EEPROM.h>
#include <NTPClient.h>
#include <WiFiUdp.h>
#include "addons/TokenHelper.h"
#include "addons/RTDBHelper.h"

// OLED setup
#define SCREEN_WIDTH 128
#define SCREEN_HEIGHT 64
#define OLED_RESET -1
Adafruit_SSD1306 display(SCREEN_WIDTH, SCREEN_HEIGHT, &Wire, OLED_RESET);

// Firebase
FirebaseData fbdo;
FirebaseAuth auth;
FirebaseConfig config;

// Pin Config
#define DS18B20_PIN       0
#define TRIG_PIN          14
#define ECHO_PIN          12
#define RELAY_PIN         D0
#define TDS_PIN           A0
#define BUZZER_PIN        D8

// EEPROM
#define EEPROM_SIZE 128
String wifi_ssid = "HelloWorld";
String wifi_pass = "helloworld@123";

// Time
WiFiUDP ntpUDP;
NTPClient timeClient(ntpUDP, "pool.ntp.org", 19800, 60000);

// Objects
OneWire oneWire(DS18B20_PIN);
DallasTemperature DS18B20(&oneWire);

// Globals
bool oledEnabled, pumpEnabled, tdsEnabled, tempEnabled, ultrasonicEnabled, buzzerEnabled, autoMode;
bool relayState = false;
float distance = 0, waterTemp = 0, waterTDS = 0;
int waterLevelPer = 0, screenIndex = 0;
unsigned long lastDisplayChange = 0, lastUpdate = 0;

// EEPROM load/save
void loadEEPROM() {
  EEPROM.begin(EEPROM_SIZE);
  oledEnabled = EEPROM.read(0);
  pumpEnabled = EEPROM.read(1);
  tdsEnabled = EEPROM.read(2);
  tempEnabled = EEPROM.read(3);
  ultrasonicEnabled = EEPROM.read(4);
  buzzerEnabled = EEPROM.read(5);
  autoMode = EEPROM.read(6);
  char ssidBuf[33], passBuf[65];
  for (int i = 0; i < 32; i++) ssidBuf[i] = EEPROM.read(10 + i);
  for (int i = 0; i < 64; i++) passBuf[i] = EEPROM.read(50 + i);
  ssidBuf[32] = 0; passBuf[64] = 0;
  wifi_ssid = String(ssidBuf); wifi_pass = String(passBuf);
  EEPROM.end();
}

void saveEEPROM() {
  EEPROM.begin(EEPROM_SIZE);
  EEPROM.write(0, oledEnabled);
  EEPROM.write(1, pumpEnabled);
  EEPROM.write(2, tdsEnabled);
  EEPROM.write(3, tempEnabled);
  EEPROM.write(4, ultrasonicEnabled);
  EEPROM.write(5, buzzerEnabled);
  EEPROM.write(6, autoMode);
  for (int i = 0; i < wifi_ssid.length(); i++) EEPROM.write(10 + i, wifi_ssid[i]);
  for (int i = 0; i < wifi_pass.length(); i++) EEPROM.write(50 + i, wifi_pass[i]);
  EEPROM.commit(); EEPROM.end();
}

// Sensor Reading
void measureSensors() {
  if (ultrasonicEnabled) {
    digitalWrite(TRIG_PIN, LOW); delayMicroseconds(2);
    digitalWrite(TRIG_PIN, HIGH); delayMicroseconds(20); digitalWrite(TRIG_PIN, LOW);
    float duration = pulseIn(ECHO_PIN, HIGH);
    distance = ((duration / 2.0) * 0.0343);
    waterLevelPer = map((int)distance, 2, 20, 100, 0);
    waterLevelPer = constrain(waterLevelPer, 0, 100);
  }

  if (tempEnabled) {
    DS18B20.requestTemperatures();
    waterTemp = DS18B20.getTempCByIndex(0);
  }

  if (tdsEnabled) {
    waterTDS = analogRead(TDS_PIN) * (3.3 / 1023.0) * 500.0;
  }

  if (autoMode && pumpEnabled) {
    if (waterLevelPer <= 10 && !relayState) {
      digitalWrite(RELAY_PIN, LOW); relayState = true;
    }
    if (waterLevelPer >= 90 && relayState) {
      digitalWrite(RELAY_PIN, HIGH); relayState = false;
    }
  }
}

// Firebase Sync
void updateFirebaseStatus() {
  if (!Firebase.ready()) return;

  time_t now = timeClient.getEpochTime();
  struct tm* timeinfo = localtime(&now);
  char dateBuffer[20];
  sprintf(dateBuffer, "%04d-%02d-%02d", timeinfo->tm_year + 1900, timeinfo->tm_mon + 1, timeinfo->tm_mday);
  String dateStr = String(dateBuffer);
  String timeStr = timeClient.getFormattedTime();
  String fullDateTime = dateStr + " " + timeStr;

  Firebase.RTDB.setString(&fbdo, "/status/wifi_ssid", WiFi.SSID());
  Firebase.RTDB.setString(&fbdo, "/status/ip", WiFi.localIP().toString());
  Firebase.RTDB.setString(&fbdo, "/status/mac", WiFi.macAddress());
  Firebase.RTDB.setString(&fbdo, "/status/last_sync", timeStr);
  Firebase.RTDB.setString(&fbdo, "/status/date_time", fullDateTime);

  Firebase.RTDB.setInt(&fbdo, "/readings/water_level", waterLevelPer);
  Firebase.RTDB.setFloat(&fbdo, "/readings/temp_c", waterTemp);
  Firebase.RTDB.setFloat(&fbdo, "/readings/tds_ppm", waterTDS);
  Firebase.RTDB.setFloat(&fbdo, "/readings/distance_cm", distance);

  Firebase.RTDB.setString(&fbdo, "/status/mode", autoMode ? "AUTO" : "MANUAL");
  Firebase.RTDB.setString(&fbdo, "/status/pump", relayState ? "ON" : "OFF");
  Firebase.RTDB.setString(&fbdo, "/status/oled", oledEnabled ? "ON" : "OFF");
  Firebase.RTDB.setString(&fbdo, "/status/temp", tempEnabled ? "ON" : "OFF");
  Firebase.RTDB.setString(&fbdo, "/status/tds", tdsEnabled ? "ON" : "OFF");
  Firebase.RTDB.setString(&fbdo, "/status/buzzer", buzzerEnabled ? "ON" : "OFF");
  Firebase.RTDB.setString(&fbdo, "/status/ultrasonic", ultrasonicEnabled ? "ON" : "OFF");
}

void fetchControlSettings() {
  if (!Firebase.ready()) return;

  oledEnabled        = Firebase.RTDB.getBool(&fbdo, "/control/oled")         ? fbdo.boolData() : oledEnabled;
  pumpEnabled        = Firebase.RTDB.getBool(&fbdo, "/control/pump")         ? fbdo.boolData() : pumpEnabled;
  tdsEnabled         = Firebase.RTDB.getBool(&fbdo, "/control/tds")          ? fbdo.boolData() : tdsEnabled;
  tempEnabled        = Firebase.RTDB.getBool(&fbdo, "/control/temp")         ? fbdo.boolData() : tempEnabled;
  ultrasonicEnabled  = Firebase.RTDB.getBool(&fbdo, "/control/ultrasonic")   ? fbdo.boolData() : ultrasonicEnabled;
  buzzerEnabled      = Firebase.RTDB.getBool(&fbdo, "/control/buzzer")       ? fbdo.boolData() : buzzerEnabled;
  autoMode           = Firebase.RTDB.getBool(&fbdo, "/control/auto_mode")    ? fbdo.boolData() : autoMode;
}

void fetchWiFiCredentials() {
  if (!Firebase.ready()) return;

  if (Firebase.RTDB.getString(&fbdo, "/control/ssid")) {
    String newSSID = fbdo.stringData();
    if (newSSID.length() > 0 && newSSID != wifi_ssid) {
      wifi_ssid = newSSID;
    }
  }

  if (Firebase.RTDB.getString(&fbdo, "/control/password")) {
    String newPASS = fbdo.stringData();
    if (newPASS.length() > 0 && newPASS != wifi_pass) {
      wifi_pass = newPASS;
    }
  }

  saveEEPROM(); // Save the new credentials persistently
}

// OLED Output
void showOLED() {
  if (!oledEnabled) return;

  display.clearDisplay();
  display.setTextColor(SSD1306_WHITE);
  display.setTextSize(1);

  if (millis() < 7000) {
    display.setCursor(0, 10); display.println("Water Tank System");
    display.setCursor(0, 25); display.println("Developed by:");
    display.setCursor(0, 40); display.println("Sabin Khanal");
    display.display(); return;
  }

  if (digitalRead(BUZZER_PIN) == HIGH) {
    display.setCursor(0, 25);
    display.setTextSize(2);
    display.println("⚠ Buzzer Beeping");
    display.display(); return;
  }

  if (millis() - lastDisplayChange >= 1000) {
    screenIndex = (screenIndex + 1) % 6;
    lastDisplayChange = millis();
  }

  switch (screenIndex) {
    case 0:
      display.setCursor(0, 0); display.print("Level: "); display.print(waterLevelPer); display.println("%");
      display.setCursor(0, 20); display.print("Dist: "); display.print(distance); display.println(" cm");
      break;
    case 1:
      display.setCursor(0, 0); display.print("Temp: "); display.print(waterTemp); display.println(" C");
      display.setCursor(0, 20); display.print("TDS: "); display.print(waterTDS); display.println(" ppm");
      display.setCursor(0, 40); display.print("Water: ");
      display.println(waterTDS < 300 ? "Pure" : "Impure");
      break;
    case 2:
      display.setCursor(0, 0); display.print("SSID: "); display.println(WiFi.SSID());
      display.setCursor(0, 20); display.print("IP: "); display.println(WiFi.localIP());
      display.setCursor(0, 40); display.print("MAC: "); display.println(WiFi.macAddress());
      break;
    case 3:
      display.setCursor(0, 0); display.print("Mode: "); display.println(autoMode ? "AUTO" : "MANUAL");
      display.setCursor(0, 20); display.print("Pump: "); display.println(relayState ? "ON" : "OFF");
      break;
    case 4:
      display.setCursor(0, 0); display.println("Last Sync:");
      display.setCursor(0, 20); display.println(timeClient.getFormattedTime());
      break;
    case 5:
      display.setCursor(0, 0); display.println("Sensors:");
      display.setCursor(0, 10); display.print("TDS: "); display.println(tdsEnabled ? "ON" : "OFF");
      display.setCursor(0, 20); display.print("TEMP: "); display.println(tempEnabled ? "ON" : "OFF");
      display.setCursor(0, 30); display.print("ULTRA: "); display.println(ultrasonicEnabled ? "ON" : "OFF");
      display.setCursor(0, 40); display.print("BUZZ: "); display.println(buzzerEnabled ? "ON" : "OFF");
      display.setCursor(0, 50); display.print("OLED: "); display.println(oledEnabled ? "ON" : "OFF");
      break;
  }

  display.display();
}

// Setup
void setup() {
  Serial.begin(115200);
  EEPROM.begin(EEPROM_SIZE);

  pinMode(TRIG_PIN, OUTPUT); pinMode(ECHO_PIN, INPUT);
  pinMode(RELAY_PIN, OUTPUT); digitalWrite(RELAY_PIN, HIGH);
  pinMode(BUZZER_PIN, OUTPUT); digitalWrite(BUZZER_PIN, LOW);

  loadEEPROM();
  oledEnabled = pumpEnabled = tdsEnabled = tempEnabled = ultrasonicEnabled = buzzerEnabled = autoMode = true;
  saveEEPROM();

  WiFi.begin(wifi_ssid.c_str(), wifi_pass.c_str());
  while (WiFi.status() != WL_CONNECTED) { delay(500); Serial.print("."); }
  Serial.println("WiFi Connected");

  if (!display.begin(SSD1306_SWITCHCAPVCC, 0x3C)) Serial.println("❌ OLED init failed");

  DS18B20.begin();
  timeClient.begin(); timeClient.update();

  config.database_url = "https://sabin-water-tank-management-default-rtdb.asia-southeast1.firebasedatabase.app";
  config.signer.tokens.legacy_token = "Tv5qiWua1wlkQwzMMcrHSsC3zgRA4dQF90y7Y1Db";
  Firebase.begin(&config, &auth); Firebase.reconnectWiFi(true);
}

// Loop
void loop() {
  if (millis() - lastUpdate >= 1000) {
    timeClient.update();
    fetchWiFiCredentials(); 
    fetchControlSettings();
    measureSensors();
    updateFirebaseStatus();
    showOLED();
    lastUpdate = millis();
  }
}
