<?php

$search_query = new Allmyles\Flights\SearchQuery('BUD', 'LON', '2014-12-24T00:00:00Z');
$search_query->addPassengers(1);
$flights = $allmyles->searchFlight($search_query, false)->get();
var_dump($flights);
