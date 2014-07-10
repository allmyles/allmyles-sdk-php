<?php
namespace Allmyles;

class SearchQueryBuilderTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        date_default_timezone_set('UTC');
    }

    public function testSearchQueryOneway()
    {
        $query = new Flights\SearchQuery('BUD', 'LON', '2014-12-24T00:00:00Z');
        $query->addPassengers(1);

        $this->assertEquals(
            $query->getData(),
            Array(
                'fromLocation' => 'BUD',
                'toLocation' => 'LON',
                'departureDate' => '2014-12-24T00:00:00Z',
                'persons' => Array(
                    Array(
                        'passengerType' => 'ADT',
                        'quantity' => 1
                    ),
                    Array(
                        'passengerType' => 'CHD',
                        'quantity' => 0
                    ),
                    Array(
                        'passengerType' => 'INF',
                        'quantity' => 0
                    )
                )
            )
        );
    }

    public function testSearchQueryReturn()
    {
        $query = new Flights\SearchQuery('BUD', 'LON', '2014-12-24T00:00:00Z', '2014-12-29T00:00:00Z');
        $query->addPassengers(1);

        $this->assertEquals(
            $query->getData(),
            Array(
                'fromLocation' => 'BUD',
                'toLocation' => 'LON',
                'departureDate' => '2014-12-24T00:00:00Z',
                'returnDate' => '2014-12-29T00:00:00Z',
                'persons' => Array(
                    Array(
                        'passengerType' => 'ADT',
                        'quantity' => 1
                    ),
                    Array(
                        'passengerType' => 'CHD',
                        'quantity' => 0
                    ),
                    Array(
                        'passengerType' => 'INF',
                        'quantity' => 0
                    )
                )
            )
        );
    }

    public function testSearchQueryInfants()
    {
        $query = new Flights\SearchQuery('BUD', 'LON', '2014-12-24T00:00:00Z');
        $query->addPassengers(5, 0, 2);

        $this->assertEquals(
            $query->getData(),
            Array(
                'fromLocation' => 'BUD',
                'toLocation' => 'LON',
                'departureDate' => '2014-12-24T00:00:00Z',
                'persons' => Array(
                    Array(
                        'passengerType' => 'ADT',
                        'quantity' => 5
                    ),
                    Array(
                        'passengerType' => 'CHD',
                        'quantity' => 0
                    ),
                    Array(
                        'passengerType' => 'INF',
                        'quantity' => 2
                    )
                )
            )
        );
    }

    public function testSearchQueryFilters()
    {
        $query = new Flights\SearchQuery('BUD', 'LON', '2014-12-24T00:00:00Z');
        $query->addPassengers(1, 0, 0);
        $query->addProviderFilter($query::PROVIDER_LOWCOST);
        $query->addAirlineFilter('BA');

        $this->assertEquals(
            $query->getData(),
            Array(
                'fromLocation' => 'BUD',
                'toLocation' => 'LON',
                'departureDate' => '2014-12-24T00:00:00Z',
                'persons' => Array(
                    Array(
                        'passengerType' => 'ADT',
                        'quantity' => 1
                    ),
                    Array(
                        'passengerType' => 'CHD',
                        'quantity' => 0
                    ),
                    Array(
                        'passengerType' => 'INF',
                        'quantity' => 0
                    )
                ),
                'providerType' => 'OnlyLowCost',
                'preferredAirlines' => Array('BA')
            )
        );
        $this->assertNotNull($query::PROVIDER_LOWCOST);
        $this->assertNotNull($query::PROVIDER_TRADITIONAL);
        $this->assertNotNull($query::PROVIDER_ALL);
    }


    public function testSearchQueryMultipleFilters()
    {
        $query = new Flights\SearchQuery('BUD', 'LON', '2014-12-24T00:00:00Z');
        $query->addPassengers(1, 1, 1);
        $query->addPassengers(1, 1, 0);
        $query->addPassengers(1, 0, 0);
        $query->addProviderFilter($query::PROVIDER_LOWCOST);
        $query->addProviderFilter($query::PROVIDER_TRADITIONAL);
        $query->addProviderFilter($query::PROVIDER_TRADITIONAL);
        $query->addAirlineFilter('BA');
        $query->addAirlineFilter(['W6', 'FR']);
        $query->addAirlineFilter('BA');

        $this->assertEquals(
            $query->getData(),
            Array(
                'fromLocation' => 'BUD',
                'toLocation' => 'LON',
                'departureDate' => '2014-12-24T00:00:00Z',
                'persons' => Array(
                    Array(
                        'passengerType' => 'ADT',
                        'quantity' => 3
                    ),
                    Array(
                        'passengerType' => 'CHD',
                        'quantity' => 2
                    ),
                    Array(
                        'passengerType' => 'INF',
                        'quantity' => 1
                    )
                ),
                'providerType' => 'OnlyTraditional',
                'preferredAirlines' => Array('BA', 'W6', 'FR')
            )
        );
    }
}
