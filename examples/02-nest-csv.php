<?php

include "../utils.php";
include "../src/ObjectTranspose.php";

$transpose = new ObjectTranspose();

$csv_array = read_csv("../sample_data/example.csv");
array_to_table($csv_array, "ORIGINAL SHAPE");

$response = $transpose->nestArrayOfObjects($csv_array);

pp($response, 0, "NESTED SHAPE");
exit;



