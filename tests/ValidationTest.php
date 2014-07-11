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
            Array(2, 2, 2),
            Array(9, 0, 0),
            Array(3, 0, 3)
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
            Array(0, 1, 0),
            Array(-1, 0, 0),
            Array(0, 1, 1),
            Array(10, 0, 0), // can't be more than 6 passengers total
            Array(10, -1, 0),
            Array(9, 0, 1),
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
}
