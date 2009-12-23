<?php

ini_set('max_execution_time', 300);
ini_set('error_reporting', E_ALL | E_STRICT);
ini_set('date.timezone', 'Europe/Berlin');

require_once '../ANN/Loader.php';

try
{
  $network = ANN_Network::loadFromFile('strings.dat');
}
catch(Exception $e)
{
	print "\nCreating a new one...";

	$network = new ANN_Network(1, 8, 2);

	$objStringValues = new ANN_StringValue(15);
	
	$objStringValues->saveToFile('input_strings.dat');
	
	$objValues = new ANN_Values;
  
  $objValues->train()
  					->input($objStringValues->getInputValue('Hallo Welt'))
  					->output(1, 0)
  					->input($objStringValues->getInputValue('Hello World'))
  					->output(0, 1);
  
  $objValues->saveToFile('values_strings.dat');
  
  unset($objValues);
}

try
{
  $objValues = ANN_Values::loadFromFile('values_strings.dat');
}
catch(Exception $e)
{
  die('Loading of values failed');
}

$network->setValues($objValues);

$network->train();

$network->saveToFile('strings.dat');

$network->printNetwork();
