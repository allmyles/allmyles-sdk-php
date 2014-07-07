<?php

require 'Client.php';

$allmyles = new Allmyles\Client('http://localhost:8084/v2.0', 'allmyles-test');

$search_query = new Allmyles\Flights\SearchQuery('BUD', 'LON', '2014-12-24T00:00:00Z');
$search_query->addPassengers(1);
$flights = $allmyles->searchFlight($search_query, false);
var_dump($flights->get());
