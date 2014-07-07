<?php

require 'Client.php';

$allmyles = new Allmyles\Client('http://localhost:8084/v2.0', 'allmyles-test');

$search_data = Array(
    'fromLocation' => 'BUD',
    'toLocation' => 'LON',
    'departureDate' => '2015-01-01T00:00:00Z',
    'persons' => [Array('passengerType' => 'ADT', 'quantity' => 1)]
);

$flights = $allmyles->searchFlight($search_data, false);
var_dump($flights->get());
