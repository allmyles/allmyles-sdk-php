<?php
namespace Allmyles\Flights;

define('FLIGHT_PROVIDER_ALL', 'All');
define('FLIGHT_PROVIDER_TRADITIONAL', 'OnlyTraditional');
define('FLIGHT_PROVIDER_LOWCOST', 'OnlyLowCost');

class SearchQuery
{
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
        }

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
        };
    }
}

class FlightResult
{
    public $context;
    public $breakdown;
    public $totalFare;
    public $combinations;

    public function __construct($result, &$context)
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

    public function __construct($combination, &$flightResult)
    {
        $this->flightResult = $flightResult;
        $this->context = &$this->flightResult->context;

        $this->bookingId = $combination['bookingId'];
        $this->providerType = $combination['providerType'];
        $this->legs = [
            $this->createLeg($combination, 'firstLeg'),
            $this->createLeg($combination, 'returnLeg')
        ];
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
        $parameters['bookingId'] = $this->bookingId;
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

class Leg
{
    public $combination;
    public $context;
    public $length;
    public $segments;

    public function __construct($leg, &$combination)
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
    public $seats;
    public $cabin;

    public function __construct($segment, &$leg)
    {
        $this->leg = $leg;
        $this->context = &$this->leg->context;

        $this->arrival = new Stop($segment['arrival'], $this);
        $this->departure = new Stop($segment['departure'], $this);
        $this->airline = $segment['operatingAirline'];
        $this->flightNumber = $segment['flightNumber'];
        $this->availableSeats = $segment['availableSeats'];
        $this->seats = $segment['availableSeats'];
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

    public function __construct($stop, &$segment)
    {
        $this->segment = $segment;
        $this->context = &$this->segment->context;

        $this->time = date_create($stop['dateTime'], new \DateTimeZone('UTC'));
        $this->airport = $stop['airport']['code'];
        $this->terminal = $stop['airport']['terminal'];
    }
}
