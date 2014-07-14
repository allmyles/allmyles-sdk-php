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
        if (!$this->locationIsValid($fromLocation) ||
            !$this->locationIsValid($toLocation) ||
            $fromLocation == $toLocation) {
            throw new \Allmyles\Exceptions\ValidationException('Invalid location code given!');
        };
        $this->fromLocation = $fromLocation;
        $this->toLocation = $toLocation;
        $this->departureDate = $this->getTimestamp($departureDate);
        $this->returnDate = $this->getTimestamp($returnDate);
        $this->passengers = Array();
        $this->providerType = null;
        $this->preferredAirlines = null;
        $this->passengers['ADT'] = null;
        $this->passengers['CHD'] = null;
        $this->passengers['INF'] = null;
    }

    private function locationIsValid($locationCode) {
        $abc = '-AaBbCcDdEeFfGgHhIiJjKkLlMmNnOoPpQqRrSsTtUuVvWwXxYyZz';
        if (is_string($locationCode)) {
            if (strlen($locationCode) == 3) {
                if ((!(strpos($abc, $locationCode[0]) == false)) and
                   (!(strpos($abc, $locationCode[1]) == false)) and
                   (!(strpos($abc, $locationCode[2]) == false))) {
                    return true;
                };
            };
        };
    }

    public function addProviderFilter($providerType)
    {
    	if ((!is_string($providerType))) throw new \Allmyles\Exceptions\ValidationException('Invalid provide filter given!');
        $this->providerType = $providerType;
    }

    public function addAirlineFilter($airlines)
    {
    	$ab0 = '-AaBbCcDdEeFfGgHhIiJjKkLlMmNnOoPpQqRrSsTtUuVvWwXxYyZz0123456789';
        if ($this->preferredAirlines == null) {
          $this->preferredAirlines = array();
        };

        if ($airlines == array()) throw new \Allmyles\Exceptions\ValidationException('Invalid airline given!');
        if (is_array($airlines)) {
            foreach ($airlines as $airline) {
            	if (is_string($airline)) {
            		if ((!(strlen($airline) == 2)) or (strpos($ab0, $airline[0]) == false) or
                   (strpos($ab0, $airline[1]) == false)) {
            			throw new \Allmyles\Exceptions\ValidationException('Invalid airline given!');
            		};
            	} else throw new \Allmyles\Exceptions\ValidationException('Invalid airline given!');
                if (in_array($airline, $this->preferredAirlines) == 0) {
                    array_push($this->preferredAirlines, $airline);
                };
            };
        } else {
        	if (is_string($airlines)) {
            		if ((!(strlen($airlines) == 2)) or (strpos($ab0, $airlines[0]) == false) or
                   (strpos($ab0, $airlines[1]) == false)) {
            			throw new \Allmyles\Exceptions\ValidationException('Invalid airline given!');
            		};
            	} else throw new \Allmyles\Exceptions\ValidationException('Invalid airline given!');
            if (in_array($airlines, $this->preferredAirlines) == 0) {
                array_push($this->preferredAirlines, $airlines);
            };
        };
    }

    public function addPassengers($adt, $chd = 0, $inf = 0)
    {
    	if (is_null($adt)) throw new \Allmyles\Exceptions\ValidationException('Invalid passenger data given!');
        if (is_null($chd)) $chd = 0;
        if (is_null($inf)) $inf = 0;
    	if ((is_int($adt)) and 
    		(is_int($chd)) and 
    		(is_int($inf))) {
    		if (!(($adt > 0) and ($chd >= 0) and ($inf >= 0) and 
    			($adt >= $inf) and ($adt + $chd <= 9) and ($inf <=9))) {
    			throw new \Allmyles\Exceptions\ValidationException('Invalid passenger data given!');
    		};
        } else {
        	throw new \Allmyles\Exceptions\ValidationException('Invalid passenger data given!');
        };
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

        $a01 = '-0123456789';
        $ab0 = '-AaBbCcDdEeFfGgHhIiJjKkLlMmNnOoPpQqRrSsTtUuVvWwXxYyZz0123456789';

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
                default:
                    throw new \Allmyles\Exceptions\ValidationException('Invalid passenger data given!');
                    break;
            }

            if ((is_string($passenger['firstName'])) and 
            	(is_string($passenger['lastName'])) and 
            	(is_string($passenger['email'])) and 
            	(is_string($passenger['document']['1'])) and
            	(is_string($passenger['document']['3']))) {
                if (!((strlen($passenger['firstName']) > 0) and 
                	(strlen($passenger['lastName']) > 0) and 
                	(strlen($passenger['email']) > 0) and 
                	(strlen($passenger['document']['1']) > 0) and 
                	(strlen($passenger['document']['3']) > 0))) {
                	    throw new \Allmyles\Exceptions\ValidationException('Invalid passenger data given!');
                } else {

                	// If names would be needed to be written in the english alphabet

                    /*for ($i = 0; $i < strlen($passenger['firstName']); $i++) {
               	        if (strpos($ab0, $passenger['firstName'][$i]) == false) {
        		    	    throw new \Allmyles\Exceptions\ValidationException('Invalid passenger data given!');
        		        };
        		    };
        		    for ($i = 0; $i < strlen($passenger['lastName']); $i++) {
               	        if (strpos($ab0, $passenger['lastName'][$i]) == false) {
        		    	    throw new \Allmyles\Exceptions\ValidationException('Invalid passenger data given!');
        		        };
        		    };*/

        		    if (strpos($passenger['email'], '@') == false) {
        		        throw new \Allmyles\Exceptions\ValidationException('Invalid passenger data given!');
        		    };
                };
            } else throw new \Allmyles\Exceptions\ValidationException('Invalid passenger data given!');

        	if (strlen($passenger['birthDate']) == 10) {
        		for ($i = 0; $i <= 9; $i++) {
               	    if (($i == 4) or ($i == 7)) {
               	    	if (!($passenger['birthDate'][$i] == '-')) {
        			        throw new \Allmyles\Exceptions\ValidationException('Invalid passenger data given!');
               	        };
        		    } else {
        		    	if (strpos($a01, $passenger['birthDate'][$i]) == false) {
        		    	    throw new \Allmyles\Exceptions\ValidationException('Invalid passenger data given!');
        		        };
        		    };
        		
        		};

        	    $passengerBirthYear = substr($passenger['birthDate'], 0, 4);
        	    $passengerBirthMonth = substr($passenger['birthDate'], 5, 2);
        	    $passengerBirthDay = substr($passenger['birthDate'], 8, 2);

        	    if (checkdate($passengerBirthMonth, $passengerBirthDay, $passengerBirthYear)) {
        	    	if (strtotime($passenger['birthDate']) > strtotime(date('Y-m-d'))) {
        	    		throw new \Allmyles\Exceptions\ValidationException('Expired document!');
        	    	};
        	    } else throw new \Allmyles\Exceptions\ValidationException('Invalid passenger data given!');

            } else throw new \Allmyles\Exceptions\ValidationException('Invalid passenger data given!');

        	switch (strtolower($passenger['passengerTypeCode'])) {
                case 'adt':
                    
                    break;
                case 'chd':
                    if (strtotime($passenger['birthDate']) < strtotime(date('Y-m-d', strtotime('-12 year')))) {
        	    		throw new \Allmyles\Exceptions\ValidationException('Expired document!');
        	    	};
                    break;
                case 'inf':
                    if (strtotime($passenger['birthDate']) < strtotime(date('Y-m-d', strtotime('-2 year')))) {
        	    		throw new \Allmyles\Exceptions\ValidationException('Expired document!');
        	    	};
                    break;
                default:
                    throw new \Allmyles\Exceptions\ValidationException('Invalid passenger data given!');
                    break;
            }

        	if (strlen($passenger['document']['0']) == 10) {
        		for ($i = 0; $i <= 9; $i++) {
               	    if (($i == 4) or ($i == 7)) {
               	    	if (!($passenger['document']['0'][$i] == '-')) {
        			        throw new \Allmyles\Exceptions\ValidationException('Invalid passenger data given!');
               	        };
        		    } else {
        		    	if (strpos($a01, $passenger['document']['0'][$i]) == false) {
        		    	    throw new \Allmyles\Exceptions\ValidationException('Invalid passenger data given!');
        		        };
        		    };
        		
        		};

        	    $yearOfExpirity = substr($passenger['document']['0'], 0, 4);
        	    $monthOfExpirity = substr($passenger['document']['0'], 5, 2);
        	    $dayOfExpirity = substr($passenger['document']['0'], 8, 2);

        	    if ((checkdate($monthOfExpirity, $dayOfExpirity, $yearOfExpirity))) {
        	    	if (strtotime($passenger['document']['0']) < strtotime(date('Y-m-d'))) {
        	    		throw new \Allmyles\Exceptions\ValidationException('Expired document!');
        	    	};
        	    } else throw new \Allmyles\Exceptions\ValidationException('Invalid passenger data given!');

            } else throw new \Allmyles\Exceptions\ValidationException('Invalid passenger data given!');

        	if ((!((is_string($passenger['document']['2'])) and (strlen($passenger['document']['2']) == 2))) or 
        	    (strpos($ab0, $passenger['document']['2'][0]) == false) or (strpos($ab0, $passenger['document']['2'][1]) == false)) {
                throw new \Allmyles\Exceptions\ValidationException('Invalid passenger data given!');
            };

            array_push($this->passengers, $passenger);
        };
    }

    public function addContactInfo($address)
    {
    	$a01 = '-0123456789';
        $ab0 = '- AaBbCcDdEeFfGgHhIiJjKkLlMmNnOoPpQqRrSsTtUuVvWwXxYyZz0123456789';

        if ((is_string($address['address']['0'])) and 
        (is_string($address['address']['1'])) and
        (is_string($address['address']['3'])) and 
        (is_string($address['email'])) and
        (is_string($address['name']))) {
            if ((strlen($address['address']['0']) > 0) and 
            (strlen($address['address']['1']) > 0) and 
            (strlen($address['address']['3']) > 0) and
            (strlen($address['email']) > 0) and 
            (strlen($address['name']) > 0)) {
                if (strpos($address['email'], '@') == false) {
        		    throw new \Allmyles\Exceptions\ValidationException('Invalid passenger data given!');
        		};
            } else throw new \Allmyles\Exceptions\ValidationException('Invalid passenger data given!');
        } else throw new \Allmyles\Exceptions\ValidationException('Invalid passenger data given!');

        if ((!((is_string($address['address']['2'])) and (strlen($address['address']['2']) == 2))) or 
    	    (strpos($ab0, $address['address']['2'][0]) == false) or (strpos($ab0, $address['address']['2'][1]) == false)) {
            throw new \Allmyles\Exceptions\ValidationException('Invalid passenger data given!');
        };

        if ((is_string($address['phone']['0'])) and 
        (is_string($address['phone']['1'])) and 
        (is_string($address['phone']['2']))) {
        	if ((is_numeric($address['phone']['0'])) and 
            (is_numeric($address['phone']['1'])) and 
            (is_numeric($address['phone']['2']))) {
                if (!((strlen($address['phone']['0']) > 0) and 
                (strlen($address['phone']['1']) > 0) and 
                (strlen($address['phone']['2']) > 0))) {
                    throw new \Allmyles\Exceptions\ValidationException('Invalid passenger data given!');
                };
            } else throw new \Allmyles\Exceptions\ValidationException('Invalid passenger data given!');
        } else throw new \Allmyles\Exceptions\ValidationException('Invalid passenger data given!');

        $this->contactInfo = $address;
    }

    public function addBillingInfo($address)
    {
        $a01 = '-0123456789';
        $ab0 = '- AaBbCcDdEeFfGgHhIiJjKkLlMmNnOoPpQqRrSsTtUuVvWwXxYyZz0123456789';

        if ((is_string($address['address']['0'])) and 
        (is_string($address['address']['1'])) and
        (is_string($address['address']['3'])) and 
        (is_string($address['email'])) and
        (is_string($address['name']))) {
            if ((strlen($address['address']['0']) > 0) and 
            (strlen($address['address']['1']) > 0) and 
            (strlen($address['address']['3']) > 0) and
            (strlen($address['email']) > 0) and 
            (strlen($address['name']) > 0)) {
                if (strpos($address['email'], '@') == false) {
        		    throw new \Allmyles\Exceptions\ValidationException('Invalid passenger data given!');
        		};
            } else throw new \Allmyles\Exceptions\ValidationException('Invalid passenger data given!');
        } else throw new \Allmyles\Exceptions\ValidationException('Invalid passenger data given!');

        if ((!((is_string($address['address']['2'])) and (strlen($address['address']['2']) == 2))) or 
    	    (strpos($ab0, $address['address']['2'][0]) == false) or (strpos($ab0, $address['address']['2'][1]) == false)) {
            throw new \Allmyles\Exceptions\ValidationException('Invalid passenger data given!');
        };

        if ((is_string($address['phone']['0'])) and 
        (is_string($address['phone']['1'])) and 
        (is_string($address['phone']['2']))) {
        	if ((is_numeric($address['phone']['0'])) and 
            (is_numeric($address['phone']['1'])) and 
            (is_numeric($address['phone']['2']))) {
                if (!((strlen($address['phone']['0']) > 0) and 
                (strlen($address['phone']['1']) > 0) and 
                (strlen($address['phone']['2']) > 0))) {
                    throw new \Allmyles\Exceptions\ValidationException('Invalid passenger data given!');
                };
            } else throw new \Allmyles\Exceptions\ValidationException('Invalid passenger data given!');
        } else throw new \Allmyles\Exceptions\ValidationException('Invalid passenger data given!');

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
