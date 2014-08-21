<?php

$search_query = new Allmyles\Hotels\SearchQuery('WAW', '2014-10-01', '2014-10-04', 1);
$hotels = $allmyles->searchHotel($search_query)->get();
var_dump($hotels);