<?php

$room = $hotelDetails['rooms'][0];
$roomDetails = $room->getDetails()->get();
var_dump($roomDetails);
