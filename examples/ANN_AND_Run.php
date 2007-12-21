<?php

ini_set('max_execution_time', 300);

require_once('../ANN/ANN_Network.php');

try
{
$network = ANN_Network::loadFromFile('and.dat');
}
catch(Exception $e)
{
	print "\nNetwork not found. Creating a new one...";
	
	$network = new ANN_Network;
}

$inputs = array(
	array(0, 0),
	array(0, 1),
	array(1, 0),
	array(1, 1)
);

$network->setInputs($inputs);

// $network->setOutputType();

print_r($network->getOutputs() );

?>
