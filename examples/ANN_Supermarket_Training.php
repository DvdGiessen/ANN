<?php

ini_set('error_reporting', E_ALL | E_STRICT);
ini_set('max_execution_time', 1200);
ini_set('precision', '5');
ini_set('xdebug.auto_trace', 'On');
ini_set('date.timezone', 'Europe/Berlin');


require_once '../ANN/ANN_Network.php';

try
{
  $network = ANN_Network::loadFromFile();
}
catch(Exception $e)
{
	print "\nCreating a new one...";
	
	$network = new ANN_Network(2,8,1);

	$network->setOutputType('lineary');

  $temperature = new ANN_InputValue(-15, 50); // Temperature
  
  $temperature->saveToFile('input_temperature.dat');
  
  unset($temperature);

  $humidity = new ANN_InputValue(0, 100); // Humidity

  $humidity->saveToFile('input_humidity.dat');

  unset($humidity);

  $quantity = new ANN_OutputValue(0, 300); // Quantity of sold articles

  $quantity->saveToFile('output_quantity.dat');

  unset($quantity);
}

$temperature = ANN_InputValue::loadFromFile('input_temperature.dat'); // Temperature

$humidity = ANN_InputValue::loadFromFile('input_humidity.dat'); // Humidity

$quantity = ANN_InputValue::loadFromFile('output_quantity.dat'); // Quantity of sold articles

$inputs = array(
	array($temperature->GetInputValue(12), $humidity->GetInputValue(22)),
	array($temperature->GetInputValue(17), $humidity->GetInputValue(19)),
	array($temperature->GetInputValue(20), $humidity->GetInputValue(10)),
	array($temperature->GetInputValue(22), $humidity->GetInputValue(9)),
	array($temperature->GetInputValue(30), $humidity->GetInputValue(40)),
	array($temperature->GetInputValue(32), $humidity->GetInputValue(30)),
	array($temperature->GetInputValue(33), $humidity->GetInputValue(20))
);

print_r($inputs);

$outputs = array(
	array(0.32),
	array(0.45),
	array(0.23),
	array(0.15),
	array(0.11),
	array(0.78),
	array(0.55)
);

print_r($outputs);


$network->setInputs($inputs);

$network->setOutputs($outputs);

print '#'. $network->train() / 60 .' minutes of training';

// $network->saveToFile();

print_r($outputs = $network->getOutputs());

print 'Loops:' . $network->getTotalLoops();

$network->printNetwork();

?>
