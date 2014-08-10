<?php

$search_query = new Allmyles\Hotels\SearchQuery('LON', '2014-09-10', '2014-09-15', 1);
$hotels = $allmyles->searchHotel($search_query)->get();
var_dump($hotels);