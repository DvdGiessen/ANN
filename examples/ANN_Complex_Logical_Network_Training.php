<?php

ini_set('max_execution_time', 60);
ini_set('error_reporting', E_ALL | E_STRICT);
ini_set('date.timezone', 'Europe/Berlin');

require_once '../ANN/Loader.php';

use ANN\Network;
use ANN\Values;

try
{
  $network = Network::loadFromFile('complex.dat');
}
catch(Exception $e)
{
	print "\nCreating a new one...";

	$network = new Network(2, 4, 1);

  $objValues = new Values;

  $objValues->train()
            ->input(0, 0, 0, 0)->output(1)
            ->input(0, 0, 0, 1)->output(0)
            ->input(0, 0, 1, 0)->output(0)
            ->input(0, 0, 1, 1)->output(1)
            ->input(0, 1, 0, 0)->output(0)
            ->input(0, 1, 0, 1)->output(0)
            ->input(0, 1, 1, 0)->output(0)
            ->input(0, 1, 1, 1)->output(1)
            ->input(1, 0, 0, 0)->output(1)
            ->input(1, 0, 0, 1)->output(0)
            ->input(1, 0, 1, 0)->output(0)
            ->input(1, 0, 1, 1)->output(1)
            ->input(1, 1, 0, 0)->output(1)
            ->input(1, 1, 0, 1)->output(0)
            ->input(1, 1, 1, 0)->output(0)
            ->input(1, 1, 1, 1)->output(1)
						;

  $objValues->saveToFile('values_complex.dat');
  
  unset($objValues);
}

try
{
  $objValues = Values::loadFromFile('values_complex.dat');
}
catch(Exception $e)
{
  die('Loading of values failed');
}

$network->setValues($objValues);

$network->train();

$network->saveToFile('complex.dat');

$network->printNetwork();
