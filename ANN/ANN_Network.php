<?php

/**
 * Artificial Neural Network - Version 2.0.0
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
 * @version ANN Version 2.0.1 by Thomas Wien
 * @copyright Copyright (c) 2002 Eddy Young
 * @copyright Copyright (c) 2007 Thomas Wien
 * @package ANN
 */

require_once('ANN_Exception.php');
require_once('ANN_Math.php');
require_once('ANN_Neuron.php');
require_once('ANN_Layer.php');
require_once('ANN_Filesystem.php');
require_once('ANN_InputValue.php');
require_once('ANN_OutputValue.php');

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
protected $output = null;
protected $countHiddenLayers = null;
protected $outputType = 'binary'; // binary or lineary
protected $totalLoops = 0;
protected $numberOfHiddenLayers = null;
protected $numberOfHiddenLayersDec = null; // decremented value
protected $maxTrainingLoops;
protected $maxTrainingLoopsFactor = 230;
protected $epocheTrainingLoops = 10;

/**#@-*/

// ****************************************************************************

/**
 * @param integer $numberOfHiddenLayers (Default:  1)
 * @param integer $numberOfNeuronsPerLayer  (Default:  10)
 * @param integer $numberOfOutputs  (Default:  1)
 * @uses ANN_Exception::__construct()
 * @uses calculateMaxTrainingLoops()
 * @uses createHiddenLayers()
 * @uses createOutputLayer()
 * @uses setLearningRate()
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

  $this->numberOfHiddenLayersDec = $this->numberOfHiddenLayers -1;
  
  $this->setLearningRate(0.5);
  
  $this->calculateMaxTrainingLoops();
}
	
// ****************************************************************************

/**
 * @param array $inputs
 */

public function setInputs($inputs)
{
  $this->inputs = $inputs;
}

// ****************************************************************************

/**
 * @param array $outputs
 */

public function setOutputs($outputs)
{
  if(isset($outputs[0]) && is_array($outputs[0]))
    if(count($outputs[0]) != $this->outputLayer->getNeuronsCount())
      throw new ANN_Exception('Count of Outputs doesn\'t fit to number of outputs on instantiation of ANN_Network');

  $this->outputs = $outputs;
}

// ****************************************************************************

/**
 * @param array $inputs
 * @uses ANN_Layer::setInputs()
 */

protected function setInputsToTrain($inputs)
{
  $this->hiddenLayers[0]->setInputs($inputs);

/*
  Großer Fehler: Nur das erste Hidden-Layer erhält die Inputs und nicht alle!!

 	foreach ($this->hiddenLayers as $k => $value)
		$this->hiddenLayers[$k]->setInputs($inputs);
*/
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
    case 'binary':
      $returnOutputs[] = $this->outputLayer->getThresholdOutputs();
      break;

    case 'lineary':
      $returnOutputs[] = $this->outputLayer->getOutputs();
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
		$this->hiddenLayers[] = new ANN_Layer($numberOfNeuronsPerLayer);
}
	
// ****************************************************************************

/**
 * @param integer $numberOfOutputs
 * @uses ANN_Layer::__construct()
 */

protected function createOutputLayer($numberOfOutputs)
{
	$this->outputLayer = new ANN_Layer($numberOfOutputs, TRUE);
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
 * @uses ANN_Exception::__construct()
 * @uses ANN_Maths::random()
 * @uses setInputs()
 * @uses setOutputs()
 * @uses getCountInputs()
 * @uses isTrainingComplete()
 * @uses setInputsToTrain()
 * @uses training()
 * @uses isTrainingLoopEpoche()
 * @throws ANN_Exception
 * @return integer Seconds of training
 */

public function train()
{
  if(!$this->inputs)
    throw new ANN_Exception('No Inputs defined. Use ANN_Network::setInputs().');

  if(!$this->outputs)
    throw new ANN_Exception('No Outputs defined. Use ANN_Network::setOutputs().');

  if($this->isTrainingComplete())
    return 0;

  $inputCountDec = $this->getCountInputs() - 1;

  $starttime = date('U');

  for ($i = 0; $i < $this->maxTrainingLoops; $i++)
  {
    $j = ANN_Maths::random(0, $inputCountDec);

    $this->setInputsToTrain($this->inputs[$j]);

    $this->training($this->outputs[$j]);

    if($this->isTrainingLoopEpoche())
      if($this->isTrainingComplete())
        break;
  }

  $this->totalLoops += $i;

  $stoptime = date('U');

  return $stoptime - $starttime;
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

protected function isTrainingLoopEpoche()
{
static $countLoop = 0;

$countLoop++;

if($countLoop >= $this->epocheTrainingLoops)
{
  $countLoop = 0;

  return TRUE;
}

return FALSE;
}

// ****************************************************************************

/**
 * @param float $learningRate (Default: 0.5) (0.1 .. 0.9)
 * @uses ANN_Exception::__construct()
 * @uses ANN_Layer::setLearningRate()
 * @throws ANN_Exception
 */

public function setLearningRate($learningRate = 0.5)
{
  if(!is_float($learningRate))
    throw new ANN_Exception('$learningRate should be between 0.1 and 0.9');

  if($learningRate <= 0 || $learningRate >= 1)
    throw new ANN_Exception('$learningRate should be between 0.1 and 0.9');

  foreach($this->hiddenLayers as $hiddenLayer)
    $hiddenLayer->setLearningRate($learningRate);

  $this->outputLayer->setLearningRate($learningRate);
}

// ****************************************************************************

/**
 * @param float $momentum (Default: 0.95) (0 .. 1)
 * @uses ANN_Exception::__construct()
 * @uses ANN_Layer::setMomentum()
 * @throws ANN_Exception
 */

public function setMomentum($momentum = 0.95)
{
  if(!is_float($momentum) && !is_integer)
    throw new ANN_Exception('$learningRate should be between 0 and 1');

  if($momentum < 0 || $momentum > 1)
    throw new ANN_Exception('$learningRate should be between 0 and 1');

  foreach($this->hiddenLayers as $hiddenLayer)
    $hiddenLayer->setMomentum($momentum);

  $this->outputLayer->setMomentum($momentum);
}

// ****************************************************************************

/**
 * @return boolean
 * @uses getOutputs()
 * @uses formatLineary()
 */

protected function isTrainingComplete()
{
  $outputs = $this->getOutputs();

  switch($this->outputType)
  {
  case 'lineary':

    foreach($this->outputs as $key1 => $output)
      foreach($output as $key2 => $value)
        if($this->formatLineary($value) != $this->formatLineary($outputs[$key1][$key2]))
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
 * @param float $value
 * @return string
 */

protected function formatLineary($value)
{
  /*
  if($value == 1)
    $value = '0.999999999999';
  */
  
  return round($value, 2);
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
		
//	for ($i = $this->numberOfHiddenLayersDec; $i > 1; $i--)
	for ($i = $this->numberOfHiddenLayersDec; $i > 0; $i--) // erstes Hidden-Layer mit berücksichtigen? Wahrscheinlich Ja!
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
 * @param string $type (Default:  'lineary') (linary or binary)
 */

public function setOutputType($type = 'lineary')
{
  $this->outputType = $type;
}

// ****************************************************************************

public function printNetwork()
{
print "<table border=\"1\" style=\"background-color: #AAAAAA\">\n";


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

  print "<td>Output-Layer</td>\n";

  foreach($this->outputLayer->getNeurons() as $neuron)
    print "<td style=\"background-color: #CCCCCC\"><b>Inputs:</b> ". (count($neuron->getWeights())-1) ." + BIAS<br />"
          ."<b>Delta:</b> ". round($neuron->getDelta(),4) ."<br />"
          ."<b>Weights:</b><br />"
          .implode('<br />', $neuron->getWeights())
          ."</td>\n";

  print "</tr>\n";

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
 * @param integer $epocheTrainingLoops (Default: 10)
 */

public function setEpocheTrainingLoops($epocheTrainingLoops = 10)
{
  $this->epocheTrainingLoops = $epocheTrainingLoops;
}

// ****************************************************************************

/**
 * @param integer $maxTrainingLoopsFactor (Default: 230)
 */

public function setMaxTrainingLoopsFactor($maxTrainingLoopsFactor = 230)
{
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
}

?>
