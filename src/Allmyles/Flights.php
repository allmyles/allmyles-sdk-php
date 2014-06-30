<?php
namespace Allmyles\Flights;

class FlightResult
{
    public $context;
    public $breakdown;
    public $currency;
    public $totalFare;
    public $combinations;

    public function __construct($result, &$context)
    {
        $this->breakdown = $result['breakdown'];
        $this->currency = $result['currency'];
        $this->totalFare = $result['total_fare'];
        $this->combinations = Array();

        $this->context = $context;

        foreach ($result['combinations'] as $combination) {
            $this->combinations[$combination['bookingId']] = new Combination($combination, $this->context);
        }
    }
}

class Combination
{
    public $context;
    public $bookingId;
    public $providerType;
    public $firstLeg;
    public $returnLeg;
    public $serviceFeeAmount;

    public function __construct($combination, &$context)
    {
        $this->bookingId = $combination['bookingId'];
        $this->providerType = $combination['providerType'];
        $this->firstLeg = $combination['firstLeg'];
        $this->returnLeg = $combination['returnLeg'];
        $this->serviceFeeAmount = $combination['serviceFeeAmount'];

        $this->context = $context;
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
