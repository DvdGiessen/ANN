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

$outputs = array(
	array(0),
	array(0),
	array(0),
	array(1)
);

for ($i = 0; $i < 10000; $i++) {
	$j = Maths::random(0, 3);
	$network->setInputs($inputs[$j]);
	$network->train(0.5, $outputs[$j]);	
}

$network->save($filename);
?>
