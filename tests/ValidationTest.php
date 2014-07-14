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
            Array(9, 0, 7),
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
            Array(9, 1, 0),
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
    
    public function SuccessfulBookingProvider()
    {
        return Array(
            Array('Mr','Janos','Kovacs','1988-01-12','ADT','aAa@gmail.com', array('2016-09-03','12345678','HU','Passport')),
            Array('Mrs','Janos','kovacs','1992-03-19','adt','aaa@gmail.com', array('2016-09-03','12345678','H4','PASSPORT')),
            Array('Ms','janos','Kovacs','1991-12-03','aDt','aaa@gmAil.com', array('2016-09-03','12345678','44','passport')),
            Array('MR','Janos','Kovacs','2005-01-12','chd','aAa@gmail.com', array('2016-09-03','12345678','HU','Passport')),
            Array('mr','Janos','Kovacs','2014-01-12','inf','aAa@gmail.com', array('2016-09-03','12345678','HU','Passport'))
        );
    }

    public function FailingBookingProvider()
    {
        return Array(
            Array('','Janos','Kovacs','1988-01-12','ADT','aAa@gmail.com', array('2016-09-03','12345678','HU','Passport')),
            Array('M','Janos','Kovacs','1988-01-12','ADT','aAa@gmail.com', array('2016-09-03','12345678','HU','Passport')),
            Array(1,'Janos','Kovacs','1988-01-12','ADT','aAa@gmail.com', array('2016-09-03','12345678','HU','Passport')),

            Array('Mr','','Kovacs','1988-01-12','ADT','aAa@gmail.com', array('2016-09-03','12345678','HU','Passport')),
            Array('Mr',1,'Kovacs','1988-01-12','ADT','aAa@gmail.com', array('2016-09-03','12345678','HU','Passport')),

            Array('Mr','Janos','','1988-01-12','ADT','aAa@gmail.com', array('2016-09-03','12345678','HU','Passport')),
            Array('Mr','Janos',1,'1988-01-12','ADT','aAa@gmail.com', array('2016-09-03','12345678','HU','Passport')),

            Array('Mr','Janos','Kovacs','1988-00-12','ADT','aAa@gmail.com', array('2016-09-03','12345678','HU','Passport')),
            Array('Mr','Janos','Kovacs','19a8-01-12','ADT','aAa@gmail.com', array('2016-09-03','12345678','HU','Passport')),
            Array('Mr','Janos','Kovacs','1988-01-00','ADT','aAa@gmail.com', array('2016-09-03','12345678','HU','Passport')),
            Array('Mr','Janos','Kovacs','1988-13-12','ADT','aAa@gmail.com', array('2016-09-03','12345678','HU','Passport')),
            Array('Mr','Janos','Kovacs','1988-01-32','ADT','aAa@gmail.com', array('2016-09-03','12345678','HU','Passport')),
            Array('Mr','Janos','Kovacs','2988-01-12','ADT','aAa@gmail.com', array('2016-09-03','12345678','HU','Passport')),
            Array('Mr','Janos','Kovacs','1988-00-12','ADT','aAa@gmail.com', array('2016-09-03','12345678','HU','Passport')),
            Array('Mr','Janos','Kovacs','1988-01-2','ADT','aAa@gmail.com', array('2016-09-03','12345678','HU','Passport')),
            Array('Mr','Janos','Kovacs','1988-0112','ADT','aAa@gmail.com', array('2016-09-03','1034654','HU','Passport')),
            Array('Mr','Janos','Kovacs','19880112','ADT','aAa@gmail.com', array('2016-09-03','1034654','HU','Passport')),
            Array('Mr','Janos','Kovacs','1988-01412','ADT','aAa@gmail.com', array('2016-09-03','12345678','HU','Passport')),
            Array('Mr','Janos','Kovacs','','ADT','aAa@gmail.com', array('2016-09-03','1034654','HU','Passport')),
            Array('Mr','Janos','Kovacs',1,'ADT','aAa@gmail.com', array('2016-09-03','1034654','HU','Passport')),

            Array('Mr','Janos','Kovacs','2012-01-12','inf','aAa@gmail.com', array('2016-09-03','12345678','HU','Passport')),
            Array('Mr','Janos','Kovacs','2002-01-12','chd','aAa@gmail.com', array('2016-09-03','12345678','HU','Passport')),

            Array('Mr','Janos','Kovacs','1988-01-12','','aAa@gmail.com', array('2016-09-03','12345678','HU','Passport')),
            Array('Mr','Janos','Kovacs','1988-01-12','A','aAa@gmail.com', array('2016-09-03','12345678','HU','Passport')),
            Array('Mr','Janos','Kovacs','1988-01-12',1,'aAa@gmail.com', array('2016-09-03','12345678','HU','Passport')),

            Array('Mr','Janos','Kovacs','1988-01-12','ADT','', array('2016-09-03','12345678','HU','Passport')),
            Array('Mr','Janos','Kovacs','1988-01-12','ADT',1, array('2016-09-03','12345678','HU','Passport')),

            Array('Mr','Janos','Kovacs','1988-01-12','ADT','aAa@gmail.com', array('20a6-09-03','12345678','HU','Passport')),
            Array('Mr','Janos','Kovacs','1988-01-12','ADT','aAa@gmail.com', array('2016009-03','12345678','HU','Passport')),
            Array('Mr','Janos','Kovacs','1988-01-12','ADT','aAa@gmail.com', array('2016-9-03','12345678','HU','Passport')),
            Array('Mr','Janos','Kovacs','1988-01-12','ADT','aAa@gmail.com', array('2016-13-03','12345678','HU','Passport')),
            Array('Mr','Janos','Kovacs','1988-01-12','ADT','aAa@gmail.com', array('2016-09-00','12345678','HU','Passport')),
            Array('Mr','Janos','Kovacs','1988-01-12','ADT','aAa@gmail.com', array('2016-09-33','12345678','HU','Passport')),
            Array('Mr','Janos','Kovacs','1988-01-12','ADT','aAa@gmail.com', array('','12345678','HU','Passport')),
            Array('Mr','Janos','Kovacs','1988-01-12','ADT','aAa@gmail.com', array(1,'12345678','HU','Passport')),
            Array('Mr','Janos','Kovacs','1988-01-12','ADT','aAa@gmail.com', array('2013-09-03','12345678','HU','Passport')),

            Array('Mr','Janos','Kovacs','1988-01-12','ADT','aAa@gmail.com', array('2016-09-03','','HU','Passport')),
            Array('Mr','Janos','Kovacs','1988-01-12','ADT','aAa@gmail.com', array('2016-09-03',1,'HU','Passport')),

            Array('Mr','Janos','Kovacs','1988-01-12','ADT','aAa@gmail.com', array('2016-09-03','1034654','HUB','Passport')),
            Array('Mr','Janos','Kovacs','1988-01-12','ADT','aAa@gmail.com', array('2016-09-03','1034654','','Passport')),
            Array('Mr','Janos','Kovacs','1988-01-12','ADT','aAa@gmail.com', array('2016-09-03','1034654',1,'Passport')),

            Array('Mr','Janos','Kovacs','1988-01-12','ADT','aAa@gmail.com', array('2016-09-03','1034654','HU','')),
            Array('Mr','Janos','Kovacs','1988-01-12','ADT','aAa@gmail.com', array('2016-09-03','1034654','HU',1))
        );
    }

    /**
     * @dataProvider SuccessfulBookingProvider
     */
    public function testSuccessfulBooking($prefix, $firstName, $lastName, $birthDate, $TypeCode, $email, $document)
    {
        $query = new Flights\BookQuery();
        $query->addPassengers(
        	Array(
                'namePrefix' => $prefix,
                'firstName' => $firstName,
                'lastName' => $lastName,
                'birthDate' => $birthDate,
                'passengerTypeCode' => $TypeCode,
                'email' => $email,
                'document' => $document
            )
        );
    }

    /**
     * @dataProvider FailingBookingProvider
     * @expectedException \Allmyles\Exceptions\ValidationException
     */
    public function testFailingBooking($prefix, $firstName, $lastName, $birthDate, $TypeCode, $email, $document)
    {
        $query = new Flights\BookQuery();
        $query->addPassengers(
            Array(
                'namePrefix' => $prefix,
                'firstName' => $firstName,
                'lastName' => $lastName,
                'birthDate' => $birthDate,
                'passengerTypeCode' => $TypeCode,
                'email' => $email,
                'document' => $document
            )
        );
    }
}
