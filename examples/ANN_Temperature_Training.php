<?php

ini_set('max_execution_time', 90);

require_once '../ANN/Loader.php';

try
{
  $objNetwork = ANN_Network::loadFromFile('icecreams.dat');
}
catch(Exception $e)
{
  print 'Creating a new one...';
 
  $objNetwork = new ANN_Network(2, 5, 1);
 
 
  $objTemperature = new ANN_InputValue(-15, 50); // Temperature in Celsius
 
  $objTemperature->saveToFile('input_temperature.dat');
 
 
  $objHumidity    = new ANN_InputValue(0, 100);  // Humidity percentage
 
  $objHumidity->saveToFile('input_humidity.dat');
 
 
  $objIcecream    = new ANN_OutputValue(0, 300); // Quantity of sold ice-creams
 
  $objIcecream->saveToFile('output_quantity.dat');
 
 
  $objValues = new ANN_Values;
 
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
  $objTemperature = ANN_InputValue::loadFromFile('input_temperature.dat'); // Temperature in Celsius
 
  $objHumidity    = ANN_InputValue::loadFromFile('input_humidity.dat'); // Humidity percentage
 
  $objIcecream    = ANN_OutputValue::loadFromFile('output_quantity.dat'); // Quantity of sold ice-creams
}
catch(Exception $e)
{
  die('Error loading value objects');
}
 
try
{
  $objValues = ANN_Values::loadFromFile('values_icecreams.dat');
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
