<?php

function convertMinutesToStr($total_minutes) {
    $minutesInAnHour = 60;
    $minutesInADay = 24 * $minutesInAnHour;

    $days = floor($total_minutes / $minutesInADay);
    $hours = floor(($total_minutes % $minutesInADay) / $minutesInAnHour);
    $minutes = $total_minutes % $minutesInAnHour;

    $timeParts = [];
    $sections = [
        'day' => (int)$days,
        'hour' => (int)$hours,
        'minute' => (int)$minutes
    ];

    foreach ($sections as $name => $value) {
        if ($value > 0) {
            $timeParts[] = $value . ' ' . $name . ($value == 1 ? '' : 's');
        }
    }

    return sizeof($timeParts) ? implode(', ', $timeParts) : '0 minutes';
}
