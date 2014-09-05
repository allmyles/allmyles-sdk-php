<?php

$search_query = new Allmyles\Hotels\SearchQuery('PAR', '2015-03-01', '2015-03-04', 1);
$hotels = $allmyles->searchHotel($search_query)->get();
var_dump($hotels);