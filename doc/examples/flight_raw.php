<?php

require 'Client.php';

$allmyles = new Allmyles\Client('http://localhost:8084/v2.0', 'allmyles-test');

include 'raw/flight_search.php';
include 'raw/flight_details.php';
include 'raw/flight_book.php';
include 'raw/flight_payment.php';
include 'raw/flight_ticketing.php';
