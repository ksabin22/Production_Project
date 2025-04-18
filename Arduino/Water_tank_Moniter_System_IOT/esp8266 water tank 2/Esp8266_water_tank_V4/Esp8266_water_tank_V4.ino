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
