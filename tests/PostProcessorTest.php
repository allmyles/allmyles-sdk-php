<?php
namespace Allmyles;

class SearchPostProcessorTest extends \PHPUnit_Framework_TestCase
{
    protected $context;
    protected $flightSearchResponse;
    protected $flightSearchProcessor;
    protected $flightSearchResult;

    protected function setUp()
    {
        $this->context = new Context($this, null);
        date_default_timezone_set('UTC');

        $this->flightSearchResponse = json_decode(file_get_contents('tests/messages/flightSearchNormal.json'), true);
        $this->flightSearchProcessor = new Common\PostProcessor('searchFlight', $this->context);
        $this->flightSearchResult = $this->flightSearchProcessor->process($this->flightSearchResponse);
    }

    public function testFlightSearchNormal()
    {
        $data = $this->flightSearchResponse;
        $output = $this->flightSearchResult;
        $expected = Array(
            new Flights\FlightResult($data["flightResultSet"][0], $this->context),
            new Flights\FlightResult($data["flightResultSet"][1], $this->context)
        );

        $this->assertEquals($output, $expected);
    }

    public function testFlightResult()
    {
        $result = $this->flightSearchResponse["flightResultSet"][0];
        $output = $this->flightSearchResult[0];

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
            Array("801_0_0" => new Flights\Combination($result["combinations"][0], $output))
        );
    }

    public function testCombination()
    {
        $combination = $this->flightSearchResponse["flightResultSet"][0]["combinations"][0];
        $output = $this->flightSearchResult[0]->combinations["801_0_0"];

        $this->assertEquals($output->context, $this->context);
        $this->assertEquals(
            $output->flightResult,
            $this->flightSearchResult[0]
        );
        $this->assertEquals($output->bookingId, '801_0_0');
        $this->assertEquals($output->providerType, 'TravelFusionProvider');
        $this->assertEquals(
            $output->legs,
            Array(
                new Flights\Leg($combination["firstLeg"], $output),
                new Flights\Leg($combination["returnLeg"], $output)
            )
        );
        $this->assertEquals(
            $output->serviceFee,
            new Common\Price(Array('amount' => 5.0, 'currency' => 'EUR'))
        );
    }

    public function testLeg()
    {
        $leg = $this->flightSearchResponse["flightResultSet"][0]["combinations"][0]["firstLeg"];
        $output = $this->flightSearchResult[0]->combinations["801_0_0"]->legs[0];

        $this->assertEquals($output->context, $this->context);
        $this->assertEquals(
            $output->combination, $this->flightSearchResult[0]->combinations["801_0_0"]
        );
        $this->assertEquals($output->length, new \DateInterval('PT2H35M'));
        $this->assertEquals(
            $output->segments,
            Array(new Flights\Segment($leg["flightSegments"][0], $output))
        );
    }

    public function testSegment()
    {
        $segment = $this->flightSearchResponse["flightResultSet"][0]["combinations"][0]["firstLeg"]["flightSegments"][0];
        $output = $this->flightSearchResult[0]->combinations["801_0_0"]->legs[0]->segments[0];

        $this->assertEquals($output->context, $this->context);
        $this->assertEquals(
            $output->leg,
            $this->flightSearchResult[0]->combinations["801_0_0"]->legs[0]
        );
        $this->assertEquals($output->availableSeats, 0);
        $this->assertEquals($output->cabin, "economy");
        $this->assertEquals($output->flightNumber, "8446");
        $this->assertEquals($output->airline, "FR");
        $this->assertEquals(
            $output->departure, new Flights\Stop($segment["departure"], $output)
        );
        $this->assertEquals(
            $output->arrival, new Flights\Stop($segment["arrival"], $output)
        );
    }

    public function testStop()
    {
        $output = $this->flightSearchResult[0]->combinations["801_0_0"]->legs[0]->segments[0]->departure;
        $this->assertEquals($output->context, $this->context);
        $this->assertEquals($output->airport, 'BUD');
        $this->assertEquals($output->terminal, null);
        $this->assertEquals($output->time, new \DateTime("2014-12-24T14:25:00"));
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
