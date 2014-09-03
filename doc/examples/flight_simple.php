<?php

require '../../src/Allmyles/Client.php';

$allmyles = new Allmyles\Client('http://localhost:8084/v2.0', 'allmyles-test');

try {
    include 'simple/flight_search.php';
    include 'simple/flight_details.php';
    include 'simple/flight_book.php';
    include 'simple/flight_payment.php';
    include 'simple/flight_ticketing.php';
} catch (Exception $e) {
    echo 'HTTP Error ' . $e->getCode() . ":\r\n\r\n" . $e->getMessage();
}
