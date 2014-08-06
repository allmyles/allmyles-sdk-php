<?php
namespace Allmyles;

require 'Connector.php';
require 'Classes/Common.php';
require 'Classes/Exceptions.php';
require 'Classes/Flights.php';
require 'Classes/Masterdata.php';

define('ALLMYLES_VERSION', 'allmyles-sdk-php v1.0.1');

class Client
{
    protected $connector;

    public function __construct(string $baseUrl, string $authKey)
    {
        $this->connector = new Connector\ServiceConnector($baseUrl, $authKey);
    }

    private function sendRequest($method, $path, $context, $data = Array())
    {
        return $this->connector->$method($path, $context, $data);
    }

    public function searchFlight($parameters, boolean $async = true, string $session = null)
    {
        $context = new Context($this, ($session ? $session : uniqid()));

        if (is_array($parameters)) {
            $data = json_encode($parameters);
        } else {
            if (is_object($parameters)) {
                if (get_class($parameters) == 'Flights\SearchQuery') {
                    $data = json_encode($parameters->getData());
                } else throw new Exception('Fatal Error: Argument 1 must be an object of class Flights\SearchQuery, or an array');       
            } else throw new Exception('Fatal Error: Argument 1 must be an object of class Flights\SearchQuery, or an array');
        }

        $response = $this->sendRequest('post', 'flights', $context, $data);

        if (!$async && $response->incomplete) {
            while ($response->incomplete) {
                sleep(5);
                $response = $response->retry();
            };
        };

        $response->postProcessor = new Common\PostProcessor('searchFlight', $context);

        return $response;
    }

    public function getFlightDetails(string $bookingId, string $session = null) {
        $context = new Context($this, ($session ? $session : uniqid()));

        $response = $this->connector->get('flights/' . $bookingId, $context);

        $response->postProcessor = new Common\PostProcessor('getFlightDetails', $context);

        return $response;
    }

    public function bookFlight(array $parameters, string $session = null) {
        $context = new Context($this, ($session ? $session : uniqid()));

        if (is_array($parameters)) {
            $data = json_encode($parameters);
        } else {
            $data = json_encode($parameters->getData());
        }

        $response = $this->sendRequest('post', 'books', $context, $data);

        $response->postProcessor = new Common\PostProcessor('bookFlight', $context);

        return $response;
    }

    public function addPayuPayment(string $payuId, string $session = null) {
        $context = new Context($this, ($session ? $session : uniqid()));

        $data = json_encode(Array('payuId' => $payuId));
        $response = $this->sendRequest('post', 'payment', $context, $data);

        $response->postProcessor = new Common\PostProcessor('addPayuPayment', $context);

        return $response;
    }

    public function createFlightTicket(string $bookingId, string $session = null) {
        $context = new Context($this, ($session ? $session : uniqid()));

        $response = $this->sendRequest('get', 'tickets/' . $bookingId, $context);

        $response->postProcessor = new Common\PostProcessor('createFlightTicket', $context);

        return $response;
    }

    public function searchLocations(array $parameters, string $session = null)
    {
        $context = new Context($this, ($session ? $session : uniqid()));
        $response = $this->sendRequest('get', 'masterdata/search', $context, $parameters);

        $response->postProcessor = new Common\PostProcessor('searchLocations', $context);

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
