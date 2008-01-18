<?php

/**
 * Artificial Neural Network - Version 2.0
 *
 * For updates and changes visit the project page at http://ann.thwien.de/
 *
 *
 *
 * <b>LICENCE</b>
 *
 * This source file is freely re-distributable, with or without modifications
 * provided the following conditions are met:
 * 
 * 1.	The source files must retain the copyright notice below, this list of
 *		conditions and the following disclaimer.
 *
 * 2.	The name of the author must not be used to endorse or promote products
 *		derived from this source file without prior written permission. For 
 *		written permission, please contact me.
 *
 * <b>DISCLAIMER</b>
 *
 * THIS SOFTWARE IS PROVIDED BY THE AUTHOR `AS IS'' AND
 * ANY EXPRESSED OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO,
 * THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A
 * PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE PHP
 * AUTHOR OR HIS CONTRIBUTORS BE LIABLE FOR ANY DIRECT,
 * INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION)
 * HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT,
 * STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED
 * OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * @author Eddy Young <jeyoung_at_priscimon_dot_com>
 * @author Thomas Wien <info_at_thwien_dot_de>
 * @version ANN Version 1.0 by Eddy Young
 * @version ANN Version 2.0 by Thomas Wien
 * @copyright Copyright (c) 2002 by Eddy Young
 * @copyright Copyright (c) 2007-08 by Thomas Wien
 * @package ANN
 */

require_once('ANN_Exception.php');
require_once('ANN_Math.php');
require_once('ANN_Neuron.php');
require_once('ANN_Layer.php');
require_once('ANN_Filesystem.php');
require_once('ANN_InputValue.php');
require_once('ANN_OutputValue.php');
require_once('ANN_NetworkGraph.php');
require_once('ANN_Logging.php');

/**
 * @package ANN
 * @access public
 */

class ANN_Network extends ANN_Filesystem
{

/**#@+
 * @ignore
 */
 
protected $outputLayer = array();
protected $hiddenLayers = array();
protected $inputs = null;
protected $outputs = null;
protected $countHiddenLayers = null;
protected $outputType = 'binary'; // binary or linear
protected $totalLoops = 0;
protected $numberOfHiddenLayers = null;
protected $numberOfHiddenLayersDec = null; // decremented value
protected $maxTrainingLoops;
protected $maxTrainingLoopsFactor = 230;
protected $numberEpoch = 0;
protected $loggingWeights = FALSE;
protected $loggingNetworkErrors = FALSE;
protected $trained = FALSE;
protected $trainingTime = 0; // Seconds
protected $objLoggingWeights = null;
protected $objLoggingNetworkErrors = null;
protected $dynamicLearningRate = TRUE;
private $networkErrorCurrent = 10;
private $networkErrorPrevious = 10;
public $momentum = 0.95;
public $learningRate = 0.5;
public $weightDecayMode = TRUE;
public $weightDecay = 0.05;
public $quickPropMode = TRUE;
public $quickPropMaxWeightChangeFactor = 2.25;
public $firstLoopOfTraining = TRUE;

/**#@-*/

// ****************************************************************************

/**
 * @param integer $numberOfHiddenLayers (Default: 1)
 * @param integer $numberOfNeuronsPerLayer  (Default: 10)
 * @param integer $numberOfOutputs  (Default: 1)
 * @uses ANN_Exception::__construct()
 * @uses calculateMaxTrainingLoops()
 * @uses createHiddenLayers()
 * @uses createOutputLayer()
 * @throws ANN_Exception
 */

public function __construct($numberOfHiddenLayers = 1, $numberOfNeuronsPerLayer = 10, $numberOfOutputs = 1)
{
  if(!is_integer($numberOfHiddenLayers) && $numberOfHiddenLayers < 2)
    throw new ANN_Exception('Constraints: $numberOfHiddenLayers must be a positiv integer >= 2');

  if(!is_integer($numberOfNeuronsPerLayer) && $numberOfNeuronsPerLayer < 1)
    throw new ANN_Exception('Constraints: $numberOfNeuronsPerLayer must be a positiv integer number > 1');

  if(!is_integer($numberOfOutputs) && $numberOfOutputs < 1)
    throw new ANN_Exception('Constraints: $numberOfOutputs must be a positiv integer number > 1');

	$this->createHiddenLayers($numberOfHiddenLayers, $numberOfNeuronsPerLayer);

	$this->createOutputLayer($numberOfOutputs);
	
	$this->numberOfHiddenLayers = $numberOfHiddenLayers;

  $this->numberOfHiddenLayersDec = $this->numberOfHiddenLayers - 1;
  
  $this->calculateMaxTrainingLoops();
}
	
// ****************************************************************************

/**
 * @param array $inputs
 */

public function setInputs($inputs)
{
  if(!is_array($inputs))
    throw new ANN_Exception('Constraints: $inputs should be an array');

  $this->inputs = $inputs;
  
  $this->numberEpoch = count($inputs);
  
  $this->nextIndexInputToTrain = 0;
}

// ****************************************************************************

/**
 * @param array $outputs
 * @uses ANN_Exception::__construct()
 * @uses detectOutputType()
 * @uses ANN_Layer::getNeuronsCount()
 * @throws ANN_Exception
 */

public function setOutputs($outputs)
{
  if(isset($outputs[0]) && is_array($outputs[0]))
    if(count($outputs[0]) != $this->outputLayer->getNeuronsCount())
      throw new ANN_Exception('Count of Outputs doesn\'t fit to number of outputs on instantiation of ANN_Network');

  $this->outputs = $outputs;
  
  $this->detectOutputType();
}

// ****************************************************************************

/**
 * @param array $inputs
 * @uses ANN_Layer::setInputs()
 */

protected function setInputsToTrain($inputs)
{
  $this->hiddenLayers[0]->setInputs($inputs);
}
	
// ****************************************************************************

/**
 * @return array
 * @uses activate()
 * @uses getCountInputs()
 * @uses ANN_Layer::getOutputs()
 * @uses setInputsToTrain()
 */

public function getOutputs()
{
  $returnOutputs = array();

  $countInputs = $this->getCountInputs();
  
	for ($i = 0; $i < $countInputs; $i++)
	{
    $this->setInputsToTrain($this->inputs[$i]);
      
    $this->activate();
	    
    switch($this->outputType)
    {
    case 'linear':
      $returnOutputs[] = $this->outputLayer->getOutputs();
      break;

    case 'binary':
      $returnOutputs[] = $this->outputLayer->getThresholdOutputs();
      break;
    }
  }

	return $returnOutputs;
}
	
// ****************************************************************************

/**
 * @param integer $numberOfHiddenLayers
 * @param integer $numberOfNeuronsPerLayer
 * @uses ANN_Layer::__construct()
 */

protected function createHiddenLayers($numberOfHiddenLayers, $numberOfNeuronsPerLayer)
{
	for ($i = 0; $i < $numberOfHiddenLayers; $i++)
		$this->hiddenLayers[] = new ANN_Layer($this, $numberOfNeuronsPerLayer);
}
	
// ****************************************************************************

/**
 * @param integer $numberOfOutputs
 * @uses ANN_Layer::__construct()
 */

protected function createOutputLayer($numberOfOutputs)
{
	$this->outputLayer = new ANN_Layer($this, $numberOfOutputs, TRUE);
}
	
// ****************************************************************************

/**
 * @uses ANN_Layer::setInputs()
 * @uses ANN_Layer::activate()
 * @uses ANN_Layer::getOutputs()
 */

protected function activate()
{
	for ($i = 0; $i < $this->numberOfHiddenLayersDec; $i++)
  {
		$this->hiddenLayers[$i]->activate();
			
		$this->hiddenLayers[$i + 1]->setInputs($this->hiddenLayers[$i]->getOutputs() );
	}
		
	$this->hiddenLayers[$i]->activate();
		
	$this->outputLayer->setInputs($this->hiddenLayers[$i]->getOutputs() );
		
	$this->outputLayer->activate();
}
	
// ****************************************************************************

/**
 * @return boolean
 * @uses ANN_Exception::__construct()
 * @uses setInputs()
 * @uses setOutputs()
 * @uses isTrainingComplete()
 * @uses setInputsToTrain()
 * @uses training()
 * @uses isEpoch()
 * @uses logWeights()
 * @uses logNetworkErrors()
 * @uses getNextIndexInputsToTrain()
 * @uses adjustLearningRate()
 * @throws ANN_Exception
 */

public function train()
{
  if(!$this->inputs)
    throw new ANN_Exception('No Inputs defined. Use ANN_Network::setInputs().');

  if(!$this->outputs)
    throw new ANN_Exception('No Outputs defined. Use ANN_Network::setOutputs().');

  if($this->isTrainingComplete())
    return 0;

  $starttime = date('U');
  
  $this->getNextIndexInputsToTrain(TRUE);

  $this->firstLoopOfTraining = TRUE;

  for ($i = 0; $i < $this->maxTrainingLoops; $i++)
  {
    $j = $this->getNextIndexInputsToTrain();

    $this->setInputsToTrain($this->inputs[$j]);

    $this->training($this->outputs[$j]);

    if($this->isEpoch())
    {
      if($this->loggingWeights)
        $this->logWeights();

      if($this->loggingNetworkErrors)
        $this->logNetworkErrors();

      if($this->isTrainingComplete())
        break;
        
      $this->adjustLearningRate();
    }

    $this->firstLoopOfTraining = FALSE;
  }

  $stoptime = date('U');

  $this->totalLoops += $i;

  $this->trainingTime += $stoptime - $starttime;
  
  $this->trained = $this->isTrainingComplete();
  
  return $this->trained;
}
	
// ****************************************************************************

/**
 * @param boolean $reset (Default: FALSE)
 * @return integer
 */

protected function getNextIndexInputsToTrain($reset = FALSE)
{
static $arrIndex = array();
static $index = -1;

if($reset)
{
  $arrIndex = array_keys($this->inputs);
  $index = -1;
  
  return;
}

$index++;

if(!isset($arrIndex[$index]))
{
  shuffle($arrIndex);
  $index = 0;
}

return $arrIndex[$index];
}
	
// ****************************************************************************

/**
 * @return integer
 */

public function getTotalLoops()
{
  return $this->totalLoops;
}

// ****************************************************************************

/**
 * @return boolean
 */

protected function isEpoch()
{
static $countLoop = 0;

$countLoop++;

if($countLoop >= $this->numberEpoch)
{
  $countLoop = 0;

  return TRUE;
}

return FALSE;
}

// ****************************************************************************

/**
 * Setting the learning rate disables dynamic learning rate automatically.
 *
 * @param float $learningRate (Default: 0.5) (0.1 .. 0.9)
 * @uses ANN_Exception::__construct()
 * @throws ANN_Exception
 */

public function setLearningRate($learningRate = 0.5)
{
  if(!is_float($learningRate))
    throw new ANN_Exception('$learningRate should be between 0.1 and 0.9');

  if($learningRate <= 0 || $learningRate >= 1)
    throw new ANN_Exception('$learningRate should be between 0.1 and 0.9');

  $this->learningRate = $learningRate;
  
  $this->dynamicLearningRate = FALSE;
}

// ****************************************************************************

/**
 * @param float $momentum (Default: 0.95) (0 .. 1)
 * @uses ANN_Exception::__construct()
 * @throws ANN_Exception
 */

public function setMomentum($momentum = 0.95)
{
  if(!is_float($momentum) && !is_integer($momentum))
    throw new ANN_Exception('$learningRate should be between 0 and 1');

  if($momentum <= 0 || $momentum > 1)
    throw new ANN_Exception('$learningRate should be between 0 and 1');

  $this->momentum = $momentum;
}

// ****************************************************************************

/**
 * @return boolean
 * @uses getOutputs()
 */

protected function isTrainingComplete()
{
  $outputs = $this->getOutputs();

  switch($this->outputType)
  {
  case 'linear':

    foreach($this->outputs as $key1 => $output)
      foreach($output as $key2 => $value)
        if(round($value, 2) != round($outputs[$key1][$key2], 2))
          return FALSE;

    return TRUE;
  break;

  case 'binary':

    foreach($this->outputs as $key1 => $output)
      foreach($output as $key2 => $value)
        if($value != $outputs[$key1][$key2])
          return FALSE;

    return TRUE;
  break;
  }
}
	
// ****************************************************************************

/**
 * @return integer
 */

protected function getCountInputs()
{
  if(isset($this->inputs) && is_array($this->inputs))
    return count($this->inputs);

  return 0;
}

// ****************************************************************************

/**
 * @param array $outputs
 * @uses activate()
 * @uses ANN_Layer::calculateHiddenDeltas()
 * @uses ANN_Layer::adjustWeights()
 * @uses ANN_Layer::calculateOutputDeltas()
 */

protected function training($outputs)
{
	$this->activate();
		
	$this->outputLayer->calculateOutputDeltas($outputs);
		
	$this->hiddenLayers[$this->numberOfHiddenLayersDec]->calculateHiddenDeltas($this->outputLayer);
		
	for ($i = $this->numberOfHiddenLayersDec; $i > 0; $i--)
		$this->hiddenLayers[$i - 1]->calculateHiddenDeltas($this->hiddenLayers[$i]);
		
	$this->outputLayer->adjustWeights();
		
	for ($i = $this->numberOfHiddenLayers; $i > 0; $i--)
		$this->hiddenLayers[$i - 1]->adjustWeights();
}

// ****************************************************************************

/**
 * @return string Filename
 */

protected static function getDefaultFilename()
{
  return preg_replace('/\.php$/', '.dat', basename($_SERVER['PHP_SELF']));
}

// ****************************************************************************

/**
 * @param string $type (Default:  'linear') (linear or binary)
 * @uses ANN_Exception::__construct()
 * @throws ANN_Exception
 */

protected function setOutputType($type = 'linear')
{
  settype($type, 'string');

  switch($type)
  {
  case 'linear':
  case 'binary':
    $this->outputType = $type;
    break;
    
  default:
    throw new ANN_Exception('$type must be "linear" or "binary"');
  }
}

// ****************************************************************************

/**
 * @uses getNetworkError()
 * @uses ANN_Layer::getNeurons()
 * @uses ANN_Neuron::getDelta()
 * @uses ANN_Neuron::getWeights()
 * @uses ANN_Layer::getNeurons()
 */

public function printNetwork()
{
  print "<table border=\"1\" style=\"background-color: #AAAAAA\" cellpadding=\"2\" cellspacing=\"0\">\n";

  print "<tr>\n";
  print "<td>Detected output type</td>\n";
  print "<td style=\"background-color: #CCCCCC\">"
        .$this->outputType
        ."</td>\n";
  print "</tr>\n";

  print "<tr>\n";
  print "<td>Quick propagation</td>\n";
  print "<td style=\"background-color: #CCCCCC\">"
        .(($this->quickPropMode) ? 'Yes' : 'No')
        ."</td>\n";
  print "</tr>\n";

  print "<tr>\n";
  print "<td>Momentum</td>\n";
  print "<td style=\"background-color: #CCCCCC\">"
        .$this->momentum
        ."</td>\n";
  print "</tr>\n";

  print "<tr>\n";
  print "<td>Learning rate</td>\n";
  print "<td style=\"background-color: #CCCCCC\">"
        .$this->learningRate
        ."</td>\n";
  print "</tr>\n";

  print "<tr>\n";
  print "<td>Weight decay</td>\n";
  print "<td style=\"background-color: #CCCCCC\">"
        .(($this->weightDecayMode) ? $this->weightDecay : 'Off')
        ."</td>\n";
  print "</tr>\n";

  print "<tr>\n";
  print "<td>Network error</td>\n";
  print "<td style=\"background-color: #CCCCCC\">"
        .$this->getNetworkError()
        ."</td>\n";
  print "</tr>\n";

  print "<tr>\n";
  print "<td>Training loops</td>\n";
  print "<td style=\"background-color: #CCCCCC\">"
        .number_format($this->totalLoops, 0, '.', ',')
        ." loops</td>\n";
  print "</tr>\n";

  print "<tr>\n";
  print "<td>Epoch</td>\n";
  print "<td style=\"background-color: #CCCCCC\">"
        .$this->numberEpoch
        ." loops</td>\n";
  print "</tr>\n";

  $trainingTime = ($this->trainingTime > 0) ? $this->trainingTime : 1;

  print "<tr>\n";
  print "<td>Training time</td>\n";
  print "<td style=\"background-color: #CCCCCC\">"
        .$this->trainingTime ." seconds = ". round($trainingTime / 60,1) ." minutes</td>\n";
  print "</tr>\n";

  print "<tr>\n";
  print "<td>Loops / second</td>\n";
  print "<td style=\"background-color: #CCCCCC\">"
        .round($this->totalLoops / $trainingTime) ." loops / second</td>\n";
  print "</tr>\n";

  print "<tr>\n";
  print "<td>Training finished</td>\n";
  print "<td style=\"background-color: #CCCCCC\">"
        .(($this->trained) ? 'Yes' : 'No') ."</td>\n";
  print "</tr>\n";

  print "</table>\n<br />\n";

  print "<table border=\"1\" style=\"background-color: #AAAAAA\" cellpadding=\"2\" cellspacing=\"0\">\n";

  print "<tr>\n";
  print "<td>Input-Layer</td>\n";

  foreach($this->inputs[0] as $key => $input)
  {
  print "<td style=\"background-color: #CCCCCC\">"
          ."<b>Input ". ($key + 1) ."</b></td>\n";
  }
  
  print "</tr>\n";


foreach($this->hiddenLayers as $idx => $hiddenLayer)
{
  print "<tr>\n";
  print "<td>Hidden-Layer ". ($idx+1) ."</td>\n";

  foreach($hiddenLayer->getNeurons() as $neuron)
    print "<td style=\"background-color: #CCCCCC\"><b>Inputs:</b> ". (count($neuron->getWeights())-1) ." + BIAS<br />"
          ."<b>Delta:</b> ". round($neuron->getDelta(),4) ."<br />"
          ."<b>Weights:</b><br />"
          .implode('<br />', $neuron->getWeights())
          ."</td>\n";

  print "</tr>\n";
}

  print "<tr>\n";
  print "<td rowspan=\"2\">Output-Layer</td>\n";

  foreach($this->outputLayer->getNeurons() as $neuron)
    print "<td style=\"background-color: #CCCCCC\"><b>Inputs:</b> ". (count($neuron->getWeights())-1) ." + BIAS<br />"
          ."<b>Delta:</b> ". round($neuron->getDelta(),4) ."<br />"
          ."<b>Weights:</b><br />"
          .implode('<br />', $neuron->getWeights())
          ."</td>\n";

  print "</tr>\n";
  print "<tr>\n";

  foreach($this->outputLayer->getNeurons() as $key => $neuron)
    print "<td style=\"background-color: #CCCCCC\"><b>Output ". ($key+1) ."</b></td>\n";

  print "<tr>\n";
  print "</table>\n";
}

// ****************************************************************************

protected function calculateMaxTrainingLoops()
{
$seconds = (int)ini_get('max_execution_time');

$this->maxTrainingLoops = $seconds * $this->maxTrainingLoopsFactor;
}

// ****************************************************************************

/**
 * @param integer $maxTrainingLoopsFactor (Default: 230)
 */

public function setMaxTrainingLoopsFactor($maxTrainingLoopsFactor = 230)
{
  if(!is_int($maxTrainingLoopsFactor) && $maxTrainingLoopsFactor > 0)
    throw new ANN_Exception('Constraints: $maxTrainingLoopsFactor should be an positive integer');

  $this->maxTrainingLoopsFactor = $maxTrainingLoopsFactor;
}

// ****************************************************************************

/**
 * @uses calculateMaxTrainingLoops()
 */

public function __wakeup()
{
  $this->calculateMaxTrainingLoops();
}

// ****************************************************************************

/**
 * @param string $filename (Default: null)
 * @uses parent::loadFromFile()
 * @uses self::getDefaultFilename()
 */

public static function loadFromFile($filename = null)
{
  if($filename === null)
    $filename = self::getDefaultFilename();
  
  return parent::loadFromFile($filename);
}

// ****************************************************************************

/**
 * @param string $filename (Default: null)
 * @uses parent::saveToFile()
 * @uses self::getDefaultFilename()
 */

public function saveToFile($filename = null)
{
if($filename === null)
  $filename = self::getDefaultFilename();

parent::saveToFile($filename);
}

// ****************************************************************************

/**
 * @return integer
 */

public function getNumberInputs()
{
if(isset($this->inputs) && is_array($this->inputs))
  if(isset($this->inputs[0]))
    return count($this->inputs[0]);
    
return 0;
}

// ****************************************************************************

/**
 * @return integer
 */

public function getNumberHiddenLayers()
{
if(isset($this->hiddenLayers) && is_array($this->hiddenLayers))
  return count($this->hiddenLayers);

return 0;
}

// ****************************************************************************

/**
 * @return integer
 */

public function getNumberHiddens()
{
if(isset($this->hiddenLayers) && is_array($this->hiddenLayers))
  if(isset($this->hiddenLayers[0]))
    return $this->hiddenLayers[0]->getNeuronsCount();

return 0;
}

// ****************************************************************************

/**
 * @return integer
 */

public function getNumberOutputs()
{
if(isset($this->outputs[0]) && is_array($this->outputs[0]))
  return count($this->outputs[0]);

return 0;
}

// ****************************************************************************

/**
 * Log weights while training in CSV format
 *
 * @param string $filename
 * @uses ANN_Logging::__construct()
 * @uses ANN_Logging::setFilename()
 */

public function logWeightsToFile($filename)
{
$this->loggingWeights = TRUE;

$this->objLoggingWeights = new ANN_Logging;

$this->objLoggingWeights->setFilename($filename);
}

// ****************************************************************************

/**
 * Log network errors while training in CSV format
 *
 * @param string $filename
 * @uses ANN_Logging::__construct()
 * @uses ANN_Logging::setFilename()
 */

public function logNetworkErrorsToFile($filename)
{
$this->loggingNetworkErrors = TRUE;

$this->objLoggingNetworkErrors = new ANN_Logging;

$this->objLoggingNetworkErrors->setFilename($filename);
}

// ****************************************************************************

/**
 * @uses ANN_Layer::getNeurons()
 * @uses ANN_Logging::logData()
 * @uses ANN_Neuron::getWeights()
 * @uses getNetworkError()
 */

protected function logWeights()
{
$arrData = array();

$arrData['E'] = $this->getNetworkError();

// ****** HiddenLayers ****************

foreach($this->hiddenLayers as $keyLayer => $objHiddenLayer)
{
$arrNeurons = $objHiddenLayer->getNeurons();

foreach($arrNeurons as $keyNeuron => $objNeuron)
  foreach($objNeuron->getWeights() as $keyWeight => $weight)
      $arrData["H$keyLayer-N$keyNeuron-W$keyWeight"] = round($weight, 5);
}

// ****** OutputLayer *****************

$arrNeurons = $this->outputLayer->getNeurons();

foreach($arrNeurons as $keyNeuron => $objNeuron)
  foreach($objNeuron->getWeights() as $keyWeight => $weight)
      $arrData["O-N$keyNeuron-W$keyWeight"] = round($weight, 5);

// ************************************

$this->objLoggingWeights->logData($arrData);
}

// ****************************************************************************

/**
 * @uses getNetworkError()
 * @uses ANN_Logging::logData()
 */

protected function logNetworkErrors()
{
$arrData = array();

$arrData['network error'] = $this->getNetworkError();
$arrData['learning rate'] = $this->learningRate;

$this->objLoggingNetworkErrors->logData($arrData);
}

// ****************************************************************************

/**
 * @return float
 * @uses getOutputs()
 * @uses setOutputType()
 */

protected function getNetworkError()
{
$error = 0;

$arrOutputs = $this->getOutputs();

foreach($this->outputs as $keyOutputs => $outputs)
  foreach($outputs as $keyOutput => $output)
    $error += pow($arrOutputs[$keyOutputs][$keyOutput] - $output, 2);

return $error / 2;
}

// ****************************************************************************

/**
 * @param string $username
 * @param string $password
 * @param string $host
 * @return ANN_Network
 * @throws ANN_Exception
 */

public function trainByHost($username, $password, $host)
{
if(!extension_loaded('curl'))
  throw new ANN_Exception('Curl extension is not installed or active on this system');

$ch = curl_init();

settype($username, 'string');
settype($password, 'string');
settype($host, 'string');

curl_setopt($ch, CURLOPT_URL, $host);
curl_setopt($ch, CURLOPT_POST, TRUE);
curl_setopt($ch, CURLOPT_POSTFIELDS, "mode=trainbyhost&username=$username&password=$password&network=". serialize($this));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

$result = curl_exec($ch);

curl_close($ch);

$network = @unserialize($result);

if($network instanceof ANN_Network)
  return $network;
}

// ****************************************************************************

/**
 * @param string $username
 * @param string $password
 * @param string $host
 * @throws ANN_Exception
 */

public function saveToHost($username, $password, $host)
{
if(!extension_loaded('curl'))
  throw new ANN_Exception('Curl extension is not installed or active on this system');

$ch = curl_init();

settype($username, 'string');
settype($password, 'string');
settype($host, 'string');

curl_setopt($ch, CURLOPT_URL, $host);
curl_setopt($ch, CURLOPT_POST, TRUE);
curl_setopt($ch, CURLOPT_POSTFIELDS, "mode=savetohost&username=$username&password=$password&network=". serialize($this));

curl_exec($ch);

curl_close($ch);
}

// ****************************************************************************

/**
 * @param string $username
 * @param string $password
 * @param string $host
 * @return ANN_Network
 * @throws ANN_Exception
 */

public static function loadFromHost($username, $password, $host)
{
if(!extension_loaded('curl'))
  throw new ANN_Exception('Curl extension is not installed or active on this system');

$ch = curl_init();

settype($username, 'string');
settype($password, 'string');
settype($host, 'string');

curl_setopt($ch, CURLOPT_URL, $host);
curl_setopt($ch, CURLOPT_POST, TRUE);
curl_setopt($ch, CURLOPT_POSTFIELDS, "mode=loadfromhost&username=$username&password=$password");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

$result = curl_exec($ch);

curl_close($ch);

$network = unserialize(trim($result));

if($network instanceof ANN_Network)
  return $network;
}

// ****************************************************************************

/**
 * @uses setOutputType()
 */

protected function detectOutputType()
{
foreach($this->outputs as $arrOutputs)
  foreach($arrOutputs as $output)
    if($output < 1 && $output > 0)
    {
      $this->setOutputType('linear');
      return;
    }
  
$this->setOutputType('binary');
}

// ****************************************************************************

/**
 * Adjusting learning rate dynamically
 *
 * If network error of current epoch is higher than the network error of the previous
 * epoch the learning rate is adjusted by minus 1 per cent of current learning rate.
 * Otherwise the learning rate is adjusted by plus 1 per cent of current learning
 * rate. So, learning rate increases faster than decreasing does. But if learning rate
 * reaches 0.9 it switches back to 0.5 to avoid endless training. The lowest learning
 * rate is 0.5 also to avoid endless training.
 *
 * @uses getNetworkError()
 */

protected function adjustLearningRate()
{
if(!$this->dynamicLearningRate)
  return;

$this->networkErrorCurrent = $this->getNetworkError();

if($this->networkErrorCurrent >= $this->networkErrorPrevious)
{
$this->learningRate *= 1.01;

if($this->learningRate > 0.9)
  $this->learningRate = 0.5;
}
else
{
$this->learningRate *= 0.99;

if($this->learningRate < 0.5)
  $this->learningRate = 0.5;
}

$this->networkErrorPrevious = $this->networkErrorCurrent;
}

// ****************************************************************************

/**
 * @param boolean $dynamicLearningRate (Default: TRUE)
 * @uses ANN_Exception::__construct()
 * @throws ANN_Exception
 */

public function setDynamicLearningRate($dynamicLearningRate = TRUE)
{
if(!is_bool($dynamicLearningRate))
  throw new ANN_Exception('$dynamicLearningRate must be boolean');

$this->dynamicLearningRate = $dynamicLearningRate;
}

// ****************************************************************************

/**
 * @param boolean $weightDecayMode (Default: TRUE)
 * @uses ANN_Exception::__construct()
 * @throws ANN_Exception
 */

public function setWeightDecayMode($weightDecayMode = TRUE)
{
if(!is_bool($weightDecayMode))
  throw new ANN_Exception('$weightDecayMode must be boolean');

$this->weightDecayMode = $weightDecayMode;
}

// ****************************************************************************

/**
 * @param float $weightDecay (Default: 0.05)
 * @uses ANN_Exception::__construct()
 * @throws ANN_Exception
 */

public function setWeightDecay($weightDecay = 0.05)
{
if($weightDecay < 0.03 || $weightDecay > 0.05)
  throw new ANN_Exception('$weightDecay must be between 0.03 and 0.05');

$this->weightDecay = $weightDecay;

$this->weightDecayMode = TRUE;
}

// ****************************************************************************

/**
 * @param boolean $quickPropMode (Default: TRUE)
 * @uses ANN_Exception::__construct()
 * @throws ANN_Exception
 */

public function setQuickPropMode($quickPropMode = TRUE)
{
if(!is_bool($quickPropMode))
  throw new ANN_Exception('$quickPropMode must be boolean');

$this->quickPropMode = $quickPropMode;
}

// ****************************************************************************

/**
 * @param float $weightDecay (Default: 2.25)
 * @uses ANN_Exception::__construct()
 * @throws ANN_Exception
 */

public function setQuickPropMaxWeightChangeFactor($quickPropMaxWeightChangeFactor = 2.25)
{
if($quickPropMaxWeightChangeFactor < 1.75 || $quickPropMaxWeightChangeFactor > 2.25)
  throw new ANN_Exception('$quickPropMaxWeightChangeFactor must be between 1.75 and 2.25');

$this->quickPropMaxWeightChangeFactor = $quickPropMaxWeightChangeFactor;

$this->quickPropMode = TRUE;
}

// ****************************************************************************
}

?>
