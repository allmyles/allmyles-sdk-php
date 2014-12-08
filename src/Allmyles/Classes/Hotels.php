<?php
namespace Allmyles\Hotels;

use Allmyles\Common\Location;
use Allmyles\Common\Price;
use Allmyles\Common\PriceRange;

class SearchQuery
{
    private $location;
    private $arrivalDate;
    private $leaveDate;
    private $occupants;
    private $filters;

    public function __construct($location, $arrivalDate, $leaveDate, $occupants = 1) {
        $this->location = $location;
        $this->arrivalDate = $this->getTimestamp($arrivalDate);
        $this->leaveDate = $this->getTimestamp($leaveDate);
        $this->occupants = $occupants;
        $this->filters = new \ArrayObject();
    }

    public function getData()
    {
        $data = Array(
            'cityCode' => $this->location,
            'arrivalDate' => $this->arrivalDate,
            'leaveDate' => $this->leaveDate,
            'occupancy' => $this->occupants,
            'filters' => $this->filters
        );

        return $data;
    }

    public function addHotelCodeFilter($hotelCode)
    {
        $this->filters['hotel_code'] = $hotelCode;
    }

    public function addHotelNameFilter($hotelName)
    {
        $this->filters['hotel_name'] = $hotelName;
    }

    public function addChainCodeFilter($chainCode)
    {
        $this->filters['chain_code'] = $chainCode;
    }

    public function addChainNameFilter($chainName)
    {
        $this->filters['chain_name'] = $chainName;
    }

    public function addBrandCodeFilter($brandCode)
    {
        $this->filters['brand_code'] = $brandCode;
    }

    public function addBrandNameFilter($brandName)
    {
        $this->filters['brand_name'] = $brandName;
    }

    private function getTimestamp($datetime)
    {
        if (is_string($datetime) || $datetime == null) {
            return $datetime;
        } else {
            return $datetime->format('Y-m-d');
        }
    }
}

class Hotel
{
    public $hotelId;
    public $hotelName;
    public $chainName;
    public $thumbnailUrl;
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
        $this->priceRange = new PriceRange(
            $result['min_rate']['amount'],
            $result['max_rate']['amount'],
            $result['min_rate']['currency']
        );
        $this->location = new Location(
            $result['latitude'], $result['longitude']
        );
        $this->amenities = $result['amenities'];
    }

    public function getDetails()
    {
        return $this->context->client->getHotelDetails($this);
    }
}

class Room
{
    public $context;
    public $hotel;

    public $roomId;
    public $bookingId;
    public $price;
    public $priceVaries;
    public $priceScope;
    public $traits;
    public $bed;
    public $description;
    public $quantity;

    public function __construct($room, $hotel)
    {
        $this->context = &$hotel->context;
        $this->hotel = &$hotel;

        $this->roomId = $room['room_id'];
        $this->bookingId = $room['booking_id'];
        $this->price = new Price($room['price']);
        $this->priceVaries = $room['price']['rate_varies'];
        $this->priceScope = $room['price']['covers'];
        $this->traits = $room['room_type'];
        $this->bed = $room['bed_type'];
        $this->description = $room['description'];
        $this->quantity = $room['quantity'];
    }

    public function getDetails()
    {
        return $this->context->client->getHotelRoomDetails($this);
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
    private $occupants;
    private $billingInfo;
    private $contactInfo;

    public function __construct($occupants = null, $contactInfo = null, $billingInfo = null)
    {
        if ($occupants != null) {
            $this->addOccupants($occupants);
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

    public function addOccupants($occupants)
    {
        if ($this->occupants == null) {
          $this->occupants = array();
        };

        foreach (array_values($occupants) as $value) {
            // Check if all items in $passengers are arrays. If not, then we
            // got a single passenger only, and need to wrap it in an array.
            if (!is_array($value)) {
                $occupants = Array($occupants);
                break;
            }
        }

        foreach ($occupants as $occupant) {
            $occupant['baggage'] = 0;
            switch (strtolower($occupant['namePrefix'])) {
                case 'mr':
                    $occupant['gender'] = 'MALE';
                    break;
                case 'ms':
                    $occupant['gender'] = 'FEMALE';
                    break;
                case 'mrs':
                    $occupant['gender'] = 'FEMALE';
                    break;
            }

            array_push($this->occupants, $occupant);
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
        $data['persons'] = $this->occupants;
        $data['contactInfo'] = $this->contactInfo;
        $data['billingInfo'] = array_key_exists('billingInfo', $data) ? $this->billingInfo : $this->contactInfo;
        $data['bookBasket'] = Array($this->bookingId);
        return $data;
    }
}
