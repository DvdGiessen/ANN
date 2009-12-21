<?php

ini_set('max_execution_time', 1200);
ini_set('precision', '5');

require_once('../ANN/ANN_Network.php');

try
{
$network = ANN_Network::loadFromFile('icecreams.dat');
}
catch(Exception $e)
{
	print "\nNetwork not found.";
}

// print_r($network); exit;

$temperature = ANN_InputValue::loadFromFile('input_temperature.dat'); // Temperature

$humidity = ANN_InputValue::loadFromFile('input_humidity.dat'); // Humidity

$icecream = ANN_OutputValue::loadFromFile('output_quantity.dat'); // Ice-Cream

$inputs = array(
//	array($temperature->GetInputValue(21), $humidity->GetInputValue(35)),
	array($temperature->GetInputValue(20), $humidity->GetInputValue(10)),
	array($temperature->GetInputValue(30), $humidity->GetInputValue(40)),
	array($temperature->GetInputValue(32), $humidity->GetInputValue(30)),
	array($temperature->GetInputValue(33), $humidity->GetInputValue(20))
);

$network->setInputs($inputs);

print_r($outputs = $network->getOutputs());

foreach($outputs as $output)
  print $icecream->GetRealOutputValue($output). '<br>';

$img = new ANN_NetworkGraph($network);

$img->printImage();

?>
