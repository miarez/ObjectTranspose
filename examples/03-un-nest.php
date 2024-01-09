<?php

include "../utils.php";
include "../src/ObjectTranspose.php";

$transpose = new ObjectTranspose();
$object = read_json("../sample_data/example.json");

pp($object, 0, "ORIGINAL SHAPE");

$out = $transpose->unNest($object);
/**
Output comes back as:
col0  col1 col2 col3 col4
usa clicks Jan valid 50
usa clicks Jan total 100
...
 */
array_to_table($out, "UN-NESTED TABLE/CSV");

