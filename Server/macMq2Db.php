<?php

/*  macMq2Db.php
 *  Subscribes to the MAC Mosquitto messages sent by the Hotspot over Mosquitto
 *  Stores elements of the messages into Mongo DB database
 *  Database - thingithondb
 *  Collection - macData
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
$appEUI = "70B3D57ED00002F3";
$client = new Mosquitto\Client();
$client->onConnect('connect');
$client->onDisconnect('disconnect');
$client->onSubscribe('subscribe');
$client->onMessage('message');
$client->connect("5.44.237.19", 1883, 60);
$client->subscribe("thingithon/mac", 1);

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
	$c_macData = $db->macData;
        printf("\nGot a message on topic %s with payload:%s", 
          $message->topic, $message->payload);
	$readableJson=json_decode($message->payload, true);
        foreach ($readableJson as $k => $v) {
          echo $k, " : ", $v, "\n";
	  switch ($k) {
	    case "mac":
	      $mac =$v;
	      break;
	    case "time":
	      $timestamp =$v;
	      break;
	  }
	}
	$macRec = array( 
          'mac' => $mac,
          'timestamp' => $timestamp
	  );

	$c_macData->save($macRec);
	$m->close();
}
 
function disconnect() {
        echo "Disconnected cleanly\n";
}

