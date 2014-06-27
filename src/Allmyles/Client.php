<?php
namespace Allmyles;

require 'Connector.php';
require 'Flights.php';
// require 'Masterdata.php';

define('SDK_VERSION', 'allmyles-sdk-php v1.0.0-dev');

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
