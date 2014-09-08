<?php
namespace Allmyles\Cars;

use Allmyles\Common\Price;

class SearchQuery
{
    private $location;
    private $startDate;
    private $endDate;

    public function __construct($location, $startDate, $endDate) {
        $this->location = $location;
        $this->startDate = $this->getTimestamp($startDate);
        $this->endDate = $this->getTimestamp($endDate);
    }

    public function getData()
    {
        $data = Array(
            'airport_code' => $this->location,
            'start_date' => $this->startDate,
            'end_date' => $this->endDate
        );

        return $data;
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

class Car
{
    public $context;

    public $vehicleId;
    public $price;
    public $vendor;
    public $isAvailable;
    public $isUnlimited;
    public $overageFee;
    public $traits;

    public function __construct($result, $context)
    {
        $this->context = &$context;

        $this->vehicleId = $result['vehicle_id'];
        $this->price = new Price($result['price']);
        $this->vendor = new Vendor(
            $result['vendor_id'], $result['vendor_name'], $result['vendor_code'], $this->context
        );
        $this->isAvailable = $result['available'];
        $this->isUnlimited = $result['unlimited'];
        $this->overageFee = Array(
            'includedDistance' => $result['overage_fee']['included_distance'],
            'unit' => $result['overage_fee']['unit'],
            'price' => new Price($result['overage_fee'])
        );
        $this->traits = $result['traits'];
    }

    public function getDetails()
    {
        return $this->context->client->getCarDetails($this->vehicleId, $this->context->session);
    }

    public function book($parameters)
    {
        if (is_array($parameters)) {
            $parameters['bookingId'] = $this->vehicleId;
        } else {
            $parameters->setBookingId($this->vehicleId);
        };
        $bookResponse = $this->context->client->bookCar(
            $parameters, $this->context->session
        );
        return $bookResponse;
    }

    public function addPayuPayment($payuId)
    {
        $paymentResponse = $this->context->client->addPayuPayment(
            Array('payuId' => $payuId, 'basket' => Array($this->vehicleId)), $this->context->session
        );
        return $paymentResponse;
    }
}

class Vendor
{
    public $context;

    public $id;
    public $name;
    public $code;

    public function __construct($id, $name, $code, $context)
    {
        $this->context = &$context;

        $this->id = $id;
        $this->name = $name;
        $this->code = $code;
    }

    public function searchCars()
    {
        return $this->context->client->searchCar(Array('vendor_id' => $this->id));
    }
}

class BookQuery
{
    private $bookingId;
    private $persons;
    private $billingInfo;
    private $contactInfo;

    public function __construct($persons = null, $contactInfo = null, $billingInfo = null)
    {
        if ($persons != null) {
            $this->addPersons($persons);
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

    public function addPersons($persons)
    {
        if ($this->persons == null) {
          $this->persons = array();
        };

        foreach (array_values($persons) as $value) {
            // Check if all items in $persons are arrays. If not, then we
            // got a single person only, and need to wrap it in an array.
            if (!is_array($value)) {
                $persons = Array($persons);
                break;
            }
        }

        foreach ($persons as $person) {
            $person['baggage'] = 0;
            switch (strtolower($person['namePrefix'])) {
                case 'mr':
                    $person['gender'] = 'MALE';
                    break;
                case 'ms':
                    $person['gender'] = 'FEMALE';
                    break;
                case 'mrs':
                    $person['gender'] = 'FEMALE';
                    break;
            }

            array_push($this->persons, $person);
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
        $data['persons'] = $this->persons;
        $data['billingInfo'] = $this->billingInfo;
        $data['contactInfo'] = $this->contactInfo;
        $data['bookBasket'] = Array($this->bookingId);
        return $data;
    }
}
