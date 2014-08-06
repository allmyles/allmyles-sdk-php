<?php
namespace Allmyles\Common;

class Price
{
    public $currency;
    public $amount;

    public function __construct($price)
    {
        $this->currency = $price['currency'];
        $this->amount = $price['amount'];
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
            $instance = new \Allmyles\Flights\FlightResult($flight, $context);
            array_push($result, $instance);
        };

        return $result;
    }

    private function getFlightDetails($data, $context)
    {
        $results = $data['flightDetails'];
        $results['surcharge'] = new \Allmyles\Common\Price($results['surcharge']);
        $results['price'] = new \Allmyles\Common\Price($results['price']);
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
        };
    }

    private function addPayuPayment($data, $context)
    {
        // We are expecting no content
        if ($data == null) {
            return true;
        };
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

}
