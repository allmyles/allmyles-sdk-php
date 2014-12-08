<?php

require '../../src/Allmyles/Client.php';
use Allmyles\Client;
use Allmyles\Exceptions\ServiceException;
use Allmyles\Flights\SearchQuery;
use Allmyles\Flights\BookQuery;

$allmyles = new Client('http://localhost:8084/v2.0', 'allmyles-test');

try {
  // search
  $search_query = new SearchQuery('BUD', 'LON', '2015-06-06T00:00:00Z');
  $search_query->addPassengers(1);
  $flights = $allmyles->searchFlight($search_query, false)->get();

  // details
  $flight = reset($flights[0]->combinations);
  $flight->getDetails()->get();

  // book
  $passenger = Array(
    'namePrefix' => 'Mr',
    'firstName' => 'Janos',
    'lastName' => 'Kovacs',
    'birthDate' => '1974-01-01',
    'passengerTypeCode' => 'ADT',
    'email' => 'test@gmail.com',
    'document' => Array('dateOfExpiry' => '2020-01-01', 'id' => 'AB1234', 'issueCountry' => 'HU', 'type' => 'Passport'),
  );
  $address = Array(
    'address' => Array('addressLine1' => '1 1st St', 'cityName' => 'York', 'countryCode' => 'HU', 'zipCode' => '1234'),
    'email' => 'test@gmail.com',
    'name' => 'Kovacs Gyula',
    'phone' => Array('countryCode' => '36', 'areaCode' => '30', 'phoneNumber' => '1234567')
  );

  $book_query = new BookQuery($passenger, $address);
  $flight->book($book_query)->get();

  // payment
  $flight->addPayuPayment('1234')->get();

  // ticketing
  var_dump($flight->createTicket()->get());
} catch (ServiceException $e) {
  echo 'HTTP Error ' . $e->getCode() . ":\r\n\r\n" . $e->getMessage();
}
