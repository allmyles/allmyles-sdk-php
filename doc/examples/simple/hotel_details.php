<?php

$hotel = $hotels[0];
$hotelDetails = $hotel->getDetails()->get();
var_dump($hotelDetails);
