<?php

ini_set('max_execution_time', 1200);
ini_set('precision', '5');

require_once '../ANN/ANN_Network.php';

try
{
$network = ANN_Network::loadFromFile('icecreams.dat');
}
catch(Exception $e)
{
	print "\nCreating a new one...";
	
	$network = new ANN_Network(2,8,1);
}

/*
$inputs = array(
	array(0, 0),
	array(1, 1),
	array(0, 1),
	array(1, 0)
);
*/

$temperature = new ANN_InputValue(-15, 50); // Temperature

$humidity = new ANN_InputValue(0, 100); // Humidity

$icecream = new ANN_OutputValue(0, 300); // Ice-Cream

$inputs = array(
	array($temperature->GetInputValue(20), $humidity->GetInputValue(10)),
	array($temperature->GetInputValue(30), $humidity->GetInputValue(40)),
	array($temperature->GetInputValue(32), $humidity->GetInputValue(30)),
	array($temperature->GetInputValue(33), $humidity->GetInputValue(20))
);

print_r($inputs);

/*
$outputs = array(
	array($icecream->GetInputValue(20)),
	array($icecream->GetInputValue(90)),
	array($icecream->GetInputValue(70)),
	array($icecream->GetInputValue(75))
);
*/

$outputs = array(
	array($icecream->GetOutputValue(20)),
	array($icecream->GetOutputValue(90)),
	array($icecream->GetOutputValue(70)),
	array($icecream->GetOutputValue(75))
);

print_r($outputs);


$network->setInputs($inputs);

$network->setOutputs($outputs);

print '#'. $network->train() / 60 .' minutes of training';

$network->saveToFile('icecreams.dat');

?>
