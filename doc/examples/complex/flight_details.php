<?php

$flight = reset($flights[0]->combinations);
var_dump($flight->getDetails()->get());
