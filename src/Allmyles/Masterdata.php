<?php
namespace Allmyles\Masterdata;

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
