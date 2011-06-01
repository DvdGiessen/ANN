<?php

ini_set('max_execution_time', 1200);
ini_set('precision', '5');

require_once('../ANN/Loader.php');

use ANN\Network;
use ANN\InputValue;
use ANN\OutputValue;
use ANN\Values;
use ANN\NetworkGraph;

try
{
  $objNetwork = Network::loadFromFile('icecreams.dat');
}
catch(Exception $e)
{
  die('Network not found');
}

try
{
  $objTemperature = InputValue::loadFromFile('input_temperature.dat'); // Temperature in Celsius

  $objHumidity    = InputValue::loadFromFile('input_humidity.dat');    // Humidity percentage

  $objIcecream    = OutputValue::loadFromFile('output_quantity.dat');  // Quantity of sold ice-creams
}
catch(Exception $e)
{
  die('Error loading value objects');
}

try
{
  $objValues = Values::loadFromFile('values_icecreams.dat');
}
catch(Exception $e)
{
  die('Loading of values failed');
}

$objValues->input( // input values appending the loaded ones
                 $objTemperature->getInputValue(17),
                 $objHumidity->getInputValue(12)
                 )
          ->input(
                 $objTemperature->getInputValue(31),
                 $objHumidity->getInputValue(42)
                 )
          ->input(
                 $objTemperature->getInputValue(31),
                 $objHumidity->getInputValue(34)
                 )
          ->input(
                 $objTemperature->getInputValue(34),
                 $objHumidity->getInputValue(21)
                 );

$objNetwork->setValues($objValues);

$arrOutputs = $objNetwork->getOutputs();

/*
foreach($arrOutputs as $arrOutput)
  foreach($arrOutput as $floatOutput)
    print $objIcecream->getRealOutputValue($floatOutput). '<br />';
*/

$objImage = new NetworkGraph($objNetwork);

$objImage->printImage();
