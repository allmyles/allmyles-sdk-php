<?php

require 'Client.php';

$allmyles = new Allmyles\Client('http://localhost:8084/v2.0', 'allmyles-test');

include 'simple/flight_search.php';
include 'simple/flight_details.php';
include 'simple/flight_book.php';
include 'simple/flight_payment.php';
include 'simple/flight_ticketing.php';
