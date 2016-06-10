/*  ThingithonHotspot.ino
 *  Takes a message from the Pi attached to the serial (USB) port 
 *  Sends it to The Things Network over LoraWAN
 *  
 *  (c) Mark Stanley 2016
 *
 *  This code is from the Reading Hotspot project, part of Reading's Year of Culture.
 *  It was funded by Reading Council and Coraledge Ltd
 * 
 *  You are licensed to use, modify and improve this code but you must keep these 
 *  comments at the top.
 */

//#include "SoftwareSerial.h"
#include "TheThingsUno.h"
#define RN_RX 3
#define RN_TX 4
#define RN_RESET 5

// Set your AppEUI and AppKey
const byte appEui[8] = { 0x70, 0xB3, 0xD5, 0x7E, 0xD0, 0x00, 0x03, 0x27 }; //for example: {0x70, 0xB3, 0xD5, 0x7E, 0xE0, 0xE0, 0x01, 0x4A1};
const byte appKey[16] = { 0xCA, 0x30, 0xBD, 0x8A, 0xBE, 0xA3, 0x1C, 0x57, 0xFC, 0x81, 0x80, 0x31, 0x34, 0x64, 0xB1, 0x25 };

#define hotspotSerial Serial
//SoftwareSerial loraSerial(RN_RX, RN_TX); // RX, TX
#define loraSerial Serial1

#define debugPrintLn(...) { if (hotspotSerial) hotspotSerial.println(__VA_ARGS__); }
#define debugPrint(...) { if (hotspotSerial) hotspotSerial.print(__VA_ARGS__); }

TheThingsUno ttu;

void setup()
{
  hotspotSerial.begin(9600);
  loraSerial.begin(57600);

  delay(1000);
  ttu.init(loraSerial, hotspotSerial);
  ttu.reset();
  ttu.join(appEui, appKey);

  delay(6000);
  ttu.showStatus();
  debugPrintLn("Setup for The Things Network complete");

  delay(1000);
}

void loop() {
  String message;
  int msgLength=0;
  // Hang around waiting for a message from the hotspot
  if(hotspotSerial.available()>0) {
    message=hotspotSerial.readStringUntil('|');
    msgLength=sizeof(message);
    debugPrintLn("Message is " + message);
   
  }

  delay(6000);

  // Send the message if one has been received
  if(msgLength>0) {
    ttu.sendString(message);
  }
  
  delay(20000);
    
}
