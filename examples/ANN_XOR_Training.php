<?php

ini_set('max_execution_time', 300);
ini_set('error_reporting', E_ALL | E_STRICT);
ini_set('date.timezone', 'Europe/Berlin');

require_once '../ANN/ANN_Loader.php';

try
{
  $network = ANN_Network::loadFromFile('xor.dat');
}
catch(Exception $e)
{
	print "\nCreating a new one...";

	$network = new ANN_Network(2, 10, 1);

  $objValues = new ANN_Values;

  $objValues->train()
            ->input(0, 0)->output(0)
            ->input(0, 1)->output(1)
            ->input(1, 0)->output(1)
            ->input(1, 1)->output(0);

  $objValues->saveToFile('values_xor.dat');
  
  unset($objValues);
}

// $network->setBackpropagationAlgorithm(ANN_Network::ALGORITHM_RPROP);

// $network->setDynamicLearningRate(FALSE);

// $network->logNetworkErrorsToFile('xor_errors.log.csv');

try
{
  $objValues = ANN_Values::loadFromFile('values_xor.dat');
}
catch(Exception $e)
{
  die('Loading of values failed');
}

$network->setValues($objValues);

$network->train();

$network->saveToFile('xor.dat');

$network->printNetwork();

?>
