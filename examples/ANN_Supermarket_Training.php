<?php

ini_set('error_reporting', E_ALL | E_STRICT);
ini_set('max_execution_time', 400);
ini_set('precision', '6');
// ini_set('xdebug.auto_trace', 'On');
ini_set('date.timezone', 'Europe/Berlin');


require_once '../ANN/ANN_Loader.php';

try
{
  $network = ANN_Network::loadFromFile();
}
catch(Exception $e)
{
	print "\nCreating a new one...";
	
	$network = new ANN_Network(1, 8, 1);

	$network->setBackpropagationAlgorithm(ANN_Network::ALGORITHM_ILR);
	
  $temperature = new ANN_InputValue(-15, 50); // Temperature
  
  $temperature->saveToFile('input_temperature.dat');
  
  $humidity = new ANN_InputValue(0, 100); // Humidity

  $humidity->saveToFile('input_humidity.dat');

  $quantity = new ANN_OutputValue(0, 300); // Quantity of sold articles

  $quantity->saveToFile('output_quantity.dat');

  $objValues = new ANN_Values;

  $objValues->train()
            ->input(
                $temperature->GetInputValue(10),
                $humidity->GetInputValue(10)
                )
            ->output(
                $quantity->GetOutputValue(10)
                )
            ->input(
                $temperature->GetInputValue(20),
                $humidity->GetInputValue(20)
                )
            ->output(
                $quantity->GetOutputValue(20)
                )
            ->input(
                $temperature->GetInputValue(30),
                $humidity->GetInputValue(30)
                )
            ->output(
                $quantity->GetOutputValue(30)
                );
                
  $objValues->saveToFile('values_supermarket.dat');

  unset($objValues);
  unset($quantity);
  unset($humidity);
  unset($temperature);
}

// $network->logWeightsToFile('supermarket_weights.log.csv');
// $network->logNetworkErrorsToFile('supermarket_error.log.csv');

// $network->setMomentum(1);

$network->setWeightDecayMode(FALSE);


$temperature = ANN_InputValue::loadFromFile('input_temperature.dat'); // Temperature

$humidity = ANN_InputValue::loadFromFile('input_humidity.dat'); // Humidity

$quantity = ANN_OutputValue::loadFromFile('output_quantity.dat'); // Quantity of sold articles

try
{
  $objValues = ANN_Values::loadFromFile('values_supermarket.dat');
}
catch(Exception $e)
{
  die('Loading of values failed');
}

$network->setValues($objValues);

$network->setOutputErrorTolerance(0.01);

// $network->logNetworkErrorsToFile('network_errors.csv');

$network->setLearningRate(0.3);

$network->train();

$network->saveToFile();

$network->printNetwork();


// $img = new ANN_NetworkGraph($network);

// $img->saveToFile('network.png');

?>
