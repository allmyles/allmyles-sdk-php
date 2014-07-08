<?php
namespace Allmyles;

class PostProcessorTest extends \PHPUnit_Framework_TestCase
{
    protected $context;

    protected function setUp()
    {
        $this->context = new Context($this, null);
    }

    public function testFlightSearchNormal()
    {
        $data = json_decode(file_get_contents('tests/messages/flightSearchNormal.json'), true);
        $processor = new Common\PostProcessor('searchFlight', $this->context);
        $output = $processor->process($data);
        $expected = Array(
            new Flights\FlightResult($data["flightResultSet"][0], $this->context),
            new Flights\FlightResult($data["flightResultSet"][1], $this->context)
        );

        $this->assertEquals($output, $expected);
    }

    public function testFlightResult()
    {
        $data = json_decode(file_get_contents('tests/messages/flightSearchNormal.json'), true);
        $result = $data["flightResultSet"][0];
        $output = new Flights\FlightResult($result, $this->context);

        $this->assertEquals($output->context, $this->context);
        $this->assertEquals(
            $output->totalFare,
            new Common\Price(Array('amount' => 94.9136, 'currency' => 'EUR'))
        );
        $this->assertEquals(
            $output->breakdown,
            Array(
                Array(
                    "passengerFare" => Array(
                        "fare" => 89.9136,
                        "quantity" => 1,
                        "ticketDesignators" => Array(),
                        "type" => "ADT",
                    )
                )
            )
        );

        $this->assertEquals(
            $output->combinations,
            Array(
                "801_0_0" => new Flights\Combination($result["combinations"][0], $output)
            )
        );
    }

    public function testFlightSearchEmpty()
    {
        $data = json_decode(file_get_contents('tests/messages/flightSearchEmpty.json'), true);
        $processor = new Common\PostProcessor('searchFlight', $this->context);
        $output = $processor->process($data);
        $expected = Array();

        $this->assertEquals($output, $expected);
    }

}
