<?php

ini_set('max_execution_time', 1200);
ini_set('precision', '5');

require_once '../ANN/Loader.php';

try
{
	$objNetwork = ANN_Network::loadFromFile('icecreams.dat');
}
catch(Exception $e)
{
	print "\nCreating a new one...";
	
	$objNetwork = new ANN_Network(1, 8, 1);
	
	$objTemperature = new ANN_InputValue(-15, 50); // Temperature
	
	$objHumidity = new ANN_InputValue(0, 100); // Humidity
	
	$objIcecream = new ANN_OutputValue(0, 300); // Ice-Cream

	$objValues = new ANN_Values;

  $objValues->train()
            ->input($objTemperature->GetInputValue(20), $objHumidity->GetInputValue(10))
            	->output($objIcecream->GetOutputValue(20))
            ->input($objTemperature->GetInputValue(30), $objHumidity->GetInputValue(40))
            	->output($objIcecream->GetOutputValue(90))
            ->input($objTemperature->GetInputValue(32), $objHumidity->GetInputValue(30))
            	->output($objIcecream->GetOutputValue(70))
            ->input($objTemperature->GetInputValue(33), $objHumidity->GetInputValue(20))
            	->output($objIcecream->GetOutputValue(75));

  $objValues->saveToFile('values_xor.dat');
  
  unset($objValues);
}

try
{
  $objValues = ANN_Values::loadFromFile('values_xor.dat');
}
catch(Exception $e)
{
  die('Loading of values failed');
}

$objNetwork->setValues($objValues);

$objNetwork->train();

$objNetwork->saveToFile('icecreams.dat');
