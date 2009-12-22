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
 * @copyright Copyright (c) 2007-09 by Thomas Wien
 * @package ANN
 */

/**
 * @package ANN
 * @access public
 */

class ANN_Network extends ANN_Filesystem
{

/**#@+
 * @ignore
 */
 
protected $objOutputLayer = null;
protected $arrHiddenLayers = array();
protected $arrInputs = null;
protected $arrOutputs = null;
protected $intTotalLoops = 0;
protected $intTotalTrainings = 0;
protected $intTotalActivations = 0;
protected $intTotalActivationsRequests = 0;
protected $intNumberOfHiddenLayers = null;
protected $intNumberOfHiddenLayersDec = null; // decremented value
protected $intMaxTrainingLoops;
protected $intMaxTrainingLoopsFactor = 230;
protected $intNumberEpoch = 0;
protected $boolLoggingWeights = FALSE;
protected $boolLoggingNetworkErrors = FALSE;
protected $boolTrained = FALSE;
protected $intTrainingTime = 0; // Seconds
protected $objLoggingWeights = null;
protected $objLoggingNetworkErrors = null;
protected $boolNetworkActivated = FALSE;
protected $arrTrainingComplete = array();
protected $intNumberOfNeuronsPerLayer = 0;
protected $floatOutputErrorTolerance = 0.02;
private $floatNetworkErrorCurrent = 10;
private $floatNetworkErrorPrevious = 10;
private $arrInputsToTrain = array();
private $intInputsToTrainIndex = -1;
public $intOutputType = self::OUTPUT_LINEAR;
public $floatLearningRate = 0.7;
public $boolFirstLoopOfTraining = TRUE;
public $boolFirstEpochOfTraining = TRUE;
public $floatQuickPropMaxWeightChangeFactor = 0;

/**#@-*/

/**
 * Linear output type
 */

const OUTPUT_LINEAR = 1;

/**
 * Binary output type
 */

const OUTPUT_BINARY = 2;

// ****************************************************************************

/**
 * @param integer $intNumberOfHiddenLayers (Default: 1)
 * @param integer $intNumberOfNeuronsPerLayer  (Default: 6)
 * @param integer $intNumberOfOutputs  (Default: 1)
 * @uses ANN_Exception::__construct()
 * @uses calculateMaxTrainingLoops()
 * @uses createHiddenLayers()
 * @uses createOutputLayer()
 * @throws ANN_Exception
 */

public function __construct($intNumberOfHiddenLayers = 1, $intNumberOfNeuronsPerLayer = 6, $intNumberOfOutputs = 1)
{
  if(!is_integer($intNumberOfHiddenLayers) && $intNumberOfHiddenLayers < 2)
    throw new ANN_Exception('Constraints: $intNumberOfHiddenLayers must be a positiv integer >= 2');

  if(!is_integer($intNumberOfNeuronsPerLayer) && $intNumberOfNeuronsPerLayer < 1)
    throw new ANN_Exception('Constraints: $intNumberOfNeuronsPerLayer must be a positiv integer number > 1');

  if(!is_integer($intNumberOfOutputs) && $intNumberOfOutputs < 1)
    throw new ANN_Exception('Constraints: $intNumberOfOutputs must be a positiv integer number > 1');

	$this->createOutputLayer($intNumberOfOutputs);
	
	$this->createHiddenLayers($intNumberOfHiddenLayers, $intNumberOfNeuronsPerLayer);

	$this->intNumberOfHiddenLayers = $intNumberOfHiddenLayers;

  $this->intNumberOfHiddenLayersDec = $this->intNumberOfHiddenLayers - 1;
  
  $this->intNumberOfNeuronsPerLayer = $intNumberOfNeuronsPerLayer;
  
  $this->calculateMaxTrainingLoops();
}
	
// ****************************************************************************

/**
 * @param array $arrInputs
 */

protected function setInputs($arrInputs)
{
  if(!is_array($arrInputs))
    throw new ANN_Exception('Constraints: $arrInputs should be an array');

  $this->arrInputs = $arrInputs;
  
  $this->intNumberEpoch = count($arrInputs);
  
  $this->nextIndexInputToTrain = 0;
  
  $this->boolNetworkActivated = FALSE;
}

// ****************************************************************************

/**
 * @param array $arrOutputs
 * @uses ANN_Exception::__construct()
 * @uses detectOutputType()
 * @uses ANN_Layer::getNeuronsCount()
 * @throws ANN_Exception
 */

protected function setOutputs($arrOutputs)
{
  if(isset($arrOutputs[0]) && is_array($arrOutputs[0]))
    if(count($arrOutputs[0]) != $this->objOutputLayer->getNeuronsCount())
      throw new ANN_Exception('Count of arrOutputs doesn\'t fit to number of arrOutputs on instantiation of ANN_Network');

  $this->arrOutputs = $arrOutputs;
  
  $this->detectOutputType();
  
  $this->boolNetworkActivated = FALSE;
}

// ****************************************************************************

/**
 * Set Values for training or using network
 *
 * Set Values of inputs and outputs for training or just inputs for using
 * already trained network.
 *
 * <code>
 * $objNetwork = new ANN_Network(2, 4, 1);
 *
 * $objValues = new ANN_Values;
 *
 * $objValues->train()
 *           ->input(0.12, 0.11, 0.15)
 *           ->output(0.56);
 *
 * $objNetwork->setValues($objValues);
 * </code>
 *
 * @param ANN_Values $objValues
 * @uses ANN_Values::getInputsArray()
 * @uses ANN_Values::getOutputsArray()
 * @uses setInputs()
 * @uses setOutputs()
 * @since 2.0.6
 */

public function setValues(ANN_Values $objValues)
{
  $this->setInputs($objValues->getInputsArray());

  $this->setOutputs($objValues->getOutputsArray());
}

// ****************************************************************************

/**
 * @param array $arrInputs
 * @uses ANN_Layer::setInputs()
 */

protected function setInputsToTrain($arrInputs)
{
  $this->arrHiddenLayers[0]->setInputs($arrInputs);
  
  $this->boolNetworkActivated = FALSE;
}
	
// ****************************************************************************

/**
 * Get the output values
 *
 * Get the output values to the related input values set by setValues(). This
 * method returns the output values as a two-dimensional array.
 *
 * @return array two-dimensional array
 * @uses activate()
 * @uses getCountInputs()
 * @uses ANN_Layer::getOutputs()
 * @uses ANN_Layer::getThresholdOutputs()
 * @uses setInputsToTrain()
 */

public function getOutputs()
{
  $arrReturnOutputs = array();

  $intCountInputs = $this->getCountInputs();

	for ($intIndex = 0; $intIndex < $intCountInputs; $intIndex++)
	{
    $this->setInputsToTrain($this->arrInputs[$intIndex]);

    $this->activate();

    switch($this->intOutputType)
    {
      case self::OUTPUT_LINEAR:
        $arrReturnOutputs[] = $this->objOutputLayer->getOutputs();
        break;

      case self::OUTPUT_BINARY:
        $arrReturnOutputs[] = $this->objOutputLayer->getThresholdOutputs();
        break;
    }
  }

	return $arrReturnOutputs;
}

// ****************************************************************************

/**
 * @param integer $intKeyInput
 * @return array
 * @uses activate()
 * @uses ANN_Layer::getOutputs()
 * @uses ANN_Layer::getThresholdOutputs()
 * @uses setInputsToTrain()
 */

public function getOutputsByInputKey($intKeyInput)
{
	$this->setInputsToTrain($this->arrInputs[$intKeyInput]);

  $this->activate();

  switch($this->intOutputType)
  {
    case self::OUTPUT_LINEAR:
      return $this->objOutputLayer->getOutputs();

    case self::OUTPUT_BINARY:
      return $this->objOutputLayer->getThresholdOutputs();
  }
}

// ****************************************************************************

/**
 * @param integer $intNumberOfHiddenLayers
 * @param integer $intNumberOfNeuronsPerLayer
 * @uses ANN_Layer::__construct()
 */

protected function createHiddenLayers($intNumberOfHiddenLayers, $intNumberOfNeuronsPerLayer)
{
  $layerId = $intNumberOfHiddenLayers;

  for ($i = 0; $i < $intNumberOfHiddenLayers; $i++)
  {
    $layerId--;

    if($i == 0)
      $nextLayer = $this->objOutputLayer;

    if($i > 0)
      $nextLayer = $this->arrHiddenLayers[$layerId + 1];

    $this->arrHiddenLayers[$layerId] = new ANN_Layer($this, $intNumberOfNeuronsPerLayer, $nextLayer);
  }

  ksort($this->arrHiddenLayers);
}
	
// ****************************************************************************

/**
 * @param integer $intNumberOfOutputs
 * @uses ANN_Layer::__construct()
 */

protected function createOutputLayer($intNumberOfOutputs)
{
	$this->objOutputLayer = new ANN_Layer($this, $intNumberOfOutputs);
}
	
// ****************************************************************************

/**
 * @uses ANN_Layer::setInputs()
 * @uses ANN_Layer::activate()
 * @uses ANN_Layer::getOutputs()
 */

protected function activate()
{
  $this->intTotalActivationsRequests++;

  if($this->boolNetworkActivated)
    return;

  $this->arrHiddenLayers[0]->activate();

	$this->boolNetworkActivated = TRUE;
	
  $this->intTotalActivations++;
}
	
// ****************************************************************************

/**
 * @return boolean
 * @uses ANN_Exception::__construct()
 * @uses setInputs()
 * @uses setOutputs()
 * @uses isTrainingComplete()
 * @uses isTrainingCompleteByEpoch()
 * @uses setInputsToTrain()
 * @uses training()
 * @uses isEpoch()
 * @uses logWeights()
 * @uses logNetworkErrors()
 * @uses getNextIndexInputsToTrain()
 * @uses isTrainingCompleteByInputKey()
 * @throws ANN_Exception
 */

public function train()
{
  if(!$this->arrInputs)
    throw new ANN_Exception('No arrInputs defined. Use ANN_Network::setValues().');

  if(!$this->arrOutputs)
    throw new ANN_Exception('No arrOutputs defined. Use ANN_Network::setValues().');

  if($this->isTrainingComplete())
  {
    $this->boolTrained = TRUE;
    
    return $this->boolTrained;
  }

  $intStartTime = date('U');
  
  $this->getNextIndexInputsToTrain(TRUE);

  $this->boolFirstLoopOfTraining = TRUE;
  
  $this->boolFirstEpochOfTraining = TRUE;

  for ($i = 0; $i < $this->intMaxTrainingLoops; $i++)
  {
    $j = $this->getNextIndexInputsToTrain();

    $this->setInputsToTrain($this->arrInputs[$j]);

    if(!($this->arrTrainingComplete[$j] = $this->isTrainingCompleteByInputKey($j)))
      $this->training($this->arrOutputs[$j]);

    if($this->isEpoch())
    {
      if($this->boolLoggingWeights)
        $this->logWeights();

      if($this->boolLoggingNetworkErrors)
        $this->logNetworkErrors();

      if($this->isTrainingCompleteByEpoch())
        break;
        
      $this->boolFirstEpochOfTraining = FALSE;
    }

    $this->boolFirstLoopOfTraining = FALSE;
  }

  $intStopTime = date('U');

  $this->intTotalLoops += $i;

  $this->intTrainingTime += $intStopTime - $intStartTime;
  
  $this->boolTrained = $this->isTrainingComplete();
  
  return $this->boolTrained;
}
	
// ****************************************************************************

/**
 * @param boolean $boolReset (Default: FALSE)
 * @return integer
 */

protected function getNextIndexInputsToTrain($boolReset = FALSE)
{
  if($boolReset)
  {
    $this->arrInputsToTrain = array_keys($this->arrInputs);

    $this->intInputsToTrainIndex = -1;

    return;
  }

  $this->intInputsToTrainIndex++;

  if(!isset($this->arrInputsToTrain[$this->intInputsToTrainIndex]))
  {
    shuffle($this->arrInputsToTrain);

    $this->intInputsToTrainIndex = 0;
  }

  return $this->arrInputsToTrain[$this->intInputsToTrainIndex];
}
	
// ****************************************************************************

/**
 * @return integer
 */

public function getTotalLoops()
{
  return $this->intTotalLoops;
}

// ****************************************************************************

/**
 * @return boolean
 */

protected function isEpoch()
{
  static $countLoop = 0;

  $countLoop++;

  if($countLoop >= $this->intNumberEpoch)
  {
    $countLoop = 0;

    return TRUE;
  }

  return FALSE;
}

// ****************************************************************************

/**
 * Setting the learning rate
 *
 * @param float $floatLearningRate (Default: 0.5) (0.1 .. 0.9)
 * @uses ANN_Exception::__construct()
 * @throws ANN_Exception
 */

public function setLearningRate($floatLearningRate = 0.5)
{
  if(!is_float($floatLearningRate))
    throw new ANN_Exception('$floatLearningRate should be between 0.1 and 0.9');

  if($floatLearningRate <= 0 || $floatLearningRate >= 1)
    throw new ANN_Exception('$floatLearningRate should be between 0.1 and 0.9');

  $this->floatLearningRate = $floatLearningRate;
}

// ****************************************************************************

/**
 * @return boolean
 * @uses getOutputs()
 */

protected function isTrainingComplete()
{
  $arrOutputs = $this->getOutputs();
  
  switch($this->intOutputType)
  {
    case self::OUTPUT_LINEAR:

      foreach($this->arrOutputs as $intKey1 => $arrOutput)
        foreach($arrOutput as $intKey2 => $floatValue)
          if(($floatValue > round($arrOutputs[$intKey1][$intKey2] + $this->floatOutputErrorTolerance, 3)) || ($floatValue < round($arrOutputs[$intKey1][$intKey2] - $this->floatOutputErrorTolerance, 3)))
            return FALSE;

      return TRUE;

    case self::OUTPUT_BINARY:

      foreach($this->arrOutputs as $intKey1 => $arrOutput)
        foreach($arrOutput as $intKey2 => $floatValue)
          if($floatValue != $arrOutputs[$intKey1][$intKey2])
            return FALSE;

      return TRUE;
  }
}

// ****************************************************************************

/**
 * @return boolean
 */

protected function isTrainingCompleteByEpoch()
{
  foreach($this->arrTrainingComplete as $trainingComplete)
    if(!$trainingComplete)
      return FALSE;
    
  return TRUE;
}

// ****************************************************************************

/**
 * @param integer $intKeyInput
 * @return boolean
 * @uses getOutputsByInputKey()
 */

protected function isTrainingCompleteByInputKey($intKeyInput)
{
  $arrOutputs = $this->getOutputsByInputKey($intKeyInput);

  if(!isset($this->arrOutputs[$intKeyInput]))
    return TRUE;

  switch($this->intOutputType)
  {
    case self::OUTPUT_LINEAR:

        foreach($this->arrOutputs[$intKeyInput] as $intKey => $floatValue)
          if(($floatValue > round($arrOutputs[$intKey] + $this->floatOutputErrorTolerance, 3)) || ($floatValue < round($arrOutputs[$intKey] - $this->floatOutputErrorTolerance, 3)))
            return FALSE;

      return TRUE;

    case self::OUTPUT_BINARY:

        foreach($this->arrOutputs[$intKeyInput] as $intKey => $floatValue)
          if($floatValue != $arrOutputs[$intKey])
            return FALSE;

      return TRUE;
  }
}

// ****************************************************************************

/**
 * @return integer
 */

protected function getCountInputs()
{
  if(isset($this->arrInputs) && is_array($this->arrInputs))
    return count($this->arrInputs);

  return 0;
}

// ****************************************************************************

/**
 * @param array $arrOutputs
 * @uses activate()
 * @uses ANN_Layer::calculateHiddenDeltas()
 * @uses ANN_Layer::adjustWeights()
 * @uses ANN_Layer::calculateOutputDeltas()
 * @uses getNetworkError()
 */

protected function training($arrOutputs)
{
	$this->activate();
	
	$this->objOutputLayer->calculateOutputDeltas($arrOutputs);

	for ($i = $this->intNumberOfHiddenLayersDec; $i >= 0; $i--)
		$this->arrHiddenLayers[$i]->calculateHiddenDeltas();
		
	$this->objOutputLayer->adjustWeights();
		
	for ($i = $this->intNumberOfHiddenLayersDec; $i >= 0; $i--)
		$this->arrHiddenLayers[$i]->adjustWeights();
		
	$this->intTotalTrainings++;

  $this->boolNetworkActivated = FALSE;
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
 * @param integer $intType (Default: ANN_Network::OUTPUT_LINEAR)
 * @uses ANN_Exception::__construct()
 * @throws ANN_Exception
 */

protected function setOutputType($intType = self::OUTPUT_LINEAR)
{
  settype($intType, 'integer');

  switch($intType)
  {
    case self::OUTPUT_LINEAR:
    case self::OUTPUT_BINARY:
      $this->intOutputType = $intType;
      break;

    default:
      throw new ANN_Exception('$strType must be ANN_Network::OUTPUT_LINEAR or ANN_Network::OUTPUT_BINARY');
  }
}

// ****************************************************************************

/**
 * @param integer $intLevel (0, 1, 2) (Default: 2)
 * @uses ANN_Neuron::getDelta()
 * @uses ANN_Neuron::getWeights()
 * @uses ANN_Layer::getNeurons()
 * @uses getNumberInputs()
 * @uses getNumberOutputs()
 * @uses printNetworkDetails1()
 * @uses printNetworkDetails2()
 */

public function printNetwork($intLevel = 2)
{
  if($intLevel >= 1)
    $this->printNetworkDetails1();

  $countColumns = max($this->intNumberOfNeuronsPerLayer, $this->getNumberInputs(), $this->getNumberOutputs());

  print "<table border=\"1\" style=\"background-color: #AAAAAA; border-width: 1px; border-collapse:collapse; empty-cells:show\" cellpadding=\"2\" cellspacing=\"0\">\n";

  print "<tr>\n";
  print "<td style=\"color: #DDDDDD; text-align: center\">Input<br />Layer</td>\n";

  foreach($this->arrInputs[0] as $intKey => $input)
  {
    print "<td style=\"background-color: #CCCCCC\">"
            ."<b>Input ". ($intKey + 1) ."</b></td>\n";
  }
  
  for($i = $this->getNumberInputs() + 1; $i <= $countColumns; $i++)
    print "<td style=\"background-color: #CCCCCC\">&nbsp;</td>\n";

  print "</tr>\n";


foreach($this->arrHiddenLayers as $intIndex => $objHiddenLayer)
{
  print "<tr>\n";
  print "<td style=\"color: #DDDDDD; text-align: center\">Hidden<br />Layer<br />". ($intIndex + 1) ."</td>\n";

  foreach($objHiddenLayer->getNeurons() as $objNeuron)
    print "<td style=\"background-color: #CCCCCC; text-align: right\"><p style=\"border: solid #00FF00 1px;\"><b>Inputs</b><br /> ". (count($objNeuron->getWeights()) - 1) ." + BIAS</p>"
          ."<p style=\"border: solid #0000FF 1px;\"><b>Delta</b><br /> ". round($objNeuron->getDelta(), 6) ."</p>"
          ."<p style=\"border: solid #FF0000 1px;\"><b>Weights</b><br />"
          .implode('<br />', $objNeuron->getWeights())
          ."</p></td>\n";

  for($i = $this->intNumberOfNeuronsPerLayer + 1; $i <= $countColumns; $i++)
    print "<td style=\"background-color: #CCCCCC\">&nbsp;</td>\n";


  print "</tr>\n";
}

  print "<tr>\n";
  print "<td style=\"color: #DDDDDD; text-align: center\" rowspan=\"2\">Output<br />Layer</td>\n";

  foreach($this->objOutputLayer->getNeurons() as $objNeuron)
    print "<td style=\"background-color: #CCCCCC; text-align: right\"><p style=\"border: solid #00FF00 1px;\"><b>Inputs</b><br /> ". (count($objNeuron->getWeights()) - 1) ." + BIAS</p>"
          ."<p style=\"border: solid #0000FF 1px;\"><b>Delta</b><br /> ". round($objNeuron->getDelta(), 6) ."</p>"
          ."<p style=\"border: solid #FF0000 1px;\"><b>Weights</b><br />"
          .implode('<br />', $objNeuron->getWeights())
          ."</p></td>\n";

  for($i = $this->getNumberOutputs() + 1; $i <= $countColumns; $i++)
    print "<td style=\"background-color: #CCCCCC\">&nbsp;</td>\n";

  print "</tr>\n";
  print "<tr>\n";

  foreach($this->objOutputLayer->getNeurons() as $intKey => $objNeuron)
    print "<td style=\"background-color: #CCCCCC\"><b>Output ". ($intKey + 1) ."</b></td>\n";

  for($i = $this->getNumberOutputs() + 1; $i <= $countColumns; $i++)
    print "<td style=\"background-color: #CCCCCC\">&nbsp;</td>\n";

  print "<tr>\n";
  print "</table>\n";
  
  if($intLevel >= 2)
    $this->printNetworkDetails2();
}

// ****************************************************************************

/**
 * @uses getNetworkError()
 */

protected function printNetworkDetails1()
{
  print "<table border=\"1\" style=\"background-color: #AAAAAA; border: solid #000000 1px; border-collapse:collapse; empty-cells:show\" cellpadding=\"2\" cellspacing=\"0\">\n";

  print "<tr>\n";
  print "<td style=\"color: #DDDDDD\">Detected output type</td>\n";
  print "<td style=\"background-color: #CCCCCC\">"
        .(($this->intOutputType == self::OUTPUT_BINARY) ? 'Binary' : 'Linear')
        ."</td>\n";
  print "</tr>\n";

  print "<tr>\n";
  print "<td style=\"color: #DDDDDD\">Backpropagation algorithm</td>\n";
  print "<td style=\"background-color: #CCCCCC\">Back propagation";

  print "</td>\n";
  print "</tr>\n";

  print "<tr>\n";
  print "<td style=\"color: #DDDDDD\">Activation function</td>\n";
  print "<td style=\"background-color: #CCCCCC\">Sigmoid</td>\n";
  print "</tr>\n";

  print "<tr>\n";
  print "<td style=\"color: #DDDDDD\">Learning rate</td>\n";
  print "<td style=\"background-color: #CCCCCC\">"
        .$this->floatLearningRate
        ."</td>\n";
  print "</tr>\n";

  print "<tr>\n";
  print "<td style=\"color: #DDDDDD\">Network error</td>\n";
  print "<td style=\"background-color: #CCCCCC\">"
        .$this->getNetworkError()
        ."</td>\n";
  print "</tr>\n";

  print "<tr>\n";
  print "<td style=\"color: #DDDDDD\">Output error tolerance</td>\n";
  print "<td style=\"background-color: #CCCCCC\">+/- "
        .$this->floatOutputErrorTolerance
        ."</td>\n";
  print "</tr>\n";

  print "<tr>\n";
  print "<td style=\"color: #DDDDDD\">Total loops</td>\n";
  print "<td style=\"background-color: #CCCCCC\">"
        .number_format($this->intTotalLoops, 0, '.', ',')
        ." loops</td>\n";
  print "</tr>\n";

  print "<tr>\n";
  print "<td style=\"color: #DDDDDD\">Total trainings</td>\n";
  print "<td style=\"background-color: #CCCCCC\">"
        .number_format($this->intTotalTrainings, 0, '.', ',')
        ." trainings</td>\n";
  print "</tr>\n";

  print "<tr>\n";
  print "<td style=\"color: #DDDDDD\">Total activations</td>\n";
  print "<td style=\"background-color: #CCCCCC\">"
        .number_format($this->intTotalActivations, 0, '.', ',')
        ." activations</td>\n";
  print "</tr>\n";

  print "<tr>\n";
  print "<td style=\"color: #DDDDDD\">Total activation requests</td>\n";
  print "<td style=\"background-color: #CCCCCC\">"
        .number_format($this->intTotalActivationsRequests, 0, '.', ',')
        ." activation requests</td>\n";
  print "</tr>\n";

  print "<tr>\n";
  print "<td style=\"color: #DDDDDD\">Epoch</td>\n";
  print "<td style=\"background-color: #CCCCCC\">"
        .$this->intNumberEpoch
        ." loops</td>\n";
  print "</tr>\n";

  $intTrainingTime = ($this->intTrainingTime > 0) ? $this->intTrainingTime : 1;

  print "<tr>\n";
  print "<td style=\"color: #DDDDDD\">Training time</td>\n";
  print "<td style=\"background-color: #CCCCCC\">"
        .$this->intTrainingTime ." seconds = ". round($intTrainingTime / 60, 1) ." minutes</td>\n";
  print "</tr>\n";

  print "<tr>\n";
  print "<td style=\"color: #DDDDDD\">Loops / second</td>\n";
  print "<td style=\"background-color: #CCCCCC\">"
        .round($this->intTotalLoops / $intTrainingTime) ." loops / second</td>\n";
  print "</tr>\n";

  print "<tr>\n";
  print "<td style=\"color: #DDDDDD\">Training finished</td>\n";
  print "<td style=\"background-color: #CCCCCC\">"
        .(($this->boolTrained) ? 'Yes' : 'No') ."</td>\n";
  print "</tr>\n";

  print "</table>\n<br />\n";
}

// ****************************************************************************

/**
 * @uses getOutputsByInputKey()
 * @uses isTrainingCompleteByInputKey()
 */

protected function printNetworkDetails2()
{
  $boolTrained = 0;

  print "<br />\n";

  print "<table border=\"1\" style=\"background-color: #AAAAAA; border: solid #000000 1px; border-collapse:collapse; empty-cells:show\" cellpadding=\"2\" cellspacing=\"0\">\n";

  print "<tr>\n";
  print "<td style=\"color: #DDDDDD\">Input</td>\n";
  print "<td style=\"color: #DDDDDD\">Output</td>\n";

  if(!$this->boolTrained)
  {
    print "<td style=\"color: #DDDDDD\">Desired output</td>\n";
    print "<td style=\"color: #DDDDDD\">Differences</td>\n";
  }
  
  print "</tr>\n";

  foreach($this->arrInputs as $intKeyInputs => $arrInputs)
  {
    print "<tr>\n";

    foreach($arrInputs as $intKeyInput => $input)
      $arrInputs[$intKeyInput] = round($input, 3);

    print "<td style=\"color: #DDDDDD\" align=\"right\">&nbsp;<b>f</b>(". implode(', ', $arrInputs) .") =&nbsp;</td>\n";

    $arrOutputs = $this->getOutputsByInputKey($intKeyInputs);

    foreach($arrOutputs as $intKeyOutput => $output)
      $arrOutputs[$intKeyOutput] = round($output, 3);

    if(!$this->boolTrained)
    {
      $arrDesiredOutputs = $this->arrOutputs[$intKeyInputs];

      foreach($arrDesiredOutputs as $intKeyDesiredOutput => $desiredOutput)
        $arrDesiredOutputs[$intKeyDesiredOutput] = round($desiredOutput, 3);

      foreach($arrDesiredOutputs as $intKeyOutput => $desiredOutput)
        $arrDesiredOutputsDifferences[$intKeyOutput] = abs($arrDesiredOutputs[$intKeyOutput] - $arrOutputs[$intKeyOutput]);

      $strDesiredArrayOutputs = implode(',', $arrDesiredOutputs);

      $strDesiredArrayOutputsDifferences = implode(',', $arrDesiredOutputsDifferences);
    }

    $strArrayOutputs = implode(',', $arrOutputs);

    if(!$this->boolTrained)
    {
      $color = ($this->isTrainingCompleteByInputKey($intKeyInputs)) ? '#CCFF99' : '#F0807F';
    }
    else
    {
      $color = '#00AACC';
    }

    if($this->isTrainingCompleteByInputKey($intKeyInputs))
      $boolTrained++;

    print "<td style=\"background-color: $color\">$strArrayOutputs</td>\n";

    if(!$this->boolTrained)
    {
      print "<td style=\"background-color: $color\">$strDesiredArrayOutputs</td>\n";

      print "<td style=\"background-color: $color\">$strDesiredArrayOutputsDifferences</td>\n";
    }
    
    print "</tr>\n";
  }

  $boolTrainedPerCent = round(($boolTrained / @count($this->arrOutputs)) * 100, 1);

  if(!$this->boolTrained)
  {
    print "<tr>\n";

    print "<td colspan=\"3\">$boolTrainedPerCent per cent trained patterns</td>\n";

    print "</tr>\n";
  }
  
  print "</table>\n";
}

// ****************************************************************************

protected function calculateMaxTrainingLoops()
{
  $seconds = (int)ini_get('max_execution_time');

  $this->intMaxTrainingLoops = $seconds * $this->intMaxTrainingLoopsFactor;
}

// ****************************************************************************

/**
 * @param integer $intMaxTrainingLoopsFactor (Default: 230)
 * @throws ANN_Exception
 */

public function setMaxTrainingLoopsFactor($intMaxTrainingLoopsFactor = 230)
{
  if(!is_int($intMaxTrainingLoopsFactor) && $intMaxTrainingLoopsFactor > 0)
    throw new ANN_Exception('Constraints: $intMaxTrainingLoopsFactor should be an positive integer');

  $this->intMaxTrainingLoopsFactor = $intMaxTrainingLoopsFactor;
}

// ****************************************************************************

/**
 * @uses calculateMaxTrainingLoops()
 */

public function __wakeup()
{
  $this->calculateMaxTrainingLoops();

  $this->boolNetworkActivated = FALSE;
}

// ****************************************************************************

/**
 * @param string $strFilename (Default: null)
 * @uses parent::loadFromFile()
 * @uses self::getDefaultFilename()
 */

public static function loadFromFile($strFilename = null)
{
  if($strFilename === null)
    $strFilename = self::getDefaultFilename();
  
  return parent::loadFromFile($strFilename);
}

// ****************************************************************************

/**
 * @param string $strFilename (Default: null)
 * @uses parent::saveToFile()
 * @uses self::getDefaultFilename()
 */

public function saveToFile($strFilename = null)
{
  if($strFilename === null)
    $strFilename = self::getDefaultFilename();

  parent::saveToFile($strFilename);
}

// ****************************************************************************

/**
 * @return integer
 */

public function getNumberInputs()
{
  if(isset($this->arrInputs) && is_array($this->arrInputs))
    if(isset($this->arrInputs[0]))
      return count($this->arrInputs[0]);

  return 0;
}

// ****************************************************************************

/**
 * @return integer
 */

public function getNumberHiddenLayers()
{
  if(isset($this->arrHiddenLayers) && is_array($this->arrHiddenLayers))
    return count($this->arrHiddenLayers);

  return 0;
}

// ****************************************************************************

/**
 * @return integer
 */

public function getNumberHiddens()
{
  if(isset($this->arrHiddenLayers) && is_array($this->arrHiddenLayers))
    if(isset($this->arrHiddenLayers[0]))
      return $this->arrHiddenLayers[0]->getNeuronsCount();

  return 0;
}

// ****************************************************************************

/**
 * @return integer
 */

public function getNumberOutputs()
{
  if(isset($this->arrOutputs[0]) && is_array($this->arrOutputs[0]))
    return count($this->arrOutputs[0]);

  return 0;
}

// ****************************************************************************

/**
 * Log weights while training in CSV format
 *
 * @param string $strFilename
 * @uses ANN_Logging::__construct()
 * @uses ANN_Logging::setFilename()
 */

public function logWeightsToFile($strFilename)
{
  $this->boolLoggingWeights = TRUE;

  $this->objLoggingWeights = new ANN_Logging;

  $this->objLoggingWeights->setFilename($strFilename);
}

// ****************************************************************************

/**
 * Log network errors while training in CSV format
 *
 * @param string $strFilename
 * @uses ANN_Logging::__construct()
 * @uses ANN_Logging::setFilename()
 */

public function logNetworkErrorsToFile($strFilename)
{
  $this->boolLoggingNetworkErrors = TRUE;

  $this->objLoggingNetworkErrors = new ANN_Logging;

  $this->objLoggingNetworkErrors->setFilename($strFilename);
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

  // ****** arrHiddenLayers ****************

  foreach($this->arrHiddenLayers as $intKeyLayer => $objHiddenLayer)
  {
    $arrNeurons = $objHiddenLayer->getNeurons();

    foreach($arrNeurons as $intKeyNeuron => $objNeuron)
      foreach($objNeuron->getWeights() as $intKeyWeight => $weight)
          $arrData["H$intKeyLayer-N$intKeyNeuron-W$intKeyWeight"] = round($weight, 5);
  }

  // ****** objOutputLayer *****************

  $arrNeurons = $this->objOutputLayer->getNeurons();

  foreach($arrNeurons as $intKeyNeuron => $objNeuron)
    foreach($objNeuron->getWeights() as $intKeyWeight => $weight)
        $arrData["O-N$intKeyNeuron-W$intKeyWeight"] = round($weight, 5);

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

  $arrData['network error'] = number_format($this->getNetworkError(), 8, ',', '');

  $arrData['learning rate'] = $this->floatLearningRate;

  $this->objLoggingNetworkErrors->logData($arrData);
}

// ****************************************************************************

/**
 * @return float
 * @uses getOutputs()
 */

protected function getNetworkError()
{
  $floatError = 0;

  $arrNetworkOutputs = $this->getOutputs();
  
  foreach($this->arrOutputs as $intKeyOutputs => $arrDesiredOutputs)
    foreach($arrDesiredOutputs as $intKeyOutput => $floatDesiredOutput)
      $floatError += pow($arrNetworkOutputs[$intKeyOutputs][$intKeyOutput] - $floatDesiredOutput, 2);

  return $floatError / 2;
}

// ****************************************************************************

/**
 * @param string $strUsername
 * @param string $strPassword
 * @param string $strHost
 * @return ANN_Network
 * @throws ANN_Exception
 */

public function trainByHost($strUsername, $strPassword, $strHost)
{
  if(!extension_loaded('curl'))
    throw new ANN_Exception('Curl extension is not installed or active on this system');

  $handleCurl = curl_init();

  settype($strUsername, 'string');
  settype($strPassword, 'string');
  settype($strHost, 'string');

  curl_setopt($handleCurl, CURLOPT_URL, $strHost);
  curl_setopt($handleCurl, CURLOPT_POST, TRUE);
  curl_setopt($handleCurl, CURLOPT_POSTFIELDS, "mode=trainbyhost&username=$strUsername&password=$strPassword&network=". serialize($this));
  curl_setopt($handleCurl, CURLOPT_RETURNTRANSFER, 1);

  $strResult = curl_exec($handleCurl);

  curl_close($handleCurl);

  $objNetwork = @unserialize($strResult);

  if($objNetwork instanceof ANN_Network)
    return $objNetwork;
}

// ****************************************************************************

/**
 * @param string $strUsername
 * @param string $strPassword
 * @param string $strHost
 * @throws ANN_Exception
 */

public function saveToHost($strUsername, $strPassword, $strHost)
{
  if(!extension_loaded('curl'))
    throw new ANN_Exception('Curl extension is not installed or active on this system');

  $handleCurl = curl_init();

  settype($strUsername, 'string');
  settype($strPassword, 'string');
  settype($strHost,     'string');

  curl_setopt($handleCurl, CURLOPT_URL, $strHost);
  curl_setopt($handleCurl, CURLOPT_POST, TRUE);
  curl_setopt($handleCurl, CURLOPT_POSTFIELDS, "mode=savetohost&username=$strUsername&password=$strPassword&network=". serialize($this));

  curl_exec($handleCurl);

  curl_close($handleCurl);
}

// ****************************************************************************

/**
 * @param string $strUsername
 * @param string $strPassword
 * @param string $strHost
 * @return ANN_Network
 * @throws ANN_Exception
 */

public static function loadFromHost($strUsername, $strPassword, $strHost)
{
  if(!extension_loaded('curl'))
    throw new ANN_Exception('Curl extension is not installed or active on this system');

  $handleCurl = curl_init();

  settype($strUsername, 'string');
  settype($strPassword, 'string');
  settype($strHost,     'string');

  curl_setopt($handleCurl, CURLOPT_URL, $strHost);
  curl_setopt($handleCurl, CURLOPT_POST, TRUE);
  curl_setopt($handleCurl, CURLOPT_POSTFIELDS, "mode=loadfromhost&username=$strUsername&password=$strPassword");
  curl_setopt($handleCurl, CURLOPT_RETURNTRANSFER, 1);

  $strResult = curl_exec($handleCurl);

  curl_close($handleCurl);

  $objNetwork = unserialize(trim($strResult));

  if($objNetwork instanceof ANN_Network)
    return $objNetwork;
}

// ****************************************************************************

/**
 * @uses setOutputType()
 */

protected function detectOutputType()
{
  foreach($this->arrOutputs as $arrOutputs)
    foreach($arrOutputs as $floatOutput)
      if($floatOutput < 1 && $floatOutput > 0)
      {
        $this->setOutputType(self::OUTPUT_LINEAR);

        return;
      }

  $this->setOutputType(self::OUTPUT_BINARY);
}

// ****************************************************************************

/**
 * Setting the percentage of output error in comparison to the desired output
 *
 * @param float $floatOutputErrorTolerance (Default: 0.02)
 */

public function setOutputErrorTolerance($floatOutputErrorTolerance = 0.02)
{
  if($floatOutputErrorTolerance < 0 || $floatOutputErrorTolerance > 0.1)
    throw new ANN_Exception('$floatOutputErrorTolerance must be between 0 and 0.1');

  $this->floatOutputErrorTolerance = $floatOutputErrorTolerance;
}

// ****************************************************************************
}
