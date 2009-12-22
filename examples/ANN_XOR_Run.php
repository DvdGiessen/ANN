<?php

ini_set('max_execution_time', 10);

require_once('../ANN/Loader.php');

try
{
$network = ANN_Network::loadFromFile('xor.dat');
}
catch(Exception $e)
{
	print "\nNetwork not found. Creating a new one...";
	
	$network = new ANN_Network;
}

try
{
  $objValues = ANN_Values::loadFromFile('values_xor.dat');
}
catch(Exception $e)
{
  die('Loading of values failed');
}

$objValues->input(0, 1)
          ->input(1, 1)
          ->input(1, 0)
          ->input(0, 0)
          ->input(0, 1)
          ->input(1, 1);

$network->setValues($objValues);

// $network->setOutputType();

$network->printNetwork();

$objNetworkGraph = new ANN_NetworkGraph($network);

$objNetworkGraph->saveToFile('network.png');
