<?php

include "../utils.php";
include "../src/ObjectTranspose.php";

$transpose = new ObjectTranspose();

$tuple = [
    "Country"       => "Canada",
    "Province"      => "Quebec",
    "City"          => "Montreal",
    "Population"    => "100,000"
];

pp($tuple, 0, "ORIGINAL SHAPE");

$response = $transpose->nestObject($tuple);

pp($response, 0, "NESTED SHAPE");

