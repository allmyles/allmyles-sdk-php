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
