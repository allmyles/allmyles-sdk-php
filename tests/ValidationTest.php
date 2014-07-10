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
}
