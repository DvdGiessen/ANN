<?php

require_once '../ANN/Loader.php';

use ANN\Network;
use ANN\Values;

try
{
  $objNetwork = Network::loadFromFile('complex.dat');
}
catch(Exception $e)
{
  die('Network not found');
}
 
try
{
  $objValues = Values::loadFromFile('values_complex.dat');
}
catch(Exception $e)
{
  die('Loading of values failed');
}
 
$objNetwork->setValues($objValues);
 
print_r($objNetwork->getOutputs());
