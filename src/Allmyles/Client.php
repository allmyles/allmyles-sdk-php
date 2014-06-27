<?php
namespace Allmyles;

require 'Connector.php';
require 'Flights.php';
// require 'Masterdata.php';

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

        $flights = json_decode($response->data, true)['flightResultSet'];

        $result = Array();

        foreach ($flights as $flight) {
            $instance = new Flights\FlightResult($flight, $context);
            array_push($result, $instance);
        };

        return $result;
    }

    public function searchLocations($params, $session = null)
    {
        $context = new Context($this, ($session ? $session : uniqid()));

        $response = $this->connector->get('masterdata/search', $context, $params);

        if (!$async && $response->incomplete) {
            while ($response->incomplete) {
                sleep(5);
                $response = $response->retry();
            };
        };

        $flights = json_decode($response->data, true)['flightResultSet'];

        $result = Array();

        foreach ($flights as $flight) {
            $instance = new Flights\FlightResult($flight, $context);
            array_push($result, $instance);
        };

        return $result;
    }

    public function getFlightDetails($bookingId, $session = null) {
        $context = new Context($this, ($session ? $session : uniqid()));

        $response = $this->connector->get('flights/' . $bookingId, $context);

        return json_decode($response->data, true)['flightDetails'];
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
