<?php

require_once '../ANN/Loader.php';
 
try
{
  $objNetwork = ANN_Network::loadFromFile('xor.dat');
}
catch(Exception $e)
{
  die('Network not found');
}
 
try
{
  $objValues = ANN_Values::loadFromFile('values_xor.dat');
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