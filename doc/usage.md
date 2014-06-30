# General

## Initializing the client:

```php
require 'Client.php';

$allmyles = new Allmyles\Client('https://example.com/v2.0', 'api-key');
```

# Flights

## Search

Flight searches are async requests—you send a request, the API starts
processing it (and returns an HTTP 202 status code), and you have to retry the
same request until the API returns something other than an HTTP 202,
signifying the completion of your request.

You will generally want to handle these retries manually so prevent a long
running request frorm blocking your web server (as seen in the
[Manual Asynchronosity example](#manual-asynchronosity)), but if you don't
have any concerns about that, you can defer handling asyncronosity to the
SDK; see the [Automatic Asynchronosity example](#automatic-asynchronosity).

### Manual Asynchronosity

```php
$search_data = Array(
    'fromLocation' => 'BUD',
    'toLocation' => 'LON',
    'departureDate' => '2015-01-01T00:00:00Z',
    'persons' => [Array('passengerType' => 'ADT', 'quantity' => 1)]
);

$flights = $allmyles->searchFlight($search_data);

while ($flights->incomplete) {$flights = $flights->retry(5);};

var_dump($flights->get());
```

### Automatic Asynchronosity

```php
$search_data = Array(
    'fromLocation' => 'BUD',
    'toLocation' => 'LON',
    'departureDate' => '2015-01-01T00:00:00Z',
    'persons' => [Array('passengerType' => 'ADT', 'quantity' => 1)]
);

$flights = $allmyles->searchFlight($search_data, false);

var_dump($flights->get());
```

## Details

```php
// Successful search response assumed to be saved in $flights

$flight = reset($flights->get()[0]->combinations);

var_dump($flight->getDetails()->get());
```

# Masterdata

## Search

```php
$locations = $allmyles->searchLocations(Array('keyword' => 'LON'));

var_dump($locations->get());
```
