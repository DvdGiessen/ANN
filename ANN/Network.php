<?php

/**
 * Artificial Neural Network - Version 2.1
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
 * @version ANN Version 2.1 by Thomas Wien
 * @copyright Copyright (c) 2002 by Eddy Young
 * @copyright Copyright (c) 2007-2011 by Thomas Wien
 * @package ANN
 */

namespace ANN;

/**
 * @package ANN
 * @access public
 */

class Network extends Filesystem
{
	/**#@+
	 * @ignore
	 */

	/**
	 * @var Layer
	 */
	protected $objOutputLayer = null;

	/**
	 * @var array
	 */
	protected $arrHiddenLayers = array();

	/**
	 * @var array
	 */
	protected $arrInputs = null;

	/**
	 * @var array
	 */
	protected $arrOutputs = null;

	/**
	 * @var integer
	 */
	protected $intTotalLoops = 0;

	/**
	 * @var integer
	 */
	protected $intTotalTrainings = 0;

	/**
	 * @var integer
	 */
	protected $intTotalActivations = 0;

	/**
	 * @var integer
	 */
	protected $intTotalActivationsRequests = 0;

	/**
	 * @var integer
	 */
	protected $intNumberOfHiddenLayers = null;

	/**
	 * @var integer
	 */
	protected $intNumberOfHiddenLayersDec = null; // decremented value

	/**
	 * @var integer
	 */
	protected $intMaxExecutionTime = 0;

	/**
	 * @var integer
	 */
	protected $intNumberEpoch = 0;

	/**
	 * @var boolean
	 */
	protected $boolLoggingWeights = FALSE;

	/**
	 * @var boolean
	 */
	protected $boolLoggingNetworkErrors = FALSE;

	/**
	 * @var boolean
	 */
	protected $boolTrained = FALSE;

	/**
	 * @var integer
	 */
	protected $intTrainingTime = 0; // Seconds

	/**
	 * @var Logging
	 */
	protected $objLoggingWeights = null;

	/**
	 * @var Logging
	 */
	protected $objLoggingNetworkErrors = null;

	/**
	 * @var boolean
	 */
	protected $boolNetworkActivated = FALSE;

	/**
	 * @var array
	 */
	protected $arrTrainingComplete = array();

	/**
	 * @var integer
	 */
	protected $intNumberOfNeuronsPerLayer = 0;

	/**
	 * @var float
	 */
	protected $floatOutputErrorTolerance = 0.02;

	/**
	 * @var float
	 */
	public $floatMomentum = 0.95;

	/**
	 * @var array
	 */
	private $arrInputsToTrain = array();

	/**
	 * @var integer
	 */
	private $intInputsToTrainIndex = -1;

	/**
	 * @var integer
	 */
	public $intOutputType = self::OUTPUT_LINEAR;

	/**
	 * @var float
	 */
	public $floatLearningRate = 0.7;

	/**
	 * @var boolean
	 */
	public $boolFirstLoopOfTraining = TRUE;

	/**
	 * @var boolean
	 */
	public $boolFirstEpochOfTraining = TRUE;
	
	/**#@-*/
	
	/**
	 * Linear output type
	 */
	
	const OUTPUT_LINEAR = 1;
	
	/**
	 * Binary output type
	 */
	
	const OUTPUT_BINARY = 2;
	
	/**
	 * @param integer $intNumberOfHiddenLayers (Default: 1)
	 * @param integer $intNumberOfNeuronsPerLayer (Default: 6)
	 * @param integer $intNumberOfOutputs (Default: 1)
	 * @uses Exception::__construct()
	 * @uses setMaxExecutionTime()
	 * @uses createHiddenLayers()
	 * @uses createOutputLayer()
	 * @throws Exception
	 */
	
	public function __construct($intNumberOfHiddenLayers = 1, $intNumberOfNeuronsPerLayer = 6, $intNumberOfOutputs = 1)
	{
	  if(!is_integer($intNumberOfHiddenLayers) && $intNumberOfHiddenLayers < 2)
	    throw new Exception('Constraints: $intNumberOfHiddenLayers must be a positiv integer >= 2');
	
	  if(!is_integer($intNumberOfNeuronsPerLayer) && $intNumberOfNeuronsPerLayer < 1)
	    throw new Exception('Constraints: $intNumberOfNeuronsPerLayer must be a positiv integer number > 1');
	
	  if(!is_integer($intNumberOfOutputs) && $intNumberOfOutputs < 1)
	    throw new Exception('Constraints: $intNumberOfOutputs must be a positiv integer number > 1');
	
		$this->createOutputLayer($intNumberOfOutputs);
		
		$this->createHiddenLayers($intNumberOfHiddenLayers, $intNumberOfNeuronsPerLayer);
	
		$this->intNumberOfHiddenLayers = $intNumberOfHiddenLayers;
	
	  $this->intNumberOfHiddenLayersDec = $this->intNumberOfHiddenLayers - 1;
	  
	  $this->intNumberOfNeuronsPerLayer = $intNumberOfNeuronsPerLayer;
	  
	  $this->setMaxExecutionTime();
	}
		
	/**
	 * @param array $arrInputs
	 */
	
	protected function setInputs($arrInputs)
	{
	  if(!is_array($arrInputs))
	    throw new Exception('Constraints: $arrInputs should be an array');
	
	  $this->arrInputs = $arrInputs;
	  
	  $this->intNumberEpoch = count($arrInputs);
	  
	  $this->nextIndexInputToTrain = 0;
	  
	  $this->boolNetworkActivated = FALSE;
	}
	
	/**
	 * @param array $arrOutputs
	 * @uses Exception::__construct()
	 * @uses detectOutputType()
	 * @uses Layer::getNeuronsCount()
	 * @throws Exception
	 */
	
	protected function setOutputs($arrOutputs)
	{
	  if(isset($arrOutputs[0]) && is_array($arrOutputs[0]))
	    if(count($arrOutputs[0]) != $this->objOutputLayer->getNeuronsCount())
	      throw new Exception('Count of arrOutputs doesn\'t fit to number of arrOutputs on instantiation of \\'. __NAMESPACE__ .'\\Network');
	
	  $this->arrOutputs = $arrOutputs;
	  
	  $this->detectOutputType();
	  
	  $this->boolNetworkActivated = FALSE;
	}
	
	/**
	 * Set Values for training or using network
	 *
	 * Set Values of inputs and outputs for training or just inputs for using
	 * already trained network.
	 *
	 * <code>
	 * $objNetwork = new \ANN\Network(2, 4, 1);
	 *
	 * $objValues = new \ANN\Values;
	 *
	 * $objValues->train()
	 *           ->input(0.12, 0.11, 0.15)
	 *           ->output(0.56);
	 *
	 * $objNetwork->setValues($objValues);
	 * </code>
	 *
	 * @param Values $objValues
	 * @uses Values::getInputsArray()
	 * @uses Values::getOutputsArray()
	 * @uses setInputs()
	 * @uses setOutputs()
	 * @since 2.0.6
	 */
	
	public function setValues(Values $objValues)
	{
	  $this->setInputs($objValues->getInputsArray());
	
	  $this->setOutputs($objValues->getOutputsArray());
	}
	
	/**
	 * @param array $arrInputs
	 * @uses Layer::setInputs()
	 */
	
	protected function setInputsToTrain($arrInputs)
	{
	  $this->arrHiddenLayers[0]->setInputs($arrInputs);
	  
	  $this->boolNetworkActivated = FALSE;
	}
		
	/**
	 * Get the output values
	 *
	 * Get the output values to the related input values set by setValues(). This
	 * method returns the output values as a two-dimensional array.
	 *
	 * @return array two-dimensional array
	 * @uses activate()
	 * @uses getCountInputs()
	 * @uses Layer::getOutputs()
	 * @uses Layer::getThresholdOutputs()
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
	
	/**
	 * @param integer $intKeyInput
	 * @return array
	 * @uses activate()
	 * @uses Layer::getOutputs()
	 * @uses Layer::getThresholdOutputs()
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
	
	/**
	 * @param integer $intNumberOfHiddenLayers
	 * @param integer $intNumberOfNeuronsPerLayer
	 * @uses Layer::__construct()
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
	
	    $this->arrHiddenLayers[$layerId] = new Layer($this, $intNumberOfNeuronsPerLayer, $nextLayer);
	  }
	
	  ksort($this->arrHiddenLayers);
	}
		
	/**
	 * @param integer $intNumberOfOutputs
	 * @uses Layer::__construct()
	 */
	
	protected function createOutputLayer($intNumberOfOutputs)
	{
		$this->objOutputLayer = new Layer($this, $intNumberOfOutputs);
	}
		
	/**
	 * @uses Layer::setInputs()
	 * @uses Layer::activate()
	 * @uses Layer::getOutputs()
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
		
	/**
	 * @return boolean
	 * @uses Exception::__construct()
	 * @uses setInputs()
	 * @uses setOutputs()
	 * @uses hasTimeLeftForTraining()
	 * @uses isTrainingComplete()
	 * @uses isTrainingCompleteByEpoch()
	 * @uses setInputsToTrain()
	 * @uses training()
	 * @uses isEpoch()
	 * @uses logWeights()
	 * @uses logNetworkErrors()
	 * @uses getNextIndexInputsToTrain()
	 * @uses isTrainingCompleteByInputKey()
	 * @uses setDynamicLearningRate()
	 * @throws Exception
	 */
	
	public function train()
	{
	  if(!$this->arrInputs)
	    throw new Exception('No arrInputs defined. Use \\'. __NAMESPACE__ .'\\Network::setValues().');
	
	  if(!$this->arrOutputs)
	    throw new Exception('No arrOutputs defined. Use \\'. __NAMESPACE__ .'\\Network::setValues().');
	
	  if($this->isTrainingComplete())
	  {
	    $this->boolTrained = TRUE;
	    
	    return $this->boolTrained;
	  }
	
	  $intStartTime = date('U');
	  
	  $this->getNextIndexInputsToTrain(TRUE);
	
	  $this->boolFirstLoopOfTraining = TRUE;
	  
	  $this->boolFirstEpochOfTraining = TRUE;
	
	  $intLoop = 0;
	  
	  while($this->hasTimeLeftForTraining())
	  {
	  	$intLoop++;

    	$this->setDynamicLearningRate($intLoop);

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
	
	  $this->intTotalLoops += $intLoop;
	
	  $this->intTrainingTime += $intStopTime - $intStartTime;
	  
	  $this->boolTrained = $this->isTrainingComplete();
	  
	  return $this->boolTrained;
	}
	
	/**
	 * @return boolean
	 */
	protected function hasTimeLeftForTraining()
	{
		return ($_SERVER['REQUEST_TIME'] + $this->intMaxExecutionTime > date('U'));
	}
		
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
		
	/**
	 * @return integer
	 */
	
	public function getTotalLoops()
	{
	  return $this->intTotalLoops;
	}
	
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
	
	/**
	 * Setting the learning rate
	 *
	 * @param float $floatLearningRate (Default: 0.7) (0.1 .. 0.9)
	 * @uses Exception::__construct()
	 * @throws Exception
	 */
	
	protected function setLearningRate($floatLearningRate = 0.7)
	{
	  if(!is_float($floatLearningRate))
	    throw new Exception('$floatLearningRate should be between 0.1 and 0.9');
	
	  if($floatLearningRate <= 0 || $floatLearningRate >= 1)
	    throw new Exception('$floatLearningRate should be between 0.1 and 0.9');
	
	  $this->floatLearningRate = $floatLearningRate;
	}
	
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
	
	/**
	 * @return integer
	 */
	
	protected function getCountInputs()
	{
	  if(isset($this->arrInputs) && is_array($this->arrInputs))
	    return count($this->arrInputs);
	
	  return 0;
	}
	
	/**
	 * @param array $arrOutputs
	 * @uses activate()
	 * @uses Layer::calculateHiddenDeltas()
	 * @uses Layer::adjustWeights()
	 * @uses Layer::calculateOutputDeltas()
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
	
	/**
	 * @return string Filename
	 */
	
	protected static function getDefaultFilename()
	{
	  return preg_replace('/\.php$/', '.dat', basename($_SERVER['PHP_SELF']));
	}
	
	/**
	 * @param integer $intType (Default: Network::OUTPUT_LINEAR)
	 * @uses Exception::__construct()
	 * @throws Exception
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
	      throw new Exception('$strType must be \\'. __NAMESPACE__ .'\\Network::OUTPUT_LINEAR or \\'. __NAMESPACE__ .'\\Network::OUTPUT_BINARY');
	  }
	}
	
	/**
	 * @param integer $intLevel (0, 1, 2) (Default: 2)
	 * @uses Neuron::getDelta()
	 * @uses Neuron::getWeights()
	 * @uses Layer::getNeurons()
	 * @uses getNumberInputs()
	 * @uses getNumberOutputs()
	 * @uses getPrintNetworkDetails1()
	 * @uses getPrintNetworkDetails2()
	 * @return string
	 */
	
	protected function getPrintNetwork($intLevel = 2)
	{
		$strPrint = ''; 
		
	  if($intLevel >= 1)
	    $strPrint .= $this->getPrintNetworkDetails1();
	
	  $countColumns = max($this->intNumberOfNeuronsPerLayer, $this->getNumberOutputs());
	
	  $strPrint .= "<table border=\"1\" style=\"background-color: #AAAAAA; border-width: 1px; border-collapse:collapse; empty-cells:show\" cellpadding=\"2\" cellspacing=\"0\">\n";
	
	  $strPrint .= "<tr>\n";
	  $strPrint .= "<td style=\"color: #DDDDDD; text-align: center\">Input<br />Layer</td>\n";
	
	  $strPrint .= "<td style=\"background-color: #CCCCCC; text-align: center\" colspan=\"$countColumns\">"
	           ."<b>". $this->getNumberInputs() ." Inputs</b></td>\n";
	  
	  $strPrint .= "</tr>\n";
	
	
		foreach($this->arrHiddenLayers as $intIndex => $objHiddenLayer)
		{
		  $strPrint .= "<tr>\n";
		  $strPrint .= "<td style=\"color: #DDDDDD; text-align: center\">Hidden<br />Layer<br />". ($intIndex + 1) ."</td>\n";
		
		  foreach($objHiddenLayer->getNeurons() as $objNeuron)
		    $strPrint .= "<td style=\"background-color: #CCCCCC; text-align: right\"><p style=\"border: solid #00FF00 1px;\"><b>Inputs</b><br /> ". (count($objNeuron->getWeights()) - 1) ." + BIAS</p>"
		          ."<p style=\"border: solid #0000FF 1px;\"><b>Delta</b><br /> ". round($objNeuron->getDelta(), 6) ."</p>"
		          ."<p style=\"border: solid #FF0000 1px;\"><b>Weights</b><br />"
		          .implode('<br />', $objNeuron->getWeights())
		          ."</p></td>\n";
		
		  for($i = $this->intNumberOfNeuronsPerLayer + 1; $i <= $countColumns; $i++)
		    $strPrint .= "<td style=\"background-color: #CCCCCC\">&nbsp;</td>\n";
		
		
		  $strPrint .= "</tr>\n";
		}
	
	  $strPrint .= "<tr>\n";
	  $strPrint .= "<td style=\"color: #DDDDDD; text-align: center\" rowspan=\"2\">Output<br />Layer</td>\n";
	
	  foreach($this->objOutputLayer->getNeurons() as $objNeuron)
	    $strPrint .= "<td style=\"background-color: #CCCCCC; text-align: right\"><p style=\"border: solid #00FF00 1px;\"><b>Inputs</b><br /> ". (count($objNeuron->getWeights()) - 1) ." + BIAS</p>"
	          ."<p style=\"border: solid #0000FF 1px;\"><b>Delta</b><br /> ". round($objNeuron->getDelta(), 6) ."</p>"
	          ."<p style=\"border: solid #FF0000 1px;\"><b>Weights</b><br />"
	          .implode('<br />', $objNeuron->getWeights())
	          ."</p></td>\n";
	
	  for($i = $this->getNumberOutputs() + 1; $i <= $countColumns; $i++)
	    $strPrint .= "<td style=\"background-color: #CCCCCC\">&nbsp;</td>\n";
	
	  $strPrint .= "</tr>\n";
	  $strPrint .= "<tr>\n";
	
	  $strPrint .= "<td style=\"background-color: #CCCCCC; text-align: center; height: 40px\" colspan=\"$countColumns\"><b>". $this->getNumberOutputs() ." Outputs</b></td>\n";
	
	  $strPrint .= "<tr>\n";
	  $strPrint .= "</table>\n";
	  
	  if($intLevel >= 2)
	    $strPrint .= $this->getPrintNetworkDetails2();
	  
	  return $strPrint;
	}
	
	/**
	 * @uses getNetworkError()
	 * @return string
	 */
	
	protected function getPrintNetworkDetails1()
	{
	  $strPrint = "<table border=\"1\" style=\"background-color: #AAAAAA; border: solid #000000 1px; border-collapse:collapse; empty-cells:show\" cellpadding=\"2\" cellspacing=\"0\">\n";
	
	  $strPrint .= "<tr>\n";
	  $strPrint .= "<td style=\"color: #DDDDDD\">Detected output type</td>\n";
	  $strPrint .= "<td style=\"background-color: #CCCCCC\">"
	        .(($this->intOutputType == self::OUTPUT_BINARY) ? 'Binary' : 'Linear')
	        ."</td>\n";
	  $strPrint .= "</tr>\n";
	
	  $strPrint .= "<tr>\n";
	  $strPrint .= "<td style=\"color: #DDDDDD\">Backpropagation algorithm</td>\n";
	  $strPrint .= "<td style=\"background-color: #CCCCCC\">Back propagation";
	
	  $strPrint .= "</td>\n";
	  $strPrint .= "</tr>\n";
	
	  $strPrint .= "<tr>\n";
	  $strPrint .= "<td style=\"color: #DDDDDD\">Activation function</td>\n";
	  $strPrint .= "<td style=\"background-color: #CCCCCC\">Sigmoid</td>\n";
	  $strPrint .= "</tr>\n";
	
	  $strPrint .= "<tr>\n";
	  $strPrint .= "<td style=\"color: #DDDDDD\">Momentum</td>\n";
	  $strPrint .= "<td style=\"background-color: #CCCCCC\">"
	        .$this->floatMomentum
	        ."</td>\n";
	  $strPrint .= "</tr>\n";

		$strPrint .= "<tr>\n";
	  $strPrint .= "<td style=\"color: #DDDDDD\">Learning rate</td>\n";
	  $strPrint .= "<td style=\"background-color: #CCCCCC\">"
	        .$this->floatLearningRate
	        ."</td>\n";
	  $strPrint .= "</tr>\n";
	
	  $strPrint .= "<tr>\n";
	  $strPrint .= "<td style=\"color: #DDDDDD\">Network error</td>\n";
	  $strPrint .= "<td style=\"background-color: #CCCCCC\">"
	        .$this->getNetworkError()
	        ."</td>\n";
	  $strPrint .= "</tr>\n";
	
	  $strPrint .= "<tr>\n";
	  $strPrint .= "<td style=\"color: #DDDDDD\">Output error tolerance</td>\n";
	  $strPrint .= "<td style=\"background-color: #CCCCCC\">+/- "
	        .$this->floatOutputErrorTolerance
	        ."</td>\n";
	  $strPrint .= "</tr>\n";
	
	  $strPrint .= "<tr>\n";
	  $strPrint .= "<td style=\"color: #DDDDDD\">Total loops</td>\n";
	  $strPrint .= "<td style=\"background-color: #CCCCCC\">"
	        .number_format($this->intTotalLoops, 0, '.', ',')
	        ." loops</td>\n";
	  $strPrint .= "</tr>\n";
	
	  $strPrint .= "<tr>\n";
	  $strPrint .= "<td style=\"color: #DDDDDD\">Total trainings</td>\n";
	  $strPrint .= "<td style=\"background-color: #CCCCCC\">"
	        .number_format($this->intTotalTrainings, 0, '.', ',')
	        ." trainings</td>\n";
	  $strPrint .= "</tr>\n";
	
	  $strPrint .= "<tr>\n";
	  $strPrint .= "<td style=\"color: #DDDDDD\">Total activations</td>\n";
	  $strPrint .= "<td style=\"background-color: #CCCCCC\">"
	        .number_format($this->intTotalActivations, 0, '.', ',')
	        ." activations</td>\n";
	  $strPrint .= "</tr>\n";
	
	  $strPrint .= "<tr>\n";
	  $strPrint .= "<td style=\"color: #DDDDDD\">Total activation requests</td>\n";
	  $strPrint .= "<td style=\"background-color: #CCCCCC\">"
	        .number_format($this->intTotalActivationsRequests, 0, '.', ',')
	        ." activation requests</td>\n";
	  $strPrint .= "</tr>\n";
	
	  $strPrint .= "<tr>\n";
	  $strPrint .= "<td style=\"color: #DDDDDD\">Epoch</td>\n";
	  $strPrint .= "<td style=\"background-color: #CCCCCC\">"
	        .$this->intNumberEpoch
	        ." loops</td>\n";
	  $strPrint .= "</tr>\n";
	
	  $intTrainingTime = ($this->intTrainingTime > 0) ? $this->intTrainingTime : 1;
	
	  $strPrint .= "<tr>\n";
	  $strPrint .= "<td style=\"color: #DDDDDD\">Training time</td>\n";
	  $strPrint .= "<td style=\"background-color: #CCCCCC\">"
	        .$this->intTrainingTime ." seconds = ". round($intTrainingTime / 60, 1) ." minutes</td>\n";
	  $strPrint .= "</tr>\n";
	
	  $strPrint .= "<tr>\n";
	  $strPrint .= "<td style=\"color: #DDDDDD\">Loops / second</td>\n";
	  $strPrint .= "<td style=\"background-color: #CCCCCC\">"
	        .round($this->intTotalLoops / $intTrainingTime) ." loops / second</td>\n";
	  $strPrint .= "</tr>\n";
	
	  $strPrint .= "<tr>\n";
	  $strPrint .= "<td style=\"color: #DDDDDD\">Training finished</td>\n";
	  $strPrint .= "<td style=\"background-color: #CCCCCC\">"
	        .(($this->boolTrained) ? 'Yes' : 'No') ."</td>\n";
	  $strPrint .= "</tr>\n";
	
	  $strPrint .= "</table>\n<br />\n";
	  
	  return $strPrint;
	}
	
	/**
	 * @uses getOutputsByInputKey()
	 * @uses isTrainingCompleteByInputKey()
	 * @return string
	 */
	
	protected function getPrintNetworkDetails2()
	{
	  $boolTrained = 0;
	
	  $strPrint = "<br />\n";
	
	  $strPrint .= "<table border=\"1\" style=\"background-color: #AAAAAA; border: solid #000000 1px; border-collapse:collapse; empty-cells:show\" cellpadding=\"2\" cellspacing=\"0\">\n";
	
	  $strPrint .= "<tr>\n";
	  $strPrint .= "<td style=\"color: #DDDDDD\">Input</td>\n";
	  $strPrint .= "<td style=\"color: #DDDDDD\">Output</td>\n";
	
	  if(!$this->boolTrained)
	  {
	    $strPrint .= "<td style=\"color: #DDDDDD\">Desired output</td>\n";
	    $strPrint .= "<td style=\"color: #DDDDDD\">Differences</td>\n";
	  }
	  
	  $strPrint .= "</tr>\n";
	
	  foreach($this->arrInputs as $intKeyInputs => $arrInputs)
	  {
	    $strPrint .= "<tr>\n";
	
	    foreach($arrInputs as $intKeyInput => $input)
	      $arrInputs[$intKeyInput] = round($input, 3);
	
	    $strPrint .= "<td style=\"color: #DDDDDD\" align=\"right\">&nbsp;<b>f</b>(". implode(', ', $arrInputs) .") =&nbsp;</td>\n";
	
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
	
	    $strPrint .= "<td style=\"background-color: $color\">$strArrayOutputs</td>\n";
	
	    if(!$this->boolTrained)
	    {
	      $strPrint .= "<td style=\"background-color: $color\">$strDesiredArrayOutputs</td>\n";
	
	      $strPrint .= "<td style=\"background-color: $color\">$strDesiredArrayOutputsDifferences</td>\n";
	    }
	    
	    $strPrint .= "</tr>\n";
	  }
	
	  $boolTrainedPerCent = round(($boolTrained / @count($this->arrOutputs)) * 100, 1);
	
	  if(!$this->boolTrained)
	  {
	    $strPrint .= "<tr>\n";
	
	    $strPrint .= "<td colspan=\"3\">$boolTrainedPerCent per cent trained patterns</td>\n";
	
	    $strPrint .= "</tr>\n";
	  }
	  
	  $strPrint .= "</table>\n";
	  
	  return $strPrint;
	}
	
	/**
	 * @throws Exception
	 */

	protected function setMaxExecutionTime()
	{
		$this->intMaxExecutionTime = (int)ini_get('max_execution_time');
		
		if($this->intMaxExecutionTime == 0)
			throw new Exception('max_execution_time is 0');
	}
	
	/**
	 * @uses setMaxExecutionTime()
	 */
	
	public function __wakeup()
	{
	  $this->setMaxExecutionTime();
	
	  $this->boolNetworkActivated = FALSE;
	}
	
	/**
	 * @param string $strFilename (Default: null)
	 * @uses parent::loadFromFile()
	 * @uses getDefaultFilename()
	 */
	
	public static function loadFromFile($strFilename = null)
	{
	  if($strFilename === null)
	    $strFilename = self::getDefaultFilename();
	  
	  return parent::loadFromFile($strFilename);
	}
	
	/**
	 * @param string $strFilename (Default: null)
	 * @uses parent::saveToFile()
	 * @uses getDefaultFilename()
	 */
	
	public function saveToFile($strFilename = null)
	{
	  if($strFilename === null)
	    $strFilename = self::getDefaultFilename();
	
	  parent::saveToFile($strFilename);
	}
	
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
	
	/**
	 * @return integer
	 */
	
	public function getNumberHiddenLayers()
	{
	  if(isset($this->arrHiddenLayers) && is_array($this->arrHiddenLayers))
	    return count($this->arrHiddenLayers);
	
	  return 0;
	}
	
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
	
	/**
	 * @return integer
	 */
	
	public function getNumberOutputs()
	{
	  if(isset($this->arrOutputs[0]) && is_array($this->arrOutputs[0]))
	    return count($this->arrOutputs[0]);
	
	  return 0;
	}
	
	/**
	 * Log weights while training in CSV format
	 *
	 * @param string $strFilename
	 * @uses Logging::__construct()
	 * @uses Logging::setFilename()
	 */
	
	public function logWeightsToFile($strFilename)
	{
	  $this->boolLoggingWeights = TRUE;
	
	  $this->objLoggingWeights = new Logging;
	
	  $this->objLoggingWeights->setFilename($strFilename);
	}
	
	/**
	 * Log network errors while training in CSV format
	 *
	 * @param string $strFilename
	 * @uses Logging::__construct()
	 * @uses Logging::setFilename()
	 */
	
	public function logNetworkErrorsToFile($strFilename)
	{
	  $this->boolLoggingNetworkErrors = TRUE;
	
	  $this->objLoggingNetworkErrors = new Logging;
	
	  $this->objLoggingNetworkErrors->setFilename($strFilename);
	}
	
	/**
	 * @uses Layer::getNeurons()
	 * @uses Logging::logData()
	 * @uses Neuron::getWeights()
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
	
	/**
	 * @uses getNetworkError()
	 * @uses Logging::logData()
	 */
	
	protected function logNetworkErrors()
	{
	  $arrData = array();
	
	  $arrData['network error'] = number_format($this->getNetworkError(), 8, ',', '');
	
	  $arrData['learning rate'] = $this->floatLearningRate;
	
	  $this->objLoggingNetworkErrors->logData($arrData);
	}
	
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
	
	/**
	 * @param string $strUsername
	 * @param string $strPassword
	 * @param string $strHost
	 * @return Network
	 * @throws Exception
	 */
	
	public function trainByHost($strUsername, $strPassword, $strHost)
	{
	  if(!extension_loaded('curl'))
	    throw new Exception('Curl extension is not installed or active on this system');
	
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
	
	  if($objNetwork instanceof Network)
	    return $objNetwork;
	}
	
	/**
	 * @param string $strUsername
	 * @param string $strPassword
	 * @param string $strHost
	 * @throws Exception
	 */
	
	public function saveToHost($strUsername, $strPassword, $strHost)
	{
	  if(!extension_loaded('curl'))
	    throw new Exception('Curl extension is not installed or active on this system');
	
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
	
	/**
	 * @param string $strUsername
	 * @param string $strPassword
	 * @param string $strHost
	 * @return Network
	 * @throws Exception
	 */
	
	public static function loadFromHost($strUsername, $strPassword, $strHost)
	{
	  if(!extension_loaded('curl'))
	    throw new Exception('Curl extension is not installed or active on this system');
	
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
	
	  if($objNetwork instanceof Network)
	    return $objNetwork;
	}
	
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
	
	/**
	 * Setting the percentage of output error in comparison to the desired output
	 *
	 * @param float $floatOutputErrorTolerance (Default: 0.02)
	 */
	
	public function setOutputErrorTolerance($floatOutputErrorTolerance = 0.02)
	{
	  if($floatOutputErrorTolerance < 0 || $floatOutputErrorTolerance > 0.1)
	    throw new Exception('$floatOutputErrorTolerance must be between 0 and 0.1');
	
	  $this->floatOutputErrorTolerance = $floatOutputErrorTolerance;
	}
	
	/**
	 * @param float $floatMomentum (Default: 0.95) (0 .. 1)
	 * @uses Exception::__construct()
	 * @throws Exception
	 */
	
	public function setMomentum($floatMomentum = 0.95)
	{
	  if(!is_float($floatMomentum) && !is_integer($floatMomentum))
	    throw new Exception('$floatLearningRate should be between 0 and 1');
	
	  if($floatMomentum <= 0 || $floatMomentum > 1)
	    throw new Exception('$floatLearningRate should be between 0 and 1');
	
	  $this->floatMomentum = $floatMomentum;
	}

	/**
	 * @param integer $intLevel (Default: 2)
	 * @uses getPrintNetwork()
	 */

	public function printNetwork($intLevel = 2)
	{
		print $this->getPrintNetwork($intLevel);
	}	
	
	/**
	 * @param integer $intLevel (Default: 2)
	 * @uses printNetwork()
	 */

	public function __invoke($intLevel = 2)
	{
		$this->printNetwork($intLevel);
	}
	
	/**
	 * @uses getPrintNetwork()
	 * @return string
	 */

	public function __toString()
	{
		return $this->getPrintNetwork();
	}
	
	/**
	 * Dynamic Learning Rate
	 *
	 * Setting learning rate all 1000 loops dynamically
	 *
	 * @param integer $intLoop
	 * @return float
	 * @uses setLearningRate()
	 */

	protected function setDynamicLearningRate($intLoop)
	{
	  if($intLoop % 1000)
	    return;
	
	  $floatLearningRate = (mt_rand(1, 7) / 10);
	  
    $this->setLearningRate($floatLearningRate);
	}
}
