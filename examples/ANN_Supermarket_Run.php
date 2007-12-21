<?php

ini_set('max_execution_time', 1200);
ini_set('precision', '5');

require_once('../ANN/ANN_Network.php');

try
{
$network = ANN_Network::loadFromFile('ANN_Supermarket_Training.dat');
}
catch(Exception $e)
{
	die('Network not found.');
}

// print_r($network); exit;

$temperature = new ANN_InputValue(-15, 50); // Temperature

$humidity = new ANN_InputValue(0, 100); // Humidity

$quantity = new ANN_OutputLinearValue(0, 300); // Quantity of sold articles


$inputs = array(
//	array($temperature->GetInputValue(21), $humidity->GetInputValue(35)),
	array($temperature->GetInputValue(21), $humidity->GetInputValue(10)),
	array($temperature->GetInputValue(30), $humidity->GetInputValue(40)),
	array($temperature->GetInputValue(32), $humidity->GetInputValue(30)),
	array($temperature->GetInputValue(33), $humidity->GetInputValue(20))
);

$network->setInputs($inputs);

// $network->setOutputType('binary');

print_r($outputs = $network->getOutputs());

foreach($outputs as $output)
foreach($output as $value)
print $quantity->getRealOutputValue($value).'#';

?>
