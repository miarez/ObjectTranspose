<?php

include "../utils.php";
include "../src/ObjectTranspose.php";

$transpose = new ObjectTranspose();
$object = read_json("../sample_data/example.json");

######################### DEFINE YOUR OWN LAMBDA FUNCTIONS #######################################

# SINGLE VARIABLE STRING FUNCTIONS
$upper = fn($x) => strtoupper($x);
$lower = fn($x) => strtoupper($x);
$stripTags = fn($x) => strip_tags($x);

# TWO VARIABLE MATH OPERATION FUNCTIONS
$divide = fn($x, $y) => $x / $y;
$multiply = fn($x, $y) => $x * $y;
$add = fn($x, $y) => $x + $y;
$subtract = fn($x, $y) => $x - $y;

######################### DO YOUR TRANSFORMATIONS #######################################

pp($object, 0, "ORIGINAL SHAPE");

/** Create a "RATIO" column by apply a division lambda on the valid / total columns **/
$object = $transpose->leafArithmeticOperation($object, "valid", "total", "RATIO", $divide);
/**
An additional leaf node was created for the "RATIO" column
    "valid": 50,
    "total": 100,
    "RATIO": 0.5 ---> our new column!!!
 */

/** We only need ratio, so let's remove the valid and total leaf nodes */
$object = $transpose->removeLeafNodes($object, ["valid", "total"]);

/**
Now the data looks like this:
"usa": {
    "clicks": {
        "Jan": {
            "RATIO": 0.5 --> as you can see, RATIO has no other siblings, meaning its "key" is a bit redundant
        },
        "Feb": {
            "RATIO": 0.7
        }
    }, ...

We can remove this ballast node via the following method
 */
$object = $transpose->reduceBallastLeaf($object);
/** The following continues in the same pattern! */
$object = $transpose->leafArithmeticOperation($object, "Feb", "Jan", "RATIO", $divide);
$object = $transpose->removeLeafNodes($object, ["Feb", "Jan"]);
$object = $transpose->reduceBallastLeaf($object);
/** Finally, transpose into the shape we want! */
$object = $transpose->transpose($object, "BAC");

pp($object, 1, "TRANSFORMED SHAPE");



