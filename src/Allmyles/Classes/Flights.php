<?php
namespace Allmyles\Flights;

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

        if (!(is_string($fromLocation))) throw new \Allmyles\Exceptions\TypeHintException('Fatal Error: Argument 1 must be a string');
        if (!(is_string($toLocation))) throw new \Allmyles\Exceptions\TypeHintException('Fatal Error: Argument 2 must be a string');

        if (!(is_string($departureDate))) {
            if (is_object($departureDate)) {
                if (!(get_class($departureDate) == 'DateTime')) throw new \Allmyles\Exceptions\TypeHintException('Fatal Error: Argument 3 must be an ISO formatted timestamp, or a DateTime object');
            } else throw new \Allmyles\Exceptions\TypeHintException('Fatal Error: Argument 3 must be an ISO formatted timestamp, or a DateTime object');
        };
        if (isset($returnDate)) {
            if (!(is_string($returnDate))) {
                if (is_object($returnDate)) {
                    if (!(get_class($returnDate) == 'DateTime')) throw new \Allmyles\Exceptions\TypeHintException('Fatal Error: Argument 4 must be an ISO formatted timestamp, or a DateTime object');
                } else throw new \Allmyles\Exceptions\TypeHintException('Fatal Error: Argument 4 must be an ISO formatted timestamp, or a DateTime object');
            };
        };

        $this->fromLocation = $fromLocation;
        $this->toLocation = $toLocation;
        $this->departureDate = $this->getTimestamp($departureDate);
        $this->returnDate = $this->getTimestamp($returnDate);
        $this->passengers = Array();
        $this->providerType = null;
        $this->preferredAirlines = Array();
        $this->passengers = Array('ADT' => 0, 'CHD' => 0, 'INF' => 0);
    }

    public function addProviderFilter($providerType)
    {
        if (!(is_string($providerType))) throw new \Allmyles\Exceptions\TypeHintException('Fatal Error: Argument 1 must be a string');

        $this->providerType = $providerType;
    }

    public function addAirlineFilter($airlines)
    {

        if (is_string($airlines)) {
            $airlines = Array($airlines);
        };
        if (is_array($airlines)) {
            foreach ($airlines as $airline) {
                if (!(is_string($airline))) throw new \Allmyles\Exceptions\TypeHintException('Fatal Error: Argument 1 must be a string, or an array of strings');
            };
            foreach ($airlines as $airline) {
                if (!in_array($airline, $this->preferredAirlines)) {
                    array_push($this->preferredAirlines, $airline);
                };
            };
        } else throw new \Allmyles\Exceptions\TypeHintException('Fatal Error: Argument 1 must be a string, or an array of strings');
    }

    public function addPassengers($adt, $chd = 0, $inf = 0)
    {
        if (!(is_int($adt))) throw new \Allmyles\Exceptions\TypeHintException('Fatal Error: Argument 1 must be an integer');
        if (!(is_int($chd)) and isset($chd)) throw new \Allmyles\Exceptions\TypeHintException('Fatal Error: Argument 2 must be an integer');
        if (!(is_int($inf)) and isset($inf)) throw new \Allmyles\Exceptions\TypeHintException('Fatal Error: Argument 3 must be an integer');
        
        $this->passengers['ADT'] += $adt;
        $this->passengers['CHD'] += $chd;
        $this->passengers['INF'] += $inf;
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
            if (is_object($parameters)) {
                if (get_class($parameters) == 'Allmyles\Flights\BookQuery') {
                    $parameters->setBookingId($this->bookingId);
                } else throw new \Allmyles\Exceptions\TypeHintException('Fatal Error: Argument 1 must be an object of class Flights\BookQuery, or an array');       
            } else throw new \Allmyles\Exceptions\TypeHintException('Fatal Error: Argument 1 must be an object of class Flights\BookQuery, or an array');
        };
        $bookResponse = $this->context->client->bookFlight(
            $parameters, $this->context->session
        );
        return $bookResponse;
    }

    public function addPayuPayment($payuId)
    {
        if (!(is_string($payuId))) throw new \Allmyles\Exceptions\TypeHintException('Fatal Error: Argument 1 must be a string');

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

class BookQuery
{
    private $bookingId;
    private $passengers;
    private $billingInfo;
    private $contactInfo;

    public function __construct(array $passengers = null, array $contactInfo = null, array $billingInfo = null)
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

    public function addPassengers(array $passengers)
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

    public function addContactInfo(array $address)
    {
        $this->contactInfo = $address;
    }

    public function addBillingInfo(array $address)
    {
        $this->billingInfo = $address;
    }

    public function getData()
    {
        $data = Array();
        $data['passengers'] = $this->passengers;
        if (isset($this->billingInfo)) {
            $data['billingInfo'] = $this->billingInfo;
        } else {
        	$data['billingInfo'] = $this->contactInfo;
        };
        $data['contactInfo'] = $this->contactInfo;
        $data['bookingId'] = $this->bookingId;
        return $data;
    }
}
