<?php

ini_set('max_execution_time', 60);
ini_set('error_reporting', E_ALL | E_STRICT);
ini_set('date.timezone', 'Europe/Berlin');

require_once '../ANN/Loader.php';

use ANN\Network;
use ANN\Values;

try
{
  $objNetwork = Network::loadFromFile('xor.dat');
}
catch(Exception $e)
{
	print "\nCreating a new one...";

	$objNetwork = new Network(2, 4, 1);

  $objValues = new Values;

  $objValues->train()
            ->input(0, 0)->output(0)
            ->input(0, 1)->output(1)
            ->input(1, 0)->output(1)
            ->input(1, 1)->output(0);

  $objValues->saveToFile('values_xor.dat');
  
  unset($objValues);
}

try
{
  $objValues = Values::loadFromFile('values_xor.dat');
}
catch(Exception $e)
{
  die('Loading of values failed');
}

$objNetwork->setValues($objValues);

$objNetwork->train();

$objNetwork->saveToFile('xor.dat');

$objNetwork->printNetwork();
