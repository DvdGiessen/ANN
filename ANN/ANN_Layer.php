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
 * @access private
 */

final class ANN_Layer
{
/**#@+
 * @ignore
 */

protected $arrNeurons   = array();
protected $arrOutputs   = array();
protected $objNetwork   = null;
protected $objNextLayer = null;
protected $intNumberOfNeurons = null;

/**#@-*/

// ****************************************************************************

/**
 * @param ANN_Network $objNetwork
 * @param integer $intNumberOfNeurons
 * @param ANN_Layer $objNextLayer (Default: null)
 * @uses createNeurons()
 */

public function __construct(ANN_Network $objNetwork, $intNumberOfNeurons, ANN_Layer $objNextLayer = null)
{
  $this->objNetwork = $objNetwork;

  $this->objNextLayer = $objNextLayer;

  $this->createNeurons($intNumberOfNeurons);
  
  $this->intNumberOfNeurons = $intNumberOfNeurons;
}
	
// ****************************************************************************

/**
 * @param array $arrInputs
 * @uses ANN_Neuron::setInputs()
 */

public function setInputs($arrInputs)
{
	foreach ($this->arrNeurons as $objNeuron)
		$objNeuron->setInputs($arrInputs);
}
	
// ****************************************************************************

/**
 * @return array
 */

public function getNeurons()
{
	return $this->arrNeurons;
}

// ****************************************************************************

/**
 * @return integer
 */

public function getNeuronsCount()
{
	return $this->intNumberOfNeurons;
}

// ****************************************************************************

/**
 * @return array
 */

public function getOutputs()
{
	return $this->arrOutputs;
}

// ****************************************************************************

/**
 * @return array
 * @uses ANN_Maths::threshold()
 */

public function getThresholdOutputs()
{
  $arrReturnOutputs = array();

  foreach($this->arrOutputs as $intKey => $floatOutput)
    $arrReturnOutputs[$intKey] = ANN_Maths::threshold($floatOutput);
  
  return $arrReturnOutputs;
}

// ****************************************************************************

/**
 * @param integer $intNumberOfNeurons
 * @uses ANN_Neuron::__construct()
 */

protected function createNeurons($intNumberOfNeurons)
{
	for ($intIndex = 0; $intIndex < $intNumberOfNeurons; $intIndex++)
		$this->arrNeurons[] = new ANN_Neuron($this->objNetwork);
}
	
// ****************************************************************************

/**
 * @uses ANN_Neuron::activate()
 * @uses ANN_Neuron::getOutput()
 * @uses ANN_Layer::setInputs()
 * @uses ANN_Layer::activate()
 */

public function activate()
{
	foreach ($this->arrNeurons as $intKey => $objNeuron)
  {
		$objNeuron->activate();

  	$this->arrOutputs[$intKey] = $objNeuron->getOutput();
	}

	if($this->objNextLayer)
	{
  	$this->objNextLayer->setInputs($this->arrOutputs);

  	$this->objNextLayer->activate();
	}
}
	
// ****************************************************************************

/**
 * @uses calculateDeltaByBackpropagation()
 * @uses calculateDeltaByQuickProp()
 * @uses calculateDeltaByRProp()
 * @uses calculateDeltaByILR()
 * @uses ANN_Neuron::getDeltaWithMomentum()
 * @uses ANN_Neuron::setDelta()
 */

public function calculateHiddenDeltas()
{
	$floatDelta = 0;

	foreach ($this->arrNeurons as $intKeyNeuron => $objNeuron)
  {
    switch($this->objNetwork->intBackpropagationAlgorithm)
    {
      case ANN_Network::ALGORITHM_BACKPROPAGATION:
        $floatDelta = $this->calculateDeltaByBackpropagation($intKeyNeuron);

        if($this->objNetwork->boolWeightDecayMode)
          $floatDelta -= $objNeuron->getDeltaWithMomentum() * $this->objNetwork->floatWeightDecay;

        $floatDelta *= $this->objNetwork->floatLearningRate;
        break;

      case ANN_Network::ALGORITHM_ILR:
        $floatDelta = $this->calculateDeltaByILR($intKeyNeuron, $objNeuron);

        if($this->objNetwork->boolWeightDecayMode)
          $floatDelta -= $objNeuron->getDeltaWithMomentum() * $this->objNetwork->floatWeightDecay;
        break;

      case ANN_Network::ALGORITHM_QUICKPROP:
        $floatDelta = $this->calculateDeltaByQuickProp($intKeyNeuron, $objNeuron);
        break;

      case ANN_Network::ALGORITHM_RPROP:
        $floatDelta = $this->calculateDeltaByRProp($intKeyNeuron, $objNeuron);
        break;
    }

		$objNeuron->setDelta($floatDelta);
	}
}
	
// ****************************************************************************

/**
 * @param integer $intKeyNeuron
 * @return float
 * @uses ANN_Neuron::getOutput()
 * @uses ANN_Neuron::getDeltaWithMomentum()
 * @uses ANN_Neuron::getWeight()
 * @uses ANN_Layer::getNeurons()
 */

protected function calculateDeltaByBackpropagation($intKeyNeuron)
{
	$arrNeuronsNextLayer = $this->objNextLayer->getNeurons();

  $floatSum = 0;

	foreach ($arrNeuronsNextLayer as $objNeuronNextLayer)
    $floatSum += $objNeuronNextLayer->getWeight($intKeyNeuron) * $objNeuronNextLayer->getDeltaWithMomentum();

  $floatOutput = $this->arrNeurons[$intKeyNeuron]->getOutput();

  return $floatOutput * (1 - $floatOutput) * $floatSum;
}

// ****************************************************************************

/**
 * Quick propagation algorithm
 *
 * EXPERIMENTAL
 *
 * @param integer $intKeyNeuron
 * @param ANN_Neuron $objNeuron
 * @return float
 * @uses calculateDeltaByBackpropagation()
 * @uses ANN_Neuron::getDeltaWithMomentum()
 */

protected function calculateDeltaByQuickProp($intKeyNeuron, ANN_Neuron $objNeuron)
{
 if($this->objNetwork->boolFirstLoopOfTraining)
    return $this->calculateDeltaByBackpropagation($intKeyNeuron, $objNeuron, $objNextLayer);

  $floatDeltaPrevious = $objNeuron->getDeltaWithMomentum();

  if(($floatDeltaPrevious - $floatDelta) != 0)
  {
    if(($floatDeltaPrevious > 0 && $floatDelta > 0) || ($floatDeltaPrevious < 0 && $floatDelta < 0))
    {
      $floatQuickPropDelta = ($floatDelta / ($floatDeltaPrevious - $floatDelta)) * $floatDeltaPrevious;
    }
    else
    {
      return $floatDelta;
    }

    $floatMaxDelta = $this->objNetwork->floatQuickPropMaxWeightChangeFactor * $floatDeltaPrevious;

    if($floatQuickPropDelta > $floatMaxDelta)
      $floatQuickPropDelta = $floatMaxDelta;

    if($floatQuickPropDelta < $floatDelta)
      return $floatDelta;

    if(is_nan($floatQuickPropDelta))
      return $floatDelta;

    return $floatQuickPropDelta;
  }

  return $floatDelta;
}

// ****************************************************************************

/**
 * Individual learning rate algorithm
 *
 * EXPERIMENTAL
 *
 * @param integer $intKeyNeuron
 * @param ANN_Neuron $objNeuron
 * @return float
 * @uses calculateDeltaByBackpropagation()
 * @uses ANN_Neuron::adjustLearningRateMinus()
 * @uses ANN_Neuron::adjustLearningRatePlus()
 * @uses ANN_Neuron::setErrorWeightDerivative()
 * @uses ANN_Neuron::getErrorWeightDerivative()
 */

protected function calculateDeltaByILR($intKeyNeuron, ANN_Neuron $objNeuron)
{
  $floatDelta = $this->calculateDeltaByBackpropagation($intKeyNeuron, $objNeuron);

  if($this->objNetwork->boolFirstEpochOfTraining)
  {
    $objNeuron->setErrorWeightDerivative($floatDelta);

    return $floatDelta * 0.5;
  }

  // $floatDelta1 = $objNeuron->updateErrorWeightDerivative($floatDelta);

  $floatDelta1 = $objNeuron->getErrorWeightDerivative();

  $objNeuron->setErrorWeightDerivative($floatDelta);

  if($floatDelta * $floatDelta1 > 0)
  {
    return $floatDelta * $objNeuron->adjustLearningRatePlus();
  }
  else
  {
    return $floatDelta * $objNeuron->adjustLearningRateMinus();
  }
}

// ****************************************************************************

/**
 * RProp algorithm
 *
 * EXPERIMENTAL
 *
 * @param integer $intKeyNeuron
 * @param ANN_Neuron $objNeuron
 * @return float
 * @uses ANN_Maths::sign()
 * @uses calculateDeltaByBackpropagation()
 * @uses ANN_Maths::sign()
 * @uses ANN_Neuron::getDeltaFactor()
 * @uses ANN_Neuron::getErrorWeightDerivative()
 * @uses ANN_Neuron::setDeltaFactor()
 * @uses ANN_Neuron::setErrorWeightDerivative()
 */

protected function calculateDeltaByRProp($intKeyNeuron, ANN_Neuron $objNeuron)
{
  $floatDelta = $this->calculateDeltaByBackpropagation($intKeyNeuron, $objNeuron);

  /*
  if($this->objNetwork->boolFirstEpochOfTraining)
  {
    $objNeuron->setErrorWeightDerivative($floatDelta);

    return $floatDelta;
  }
  */

  $debug = TRUE;

  $floatDelta1 = $objNeuron->getErrorWeightDerivative();

  $objNeuron->setErrorWeightDerivative($floatDelta);

  $floatDeltaFactor = 0;

  $floatDeltaFactor1 = $objNeuron->getDeltaFactor();

  $learningRatePlus = 1.2;
  $learningRateMinus = 0.5;

  $floatDeltaMax = 50;
  $floatDeltaMin = 0.000001;

  $floatDelta_mul_delta1 = $floatDelta * $floatDelta1;

  if($debug)
    print "k = $intKeyNeuron, D = $floatDelta, D(t-1) = $floatDelta1, sign() = ". ANN_Maths::sign($floatDelta_mul_delta1) .", DF(t-1) = $floatDeltaFactor1";

  if($debug)
  {
    static $counter = 0;

    $counter++;
  }

  // ************************************

  if($floatDelta_mul_delta1 > 0)
  {
    $floatDeltaFactor = min($floatDeltaFactor1 * $learningRatePlus, $floatDeltaMax);

    $objNeuron->setDeltaFactor($floatDeltaFactor);

    if($debug && $floatDelta != 0)
      print ", D(t) = ". (ANN_maths::sign($floatDelta) * abs($floatDeltaFactor)). "<br>\n";

    if($debug && $floatDelta == 0)
      print ", D(t) = 0<br>\n";

    if($debug)
      if($counter > 1000)
        exit;

    return ANN_maths::sign($floatDelta) * abs($floatDeltaFactor);
  }

  // ************************************

  if($floatDelta_mul_delta1 < 0)
  {
    $floatDeltaFactor = max($floatDeltaFactor1 * $learningRateMinus, $floatDeltaMin);

    $objNeuron->setDeltaFactor($floatDeltaFactor);

    if($debug && $floatDelta != 0)
      print ", D(t) = ". (ANN_maths::sign($floatDelta) * abs($floatDeltaFactor)) ."<br>\n";

    if($debug && $floatDelta == 0)
      print ", D(t) = 0<br>\n";

    if($debug)
      if($counter > 1000)
        exit;

    return ANN_maths::sign($floatDelta) * abs($floatDeltaFactor);
  }

  // ************************************

  if($floatDelta_mul_delta1 == 0)
  {
    $floatDeltaFactor = $floatDeltaFactor1;

    if($debug && $floatDelta != 0)
      print ", D(t) = ". (ANN_maths::sign($floatDelta) * abs($floatDeltaFactor)). "<br>\n";

    if($debug && $floatDelta == 0)
      print ", D(t) = 0<br>\n";

    if($debug)
      if($counter > 1000)
        exit;

    return ANN_maths::sign($floatDelta) * abs($floatDeltaFactor);
  }

  // ************************************
}

// ****************************************************************************

/**
 * @param array $arrDesiredOutputs
 * @uses calculateOutputDeltaByBackpropagation()
 * @uses calculateOutputDeltaByRProp()
 * @uses calculateOutputDeltaByILR()
 * @uses calculateOutputDeltaByQuickProp()
 * @uses ANN_Neuron::setDelta()
 */

public function calculateOutputDeltas($arrDesiredOutputs)
{
	foreach ($this->arrNeurons as $intKeyNeuron => $objNeuron)
  {
    switch($this->objNetwork->intBackpropagationAlgorithm)
    {
      case ANN_Network::ALGORITHM_BACKPROPAGATION:
        $floatDelta = $this->calculateOutputDeltaByBackpropagation($arrDesiredOutputs[$intKeyNeuron], $objNeuron);
        break;

      case ANN_Network::ALGORITHM_ILR:
        $floatDelta = $this->calculateOutputDeltaByILR($arrDesiredOutputs[$intKeyNeuron], $objNeuron);
        break;

      case ANN_Network::ALGORITHM_RPROP:
        $floatDelta = $this->calculateOutputDeltaByRProp($arrDesiredOutputs[$intKeyNeuron], $objNeuron);
        break;

      case ANN_Network::ALGORITHM_QUICKPROP:
        $floatDelta = $this->calculateOutputDeltaByQuickProp($arrDesiredOutputs[$intKeyNeuron], $objNeuron);
        break;
        
      default:
        $floatDelta = 0;
    }

	  $objNeuron->setDelta($floatDelta);
	}
}
	
// ****************************************************************************

/**
 * @param float $floatDesiredOutput
 * @param ANN_Neuron $objNeuron
 * @return float
 * @uses calculateOutputDeltaByBackpropagation()
 * @uses ANN_Neuron::adjustLearningRateMinus()
 * @uses ANN_Neuron::adjustLearningRatePlus()
 * @uses ANN_Neuron::updateErrorWeightDerivative()
 */

protected function calculateOutputDeltaByILR($floatDesiredOutput, ANN_Neuron $objNeuron)
{
  $floatDelta = $this->calculateOutputDeltaByBackpropagation($floatDesiredOutput, $objNeuron);

  if($this->objNetwork->boolFirstEpochOfTraining)
  {
    $objNeuron->setErrorWeightDerivative($floatDelta);

    return $floatDelta * 0.5;
  }

  // $floatDelta1 = $objNeuron->updateErrorWeightDerivative($floatDelta);

  $floatDelta1 = $objNeuron->getErrorWeightDerivative();

  $objNeuron->setErrorWeightDerivative($floatDelta);

  if($floatDelta * $floatDelta1 > 0)
  {
    return $floatDelta * $objNeuron->adjustLearningRatePlus();
  }
  else
  {
    return $floatDelta * $objNeuron->adjustLearningRateMinus();
  }
}

// ****************************************************************************

/**
 * RProp algorithm
 *
 * EXPERIMENTAL
 *
 * @param float $floatDesiredOutput
 * @param ANN_Neuron $objNeuron
 * @return float
 * @uses ANN_Maths::sign()
 * @uses calculateOutputDeltaByBackpropagation()
 * @uses ANN_Maths::sign()
 * @uses ANN_Neuron::getDeltaFactor()
 * @uses ANN_Neuron::getErrorWeightDerivative()
 * @uses ANN_Neuron::setDeltaFactor()
 * @uses ANN_Neuron::setErrorWeightDerivative()
 */

protected function calculateOutputDeltaByRProp($floatDesiredOutput, ANN_Neuron $objNeuron)
{
  $floatDelta = $this->calculateOutputDeltaByBackpropagation($floatDesiredOutput, $objNeuron);

  if($this->objNetwork->boolFirstEpochOfTraining)
  {
    $objNeuron->setErrorWeightDerivative($floatDelta);

    return $floatDelta;
  }

  $debug = FALSE;

  $floatDelta1 = $objNeuron->getErrorWeightDerivative();

  $objNeuron->setErrorWeightDerivative($floatDelta);

  $floatDeltaFactor = 0;

  // $floatDeltaFactor1 = $objNeuron->getDeltaFactor();

  $learningRatePlus = 1.2;
  $learningRateMinus = 0.5;

  $floatDeltaMax = 50;
  $floatDeltaMin = 0.000001;

  $floatDelta_mul_delta1 = $floatDelta * $floatDelta1;

  if($debug)
    print "k = $intKeyNeuron, D = $floatDelta, D(t-1) = $floatDelta1, sign() = ". ANN_Maths::sign($floatDelta_mul_delta1) .", DF(t-1) = $floatDeltaFactor1";

  if($debug)
  {
    static $counter = 0;

    $counter++;
  }

  // ************************************

  if($floatDelta_mul_delta1 > 0)
  {
    $floatDeltaFactor = min($floatDelta * $learningRatePlus, $floatDeltaMax);

    // $objNeuron->setDeltaFactor($floatDeltaFactor);

    if($debug && $floatDelta != 0)
      print ", D(t) = ". (ANN_maths::sign($floatDelta) * abs($floatDeltaFactor)). "<br>\n";

    if($debug && $floatDelta == 0)
      print ", D(t) = 0<br>\n";

    if($debug)
      if($counter > 1000)
        exit;

    return $floatDeltaFactor;
  }

  // ************************************

  if($floatDelta_mul_delta1 < 0)
  {
    $floatDeltaFactor = max($floatDelta * $learningRateMinus, $floatDeltaMin);

    // $objNeuron->setDeltaFactor($floatDeltaFactor);

    if($debug && $floatDelta != 0)
      print ", D(t) = ". (ANN_maths::sign($floatDelta) * abs($floatDeltaFactor)) ."<br>\n";

    if($debug && $floatDelta == 0)
      print ", D(t) = 0<br>\n";

    if($debug)
      if($counter > 1000)
        exit;

    return $floatDeltaFactor;
  }

  // ************************************

  return 0;

  // ************************************
}

// ****************************************************************************

/**
 * @param float $floatDesiredOutput
 * @uses ANN_Neuron::getOutput()
 * @uses ANN_Neuron::setDelta()
 * @uses ANN_Neuron::getDeltaWithMomentum()
 */

protected function calculateOutputDeltaByBackpropagation($floatDesiredOutput, ANN_Neuron $objNeuron)
{
  $output = $objNeuron->getOutput();

	$floatDelta = $output * ($floatDesiredOutput - $output) * (1 - $output);

  if($this->objNetwork->boolWeightDecayMode)
    $floatDelta -= $objNeuron->getDeltaWithMomentum() * $this->objNetwork->floatWeightDecay;

  return $floatDelta;
}

// ****************************************************************************

/**
 * QuickProp algorithm
 *
 * EXPERIMENTAL
 *
 * @param float $floatDesiredOutput
 * @param ANN_Neuron $objNeuron
 * @return float
 * @uses calculateOutputDeltaByBackpropagation()
 */

protected function calculateOutputDeltaByQuickProp($floatDesiredOutput, ANN_Neuron $objNeuron)
{
  return $this->calculateOutputDeltaByBackpropagation($floatDesiredOutput, $objNeuron);
}

// ****************************************************************************

/**
 * @uses ANN_Neuron::adjustWeights()
 */

public function adjustWeights()
{
	foreach ($this->arrNeurons as $objNeuron)
		$objNeuron->adjustWeights();
}

// ****************************************************************************
}

?>
