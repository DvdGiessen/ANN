<?php

ini_set('max_execution_time', 300);
ini_set('error_reporting', E_ALL | E_STRICT);
ini_set('date.timezone', 'Europe/Berlin');

require_once '../ANN/Loader.php';

use ANN\Network;
use ANN\Values;
use ANN\StringValue;

try
{
  $objNetwork = Network::loadFromFile('strings.dat');
}
catch(Exception $e)
{
	print "\nNetwork cannot be loaded";
}

try
{
  $objValues = Values::loadFromFile('values_strings.dat');
}
catch(Exception $e)
{
  die('Loading of values failed');
}

try
{
	$objStringValues = StringValue::loadFromFile('input_strings.dat');
}
catch(Exception $e)
{
  die('Loading of input values failed');
}


print_r($objStringValues->getInputValue('Helló Wórld!'));

print_r($objStringValues('Helló Wórld!'));

$objValues->input($objStringValues->getInputValue('HAllo Welt'));
$objValues->input($objStringValues->getInputValue('Hello World'));
$objValues->input($objStringValues->getInputValue('Hálló Wélt'));
$objValues->input($objStringValues->getInputValue('Hélló Wórld'));
$objValues->input($objStringValues->getInputValue('Hßllo Welt'));
$objValues->input($objStringValues->getInputValue('Hßlló Wórld'));
$objValues->input($objStringValues->getInputValue('Hallo Welt!'));
$objValues->input($objStringValues->getInputValue('Helló Wórld!'));

$objNetwork->setValues($objValues);

$objNetwork->printNetwork();
