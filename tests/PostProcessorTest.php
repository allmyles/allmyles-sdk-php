<?php
namespace Allmyles;

class PostProcessorTest extends \PHPUnit_Framework_TestCase
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

    public function testFlightSearchEmpty()
    {
        $data = json_decode(file_get_contents('tests/messages/flightSearchEmpty.json'), true);
        $processor = new Common\PostProcessor('searchFlight', $this->context);
        $output = $processor->process($data);
        $expected = Array();

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

    public function flightDetailsProvider()
    {
        return Array(
            Array(
                json_decode(file_get_contents('tests/messages/flightDetailsLcc.json'), true),
                Array(
                    'baggageTiers' => Array(
                        Array(
                            'max_quantity' => 0,
                            'max_weight' => 0.0,
                            'price' => new Common\Price(Array('amount' => 0.0, 'currency' => null)),
                            'tier' => '0'
                        ),
                        Array(
                            'max_quantity' => 1,
                            'max_weight' => 15.0,
                            'price' => new Common\Price(Array('amount' => 54.4, 'currency' => 'EUR')),
                            'tier' => '1'
                        )
                    ),
                    'fields' => Array(
                        'field1' => Array ('per_person' => true, 'required' => true),
                        'field2' => Array ('per_person' => true, 'required' => false),
                        'field3' => Array ('per_person' => false, 'required' => true),
                        'field4' => Array ('per_person' => false, 'required' => false)
                    ),
                    'options' => Array(
                        'seatSelectionAvailable' => false,
                        'travelfusionPrepayAvailable' => false
                    ),
                    'price' => new Common\Price(Array('amount' => 94.9136, 'currency' => 'EUR')),
                    'rulesLink' => 'http://www.ryanair.com/en/terms-and-conditions',
                    'surcharge' => new Common\Price(Array('amount' => 0.0, 'currency' => 'EUR'))
                )
            ),
            Array(
                json_decode(file_get_contents('tests/messages/flightDetailsTraditional.json'), true),
                Array(
                    'baggageTiers' => Array(),
                    'fields' => Array(),
                    'options' => Array(),
                    'price' => new Common\Price(Array('amount' => 112.7, 'currency' => 'EUR')),
                    'rulesLink' => null,
                    'surcharge' => new Common\Price(Array('amount' => 0.0, 'currency' => 'EUR'))
                )
            ),
        );
    }

    /**
     * @dataProvider flightDetailsProvider
     */
    public function testFlightDetails($data, $expected)
    {
        $processor = new Common\PostProcessor('getFlightDetails', $this->context);
        $output = $processor->process($data);

        $this->assertEquals($output, $expected);
    }

    public function bookFlightProvider()
    {
        return Array(
            Array(null, true),
            Array(
                json_decode(file_get_contents('tests/messages/flightBookTraditional.json'), true),
                Array(
                    'bookingReferenceId' => 'req-2115ded0dca54061b48614b078bdea67',
                    'contactInfo' => Array(
                        'address' => Array(
                            'city' => 'Budapest',
                            'countryCode': 'HU',
                            'line1': 'Váci út 13-14',
                            'line2': null,
                            'line3': null
                        ),
                        'email' => 'ccc@gmail.com',
                        'name' => 'Kovacs Gyula',
                        'phone' => Array(
                            'areaCode' => '30',
                            'countryCode' => '36',
                            'number' => '1234567'
                        )
                    ),
                    'lastTicketingDate' => new \DateTime("2014-07-15T23:59:59Z"),
                    'pnr' => '6GEHCY'
                )
            ),
        );
    }

    /**
     * @dataProvider bookFlightProvider
     */
    public function testBookFlight($data, $expected)
    {
        $processor = new Common\PostProcessor('bookFlight', $this->context);
        $output = $processor->process($data);

        $this->assertEquals($output, $expected);
    }
}
