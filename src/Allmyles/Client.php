<?php
namespace Allmyles;

require 'Connector.php';
require 'Classes/Cars.php';
require 'Classes/Common.php';
require 'Classes/Exceptions.php';
require 'Classes/Flights.php';
require 'Classes/Hotels.php';
require 'Classes/Masterdata.php';

define('ALLMYLES_VERSION', 'allmyles-sdk-php v1.0.3');

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

        if (is_array($parameters)) {
            $data = json_encode($parameters);
        } else {
            $data = json_encode($parameters->getData());
        }

        $response = $this->connector->post('flights', $context, $data);

        if (!$async && $response->incomplete) {
            while ($response->incomplete) {
                sleep(5);
                $response = $response->retry();
            };
        };

        $response->postProcessor = new Common\PostProcessor('searchFlight', $context);

        return $response;
    }

    public function getFlightDetails($bookingId, $session = null) {
        $context = new Context($this, ($session ? $session : uniqid()));

        $response = $this->connector->get('flights/' . $bookingId, $context);

        $response->postProcessor = new Common\PostProcessor('getFlightDetails', $context);

        return $response;
    }

    public function bookFlight($parameters, $session = null) {
        $context = new Context($this, ($session ? $session : uniqid()));

        if (is_array($parameters)) {
            $data = json_encode($parameters);
        } else {
            $data = json_encode($parameters->getData());
        }

        $response = $this->connector->post('books', $context, $data);

        $response->postProcessor = new Common\PostProcessor('bookFlight', $context);

        return $response;
    }

    public function addPayuPayment($parameters, $session = null) {
        $context = new Context($this, ($session ? $session : uniqid()));

        $data = json_encode($parameters);
        $response = $this->connector->post('payment', $context, $data);

        $response->postProcessor = new Common\PostProcessor('addPayuPayment', $context);

        return $response;
    }

    public function createFlightTicket($bookingId, $session = null) {
        $context = new Context($this, ($session ? $session : uniqid()));

        $response = $this->connector->get('tickets/' . $bookingId, $context);

        $response->postProcessor = new Common\PostProcessor('createFlightTicket', $context);

        return $response;
    }

    public function searchHotel($parameters, $session = null)
    {
        $context = new Context($this, ($session ? $session : uniqid()));

        if (is_array($parameters)) {
            $data = json_encode($parameters);
        } else {
            $data = json_encode($parameters->getData());
        }

        $response = $this->connector->post('hotels', $context, $data);

        $response->postProcessor = new Common\PostProcessor('searchHotel', $context);

        return $response;
    }

    public function getHotelDetails($hotel) {
        $response = $this->connector->get('hotels/' . $hotel->hotelId, $hotel->context);

        $response->postProcessor = new Common\PostProcessor('getHotelDetails', $hotel);

        return $response;
    }

    public function getHotelRoomDetails($room) {
        $response = $this->connector->get(
            'hotels/' . $room->hotel->hotelId . '/rooms/' . $room->roomId, $room->context
        );

        $response->postProcessor = new Common\PostProcessor('getHotelRoomDetails', $room->context);

        return $response;
    }

    public function bookHotel($parameters, $session = null) {
        $context = new Context($this, ($session ? $session : uniqid()));

        if (is_array($parameters)) {
            $data = json_encode($parameters);
        } else {
            $data = json_encode($parameters->getData());
        }

        $response = $this->connector->post('books', $context, $data);

        $response->postProcessor = new Common\PostProcessor('bookHotel', $context);

        return $response;
    }

    public function searchCar($parameters, $session = null)
    {
        $context = new Context($this, ($session ? $session : uniqid()));

        if (is_array($parameters)) {
            $data = json_encode($parameters);
        } else {
            $data = json_encode($parameters->getData());
        }

        $response = $this->connector->post('cars', $context, $data);

        $response->postProcessor = new Common\PostProcessor('searchCar', $context);

        return $response;
    }

    public function getCarDetails($bookingId, $session = null)
    {
        $context = new Context($this, ($session ? $session : uniqid()));

        $response = $this->connector->get('cars/' . $bookingId, $context);

        $response->postProcessor = new Common\PostProcessor('getCarDetails', $context);

        return $response;
    }

    public function bookCar($parameters, $session = null)
    {
        $context = new Context($this, ($session ? $session : uniqid()));

        if (is_array($parameters)) {
            $data = json_encode($parameters);
        } else {
            $data = json_encode($parameters->getData());
        }

        $response = $this->connector->post('books', $context, $data);

        $response->postProcessor = new Common\PostProcessor('bookCar', $context);

        return $response;
    }

    public function searchLocations($parameters, $session = null)
    {
        $context = new Context($this, ($session ? $session : uniqid()));
        $response = $this->connector->get('masterdata/search', $context, $parameters);

        $response->postProcessor = new Common\PostProcessor('searchLocations', $context);

        return $response;
    }

    public function retrieveMasterdata($repo, $session = null)
    {
        $context = new Context($this, ($session ? $session : uniqid()));
        $response = $this->connector->get('masterdata/' . $repo, $context, $parameters);

        $response->postProcessor = new Common\PostProcessor('getMasterdata', $context);

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
