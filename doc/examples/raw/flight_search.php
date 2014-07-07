<?php

$search_data = Array(
    'fromLocation' => 'BUD',
    'toLocation' => 'LON',
    'departureDate' => '2015-01-01T00:00:00Z',
    'persons' => [Array('passengerType' => 'ADT', 'quantity' => 1)]
);

$flights = $allmyles->searchFlight($search_data, false);
var_dump($flights->get());
