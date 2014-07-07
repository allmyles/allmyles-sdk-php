<?php

require 'Client.php';

$allmyles = new Allmyles\Client('http://localhost:8084/v2.0', 'allmyles-test');

$timezone = new DateTimeZone('UTC')
$search_query = new Allmyles\Flights\SearchQuery(
    'BUD', 'LON', new DateTime('tomorrow', $timezone), new DateTime('tomorrow + 7 days', $timezone)
);
$search_query->addPassengers(2, 1, 1); // required
$search_query->addProviderFilter(FLIGHT_PROVIDER_TRADITIONAL); // optional
$search_query->addAirlineFilter('BA'); // optional
$search_query->addAirlineFilter(['W6', 'FR']); // optional
$flights = $allmyles->searchFlight($search_query);

while ($flights->incomplete) {$flights = $flights->retry(5);};

var_dump($flights->get());
