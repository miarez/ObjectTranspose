<?php
require_once "../utils.php";
require_once "../src/ObjectTranspose.php";

$object = read_json("../sample_data/example.json");
pp($object, 0, "ORIGINAL OBJECT");



$out = (new ObjectTranspose())
    ->transpose(
        $object, #ABCFE (implied original shape)
        "BCDAE" # transform order
);


pp($out, 1, "TRANSPOSED OBJECT");


