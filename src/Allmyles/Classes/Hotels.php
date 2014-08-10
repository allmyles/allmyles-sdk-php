<?php
namespace Allmyles\Hotels;

use Allmyles\Common\Price;

class SearchQuery
{
    private $location;
    private $arrivalDate;
    private $leaveDate;
    private $occupants;

    public function __construct($location, $arrivalDate, $leaveDate, $occupants = 1) {
        $this->location = $location;
        $this->arrivalDate = $this->getTimestamp($arrivalDate);
        $this->leaveDate = $this->getTimestamp($leaveDate);
        $this->occupants = $occupants;
    }

    public function getData()
    {
        $data = Array(
            'cityCode' => $this->location,
            'arrivalDate' => $this->arrivalDate,
            'leaveDate' => $this->leaveDate,
            'occupancy' => $this->occupants
        );

        return $data;
    }

    private function getTimestamp($datetime)
    {
        if (is_string($datetime) || $datetime == null) {
            return $datetime;
        } else {
            return $datetime->format('Y-m-d');
        };
    }
}

class Hotel
{
    public $hotelId;
    public $hotelName;
    public $chainName;
    public $thumbnail;
    public $stars;
    public $priceRange;
    public $location;
    public $amenities;

    public function __construct($result, $context)
    {
        $this->context = &$context;

        $this->hotelId = $result['hotel_id'];
        $this->hotelName = $result['hotel_name'];
        $this->chainName = $result['chain_name'];
        $this->thumbnailUrl = $result['thumbnail'];
        $this->stars = $result['stars'];
        $this->priceRange = new \Allmyles\Common\PriceRange(
            $result['min_rate']['amount'],
            $result['max_rate']['amount'],
            $result['min_rate']['currency']
        );
        $this->location = new \Allmyles\Common\Location(
            $result['latitude'], $result['longitude']
        );
        $this->amenities = $result['amenities'];
    }

    public function getDetails()
    {
        $hotelDetails = $this->context->client->getHotelDetails(
            $this->hotelId, $this->context->session
        );
        return $hotelDetails;
    }
}

class Room
{
    public $flightResult;
    public $context;
    public $bookingId;
    public $providerType;
    public $legs;
    public $serviceFee;

    public function __construct($room, $flightResult)
    {
        $this->flightResult = $flightResult;
        $this->context = &$this->flightResult->context;

        $this->roomId = $room['booking_id'];
        $this->price = new Price($room['price']);
        $this->priceVaries = $room['price']['rate_varies'];
        $this->priceScope = $room['price']['covers'];
        $this->traits = $room['room_type'];
        $this->bed = $room['bed_type'];
        $this->description = $room['description'];
        $this->quantity = $room['quantity'];
    }

    public function getDetails($parameters)
    {
        return null;
    }

    public function book($parameters)
    {
        if (is_array($parameters)) {
            $parameters['bookingId'] = $this->bookingId;
        } else {
            $parameters->setBookingId($this->bookingId);
        };
        $bookResponse = $this->context->client->bookHotel(
            $parameters, $this->context->session
        );
        return $bookResponse;
    }
}


class BookQuery
{
    private $bookingId;
    private $passengers;
    private $billingInfo;
    private $contactInfo;

    public function __construct($passengers = null, $contactInfo = null, $billingInfo = null)
    {
        if ($passengers != null) {
            $this->addPassengers($passengers);
        };
        if ($contactInfo != null) {
            $this->addContactInfo($contactInfo);
        };
        if ($billingInfo != null) {
            $this->addBillingInfo($billingInfo);
        };
    }

    public function setBookingId($bookingId)
    {
        $this->bookingId = $bookingId;
    }

    public function addPassengers($passengers)
    {
        if ($this->passengers == null) {
          $this->passengers = array();
        };

        foreach (array_values($passengers) as $value) {
            // Check if all items in $passengers are arrays. If not, then we
            // got a single passenger only, and need to wrap it in an array.
            if (!is_array($value)) {
                $passengers = Array($passengers);
                break;
            }
        }

        foreach ($passengers as $passenger) {
            $passenger['baggage'] = 0;
            switch (strtolower($passenger['namePrefix'])) {
                case 'mr':
                    $passenger['gender'] = 'MALE';
                    break;
                case 'ms':
                    $passenger['gender'] = 'FEMALE';
                    break;
                case 'mrs':
                    $passenger['gender'] = 'FEMALE';
                    break;
            }

            array_push($this->passengers, $passenger);
        };
    }

    public function addContactInfo($address)
    {
        $this->contactInfo = $address;
    }

    public function addBillingInfo($address)
    {
        $this->billingInfo = $address;
    }

    public function getData()
    {
        $data = Array();
        $data['passengers'] = $this->passengers;
        $data['billingInfo'] = $this->billingInfo;
        $data['contactInfo'] = $this->contactInfo;
        $data['bookingId'] = $this->bookingId;
        return $data;
    }
}
