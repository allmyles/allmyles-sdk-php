<?php

$search_query = new Allmyles\Cars\SearchQuery('LHR', '2015-03-01', '2015-03-04');
$hotels = $allmyles->searchCar($search_query)->get();
var_dump($hotels);