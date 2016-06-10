<?php

/*  hotspotMq2Db.php
 *  Subscribes to TTN Thingithon application Mosquitto messages
 *  Stores elements of the messages into Mongo DB database
 *  Database - thingithondb
 *  Collection - sensedata
 *  
 *  (c) Mark Stanley 2016
 *
 *  This code is from the Reading Hotspot project, part of Reading's Year of Culture.
 *  It was funded by Reading Council and Coraledge Ltd
 * 
 *  You are licensed to use, modify and improve this code but you must keep these 
 *  comments at the top.
 */

// Mosquitto details
$appEUI = "70B3D57ED0000327";
$client = new Mosquitto\Client();
$client->onConnect('connect');
$client->onDisconnect('disconnect');
$client->onSubscribe('subscribe');
$client->onMessage('message');
$client->setCredentials($appEUI, 'mtfHdaqXFSLv0Vu2QuW38vy3XxYfuXwTaWcOWSpcaYA=');
$client->connect("staging.thethingsnetwork.org", 1883, 60);
$client->subscribe("$appEUI/devices/+/up", 1);

while (true) {
        $client->loop();
        sleep(2);
}
 
$client->disconnect();
unset($client);
 
function connect($r) {
        echo "I got code {$r}\n";
}
 
function subscribe() {
        echo "Subscribed to a topic\n";
}
 
// must return "custom";

function message($message) {
	// Mongodb Configuration
	$dbhost = 'localhost';
	$dbname = 'thingithondb';
	
	// Connect to test database
	$m = new Mongo("mongodb://$dbhost");
	$db = $m->$dbname;
	$c_senseData = $db->senseData;
        printf("\nGot a message on topic %s with payload:%s", 
          $message->topic, $message->payload);
	$readableJson=json_decode($message->payload, true);
        foreach ($readableJson as $k => $v) {
          echo $k, " : ", $v, "\n";
	  switch ($k) {
            case "dev_eui":
              $node =$v;
              break;
            case "payload":
              $msgData=base64_decode($v);
              echo "\nmsgData is ", $msgData, "\n";
              $msgDataJson=json_decode($msgData, true);
              break;
            case "metadata":
              print_r($v[0]);
              $msgTime=$v[0]['gateway_time'];
              echo "\nTime is $msgTime \n";
              break;
	  }
	}
	if($msgDataJson!="") {
	  $senseRec = array(
	    'node' => $node,
	    'time' => $msgTime,
	    'msgData' => $msgDataJson
	  );

	  $c_senseData->save($senseRec);
	}
	$m->close();
}
 
function disconnect() {
        echo "Disconnected cleanly\n";
}

