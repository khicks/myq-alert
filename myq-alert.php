#!/usr/bin/php
<?php

$pwd = __DIR__;
$config = require_once("$pwd/config.php");
require_once("$pwd/functions.php");
require_once("$pwd/vendor/autoload.php");

$file_security_token = "$pwd/data/security-token.txt";
$file_trip = "$pwd/data/trip.txt";

$myq_security_token = null;
if (file_exists($file_security_token)) {
    $myq_security_token = trim(file_get_contents($file_security_token));
}

$pushover = new Phushover\Client($config['pushover_user'], $config['pushover_app']);
$myq = new MyQ\MyQ($config['myq_username'], $config['myq_password'], $myq_security_token);
$door = $myq->getGarageDoorDevices()[0];

if ($door->getState()->getDescription() === "open") {
    // read what interval was last tripped.
    $last_tripped_interval = -1;
    if (file_exists($file_trip)) {
        $last_tripped_interval = (int)trim(file_get_contents($file_trip));
    }

    // get time garage door has been open.
    $open_time = floor($door->getState()->getDeltaInt() / 60);

    // door must have been closed and re-opened. reset trip.
    if ($open_time < $last_tripped_interval) {
        $last_tripped_interval = -1;
    }

    // determine which interval is currently active.
    $tripped_interval = -1;
    foreach ($config['intervals'] as $config_interval) {
        if ($open_time >= $config_interval && $config_interval > $tripped_interval) {
            $tripped_interval = $config_interval;
        }
    }

    // alert on tripped alarm.
    if ($tripped_interval > $last_tripped_interval) {
        $tripped_interval_str = convertMinutesToStr($tripped_interval);
        $message = new Phushover\Message("Garage door has been open for $tripped_interval_str.");
        $pushover->send($message);
        // echo "TRIPPED: ".convertMinutesToStr($tripped_interval)."\n";
    }

    // record this trip interval for next run.
    file_put_contents($file_trip, $tripped_interval);
}
else {
    // door is no longer open. reset trip.
    file_put_contents($file_trip, -1);
}

file_put_contents("$pwd/data/security-token.txt", $myq->getSecurityToken()->getValue());
