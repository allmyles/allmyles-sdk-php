<?php

require '../../src/Allmyles/Client.php';
use Allmyles\Client;
use Allmyles\Exceptions\ServiceException;
use Allmyles\Cars\SearchQuery;
use Allmyles\Cars\BookQuery;

$allmyles = new Client('http://localhost:8084/v2.0', 'allmyles-test');

try {
  // search
  $search_query = new SearchQuery('LHR', '2015-03-01', '2015-03-04');
  $cars = $allmyles->searchCar($search_query)->get();

  // details
  $car = $cars[0];
  $carDetails = $car->getDetails()->get();

  // payment
  var_dump($car->addPayuPayment('1234')->get());

  // book
  $passenger = Array(
    'namePrefix' => 'Mr',
    'firstName' => 'Janos',
    'lastName' => 'Kovacs',
    'birthDate' => '1974-01-01',
    'passengerTypeCode' => 'ADT',
    'email' => 'test@gmail.com',
  );
  $address = Array(
    'address' => Array('addressLine1' => '1 1st St', 'cityName' => 'York', 'countryCode' => 'HU', 'zipCode' => '1234'),
    'email' => 'test@gmail.com',
    'name' => 'Kovacs Gyula',
    'phone' => Array('countryCode' => '36', 'areaCode' => '30', 'phoneNumber' => '1234567')
  );

  $book_query = new BookQuery($passenger, $address);
  var_dump($car->book($book_query)->get());
} catch (ServiceException $e) {
  echo 'HTTP Error ' . $e->getCode() . ":\r\n\r\n" . $e->getMessage();
}
