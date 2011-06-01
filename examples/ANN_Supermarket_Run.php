<?php

ini_set('max_execution_time', 1200);
ini_set('precision', '5');

require_once('../ANN/Loader.php');

use ANN\Network;
use ANN\InputValue;
use ANN\OutputValue;
use ANN\Values;

try
{
  $network = Network::loadFromFile('ANN_Supermarket_Training.dat');
}
catch(Exception $e)
{
	die('Network not found.');
}

$temperature = InputValue::loadFromFile('input_temperature.dat'); // Temperature

$humidity = InputValue::loadFromFile('input_humidity.dat'); // Humidity

$quantity = OutputValue::loadFromFile('output_quantity.dat'); // Quantity of sold articles

try
{
  $objValues = Values::loadFromFile('values_supermarket.dat');
}
catch(Exception $e)
{
  die('Loading of values failed');
}


$network->setValues($objValues);

print_r($outputs = $network->getOutputs());

foreach($outputs as $output)
  foreach($output as $value)
    print $quantity->getRealOutputValue($value).'<br>';

