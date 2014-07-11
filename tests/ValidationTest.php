<?php
namespace Allmyles;

class ValidationTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        date_default_timezone_set('UTC');
    }

    public function successfulIATACodeProvider()
    {
        return Array(
            Array('BUD', 'LON'),
            Array('bud', 'LON'),
            Array('bUd', 'loN'),
            Array('bud', 'lon')
        );
    }

    public function failingIATACodeProvider()
    {
        return Array(
            Array('BU', 'LON'),
            Array('BUD', 'LO4'),
            Array('BUD', 'BUD'),
            Array('BUD', 'lonn'),
            Array('', 'LON'),
            Array('BUD', null),
            Array(1, 'LON'),
            Array('BUD', 'LÓN')
        );
    }

    /**
     * @dataProvider successfulIATACodeProvider
     */
    public function testSuccessfulIATACodes($from, $to)
    {
        new Flights\SearchQuery($from, $to, '2014-12-24T00:00:00Z');
    }

    /**
     * @dataProvider failingIATACodeProvider
     * @expectedException \Allmyles\Exceptions\ValidationException
     */
    public function testFailingIATACodes($from, $to)
    {
        new Flights\SearchQuery($from, $to, '2014-12-24T00:00:00Z');
    }

    public function successfulPassengersProvider()
    {
        return Array(
            Array(1, null, null),
            Array(1, 1, null),
            Array(1, 1, 1),
            Array(3, 3, 3),
            Array(9, 0, 0),
            Array(5, 0, 4)
        );
    }

    public function failingPassengersProvider()
    {
        return Array(
            Array('1', null, null),
            Array(1.0, null, null),
            Array(1, 0, '1'),
            Array(1, true, null),
            Array(0, 0, 0),
            Array(-1, 0, 0),
            Array(0, 1, 1),
            Array(7, 0, 0), // can't be more than 6 passengers total
            Array(7, -1, 0),
            Array(5, 0, 1),
            Array(1, 0, 2), // must have more adults than infants
            Array(null, null, null)
        );
    }

    /**
     * @dataProvider successfulPassengersProvider
     */
    public function testSuccessfulPassengers($adt, $chd, $inf)
    {
        $query = new Flights\SearchQuery('BUD', 'LON', '2014-12-24T00:00:00Z');
        $query->addPassengers($adt, $chd, $inf);
    }

    /**
     * @dataProvider failingPassengersProvider
     * @expectedException \Allmyles\Exceptions\ValidationException
     */
    public function testFailingPassengers($adt, $chd, $inf)
    {
        $query = new Flights\SearchQuery('BUD', 'LON', '2014-12-24T00:00:00Z');
        $query->addPassengers($adt, $chd, $inf);
    }

    public function successfulProviderProvider() // :D
    {
        $query = new Flights\SearchQuery('BUD', 'LON', '2014-12-24T00:00:00Z');
        return Array(
            Array($query::PROVIDER_ALL),
            Array($query::PROVIDER_TRADITIONAL),
            Array($query::PROVIDER_LOWCOST)
        );
    }

    public function failingProviderProvider()
    {
        return Array(
            Array('FakeProvider'),
            Array(1),
            Array(null),
            Array(true)
        );
    }

    /**
     * @dataProvider successfulProviderProvider
     */
    public function testSuccessfulProvider($provider)
    {
        $query = new Flights\SearchQuery('BUD', 'LON', '2014-12-24T00:00:00Z');
        $query->addProviderFilter($provider);
    }

    /**
     * @dataProvider failingProviderProvider
     * @expectedException \Allmyles\Exceptions\ValidationException
     */
    public function testFailingProvider($provider)
    {
        $query = new Flights\SearchQuery('BUD', 'LON', '2014-12-24T00:00:00Z');
        $query->addProviderFilter($provider);
    }

    public function successfulAirlinesProvider()
    {
        return Array(
            Array('BA'),
            Array('ba'),
            Array('Ba'),
            Array('W6'),
            Array(Array('BA')),
            Array(Array('ba', 'w6')),
            Array(Array('BA', 'W6'))
        );
    }

    public function failingAirlinesProvider()
    {
        return Array(
            Array(null),
            Array(1),
            Array(''),
            Array('B'),
            Array('BAA'),
            Array('B-'),
            Array(Array()),
            Array(Array('BA', '')),
            Array(Array('B', 'BA')),
            Array(Array('BA', 'BAA')),
            Array(Array('B-', 'BA')),
            Array(Array('BA', 1))
        );
    }

    /**
     * @dataProvider successfulAirlinesProvider
     */
    public function testSuccessfulAirlines($airlines)
    {
        $query = new Flights\SearchQuery('BUD', 'LON', '2014-12-24T00:00:00Z');
        $query->addAirlineFilter($airlines);
    }

    /**
     * @dataProvider failingAirlinesProvider
     * @expectedException \Allmyles\Exceptions\ValidationException
     */
    public function testFailingAirlines($airlines)
    {
        $query = new Flights\SearchQuery('BUD', 'LON', '2014-12-24T00:00:00Z');
        $query->addAirlineFilter($airlines);
    }
}
