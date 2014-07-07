<?php

$flight = reset($flights->get()[0]->combinations);
var_dump($flight->getDetails()->get());
