<?php

$hotel = $hotels[0];
$details = $hotel->getDetails()->get();
$room = $details['rooms'][0];
var_dump($details);
