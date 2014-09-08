<?php
namespace Allmyles\Flights;

use Allmyles\Common\Price;

class SearchQuery
{
    const PROVIDER_ALL = 'All';
    const PROVIDER_TRADITIONAL = 'OnlyTraditional';
    const PROVIDER_LOWCOST = 'OnlyLowCost';

    private $fromLocation;
    private $toLocation;
    private $departureDate;
    private $returnDate;
    private $passengers;
    private $providerType;
    private $preferredAirlines;

    public function __construct(
        $fromLocation,
        $toLocation,
        $departureDate,
        $returnDate = null
    ) {
        $this->fromLocation = $fromLocation;
        $this->toLocation = $toLocation;
        $this->departureDate = $this->getTimestamp($departureDate);
        $this->returnDate = $this->getTimestamp($returnDate);
        $this->passengers = Array();
        $this->providerType = null;
        $this->preferredAirlines = null;
    }

    public function addProviderFilter($providerType)
    {
        $this->providerType = $providerType;
    }

    public function addAirlineFilter($airlines)
    {
        if ($this->preferredAirlines == null) {
          $this->preferredAirlines = array();
        };

        if (is_array($airlines)) {
            foreach ($airlines as $airline) {
                array_push($this->preferredAirlines, $airline);
            };
        } else {
            array_push($this->preferredAirlines, $airlines);
        };
    }

    public function addPassengers($adt, $chd = 0, $inf = 0)
    {
        $this->passengers['ADT'] = $adt;
        $this->passengers['CHD'] = $chd;
        $this->passengers['INF'] = $inf;
    }

    public function getData()
    {
        $data = Array();
        $data['fromLocation'] = $this->fromLocation;
        $data['toLocation'] = $this->toLocation;
        $data['departureDate'] = $this->departureDate;
        if ($this->returnDate != null) {
            $data['returnDate'] = $this->returnDate;
        };
        $data['persons'] = Array();
        foreach ($this->passengers as $type => $quantity) {
            array_push(
                $data['persons'],
                Array('passengerType' => $type, 'quantity' => $quantity)
            );
        };
        if ($this->providerType != null) {
            $data['providerType'] = $this->providerType;
        };
        if ($this->preferredAirlines != null) {
            $data['preferredAirlines'] = $this->preferredAirlines;
        };

        return $data;
    }

    private function getTimestamp($datetime)
    {
        if (is_string($datetime) || $datetime == null) {
            return $datetime;
        } else {
            return $datetime->format('c');
        }
    }
}

class FlightResult
{
    public $context;
    public $breakdown;
    public $totalFare;
    public $combinations;

    public function __construct($result, $context)
    {
        $this->context = &$context;

        $this->breakdown = $result['breakdown'];
        $this->totalFare = new Price(
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
        $this->serviceFee = new Price(
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
        }
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
            Array('payuId' => $payuId, 'basket' => Array($this->bookingId)), $this->context->session
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

class Leg
{
    public $combination;
    public $context;
    public $length;
    public $segments;

    public function __construct($leg, $combination)
    {
        $this->combination = $combination;
        $this->context = &$this->combination->context;

        $this->length = new \DateInterval(
            'PT'.substr($leg['elapsedTime'], 0, 2).'H'.substr($leg['elapsedTime'], 2, 2).'M'
        );
        $this->segments = Array();
        foreach ($leg['flightSegments'] as $segment) {
            array_push($this->segments, new Segment($segment, $this));
        };
    }
}

class Segment
{
    public $leg;
    public $context;
    public $arrival;
    public $departure;
    public $airline;
    public $flightNumber;
    public $availableSeats;
    public $cabin;

    public function __construct($segment, $leg)
    {
        $this->leg = $leg;
        $this->context = &$this->leg->context;

        $this->arrival = new Stop($segment['arrival'], $this);
        $this->departure = new Stop($segment['departure'], $this);
        $this->airline = $segment['operatingAirline'];
        $this->flightNumber = $segment['flightNumber'];
        $this->availableSeats = $segment['availableSeats'];
        $this->cabin = $segment['cabin'];
    }
}

class Stop
{
    public $segment;
    public $context;
    public $time;
    public $airport;
    public $terminal;

    public function __construct($stop, $segment)
    {
        $this->segment = $segment;
        $this->context = &$this->segment->context;

        $this->time = date_create($stop['dateTime'], new \DateTimeZone('UTC'));
        $this->airport = $stop['airport']['code'];
        $this->terminal = $stop['airport']['terminal'];
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
