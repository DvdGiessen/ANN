<?php

ini_set('max_execution_time', 90);

require_once '../ANN/Loader.php';

use ANN\Network;
use ANN\InputValue;
use ANN\OutputValue;
use ANN\Values;

try
{
  $objNetwork = Network::loadFromFile('icecreams.dat');
}
catch(Exception $e)
{
  print 'Creating a new one...';
 
  $objNetwork = new Network(2, 5, 1);
 
 
  $objTemperature = new InputValue(-15, 50); // Temperature in Celsius
 
  $objTemperature->saveToFile('input_temperature.dat');
 
 
  $objHumidity    = new InputValue(0, 100);  // Humidity percentage
 
  $objHumidity->saveToFile('input_humidity.dat');
 
 
  $objIcecream    = new OutputValue(0, 300); // Quantity of sold ice-creams
 
  $objIcecream->saveToFile('output_quantity.dat');
 
 
  $objValues = new Values;
 
  $objValues->train()
            ->input(
                   $objTemperature->getInputValue(20),
                   $objHumidity->getInputValue(10)
                   )
            ->output(
                   $objIcecream->getOutputValue(20)
                   )
            ->input(
                   $objTemperature->getInputValue(30),
                   $objHumidity->getInputValue(40)
                   )
            ->output(
                   $objIcecream->getOutputValue(90)
                   )
            ->input(
                   $objTemperature->getInputValue(32),
                   $objHumidity->getInputValue(30)
                   )
            ->output(
                   $objIcecream->getOutputValue(70)
                   )
            ->input(
                   $objTemperature->getInputValue(33),
                   $objHumidity->getInputValue(20)
                   )
            ->output(
                   $objIcecream->getOutputValue(75)
                   );
 
  $objValues->saveToFile('values_icecreams.dat');
 
  unset($objValues);
  unset($objTemperature);
  unset($objHumidity);
  unset($objIcecream);
}
 
try
{
  $objTemperature = InputValue::loadFromFile('input_temperature.dat'); // Temperature in Celsius
 
  $objHumidity    = InputValue::loadFromFile('input_humidity.dat'); // Humidity percentage
 
  $objIcecream    = OutputValue::loadFromFile('output_quantity.dat'); // Quantity of sold ice-creams
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
 
$objNetwork->setValues($objValues); // to be called as of version 2.0.6
 
$boolTrained = $objNetwork->train();
 
print ($boolTrained)
        ? 'Network trained'
        : 'Network not trained completely. Please re-run the script';
 
$objNetwork->saveToFile('icecreams.dat');
 
$objNetwork->printNetwork();
