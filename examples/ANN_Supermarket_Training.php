<?php

ini_set('error_reporting', E_ALL | E_STRICT);
ini_set('max_execution_time', 400);
ini_set('date.timezone', 'Europe/Berlin');

require_once '../ANN/Loader.php';

use ANN\Network;
use ANN\InputValue;
use ANN\OutputValue;
use ANN\Values;

try
{
  $objNetwork = Network::loadFromFile();
}
catch(Exception $e)
{
	print "\nCreating a new one...";
	
	$objNetwork = new Network(1, 8, 1);

  $objTemperature = new InputValue(-15, 50); // Temperature
  
  $objTemperature->saveToFile('input_temperature.dat');
  
  $objHumidity = new InputValue(0, 100); // Humidity

  $objHumidity->saveToFile('input_humidity.dat');

  $objQuantity = new OutputValue(0, 300); // Quantity of sold articles

  $objQuantity->saveToFile('output_quantity.dat');

  $objValues = new Values;

  $objValues->train()
            ->input(
                $objTemperature->GetInputValue(10),
                $objHumidity->GetInputValue(10)
                )
            ->output(
                $objQuantity->GetOutputValue(10)
                )
            ->input(
                $objTemperature->GetInputValue(20),
                $objHumidity->GetInputValue(20)
                )
            ->output(
                $objQuantity->GetOutputValue(20)
                )
            ->input(
                $objTemperature->GetInputValue(30),
                $objHumidity->GetInputValue(30)
                )
            ->output(
                $objQuantity->GetOutputValue(30)
                );
                
  $objValues->saveToFile('values_supermarket.dat');

  unset($objValues);
  unset($objHumidity);
  unset($objQuantity);
  unset($objTemperature);
}

$objTemperature = InputValue::loadFromFile('input_temperature.dat'); // Temperature

$objHumidity = InputValue::loadFromFile('input_humidity.dat'); // Humidity

$objQuantity = OutputValue::loadFromFile('output_quantity.dat'); // Quantity of sold articles

try
{
  $objValues = Values::loadFromFile('values_supermarket.dat');
}
catch(Exception $e)
{
  die('Loading of values failed');
}

$objNetwork->setValues($objValues);

$objNetwork->train();

$objNetwork->saveToFile();

$objNetwork->printNetwork();
