<?php

ini_set('max_execution_time', 300);
ini_set('error_reporting', E_ALL | E_STRICT);
ini_set('date.timezone', 'Europe/Berlin');

require_once '../ANN/Loader.php';

try
{
  $objNetwork = ANN_Network::loadFromFile('strings.dat');
}
catch(Exception $e)
{
	print "\nCreating a new one...";

	$objClassification = new ANN_Classification(2);
	
	$objClassification->addClassifier('german');
	
	$objClassification->addClassifier('english');
	
	$objClassification->saveToFile('classifiers_strings.dat');

	$objNetwork = new ANN_Network(1, 8, 2);

	$objStringValues = new ANN_StringValue(15);
	
	$objStringValues->saveToFile('input_strings.dat');
	
	$objValues = new ANN_Values;
  
  $objValues->train()
  					->input($objStringValues->getInputValue('Hallo Welt'))
  					->output($objClassification->getOutputValue('german'))
  					->input($objStringValues->getInputValue('Hello World'))
  					->output($objClassification->getOutputValue('english'));
  
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

$objNetwork->setValues($objValues);

$objNetwork->train();

$objNetwork->saveToFile('strings.dat');

$objNetwork->printNetwork();

$arrOutputs = $objNetwork->getOutputs();

foreach($arrOutputs as $arrOutput)
	print_r($objClassification->getRealOutputValue($arrOutput));
