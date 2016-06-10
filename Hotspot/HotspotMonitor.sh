#!/bin/sh

# * Hotspot Monitor - count the mobile phones in range        */
# * (c) Mark Stanley 2016                                     */
# *                                                           */
# * Created for the Reading Hotspot project, 2016             */
# *                                                           */
# * Funded through Thingitude.com by Reading Council and      */
# * Coraledge Ltd, for The Things Network Reading.            */
# *                                                           */
# * You are welcome to use or modify as you like, but please  */
# * keep these comments at the top of the code so credit and  */
# * copyright are preserved.                                  */

#  Reset the wifi to a known state
airmon-ng stop mon0 
ifconfig wlan0 down
iwconfig wlan0 mode managed
ifconfig wlan0 up

#  Switch the wifi to monitor mode
ifconfig wlan0 down
iwconfig wlan0 mode Monitor
ifconfig wlan0 up

#  Get mon0 up and running
airmon-ng start wlan0 6

#  Set up the parameters for the wifi monitoring
monFile="/home/pi/Hotspot/monFile"
macFile="/home/pi/Hotspot/macFile"
duration=100

while [ 1 -gt 0 ]  
do
	tshark -a duration:$duration -f wlan[0]=0x40 -i mon0 -T fields -E separator=,  -e wlan.sa 1> $monFile

	sleep 30s

	#  Now filter out the unique MACs
	sort -u $monFile > $macFile
	wificount=`cat $macFile | wc -l`
	timeNow=`date +%s`

	#  Send the unique MACs to the Thingitude server via mq
	/home/pi/Hotspot/thingithonmq $macFile $wificount $timeNow

	#  Now display the number with the other data on the LCD display
	/home/pi/Hotspot/hotspotmq $wificount
done
