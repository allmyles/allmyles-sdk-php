<?php
namespace Allmyles;

class TypeHintTest extends \PHPUnit_Framework_TestCase
{
    protected $context;

    protected function setUp()
    {
        date_default_timezone_set('UTC');
        $this->client = $this->getMockBuilder('Allmyles\Client')
                             ->setConstructorArgs(Array('http://localhost', 'api-key'))
                             ->setMethods(Array('sendRequest'))
                             ->getMock();
        $this->context = new Context($this->client, null);

        $this->flightSearchResponse = json_decode(file_get_contents('tests/messages/flightSearchNormal.json'), true);
        $this->flightSearchProcessor = new Common\PostProcessor('searchFlight', $this->context);
        $this->flightSearchResult = $this->flightSearchProcessor->process($this->flightSearchResponse);

    }

    public function successfulClientConstructProvider()
    {
        return Array(
            Array('http://localhost', 'api-key')
        );
    }

    public function failingClientConstructProvider()
    {
        return Array(
            Array(1, 'api-key'),
            Array(null, 'api-key'),
            Array('http://localhost', 1),
            Array('http://localhost', null)
        );
    }

    /**
* @dataProvider successfulClientConstructProvider
*/
    public function testSuccessfulClientConstruct($baseUrl, $authKey)
    {
        new Client($baseUrl, $authKey);
    }

    /**
* @dataProvider failingClientConstructProvider
* @expectedException \Allmyles\Exceptions\TypeHintException
*/
    public function testFailingClientConstruct($baseUrl, $authKey)
    {
        new Client($baseUrl, $authKey);
    }

    public function successfulSearchFlightProvider()
    {
        return Array(
            Array(new Flights\SearchQuery('BUD', 'LON', '2014-12-24T00:00:00Z'), true, 'sess'),
            Array(Array('BUD', 'LON', '2014-12-24T00:00:00Z', Array(Array('ADT', 1), Array('CHD', 0), Array('INF', 0))), null, null)
        );
    }

    public function failingSearchFlightProvider()
    {
        return Array(
            Array('param', true, 'sess'),
            Array(new \DateTime('2014-12-24T00:00:00Z'), true, 'sess'),
            Array(new Flights\SearchQuery('BUD', 'LON', '2014-12-24T00:00:00Z'), 1, 'sess'),
            Array(new Flights\SearchQuery('BUD', 'LON', '2014-12-24T00:00:00Z'), true, 1)
        );
    }

    /**
* @dataProvider successfulSearchFlightProvider
*/
    public function testSuccessfulSearchFlight($parameters, $async, $session)
    {
        $query = $this->client;
        $query->searchFlight($parameters, $async, $session);
    }

    /**
* @dataProvider failingSearchFlightProvider
* @expectedException \Allmyles\Exceptions\TypeHintException
*/
    public function testFailingSearchFlight($parameters, $async, $session)
    {
        $query = $this->client;
        $query->searchFlight($parameters, $async, $session);
    }

    public function successfulFlightDetailsProvider()
    {
        return Array(
            Array('id', 'sess'),
            Array('id', null)
        );
    }

    public function failingFlightDetailsProvider()
    {
        return Array(
            Array(1, 'sess'),
            Array('id', 1)
        );
    }

    /**
* @dataProvider successfulFlightDetailsProvider
*/
    public function testSuccessfulFlightDetails($bookingId, $session)
    {
        $query = $this->client;
        $query->getFlightDetails($bookingId, $session);
    }

    /**
* @dataProvider failingFlightDetailsProvider
* @expectedException \Allmyles\Exceptions\TypeHintException
*/
    public function testFailingFlightDetails($bookingId, $session)
    {
        $query = $this->client;
        $query->getFlightDetails($bookingId, $session);
    }

    public function successfulBookFlightProvider()
    {
        return Array(
            Array(Array('param'), 'sess'),
            Array(Array('param'), null)
        );
    }

    public function failingBookFlightProvider()
    {
        return Array(
            Array('param', 'sess'),
            Array(Array('param'), 1)
        );
    }

    /**
* @dataProvider successfulBookFlightProvider
*/
    public function testSuccessfulBookFlight($parameters, $session)
    {
        $query = $this->client;
        $query->bookFlight($parameters, $session);
    }

    /**
* @dataProvider failingBookFlightProvider
* @expectedException \Allmyles\Exceptions\TypeHintException
*/
    public function testFailingBookFlight($parameters, $session)
    {
        $query = $this->client;
        $query->bookFlight($parameters, $session);
    }

    public function successfulPayuPaymentProvider()
    {
        return Array(
            Array('id', 'sess'),
            Array('id', null)
        );
    }

    public function failingPayuPaymentProvider()
    {
        return Array(
            Array(1, 'sess'),
            Array('id', 1)
        );
    }

    /**
* @dataProvider successfulPayuPaymentProvider
*/
    public function testSuccessfulPayuPayment($payuId, $session)
    {
        $query = $this->client;
        $query->addPayuPayment($payuId, $session);
    }

    /**
* @dataProvider failingPayuPaymentProvider
* @expectedException \Allmyles\Exceptions\TypeHintException
*/
    public function testFailingPayuPayment($payuId, $session)
    {
        $query = $this->client;
        $query->addPayuPayment($payuId, $session);
    }

    public function successfulFlightTicketProvider()
    {
        return Array(
            Array('id', 'sess'),
            Array('id', null)
        );
    }

    public function failingFlightTicketProvider()
    {
        return Array(
            Array(1, 'sess'),
            Array('id', 1)
        );
    }

    /**
* @dataProvider successfulFlightTicketProvider
*/
    public function testSuccessfulFlightTicket($bookingId, $session)
    {
        $query = $this->client;
        $query->createFlightTicket($bookingId, $session);
    }

    /**
* @dataProvider failingFlightTicketProvider
* @expectedException \Allmyles\Exceptions\TypeHintException
*/
    public function testFailingFlightTicket($bookingId, $session)
    {
        $query = $this->client;
        $query->createFlightTicket($bookingId, $session);
    }

    public function successfulLocationsProvider()
    {
        return Array(
            Array(Array('param'), 'sess')
        );
    }

    public function failingLocationsProvider()
    {
        return Array(
            Array('param', 'sess'),
            Array(Array('param'), 1)
        );
    }

    /**
* @dataProvider successfulLocationsProvider
*/
    public function testSuccessfulLocations($parameters, $session)
    {
        $query = $this->client;
        $query->searchLocations($parameters, $session);
    }

    /**
* @dataProvider failingLocationsProvider
* @expectedException \Allmyles\Exceptions\TypeHintException
*/
    public function testFailingLocations($parameters, $session)
    {
        $query = $this->client;
        $query->searchLocations($parameters, $session);
    }

    public function successfulSearchConstructProvider()
    {
        return Array(
            Array('froml', 'tol', '2015-01-01', '2015-01-02'),
            Array('froml', 'tol', new \DateTime('2015-01-01'), new \DateTime('2015-01-02')),
            Array('froml', 'tol', '2015-01-01', null)
        );
    }

    public function failingSearchConstructProvider()
    {
        return Array(
            Array(1, 'tol', '2015-01-01', '2015-01-02'),
            Array('froml', 1, '2015-01-01', '2015-01-02'),
            Array('froml', 'tol', 1, '2015-01-02'),
            Array('froml', 'tol', '2015-01-01', 1),
        );
    }

    /**
* @dataProvider successfulSearchConstructProvider
*/
    public function testSuccessfulSearchConstruct($fromLocation, $toLocation, $departureDate, $returnDate)
    {
        new Flights\SearchQuery($fromLocation, $toLocation, $departureDate,$returnDate);
    }

    /**
* @dataProvider failingSearchConstructProvider
* @expectedException \Allmyles\Exceptions\TypeHintException
*/
    public function testFailingSearchConstruct($fromLocation, $toLocation, $departureDate, $returnDate)
    {
        new Flights\SearchQuery($fromLocation, $toLocation, $departureDate, $returnDate);
    }

    public function successfulProviderFilterProvider()
    {
        return Array(
            Array('providerType')
        );
    }

    public function failingProviderFilterProvider()
    {
        return Array(
            Array(1)
        );
    }

    /**
* @dataProvider successfulProviderFilterProvider
*/
    public function testSuccessfulProviderFilter($providerType)
    {
        $query = new Flights\SearchQuery('froml', 'tol', '2015-01-01', '2015-01-02');
        $query->addProviderFilter($providerType);
    }

    /**
* @dataProvider failingProviderFilterProvider
* @expectedException \Allmyles\Exceptions\TypeHintException
*/
    public function testFailingProviderFilter($providerType)
    {
        $query = new Flights\SearchQuery('froml', 'tol', '2015-01-01', '2015-01-02');
        $query->addProviderFilter($providerType);
    }

    public function successfulAirlineFilterProvider()
    {
        return Array(
            Array('al'),
            Array(Array('a1', 'a2', 'a3'))
            
        );
    }

    public function failingAirlineFilterProvider()
    {
        return Array(
            Array(1),
            Array(Array('airline1', 'airline2', 3))
        );
    }

    /**
* @dataProvider successfulAirlineFilterProvider
*/
    public function testSuccessfulAirlineFilter($airlines)
    {
        $query = new Flights\SearchQuery('froml', 'tol', '2015-01-01', '2015-01-02');
        $query->addAirlineFilter($airlines);
    }

    /**
* @dataProvider failingAirlineFilterProvider
* @expectedException \Allmyles\Exceptions\TypeHintException
*/
    public function testFailingAirlineFilter($airlines)
    {
        $query = new Flights\SearchQuery('froml', 'tol', '2015-01-01', '2015-01-02');
        $query->addAirlineFilter($airlines);
    }

    public function successfulPassengerProvider()
    {
        return Array(
            Array(1, 1, 1),
            Array(1, null, null)
        );
    }

    public function failingPassengerProvider()
    {
        return Array(
            Array('1', 1, 1),
            Array(1, '1', 1),
            Array(1, 1, '1')
        );
    }

    /**
* @dataProvider successfulPassengerProvider
*/
    public function testSuccessfulPassenger($adt, $chd, $inf)
    {
        $query = new Flights\SearchQuery('froml', 'tol', '2015-01-01', '2015-01-02');
        $query->addPassengers($adt, $chd, $inf);
    }

    /**
* @dataProvider failingPassengerProvider
* @expectedException \Allmyles\Exceptions\TypeHintException
*/
    public function testFailingPassenger($adt, $chd, $inf)
    {
        $query = new Flights\SearchQuery('froml', 'tol', '2015-01-01', '2015-01-02');
        $query->addPassengers($adt, $chd, $inf);
    }

    public function successfulBookProvider()
    {
        return Array(
            Array(new Flights\BookQuery(
                Array(
                    Array(
                        'namePrefix' => 'Mrs',
                        'firstName' => 'Janka',
                        'lastName' => 'Kovacs',
                        'birthDate' => '1993-04-03',
                        'passengerTypeCode' => 'ADT',
                        'email' => 'aaa@gmail.com',
                        'document' => Array(
                            'dateOfExpiry' => '2016-09-03',
                            'id' => '12345678',
                            'issueCountry' => 'HU',
                            'type' => 'Passport'
                        )
                    )
                ),
                Array(
                    'address' => Array(
                        'addressLine1' => 'Váci út 13-14',
                        'cityName' => 'Budapest',
                        'countryCode' => 'HU',
                        'zipCode' => '1234'
                    ),
                    'email' => 'ccc@gmail.com',
                    'name' => 'Kovacs Gyula',
                    'phone' => Array(
                        'areaCode' => '30',
                        'countryCode' => '36',
                        'phoneNumber' => '1234567'
                    )
                ),
                Array(
                    'address' => Array(
                        'addressLine1' => 'Váci út 13-14',
                        'cityName' => 'Budapest',
                        'countryCode' => 'HU',
                        'zipCode' => '1234'
                    ),
                    'email' => 'ccc@gmail.com',
                    'name' => 'Kovacs Gyula',
                    'phone' => Array(
                        'areaCode' => '30',
                        'countryCode' => '36',
                        'phoneNumber' => '1234567'
                    )
                ))
            ),
            Array(Array(
                Array(
                    Array(
                        'namePrefix' => 'Mrs',
                        'firstName' => 'Janka',
                        'lastName' => 'Kovacs',
                        'birthDate' => '1993-04-03',
                        'passengerTypeCode' => 'ADT',
                        'email' => 'aaa@gmail.com',
                        'document' => Array(
                            'dateOfExpiry' => '2016-09-03',
                            'id' => '12345678',
                            'issueCountry' => 'HU',
                            'type' => 'Passport'
                        )
                    )
                ),
                Array(
                    'address' => Array(
                        'addressLine1' => 'Váci út 13-14',
                        'cityName' => 'Budapest',
                        'countryCode' => 'HU',
                        'zipCode' => '1234'
                    ),
                    'email' => 'ccc@gmail.com',
                    'name' => 'Kovacs Gyula',
                    'phone' => Array(
                        'areaCode' => '30',
                        'countryCode' => '36',
                        'phoneNumber' => '1234567'
                    )
                ),
                Array(
                    'address' => Array(
                        'addressLine1' => 'Váci út 13-14',
                        'cityName' => 'Budapest',
                        'countryCode' => 'HU',
                        'zipCode' => '1234'
                    ),
                    'email' => 'ccc@gmail.com',
                    'name' => 'Kovacs Gyula',
                    'phone' => Array(
                        'areaCode' => '30',
                        'countryCode' => '36',
                        'phoneNumber' => '1234567'
                    )
                ))
            )
        );
    }

    public function failingBookProvider()
    {
        return Array(
            Array(new \DateTime('2014-01-01'))
        );
    }

    /**
* @dataProvider successfulBookProvider
*/
    public function testSuccessfulBook($parameters)
    {
        $query = reset($this->flightSearchResult[0]->combinations)->book($parameters);
    }

    /**
* @dataProvider failingBookProvider
* @expectedException \Allmyles\Exceptions\TypeHintException
*/
    public function testFailingBook($parameters)
    {
        $query = reset($this->flightSearchResult[0]->combinations)->book($parameters);
    }

    public function successfulAddPayuProvider()
    {
        return Array(
            Array('payuId')
        );
    }

    public function failingAddPayuProvider()
    {
        return Array(
            Array(1)
        );
    }

    /**
* @dataProvider successfulAddPayuProvider
*/
    public function testSuccessfulAddPayu($payuId)
    {
        $query = reset($this->flightSearchResult[0]->combinations)->addPayuPayment($payuId);
    }

    /**
* @dataProvider failingAddPayuProvider
* @expectedException \Allmyles\Exceptions\TypeHintException
*/
    public function testFailingAddPayu($payuId)
    {
        $query = reset($this->flightSearchResult[0]->combinations)->addPayuPayment($payuId);
    }

}