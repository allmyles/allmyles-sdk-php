<?php
namespace Allmyles\Flights;

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
            $this->createLeg($combination['firstLeg']),
            $this->createLeg($combination['returnLeg'])
        ];
        $this->serviceFee = new \Allmyles\Common\Price(
            Array(
                'amount' => $combination['serviceFeeAmount'],
                'currency' => $this->flightResult->totalFare->currency,
            )
        );
    }

    private function createLeg($leg)
    {
        if (!$leg) {
            return null;
        } else {
            return new Leg($leg, $this);
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
