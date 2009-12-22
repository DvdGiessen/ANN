<?php

ini_set('max_execution_time', 300);

require_once '../ANN/Loader.php';

try
{
	$objNetwork = ANN_Network::loadFromFile('and.dat');
}
catch(Exception $e)
{
	print "\nCreating a new one...";
	
	$objNetwork = new ANN_Network;
}

$arrInputs = array(
	array(0, 0),
	array(0, 1),
	array(1, 0),
	array(1, 1)
);

$arrOutputs = array(
	array(0),
	array(0),
	array(0),
	array(1)
);

$objNetwork->setInputs($arrInputs);

$objNetwork->

$objNetwork->setOutputs($arrOutputs);

$objNetwork->train();

$objNetwork->saveToFile('and.dat');
