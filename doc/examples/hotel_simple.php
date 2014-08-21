<?php

require '../../src/Allmyles/Client.php';

$allmyles = new Allmyles\Client('http://localhost:8084/v2.0', 'allmyles-test');

try {
    include 'simple/hotel_search.php';
    include 'simple/hotel_details.php';
    include 'simple/hotel_room_details.php';
    include 'simple/hotel_book.php';
} catch (Exception $e) {
    echo 'HTTP Error ' . $e->getCode() . ":\r\n\r\n" . $e->getMessage();
}
