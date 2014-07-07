<?php

$book_data = Array(
    "billingInfo" => Array(
        "address" => Array(
            "addressLine1" => "Váci út 13-14",
            "cityName" => "Budapest",
            "countryCode" => "HU",
            "zipCode" => "1234"
        ),
        "email" => "ccc@gmail.com",
        "name" => "Kovacs Gyula",
        "phone" => Array(
            "areaCode" => 30,
            "countryCode" => 36,
            "phoneNumber" => 1234567
        )
    ),
    "contactInfo" => Array(
        "address" => Array(
            "addressLine1" => "Váci út 13-14",
            "cityName" => "Budapest",
            "countryCode" => "HU",
            "zipCode" => "1234"
        ),
        "email" => "ccc@gmail.com",
        "name" => "Kovacs Gyula",
        "phone" => Array(
            "areaCode" => 30,
            "countryCode" => 36,
            "phoneNumber" => 1234567
        )
    ),
    "passengers" => [
        Array(
            "baggage" => 0,
            "birthDate" => "1974-04-03",
            "document" => Array(
                "dateOfExpiry" => "2016-09-03",
                "id" => "12345678",
                "issueCountry" => "HU",
                "type" => "Passport"
            ),
            "email" => "aaa@gmail.com",
            "firstName" => "Janos",
            "gender" => "MALE",
            "lastName" => substr(str_shuffle("AABCDERFGHIIJKLMNOOPQRSTUUVWXYZ"), 0, 6),
            "namePrefix" => "Mr",
            "passengerTypeCode" => "ADT"
        )
    ]
);

var_dump($flight->book($book_data)->get());
