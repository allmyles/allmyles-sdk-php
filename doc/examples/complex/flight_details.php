<?php

$flight = reset($flights->get()[1]->combinations);
var_dump($flight->getDetails()->get());
