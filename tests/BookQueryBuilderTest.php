<?php
namespace Allmyles;

class BookQueryBuilderTest extends \PHPUnit_Framework_TestCase
{
    protected $passenger;
    protected $address;

    protected function setUp()
    {
        date_default_timezone_set('UTC');

        $this->address = Array(
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
        );
    }

    /**
     * @dataProvider passengerProvider
     */
    public function testBookQuerySimple($passengers, $expectedPassengers)
    {
        $query = new Flights\BookQuery($passengers, $this->address, $this->address);
        $query->setBookingId('801_0_1');

        $this->assertEquals(
            Array(
                'passengers' => $expectedPassengers,
                'billingInfo' => $this->address,
                'contactInfo' => $this->address,
                'bookingId' => '801_0_1'
            ),
            $query->getData()
        );
    }

    /**
     * @dataProvider passengerProvider
     */
    public function testBookQueryNoBillingInfo($passengers, $expectedPassengers)
    {
        $query = new Flights\BookQuery($passengers, $this->address);
        $query->setBookingId('801_0_1');

        $this->assertEquals(
            Array(
                'passengers' => $expectedPassengers,
                'billingInfo' => $this->address,
                'contactInfo' => $this->address,
                'bookingId' => '801_0_1'
            ),
            $query->getData()
        );
    }

    /**
     * @dataProvider passengerProvider
     */
    public function testBookQueryOneByOne($passengers, $expectedPassengers)
    {
        if (is_string(reset($passengers))) {
            $passengers = Array($passengers);
        };

        $query = new Flights\BookQuery();
        foreach ($passengers as $passenger) {
            $query->addPassengers($passenger);
        };
        $query->addContactInfo($this->address);
        $query->addBillingInfo($this->address);
        $query->setBookingId('801_0_1');

        $this->assertEquals(
            Array(
                'passengers' => $expectedPassengers,
                'billingInfo' => $this->address,
                'contactInfo' => $this->address,
                'bookingId' => '801_0_1'
            ),
            $query->getData()
        );
    }

    public function passengerProvider()
    {
        $passenger1 = Array(
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
        );

        $passenger2 = Array(
            'namePrefix' => 'Ms',
            'firstName' => 'Joli',
            'lastName' => 'Kovacs',
            'birthDate' => '2003-04-03',
            'passengerTypeCode' => 'CHD',
            'email' => 'aaa@gmail.com',
            'document' => Array(
                'dateOfExpiry' => '2016-09-03',
                'id' => '12345678',
                'issueCountry' => 'HU',
                'type' => 'Passport'
            )
        );

        $passenger3 = Array(
            'namePrefix' => 'Mr',
            'firstName' => 'Janos',
            'lastName' => 'Kovacs',
            'birthDate' => '2013-04-03',
            'passengerTypeCode' => 'INF',
            'email' => 'aaa@gmail.com',
            'document' => Array(
                'dateOfExpiry' => '2016-09-03',
                'id' => '12345678',
                'issueCountry' => 'HU',
                'type' => 'Passport'
            )
        );

        $expectedPassenger1 = Array(
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
            ),
            'gender' => 'FEMALE',
            'baggage' => 0
        );

        $expectedPassenger2 = Array(
            'namePrefix' => 'Ms',
            'firstName' => 'Joli',
            'lastName' => 'Kovacs',
            'birthDate' => '2003-04-03',
            'passengerTypeCode' => 'CHD',
            'email' => 'aaa@gmail.com',
            'document' => Array(
                'dateOfExpiry' => '2016-09-03',
                'id' => '12345678',
                'issueCountry' => 'HU',
                'type' => 'Passport'
            ),
            'gender' => 'FEMALE',
            'baggage' => 0
        );

        $expectedPassenger3 = Array(
            'namePrefix' => 'Mr',
            'firstName' => 'Janos',
            'lastName' => 'Kovacs',
            'birthDate' => '2013-04-03',
            'passengerTypeCode' => 'INF',
            'email' => 'aaa@gmail.com',
            'document' => Array(
                'dateOfExpiry' => '2016-09-03',
                'id' => '12345678',
                'issueCountry' => 'HU',
                'type' => 'Passport'
            ),
            'gender' => 'MALE',
            'baggage' => 0
        );

        return Array(
            Array($passenger1, Array($expectedPassenger1)),
            Array($passenger2, Array($expectedPassenger2)),
            Array($passenger3, Array($expectedPassenger3)),
            Array(
                Array($passenger3, $passenger1),
                Array($expectedPassenger3, $expectedPassenger1)
            ),
            Array(
                Array($passenger1, $passenger2, $passenger3),
                Array($expectedPassenger1, $expectedPassenger2, $expectedPassenger3)
            )
        );
    }
}
