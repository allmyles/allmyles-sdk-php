<?php

$passenger = Array(
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

$book_query = new Allmyles\Hotels\BookQuery($passenger, $address, $address);
var_dump($car->book($book_query)->get());
