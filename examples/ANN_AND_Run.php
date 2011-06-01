<?php

require_once '../ANN/Loader.php';

use ANN\Network;
use ANN\Values;

try
{
  $objNetwork = Network::loadFromFile('and.dat');
}
catch(Exception $e)
{
  die('Network not found');
}

try
{
  $objValues = Values::loadFromFile('values_and.dat');
}
catch(Exception $e)
{
  die('Loading of values failed');
}

$objValues->input(0, 1)  // input values appending the loaded ones
          ->input(1, 1)
          ->input(1, 0)
          ->input(0, 0)
          ->input(0, 1)
          ->input(1, 1);

$objNetwork->setValues($objValues);

print_r($objNetwork->getOutputs());
