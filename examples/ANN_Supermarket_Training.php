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
  $network = Network::loadFromFile();
}
catch(Exception $e)
{
	print "\nCreating a new one...";
	
	$network = new Network(1, 8, 1);

  $temperature = new InputValue(-15, 50); // Temperature
  
  $temperature->saveToFile('input_temperature.dat');
  
  $humidity = new InputValue(0, 100); // Humidity

  $humidity->saveToFile('input_humidity.dat');

  $quantity = new OutputValue(0, 300); // Quantity of sold articles

  $quantity->saveToFile('output_quantity.dat');

  $objValues = new Values;

  $objValues->train()
            ->input(
                $temperature->GetInputValue(10),
                $humidity->GetInputValue(10)
                )
            ->output(
                $quantity->GetOutputValue(10)
                )
            ->input(
                $temperature->GetInputValue(20),
                $humidity->GetInputValue(20)
                )
            ->output(
                $quantity->GetOutputValue(20)
                )
            ->input(
                $temperature->GetInputValue(30),
                $humidity->GetInputValue(30)
                )
            ->output(
                $quantity->GetOutputValue(30)
                );
                
  $objValues->saveToFile('values_supermarket.dat');

  unset($objValues);
  unset($quantity);
  unset($humidity);
  unset($temperature);
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

$network->train();

$network->saveToFile();

$network->printNetwork();
