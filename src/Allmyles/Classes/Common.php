<?php
namespace Allmyles\Common;

use Allmyles\Flights\FlightResult;
use Allmyles\Hotels\Hotel;
use Allmyles\Hotels\Room;

class Price
{
    public $currency;
    public $amount;

    public function __construct($price)
    {
        $this->currency = array_key_exists('currency', $price) ? $price['currency'] : null;
        $this->amount = $price['amount'];
    }
}

class PriceRange
{
    public $minimum;
    public $maximum;
    public $currency;

    public function __construct($minimum, $maximum, $currency)
    {
        $this->minimum = $minimum;
        $this->maximum = $maximum;
        $this->currency = $currency;
    }
}

class Location
{
    public $latitude;
    public $longitude;

    public function __construct($latitude, $longitude)
    {
        $this->latitude = $latitude;
        $this->longitude = $longitude;
    }
}

class PostProcessor
{
    private $methodName;
    private $context;

    public function __construct($methodName, &$context)
    {
        $this->methodName = $methodName;
        $this->context = $context;
    }

    public function process($data)
    {
        return $this->{$this->methodName}($data, $this->context);
    }

    private function searchFlight($data, $context)
    {
        $flights = $data['flightResultSet'];

        $result = Array();

        foreach ($flights as $flight) {
            $instance = new FlightResult($flight, $context);
            array_push($result, $instance);
        };

        return $result;
    }

    private function getFlightDetails($data, $context)
    {
        $results = $data['flightDetails'];
        $results['surcharge'] = new Price($results['surcharge']);
        $results['price'] = new Price($results['price']);
        unset($results['result']);
        return $results;
    }

    private function bookFlight($data, $context)
    {
        // We are expecting no content when flight is LCC
        if ($data == null) {
            return true;
        } else {
            return $data;
        }
    }

    private function addPayuPayment($data, $context)
    {
        // We are expecting no content
        if ($data == null) {
            return true;
        } else {
            return null;
        }
    }

    private function createFlightTicket($data, $context)
    {
        if (array_key_exists('tickets', $data)) {
            $results = $data['tickets'];
        } else {
            unset($data['flightData']);
            $results = $data;
        };

        return $results;
    }

    private function searchLocations($data, $context)
    {
        $results = $data['locationSearchResult'];
        return $results;
    }

    private function getMasterdata($data, $context)
    {
        return $data;
    }

    private function searchHotel($data, $context)
    {
        $hotels = $data['hotelResultSet'];

        $result = Array();

        foreach ($hotels as $hotel) {
            $instance = new Hotel($hotel, $context);
            array_push($result, $instance);
        };

        return $result;
    }

    private function getHotelDetails($data, $hotel)
    {
        $results = $data['hotel_details'];

        $rooms = Array();

        foreach ($results['rooms'] as $room) {
            $instance = new Room($room, $hotel);
            array_push($rooms, $instance);
        };

        $results['rooms'] = $rooms;
        return $results;
    }

    private function getHotelRoomDetails($data, $context)
    {
        $results = $data['hotel_room_details'];
        return $results;
    }

    private function bookHotel($data, $context)
    {
        return $data['success'];
    }

    private function getMasterdata($data, $context)
    {
            return $data;
    }

}
