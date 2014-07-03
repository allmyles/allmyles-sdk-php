<?php
namespace Allmyles;

require 'Connector.php';
require 'Flights.php';
// require 'Masterdata.php';

define('ALLMYLES_VERSION', 'allmyles-sdk-php v1.0.0-dev');

class Client
{
    protected $connector;

    public function __construct($baseUrl, $authKey)
    {
        $this->connector = new Connector\ServiceConnector($baseUrl, $authKey);
    }

    public function searchFlight($parameters, $async = true, $session = null)
    {
        $context = new Context($this, ($session ? $session : uniqid()));

        $data = json_encode($parameters);
        $response = $this->connector->post('flights', $context, $data);

        if (!$async && $response->incomplete) {
            while ($response->incomplete) {
                sleep(5);
                $response = $response->retry();
            };
        };

        $response->setPostProcessor(function($data) use (&$context) {
            $flights = $data['flightResultSet'];

            $result = Array();

            foreach ($flights as $flight) {
                $instance = new Flights\FlightResult($flight, $context);
                array_push($result, $instance);
            };

            return $result;
        });

        return $response;
    }

    public function getFlightDetails($bookingId, $session = null) {
        $context = new Context($this, ($session ? $session : uniqid()));

        $response = $this->connector->get('flights/' . $bookingId, $context);

        $response->setPostProcessor(function($data) use (&$context) {
            $results = $data['flightDetails'];
            return $results;
        });

        return $response;
    }

    public function bookFlight($parameters, $session = null) {
        $context = new Context($this, ($session ? $session : uniqid()));

        $data = json_encode($parameters);
        $response = $this->connector->post('books', $context, $data);

        return $response;
    }

    public function addPayuPayment($payuId, $session = null) {
        $context = new Context($this, ($session ? $session : uniqid()));

        $data = json_encode(Array('payuId' => $payuId));
        $response = $this->connector->post('payment', $context, $data);

        $response->setPostProcessor(function($data) use (&$context) {
            $result = true;
        return $result;
        });

        return $response;
    }

    public function createFlightTicket($bookingId, $session = null) {
        $context = new Context($this, ($session ? $session : uniqid()));

        $data = json_encode($parameters);
        $response = $this->connector->get('tickets/' . $bookingId, $context);

        $response->setPostProcessor(function($data) use (&$context) {
            if (array_key_exists('tickets', $data)) {
                $results = $data['tickets'];
            } else {
                $results = $data;
            };

            return $results;
        });

        return $response;
    }

    public function searchLocations($parameters, $session = null)
    {
        $context = new Context($this, ($session ? $session : uniqid()));
        $response = $this->connector->get('masterdata/search', $context, $parameters);

        $response->setPostProcessor(function($data) use (&$context) {
            $results = $data['locationSearchResult'];
            return $results;
        });

        return $response;
    }
}

class Context
{
    public $client;
    public $session;

    public function __construct(&$client, $session)
    {
        $this->client = $client;
        $this->session = $session;
    }
}
