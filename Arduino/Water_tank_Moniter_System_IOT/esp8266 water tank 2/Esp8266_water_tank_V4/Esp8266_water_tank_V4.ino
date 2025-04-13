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
#define BLYNK_TEMPLATE_ID "TMPL6aS_e6pPg"
#define BLYNK_TEMPLATE_NAME "ESP32 water tank monitor"
#define BLYNK_AUTH_TOKEN "akhEEUHjjN_wvjFVSZiYtWZVcb1xSHGX"

// Include required libraries (using ESP8266 libraries for NodeMCU)
#include <ESP8266WiFi.h>
#include <BlynkSimpleEsp8266.h>
#include <AceButton.h>
#include <OneWire.h>
#include <DallasTemperature.h>
#include <Wire.h>
#include <Adafruit_SSD1306.h>
using namespace ace_button;

// WiFi credentials
char ssid[] = "Sabin01";
char pass[] = "10102020";

// Bottle calibration parameters for a 20-cm bottle:
// When empty (~20 cm) → 0% water level; when full (~3 cm) → 100%
int BOTTLE_EMPTY_DISTANCE = 20;
int BOTTLE_FULL_DISTANCE  = 3;

// Pump control thresholds in AUTO mode:
const int PUMP_ON_THRESHOLD  = 10;   // Logical pump ON when water level is ≤ 10%
const int PUMP_OFF_THRESHOLD = 90;   // Logical pump OFF when water level is ≥ 90%

// TDS threshold for water purity (ppm)
const float TDS_THRESHOLD = 300.0;

// Pin Definitions (using NodeMCU D-pin labels):
#define DS18B20_PIN       0     // DS18B20 sensor on digital pin 0 (GPIO0)
#define TRIG_PIN          14    // Ultrasonic sensor TRIG on pin 14 (D5, GPIO14)
#define ECHO_PIN          12    // Ultrasonic sensor ECHO on pin 12 (D6, GPIO12)
// Inverted relay logic: LOW = pump ON, HIGH = pump OFF.
#define RELAY_PIN         D0    // Pump Relay on D0 (GPIO16)
#define TDS_PIN           A0    // TDS sensor on Analog A0
#define MODE_BUTTON_PIN   D7    // Mode Button on D7 (GPIO13)
#define MANUAL_BUTTON_PIN D4    // Manual Pump Button on D4 (GPIO2)
#define BUZZER_PIN        D8    // Buzzer on D8 (GPIO15)

// Blynk Virtual Pin Definitions:
#define VPIN_WATER_LEVEL  V1    // Water level percentage (%)
#define VPIN_DISTANCE     V2    // Distance reading (cm)
#define VPIN_MODE         V3    // Operating mode: AUTO (1) or MANUAL (0)
#define VPIN_RELAY        V4    // Pump status: ON (1) or OFF (0)
#define VPIN_TEMP         V6    // Temperature in °C
#define VPIN_TDS          V7    // TDS sensor value (ppm)

// OLED Display configuration:
#define SCREEN_WIDTH 128
#define SCREEN_HEIGHT 64
#define OLED_RESET   -1
Adafruit_SSD1306 display(SCREEN_WIDTH, SCREEN_HEIGHT, &Wire, OLED_RESET);

// Create OneWire and DS18B20 objects:
OneWire oneWire(DS18B20_PIN);
DallasTemperature DS18B20(&oneWire);

// Global sensor/control variables:
float duration;          // Ultrasonic pulse duration (µs)
float distance;          // Calculated distance (cm)
int waterLevelPer = 0;   // Water level percentage (0–100)
float waterTemp = 0.0;   // Temperature (°C)
float waterTDS  = 0.0;   // TDS sensor reading (ppm)

// "toggleRelay" indicates the logical pump state (true = pump ON, false = pump OFF)
// With inverted logic: when toggleRelay is true, physical output is LOW (pump energized).
bool toggleRelay = false;
bool modeFlag    = true;   // Operating mode: true = AUTO, false = MANUAL
String currMode  = "AUTO";

// For AUTO mode buzzer control:
bool beepScheduled = false;

// Blynk authentication (from template definitions)
char auth[] = BLYNK_AUTH_TOKEN;
BlynkTimer timer;

// Create AceButton objects for the physical buttons:
ButtonConfig modeBtnConfig;
AceButton modeButton(&modeBtnConfig, MODE_BUTTON_PIN);
ButtonConfig manualBtnConfig;
AceButton manualButton(&manualBtnConfig, MANUAL_BUTTON_PIN);

// Function prototypes:
void measureSensors();
void updateDisplay();
void modeButtonHandler(AceButton* button, uint8_t eventType, uint8_t buttonState);
void manualButtonHandler(AceButton* button, uint8_t eventType, uint8_t buttonState);
void beepNonBlocking(uint16_t durationMs);
void turnOffBuzzer();

// Non-blocking beep function using BlynkTimer:
void beepNonBlocking(uint16_t durationMs) {
  digitalWrite(BUZZER_PIN, HIGH);
  timer.setTimeout(durationMs, [](){
    digitalWrite(BUZZER_PIN, LOW);
  });
}

// BlynkTimer callback to turn off the buzzer after 15 seconds:
void turnOffBuzzer() {
  digitalWrite(BUZZER_PIN, LOW);
  beepScheduled = false;
}

// ---------------- Sensor Measurement Function ----------------
void measureSensors() {
  // Ultrasonic Sensor Measurement:
  digitalWrite(TRIG_PIN, LOW);
  delayMicroseconds(2);
  digitalWrite(TRIG_PIN, HIGH);
  delayMicroseconds(20);
  digitalWrite(TRIG_PIN, LOW);
  duration = pulseIn(ECHO_PIN, HIGH);
  distance = ((duration / 2.0) * 0.343) / 10.0;
  
  // If ultrasonic sensor is completely blocked (returns 0), force water level to 100%
  if (distance <= 0) {
    waterLevelPer = 100;
  } else if (distance <= BOTTLE_EMPTY_DISTANCE && distance >= BOTTLE_FULL_DISTANCE) {
    waterLevelPer = map((int)distance, BOTTLE_EMPTY_DISTANCE, BOTTLE_FULL_DISTANCE, 0, 100);
  } else {
    waterLevelPer = (distance > BOTTLE_EMPTY_DISTANCE) ? 0 : 100;
  }
  
  // DS18B20 Temperature Measurement:
  DS18B20.requestTemperatures();
  waterTemp = DS18B20.getTempCByIndex(0);
  
  // TDS Sensor Reading:
  waterTDS = analogRead(TDS_PIN) * (3.3 / 1023.0) * 500.0;
  
  // AUTO Mode Logic with Buzzer Alerts:
  if (modeFlag) {  // In AUTO mode:
    if (waterLevelPer <= PUMP_ON_THRESHOLD && !toggleRelay) {
      // Inverted relay logic: low output = pump ON.
      digitalWrite(RELAY_PIN, LOW);
      toggleRelay = true;
      if (!beepScheduled) {
        digitalWrite(BUZZER_PIN, HIGH);
        beepScheduled = true;
        timer.setTimeout(15000L, turnOffBuzzer);
      }
    }
    if (waterLevelPer >= PUMP_OFF_THRESHOLD && toggleRelay) {
      digitalWrite(RELAY_PIN, HIGH);
      toggleRelay = false;
      beepNonBlocking(100);
    }
  }
  
  // Blynk Virtual Pin Updates (logical state remains the same):
  Blynk.virtualWrite(VPIN_WATER_LEVEL, waterLevelPer);
  Blynk.virtualWrite(VPIN_DISTANCE, String(distance) + " cm");
  Blynk.virtualWrite(VPIN_TEMP, waterTemp);
  Blynk.virtualWrite(VPIN_TDS, waterTDS);
  Blynk.virtualWrite(VPIN_MODE, modeFlag ? 1 : 0);
  Blynk.virtualWrite(VPIN_RELAY, toggleRelay ? 1 : 0);
  
  updateDisplay();
}

// ---------------- OLED Display Update Function ----------------
void updateDisplay() {
  display.clearDisplay();
  
  // Display a small dot at the top-right if WiFi is connected:
  if (WiFi.status() == WL_CONNECTED) {
    display.fillCircle(SCREEN_WIDTH - 5, 5, 2, SSD1306_WHITE);
  }
  
  // Draw a border:
  display.drawRect(0, 0, SCREEN_WIDTH, SCREEN_HEIGHT, SSD1306_WHITE);
  
  // Top Section: Large, centered water level percentage:
  display.setTextSize(3);
  display.setTextColor(SSD1306_WHITE);
  String levelStr = String(waterLevelPer) + "%";
  int16_t x1, y1;
  uint16_t w, h;
  display.getTextBounds(levelStr, 0, 0, &x1, &y1, &w, &h);
  int centerX = (SCREEN_WIDTH - w) / 2;
  display.setCursor(centerX, 5);
  display.print(levelStr);
  
  // Middle Section: Temperature (line 1) and water purity (line 2):
  display.setTextSize(1);
  display.setTextColor(SSD1306_WHITE);
  display.setCursor(5, 30);
  display.print("Temp:");
  display.print(waterTemp, 1);
  display.print("C");
  
  display.setCursor(5, 40);
  display.print("Water:");
  display.print((waterTDS < TDS_THRESHOLD) ? "PURE" : "NOT PURE");
  
  // Bottom Section: Operating mode and pump status:
  display.setCursor(5, 55);
  display.print("Mode:");
  display.print(modeFlag ? "AUTO" : "MANUAL");
  
  display.setCursor(70, 55);
  display.print("Pump:");
  display.print(toggleRelay ? "ON" : "OFF");
  
  display.display();
}

// ---------------- Manual Pump Button Handler ----------------
void manualButtonHandler(AceButton* button, uint8_t eventType, uint8_t buttonState) {
  // Manual Pump Button (D4) toggles the pump only when in MANUAL mode.
  if (eventType == AceButton::kEventReleased) {
    if (!modeFlag) {
      toggleRelay = !toggleRelay;
      // Inverted relay logic: LOW means pump ON, HIGH means pump OFF.
      digitalWrite(RELAY_PIN, toggleRelay ? LOW : HIGH);
      Blynk.virtualWrite(VPIN_RELAY, toggleRelay ? 1 : 0);
      Serial.print("Pump manually turned ");
      Serial.println(toggleRelay ? "ON" : "OFF");
      beepNonBlocking(100);
    }
  }
}

// ---------------- Mode Button Handler ----------------
void modeButtonHandler(AceButton* button, uint8_t eventType, uint8_t buttonState) {
  // Mode Button (D7) toggles AUTO/MANUAL mode.
  if (eventType == AceButton::kEventReleased) {
    modeFlag = !modeFlag;
    Blynk.virtualWrite(VPIN_MODE, modeFlag ? 1 : 0);
    Serial.print("Mode changed to: ");
    Serial.println(modeFlag ? "AUTO" : "MANUAL");
    beepNonBlocking(100);
  }
}

#define SCREEN_WIDTH 128
#define SCREEN_HEIGHT 64

void setup() {
  Serial.begin(115200);
  
  // Set pin modes:
  pinMode(TRIG_PIN, OUTPUT);
  pinMode(ECHO_PIN, INPUT);
  // IMPORTANT: In inverted logic, initialize relay pin to HIGH (pump OFF)
  pinMode(RELAY_PIN, OUTPUT);
  pinMode(MODE_BUTTON_PIN, INPUT_PULLUP);
  pinMode(MANUAL_BUTTON_PIN, INPUT_PULLUP);
  pinMode(BUZZER_PIN, OUTPUT);
  
  digitalWrite(RELAY_PIN, HIGH);  // Pump OFF
  digitalWrite(BUZZER_PIN, LOW);
  
  if (!display.begin(SSD1306_SWITCHCAPVCC, 0x3C)) {
    Serial.println(F("OLED allocation failed"));
    for (;;);
  }
  display.clearDisplay();
  display.display();
  
  DS18B20.begin();
  
  WiFi.begin(ssid, pass);
  Blynk.config(auth);
  
  timer.setInterval(250L, measureSensors);
  
  // Configure and initialize the Mode Button (D7):
  ButtonConfig modeBtnConfig;
  modeBtnConfig.setEventHandler(modeButtonHandler);
  modeButton.init(MODE_BUTTON_PIN);
  
  // Configure and initialize the Manual Pump Button (D4):
  ButtonConfig manualBtnConfig;
  manualBtnConfig.setEventHandler(manualButtonHandler);
  manualButton.init(MANUAL_BUTTON_PIN);
  
  delay(1000);
  
  Blynk.virtualWrite(VPIN_MODE, modeFlag ? 1 : 0);
  Blynk.virtualWrite(VPIN_RELAY, toggleRelay ? 1 : 0);
}

void loop() {
  Blynk.run();
  timer.run();
  modeButton.check();
  manualButton.check();
}
