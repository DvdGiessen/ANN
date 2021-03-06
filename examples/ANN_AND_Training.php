<?php

ini_set('max_execution_time', 300);

require_once '../ANN/Loader.php';

use ANN\Network;
use ANN\Values;

try
{
  $network = Network::loadFromFile('and.dat');
}
catch(Exception $e)
{
	print "\nCreating a new one...";

	$network = new Network(2, 4, 1);

  $objValues = new Values;

  $objValues->train()
            ->input(0, 0)->output(0)
            ->input(0, 1)->output(0)
            ->input(1, 0)->output(0)
            ->input(1, 1)->output(1);

  $objValues->saveToFile('values_and.dat');

  unset($objValues);
}

try
{
  $objValues = Values::loadFromFile('values_and.dat');
}
catch(Exception $e)
{
  die('Loading of values failed');
}

$network->setValues($objValues);

$network->train();

$network->saveToFile('and.dat');

$network->printNetwork();
