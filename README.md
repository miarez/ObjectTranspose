# Object Transpose 

A hacky deeply nested object manipulator I wrote in my junior days. Suitable for inspiration, not production.

## Important Note:

- This code was originally written February 2022 (2 years ago from posting this)
- It exists because of a company-wide limitation that everything had to be written in PHP
- It was written as a mental-masturbation of sorts, just to see if anything like this even made sense
- Various parts of this code have gone into future projects I have created that very closely resemble the xArray package in python
- I have not re-written any of the code, I haven't cleaned up any of my newbie mistakes
- Lastly, I can't tell if this is extremely redundant or absolute brilliant. I'm uploading this because its an interesting idea, and that's it!

## Why this even exists:

When doing quick & dirty data manipulation in traditional scripting languages you often find yourself ripping through an array and creating deeply nested objects 
```php
$hold_object = [];
foreach($csv_array as $index=>$row)
{
    $hold_object[$row["date"][$row["country"]][$row["source"]]]["clicks"]++;
    $hold_object[$row["date"][$row["country"]][$row["source"]]]["revenue"] += $row["ppc"];
}
```
These deeply nested objects are then a nightmare to traverse through if you want to for example create a ratio column
```php
foreach($hold_object as $date=>$value){
    foreach($value as $country=>$sub_value){
        foreach($sub_value as $source=>$sub_sub_value){
            $hold_object[$date][$country][$source]["cost_per_click"] = $sub_sub_value["ppc"] / $sub_sub_value["clicks"];
        }
    }
}
```
You find your quick & dirty scripts riddled with deeply nested intermediary objects and loops within loops. 

With time, you realize its extremely easy to make a mistake.

So I made a little helper class!

```php
new ObjectTranspose()
```

## How it works:

### 01- Basic Tuple Nesting 

Given a tuple:
```php
$tuple = [
    "Country"       => "Canada",
    "Province"      => "Quebec",
    "City"          => "Montreal",
    "Population"    => "100,000"
];
```
Applying the nestObject method
```php
 $transpose->nestObject($tuple);
```
Will get you a nested representation of the tuple
```json
{
  "Canada": {
    "Quebec": {
      "Montreal": "100,000"
    }
  }
}
```
The order of the properties of the tuple determine the property's nest level


### 02 - CSV Nesting 

In PHP, a CSV can be thought of as an array of tuples
```php
$array_of_tuples = [
    [
        "Country" => "Canada",
        "Province" => "Quebec",
        "City" => "Montreal",
        "Population" => "100,000"
    ],
    [
        "Country" => "Canada",
        "Province" => "BC",
        "City" => "Vancouver",
        "Population" => "80,000"
    ],
    [
        "Country" => "Canada",
        "Province" => "Quebec",
        "City" => "Quebec City",
        "Population" => "50,000"
    ]
];
```
Meaning that we can apply the same logic as above for an array of tuples
```php
$transpose->nestArrayOfObjects($csv_array)
```
To receive a nested representation of the contents of the CSV
```json
{
    "Canada": {
        "Quebec": {
            "Montreal": "100000",
            "Quebec City": "50000"
        },
        "BC": {
            "Vancouver": "80000"
        }
    }
}
```

### 03 - Un-Nesting Deeply Nested Objects

Nothing stops us from taking a deeply nested object

key->key->key->key->value (n-dimensions deep)
```php
# Applying unNest
$transpose->unNest($object)
```
and getting back a CSV representation of that nested object
```csv
col0 col1 col2 col3 col4
usa clicks Jan valid 50
usa clicks Jan total 100
```
Note: This only works for uniform objects

### 04- Transposing!

#### Here is where it gets cool!
we can think of deeply nested objects in this representation:
- {key->}(n)->value 
- allowing us to assign a letter for each nest-level (ABCDE...)
```json
{
  //A
    "usa": {
        //B
        "clicks": {
            //C
            "Jan": {
              //D      //E               
              "valid": 50,
              "total": 100
            },
```
Now you can "swap" nest level "A" with nest level "B", or any other levels!
```php
$out = (new ObjectTranspose())
    ->transpose(
        $object, #ABCFE (implied original shape)
        "BCDAE" # transform order
);
```
To get the output:
```json
{
  // original B
    "clicks": {
        // original C
        "Jan": {
            //original D
            "valid": {
               //original A
                "usa": 50,
                "ca": 5000
            },
```

### 05 - Taking things too far

### Operations on leaf nodes of deeply nested object

Say you receive a deeply nested key->value object like this:
```json
{
  "usa": {
    "clicks": {
      "Jan": {
        "valid": 50,
        "total": 100
      },
    },
...
```
And you are tasked with figuring out something like the change month-over-month in the ratio of total clicks of relative to valid clicks (as well as revenue) by country

No problem! Assuming you've already defined some lambda functions somewhere...

```php
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

```
You can rip through your nested object and perform lambda functions on the leaf nodes of the object!
```php
$out = (new ObjectTranspose())
        ->leafArithmeticOperation($object, "valid", "total", "RATIO", $divide)
        ->removeLeafNodes($object, ["valid", "total"])
        ->reduceBallastLeaf($object)
        ->leafArithmeticOperation($object, "Feb", "Jan", "RATIO", $divide)
        ->removeLeafNodes($object, ["Feb", "Jan"])
        ->reduceBallastLeaf($object)
        ->transpose($object, "BAC");
```
Chain a couple functions together, and voila! 

```json
{
    "clicks": {
        "usa": 1.4,
        "ca": 0.09999999999999999
    },
    "revenue": {
        "usa": 1.1111111111111112,
        "ca": 0.1443
    }
}
```

Example code 05-transform.php goes into greater detail regarding what some of these various methods actually do / what their utility is

```php
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
```