<?php
namespace Allmyles\Hotels;

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
            'occupants' => $this->occupants
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

class HotelResult
{
    public $context;
    public $breakdown;
    public $totalFare;
    public $combinations;

    public function __construct($result, $context)
    {
        $this->context = &$context;

        $this->breakdown = $result['breakdown'];
        $this->totalFare = new \Allmyles\Common\Price(
            Array(
                'amount' => $result['total_fare'],
                'currency' => $result['currency'],
            )
        );
        $this->combinations = Array();
        foreach ($result['combinations'] as $combination) {
            $this->combinations[$combination['bookingId']] = new Combination($combination, $this);
        };
    }
}

class Combination
{
    public $flightResult;
    public $context;
    public $bookingId;
    public $providerType;
    public $legs;
    public $serviceFee;

    public function __construct($combination, $flightResult)
    {
        $this->flightResult = $flightResult;
        $this->context = &$this->flightResult->context;

        $this->bookingId = $combination['bookingId'];
        $this->providerType = $combination['providerType'];
        $this->legs = Array(
            $this->createLeg($combination, 'firstLeg'),
            $this->createLeg($combination, 'returnLeg')
        );
        $this->serviceFee = new \Allmyles\Common\Price(
            Array(
                'amount' => $combination['serviceFeeAmount'],
                'currency' => $this->flightResult->totalFare->currency,
            )
        );
    }

    private function createLeg($combination, $leg)
    {
        if (!array_key_exists($leg, $combination)) {
            return null;
        } else {
            return new Leg($combination[$leg], $this);
        };
    }

    public function getDetails()
    {
        $flightDetails = $this->context->client->getFlightDetails(
            $this->bookingId, $this->context->session
        );
        return $flightDetails;
    }

    public function book($parameters)
    {
        if (is_array($parameters)) {
            $parameters['bookingId'] = $this->bookingId;
        } else {
            $parameters->setBookingId($this->bookingId);
        };
        $bookResponse = $this->context->client->bookFlight(
            $parameters, $this->context->session
        );
        return $bookResponse;
    }

    public function addPayuPayment($payuId)
    {
        $paymentResponse = $this->context->client->addPayuPayment(
            $payuId, $this->context->session
        );
        return $paymentResponse;
    }

    public function createTicket()
    {
        $ticketingResponse = $this->context->client->createFlightTicket(
            $this->bookingId, $this->context->session
        );
        return $ticketingResponse;
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
