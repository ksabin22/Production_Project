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

/* Blynk Cloud Template Info */
#define BLYNK_TEMPLATE_ID "TMPL6UFMsSopO"
#define BLYNK_TEMPLATE_NAME "esp32 plant monitor"
#define BLYNK_AUTH_TOKEN  "kF0fyRTPCYOFuFP96vThExH3OiyLGQyi"

// WiFi Credentials
char ssid[] = "pradeepdeuba_wnepal";  // WiFi network name
char pass[] = "434F002D93";            // WiFi network password

// Bottle calibration parameters for a 20-cm bottle:
int BOTTLE_EMPTY_DISTANCE = 20;  // When empty (~20 cm): maps to 0%
int BOTTLE_FULL_DISTANCE  = 3;   // When full (~3 cm): maps to 100%

// Pump control thresholds in AUTO mode:
const int PUMP_ON_THRESHOLD  = 10;  // Pump turns ON when water level is ≤ 10%
const int PUMP_OFF_THRESHOLD = 90;  // Pump turns OFF when water level is ≥ 90%

// TDS threshold for water purity (ppm)
const float TDS_THRESHOLD = 300.0;

// Pin Definitions:
#define DS18B20_PIN       0     // DS18B20 sensor on digital pin 0 (GPIO0)
#define TRIG_PIN          14    // Ultrasonic sensor trigger pin
#define ECHO_PIN          12    // Ultrasonic sensor echo pin
#define RELAY_PIN         D0    // Pump Relay on D0 (GPIO16)
#define TDS_PIN           A0    // TDS sensor on analog pin A0
#define MODE_BUTTON_PIN   D7    // Mode Button on D7
#define MANUAL_BUTTON_PIN D4    // Manual Pump Button on D4
#define BUZZER_PIN        D8    // Buzzer on D8

// Blynk Virtual Pin definitions:
#define VPIN_WATER_LEVEL  V1    // Water level percentage
#define VPIN_DISTANCE     V2    // Raw distance reading (cm)
#define VPIN_MODE         V3    // Operating mode: AUTO (1) or MANUAL (0)
#define VPIN_RELAY        V4    // Pump status: ON (1) or OFF (0)
#define VPIN_TEMP         V6    // Water temperature from DS18B20
#define VPIN_TDS          V7    // TDS sensor value

// OLED Display configuration:
#define SCREEN_WIDTH 128
#define SCREEN_HEIGHT 64
#define OLED_RESET   -1
#include <Wire.h>
#include <Adafruit_SSD1306.h>
Adafruit_SSD1306 display(SCREEN_WIDTH, SCREEN_HEIGHT, &Wire, OLED_RESET);

// Include required libraries:
#include <ESP8266WiFi.h>
#include <BlynkSimpleEsp8266.h>
#include <AceButton.h>
#include <OneWire.h>
#include <DallasTemperature.h>
using namespace ace_button;

// Create OneWire and DS18B20 instances:
OneWire oneWire(DS18B20_PIN);
DallasTemperature DS18B20(&oneWire);

// Global sensor/control variables:
float duration;          // Ultrasonic sensor pulse duration (µs)
float distance;          // Calculated distance in cm
int waterLevelPer = 0;   // Water level percentage (0-100)
float waterTemp = 0.0;   // Water temperature in °C
float waterTDS  = 0.0;   // Calculated TDS value (ppm)

bool toggleRelay = false;  // Pump state: true = ON, false = OFF
bool modeFlag    = true;   // Operating mode: true = AUTO, false = MANUAL
String currMode  = "AUTO";

// For AUTO mode buzzer control:
bool beepScheduled = false;

// Function prototype for turning off the buzzer later
void turnOffBuzzer();

// Blynk authentication and timer:
char auth[] = BLYNK_AUTH_TOKEN;
BlynkTimer timer;

// Create AceButton objects for the two buttons:
ButtonConfig modeBtnConfig;
AceButton modeButton(&modeBtnConfig, MODE_BUTTON_PIN);
ButtonConfig manualBtnConfig;
AceButton manualButton(&manualBtnConfig, MANUAL_BUTTON_PIN);

// Forward declarations:
void measureSensors();
void updateDisplay();
void modeButtonHandler(AceButton* button, uint8_t eventType, uint8_t buttonState);
void manualButtonHandler(AceButton* button, uint8_t eventType, uint8_t buttonState);
void beep(uint16_t durationMs);

// Function to beep the buzzer for a given duration (in milliseconds)
void beep(uint16_t durationMs) {
  digitalWrite(BUZZER_PIN, HIGH);
  delay(durationMs);
  digitalWrite(BUZZER_PIN, LOW);
}

// This function is called via BlynkTimer to turn off the buzzer after 15 sec.
void turnOffBuzzer() {
  digitalWrite(BUZZER_PIN, LOW);
  beepScheduled = false;
}

// -------------------------- Sensor Measurement Function --------------------------
void measureSensors() {
  // --- Ultrasonic Sensor ---
  digitalWrite(TRIG_PIN, LOW);
  delayMicroseconds(2);
  digitalWrite(TRIG_PIN, HIGH);
  delayMicroseconds(20);
  digitalWrite(TRIG_PIN, LOW);
  
  duration = pulseIn(ECHO_PIN, HIGH);
  // Convert pulse duration to distance in cm:
  distance = ((duration / 2.0) * 0.343) / 10.0;
  
  // If sensor returns 0 (blocked), force water level to 100%
  if (distance <= 0) {
    waterLevelPer = 100;
  }
  else if (distance <= BOTTLE_EMPTY_DISTANCE && distance >= BOTTLE_FULL_DISTANCE) {
    waterLevelPer = map((int)distance, BOTTLE_EMPTY_DISTANCE, BOTTLE_FULL_DISTANCE, 0, 100);
  }
  else {
    waterLevelPer = (distance > BOTTLE_EMPTY_DISTANCE) ? 0 : 100;
  }
  
  // --- DS18B20 Water Temperature ---
  DS18B20.requestTemperatures();
  waterTemp = DS18B20.getTempCByIndex(0);
  
  // --- TDS Sensor ---
  waterTDS = analogRead(TDS_PIN) * (3.3 / 1023.0) * 500.0;
  
  // --- AUTO Mode Logic with Buzzer Alerts ---
  if (modeFlag) {  // In AUTO mode:
    // When water level is ≤ 10%:
    if (waterLevelPer <= PUMP_ON_THRESHOLD && !toggleRelay) {
      digitalWrite(RELAY_PIN, HIGH);
      toggleRelay = true;
      if (!beepScheduled) {
        digitalWrite(BUZZER_PIN, HIGH);  // Turn buzzer ON continuously
        beepScheduled = true;
        timer.setTimeout(15000L, turnOffBuzzer);  // Schedule buzzer OFF after 15 sec
      }
    }
    // When water level is ≥ 90%:
    if (waterLevelPer >= PUMP_OFF_THRESHOLD && toggleRelay) {
      digitalWrite(RELAY_PIN, LOW);
      toggleRelay = false;
      beep(100);  // Brief beep (100 ms)
    }
  }
  
  // --- Blynk Updates ---
  Blynk.virtualWrite(VPIN_WATER_LEVEL, waterLevelPer);
  Blynk.virtualWrite(VPIN_DISTANCE, String(distance) + " cm");
  Blynk.virtualWrite(VPIN_TEMP, waterTemp);
  Blynk.virtualWrite(VPIN_TDS, waterTDS);
  Blynk.virtualWrite(VPIN_MODE, modeFlag ? 1 : 0);
  Blynk.virtualWrite(VPIN_RELAY, toggleRelay ? 1 : 0);
  
  updateDisplay();
}

// -------------------------- OLED Display Update --------------------------
void updateDisplay() {
  display.clearDisplay();
  
  // Draw a border:
  display.drawRect(0, 0, SCREEN_WIDTH, SCREEN_HEIGHT, SSD1306_WHITE);
  
  // --- Top Section: Water Level Percentage (Large, Centered) ---
  display.setTextSize(3);
  display.setTextColor(SSD1306_WHITE);
  String levelStr = String(waterLevelPer) + "%";
  int16_t x1, y1;
  uint16_t w, h;
  display.getTextBounds(levelStr, 0, 0, &x1, &y1, &w, &h);
  int centerX = (SCREEN_WIDTH - w) / 2;
  display.setCursor(centerX, 5);
  display.print(levelStr);
  
  // --- Middle Section: Temperature and Water Purity (Two Lines) ---
  display.setTextSize(1);
  display.setTextColor(SSD1306_WHITE);
  display.setCursor(5, 30);
  display.print("Temp:");
  display.print(waterTemp, 1);
  display.print("C");
  
  display.setCursor(5, 40);
  display.print("Water:");
  display.print((waterTDS < TDS_THRESHOLD) ? "PURE" : "NOT PURE");
  
  // --- Bottom Section: Operating Mode & Pump Status ---
  display.setCursor(5, 55);
  display.print("Mode:");
  display.print(modeFlag ? "AUTO" : "MANUAL");
  
  display.setCursor(70, 55);
  display.print("Pump:");
  display.print(toggleRelay ? "ON" : "OFF");
  
  display.display();
}

// -------------------------- Mode Button Handler --------------------------
void modeButtonHandler(AceButton* button, uint8_t eventType, uint8_t buttonState) {
  // For the Mode Button (D7): a short press toggles AUTO/MANUAL mode.
  if (eventType == AceButton::kEventReleased) {
    modeFlag = !modeFlag;
    currMode = modeFlag ? "AUTO" : "MANUAL";
    Blynk.virtualWrite(VPIN_MODE, modeFlag ? 1 : 0);
    Serial.print("Mode changed to: ");
    Serial.println(currMode);
    beep(100);  // Brief beep to indicate mode change.
  }
}

// -------------------------- Setup --------------------------
void setup() {
  Serial.begin(115200);
  
  // Set pin modes:
  pinMode(TRIG_PIN, OUTPUT);
  pinMode(ECHO_PIN, INPUT);
  pinMode(RELAY_PIN, OUTPUT);
  pinMode(MODE_BUTTON_PIN, INPUT_PULLUP);
  pinMode(BUZZER_PIN, OUTPUT);
  
  // Ensure pump and buzzer are off initially:
  digitalWrite(RELAY_PIN, LOW);
  digitalWrite(BUZZER_PIN, LOW);
  
  // Initialize OLED display:
  if (!display.begin(SSD1306_SWITCHCAPVCC, 0x3C)) {
    Serial.println(F("OLED allocation failed"));
    for (;;);
  }
  display.clearDisplay();
  display.display();
  
  // Initialize DS18B20 sensor:
  DS18B20.begin();
  
  // Initialize WiFi and Blynk:
  WiFi.begin(ssid, pass);
  Blynk.config(auth);
  
  // Set up periodic sensor measurement (every 250 ms for faster updates):
  timer.setInterval(250L, measureSensors);
  
  // Configure AceButton event handler and initialize the Mode Button:
  modeBtnConfig.setEventHandler(modeButtonHandler);
  modeButton.init(MODE_BUTTON_PIN);
  
  delay(1000);
  
  // Send initial Blynk statuses:
  Blynk.virtualWrite(VPIN_MODE, modeFlag ? 1 : 0);
  Blynk.virtualWrite(VPIN_RELAY, toggleRelay ? 1 : 0);
}

// -------------------------- Main Loop --------------------------
void loop() {
  Blynk.run();
  timer.run();
  modeButton.check();
}
