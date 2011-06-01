<?php

require_once '../ANN/Loader.php';

use ANN\Network;
use ANN\Values;

try
{
  $objNetwork = new Network;

	$objValues = new Values;

	$objValues->train()
	          ->input(0, 0)->output(0)
	          ->input(0, 1)->output(1)
	          ->input(1, 0)->output(1)
	          ->input(1, 1)->output(0);

	$objNetwork->setValues($objValues);
	
	$objNetwork = $objNetwork->trainByHost('username', 'password', 'http://main.thwien.de/artificial_neural_network/source/examples/ANN_Server.php');

	if($objNetwork instanceof Network)
	  $objNetwork->printNetwork();
}
catch(Exception $e)
{
  die($e->getMessage());
}


// $network->saveToHost('username', 'password', 'http://main.thwien.de/artificial_neural_network/source/examples/ANN_Server.php');

// $network = ANN_Network::loadFromHost('username', 'password', 'http://main.thwien.de/artificial_neural_network/source/examples/ANN_Server.php');

