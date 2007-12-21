<?php

ini_set("max_execution_time", 300);

require_once "nn.php";

$filename = "xor.dat";

$network = Network::load($filename);

if ($network == null) {
	echo "\nNetwork not found. Creating a new one...";
	$network =& new Network(1, 10, 1);
}

$inputs = array(
	array(0, 0),
	array(0, 1),
	array(1, 0),
	array(1, 1)
);

for ($i = 0; $i < 4; $i++) {
	$network->setInputs($inputs[$i]);
	$network->activate();
	
	echo "\n";
	print_r($network->getOutputs() );
}

?>
