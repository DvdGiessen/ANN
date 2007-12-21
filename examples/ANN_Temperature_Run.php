<?php

ini_set('max_execution_time', 1200);
ini_set('precision', '5');

require_once('../ANN/ANN_Network.php');

try
{
$network = ANN_Network::loadFromFile('temperature.dat');
}
catch(Exception $e)
{
	print "\nNetwork not found. Creating a new one...";
	
	$network = new ANN_Network(1,40,40);
}

// print_r($network); exit;

$temperature = new ANN_InputValue(-15, 50); // Temperature

$humidity = new ANN_InputValue(0, 100); // Humidity

$icecream = new ANN_OutputValue(0, 300, 40); // Ice-Cream

$inputs = array(
//	array($temperature->GetInputValue(21), $humidity->GetInputValue(35)),
	array($temperature->GetInputValue(20), $humidity->GetInputValue(10)),
	array($temperature->GetInputValue(30), $humidity->GetInputValue(40)),
	array($temperature->GetInputValue(32), $humidity->GetInputValue(30)),
	array($temperature->GetInputValue(33), $humidity->GetInputValue(20))
);

$network->setInputs($inputs);

$network->setOutputType('binary');

print_r($outputs = $network->getOutputs());

foreach($outputs as $output)
  print $icecream->GetOutputValue($output). '<br>';


print 'Test:' . $icecream->GetOutputValue($icecream->GetOutputArray(20));

?>
