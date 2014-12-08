<?php

require '../../src/Allmyles/Client.php';
use Allmyles\Client;
use Allmyles\Exceptions\ServiceException;
use Allmyles\Hotels\SearchQuery;
use Allmyles\Hotels\BookQuery;

$allmyles = new Client('http://localhost:8084/v2.0', 'allmyles-test');

try {
  // search
  $search_query = new SearchQuery('WAW', '2015-06-01', '2015-06-02', 1);
  $hotels = $allmyles->searchHotel($search_query)->get();

  // details
  $hotel = $hotels[0];
  $hotelDetails = $hotel->getDetails()->get();

  // room details
  $room = $hotelDetails['rooms'][0];
  $room->getDetails();

  // payment
  $room->addPayuPayment('1234')->get();

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
  var_dump($room->book($book_query)->get());
} catch (ServiceException $e) {
  echo 'HTTP Error ' . $e->getCode() . ":\r\n\r\n" . $e->getMessage();
}
