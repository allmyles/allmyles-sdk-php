<?php

require '../../src/Allmyles/Client.php';

$allmyles = new Allmyles\Client('http://localhost:8084/v2.0', 'allmyles-test');

try {
    include 'complex/flight_search.php';
    include 'complex/flight_details.php';
    include 'complex/flight_book.php';
    include 'complex/flight_payment.php';
    include 'complex/flight_ticketing.php';
} catch (Exception $e) {
    echo 'HTTP Error ' . $e->getCode() . ":\r\n\r\n" . $e->getMessage();
}
