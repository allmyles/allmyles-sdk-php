<?php

$passenger1 = Array(
    'namePrefix' => 'Mr',
    'firstName' => 'Janos',
    'lastName' => 'Kovacs',
    'birthDate' => '1974-04-03',
    'passengerTypeCode' => 'ADT',
    'email' => 'aaa@gmail.com',
    'document' => Array(
        'dateOfExpiry' => '2016-09-03',
        'id' => '12345678',
        'issueCountry' => 'HU',
        'type' => 'Passport'
    )
);

$passenger2 = Array(
    'namePrefix' => 'Mr',
    'firstName' => 'Laszlo',
    'lastName' => 'Kovacs',
    'birthDate' => '1974-04-03',
    'passengerTypeCode' => 'ADT',
    'email' => 'aaa@gmail.com',
    'document' => Array(
        'dateOfExpiry' => '2016-09-03',
        'id' => '12345678',
        'issueCountry' => 'HU',
        'type' => 'Passport'
    )
);

$passenger3 = Array(
    'namePrefix' => 'Mr',
    'firstName' => 'Lajos',
    'lastName' => 'Kovacs',
    'birthDate' => '2004-04-03',
    'passengerTypeCode' => 'CHD',
    'email' => 'aaa@gmail.com',
    'document' => Array(
        'dateOfExpiry' => '2016-09-03',
        'id' => '12345678',
        'issueCountry' => 'HU',
        'type' => 'Passport'
    )
);

$passenger4 = Array(
    'namePrefix' => 'Ms',
    'firstName' => 'Zsofi',
    'lastName' => 'Kovacs',
    'birthDate' => '2013-04-03',
    'passengerTypeCode' => 'INF',
    'email' => 'aaa@gmail.com',
    'document' => Array(
        'dateOfExpiry' => '2016-09-03',
        'id' => '12345678',
        'issueCountry' => 'HU',
        'type' => 'Passport'
    )
);

$address = Array(
    'address' => Array(
        'addressLine1' => 'Váci út 13-14',
        'cityName' => 'Budapest',
        'countryCode' => 'HU',
        'zipCode' => '1234'
    ),
    'email' => 'ccc@gmail.com',
    'name' => 'Kovacs Gyula',
    'phone' => Array(
        'areaCode' => '30',
        'countryCode' => '36',
        'phoneNumber' => '1234567'
    )
);

$book_query = new Allmyles\Flights\BookQuery();
$book_query->addPassengers([$passenger1, $passenger2]);
$book_query->addPassengers($passenger3);
$book_query->addPassengers($passenger4);
$book_query->addContactInfo($address);
$book_query->addBillingInfo($address);
var_dump($flight->book($book_query)->get());
