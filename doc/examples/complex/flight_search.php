<?php

$timezone = new DateTimeZone('UTC');
$search_query = new Allmyles\Flights\SearchQuery(
    'BUD', 'LON', new DateTime('+30 days', $timezone), new DateTime('+40 days', $timezone)
);
$search_query->addPassengers(2, 1, 1); // required
$search_query->addProviderFilter(Allmyles\Flights\SearchQuery::PROVIDER_TRADITIONAL); // optional
$search_query->addAirlineFilter('BA'); // optional
$search_query->addAirlineFilter(Array('W6', 'FR')); // optional
$flights = $allmyles->searchFlight($search_query);

while ($flights->incomplete) {
    $flights = $flights->retry(5);
};

$flights = $flights->get();

var_dump($flights);
