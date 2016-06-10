/*  ttnub.c
 *  Takes a message from hotspotmq.c intended for The Things Network as argv[1]
 *  Sends it to the TTN UNO attached to the Pi via USB
 *  
 *  (c) Mark Stanley 2016
 *
 *  This code is from the Reading Hotspot project, part of Reading's Year of Culture.
 *  It was funded by Reading Council and Coraledge Ltd
 * 
 *  You are licensed to use, modify and improve this code but you must keep these 
 *  comments at the top.
 */

#include <wiringSerial.h>
#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <stdint.h>

int main (int argc, char **argv)
{
	char *ttnMsg;
	int usbPort=0;    //File descriptor for the USB port when we open it

	// Set up wifiCount if the parameter was passed
	if (argc==2) {
		ttnMsg=argv[1];
	} else {
		printf("Usage hotspotuno ttnMsg\n");
		return 1;
	}
	
	usbPort=serialOpen("/dev/ttyACM0",9600);
	delay(1000);
	printf("Port opened, number %d \n",usbPort);
	if(usbPort) {
		delay(1000);
		printf("Pause...\n");
		serialPuts(usbPort, ttnMsg);
		printf("Message posted \n");
		delay(1000);
		serialClose(usbPort);
		printf("Port closed \n");
	}

	system(ttnMsg);
	
	return 0 ;
}
