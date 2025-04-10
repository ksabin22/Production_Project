#include <Wire.h>
#include <Adafruit_GFX.h>
#include <Adafruit_SSD1306.h>
#include <DHT.h>
#include <NewPing.h>
#include <OneWire.h>
#include <DallasTemperature.h>

// OLED display configuration
#define SCREEN_WIDTH 128
#define SCREEN_HEIGHT 64
#define OLED_RESET -1
Adafruit_SSD1306 display(SCREEN_WIDTH, SCREEN_HEIGHT, &Wire, OLED_RESET);

// DHT sensor configuration
#define DHTPIN 13
#define DHTTYPE DHT11
DHT dht(DHTPIN, DHTTYPE);

// DS18B20 sensor configuration
#define DS18B20_PIN 0
OneWire oneWire(DS18B20_PIN);
DallasTemperature DS18B20(&oneWire);

// Ultrasonic sensor configuration
#define TRIG_PIN 14
#define ECHO_PIN 12
#define MAX_DISTANCE 200
NewPing sonar(TRIG_PIN, ECHO_PIN, MAX_DISTANCE);

// Relay and button configuration
#define RELAY_PIN 16
#define BUTTON_PIN 2

void setup() {
  pinMode(RELAY_PIN, OUTPUT);
  digitalWrite(RELAY_PIN, LOW); // Ensure relay is off at start-up

  pinMode(BUTTON_PIN, INPUT_PULLUP); // Set the button pin as input with internal pull-up

  Serial.begin(115200);
  dht.begin();
  DS18B20.begin();
  display.begin(SSD1306_SWITCHCAPVCC, 0x3C);

  // --- Welcome Screen ---
  display.clearDisplay();
  display.setTextSize(2);
  display.setTextColor(WHITE);
  display.setCursor(30, 30);
  display.println("Smart Water Tank System");

  display.setTextSize(1);
  display.setCursor(40, 40);
  display.println("by SABIN");
  display.display();
  delay(3000); // Show welcome screen for 3 seconds

  display.clearDisplay();
}

void loop() {
  display.clearDisplay();
  display.setTextSize(1);
  display.setTextColor(WHITE);

  // Read sensors
  float tdsValue = analogRead(A0) * (3.3 / 1023.0) * 500.0;
  float humidity = dht.readHumidity();
  float temperature = dht.readTemperature();
  DS18B20.requestTemperatures();
  float dsTemp = DS18B20.getTempCByIndex(0);
  unsigned int distance = sonar.ping_cm();

  // Button state checking
  bool buttonState = digitalRead(BUTTON_PIN); // Read the button state
  if (buttonState == LOW) { // Check if button is pressed (assuming LOW when pressed)
    digitalWrite(RELAY_PIN, !digitalRead(RELAY_PIN)); // Toggle relay state
    delay(200); // Debounce delay
  }

  // Display readings
  display.setCursor(0, 0);
  display.print("TDS: ");
  display.print(tdsValue, 1);
  display.println(" ppm");

  display.setCursor(0, 10);
  display.printf("Hum: %.1f%%  Temp: %.1fC", humidity, temperature);

  display.setCursor(0, 20);
  display.print("DS Temp: ");
  display.print(dsTemp);
  display.println(" C");

  display.setCursor(0, 30);
  display.print("Dist: ");
  display.print(distance);
  display.println(" cm");

  display.display();
  delay(2000);
}
