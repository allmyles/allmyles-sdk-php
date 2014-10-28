<?php

require '../../src/Allmyles/Client.php';

$allmyles = new Allmyles\Client('http://localhost:8084/v2.0', 'allmyles-test');

try {
    include 'simple/car_search.php';
    include 'simple/car_details.php';
    include 'simple/car_payment.php';
    include 'simple/car_book.php';
} catch (Exception $e) {
    echo 'HTTP Error ' . $e->getCode() . ":\r\n\r\n" . $e->getMessage();
}
