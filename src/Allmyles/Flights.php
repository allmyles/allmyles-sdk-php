<?php
namespace Allmyles\Flights;

class FlightResult
{
    public $context;
    public $breakdown;
    public $currency;
    public $totalFare;
    public $combinations;

    public function __construct($result, $context)
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
    protected $context;
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
        var_dump($this->context);
        $flightDetails = $this->context->client->getFlightDetails(
            $this->bookingId, $this->context->session
        );
        return $flightDetails;
    }
}
