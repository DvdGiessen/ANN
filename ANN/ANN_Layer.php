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

	
/**
 * @package ANN
 * @access private
 */

final class ANN_Layer
{
/**#@+
 * @ignore
 */

protected $neurons = array();
protected $outputs = array();
protected $network = null;
protected $nextLayer = null;

/**#@-*/

// ****************************************************************************

/**
 * @param ANN_Network $network
 * @param integer $numberOfNeurons
 * @param ANN_Layer $nextLayer (Default: null)
 * @uses createNeurons()
 */

public function __construct(ANN_Network $network, $numberOfNeurons, ANN_Layer $nextLayer = null)
{
  $this->network = $network;

  $this->nextLayer = $nextLayer;

  $this->createNeurons($numberOfNeurons);
}
	
// ****************************************************************************

/**
 * @param array $inputs
 * @uses ANN_Neuron::setInputs()
 */

public function setInputs($inputs)
{
	foreach ($this->neurons as $neuron)
		$neuron->setInputs($inputs);
}
	
// ****************************************************************************

/**
 * @return array
 */

public function getNeurons()
{
	return $this->neurons;
}

// ****************************************************************************

/**
 * @return integer
 */

public function getNeuronsCount()
{
	return count($this->neurons);
}

// ****************************************************************************

/**
 * @return array
 */

protected function getInputs()
{
	return $this->inputs;
}
	
// ****************************************************************************

/**
 * @return array
 */

public function getOutputs()
{
	return $this->outputs;
}

// ****************************************************************************

/**
 * @return array
 * @uses ANN_Maths::threshold()
 */

public function getThresholdOutputs()
{
  $return_outputs = array();

  foreach($this->outputs as $key => $output)
    $return_outputs[$key] = ANN_Maths::threshold($output);
  
  return $return_outputs;
}

// ****************************************************************************

/**
 * @param integer $numberOfNeurons
 * @param boolean $outputLayer  (Default:  FALSE)
 * @uses ANN_Neuron::__construct()
 */

protected function createNeurons($numberOfNeurons)
{
	for ($i = 0; $i < $numberOfNeurons; $i++)
		$this->neurons[] = new ANN_Neuron($this->network);
}
	
// ****************************************************************************

/**
 * @uses ANN_Neuron::activate()
 * @uses ANN_Neuron::getOutput()
 */

public function activate()
{
	foreach ($this->neurons as $k => $neuron)
  {
		$neuron->activate();

  	$this->outputs[$k] = $neuron->getOutput();
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
	$delta = 0;

	foreach ($this->neurons as $keyNeuron => $neuron)
  {
    switch($this->network->backpropagationAlgorithm)
    {
    case ANN_Network::ALGORITHM_BACKPROPAGATION:
      $delta = $this->calculateDeltaByBackpropagation($keyNeuron);

      if($this->network->weightDecayMode)
        $delta -= $neuron->getDeltaWithMomentum() * $this->network->weightDecay;

      $delta *= $this->network->learningRate;
      break;

    case ANN_Network::ALGORITHM_ILR:
      $delta = $this->calculateDeltaByILR($keyNeuron, $neuron);

      if($this->network->weightDecayMode)
        $delta -= $neuron->getDeltaWithMomentum() * $this->network->weightDecay;
      break;

    case ANN_Network::ALGORITHM_QUICKPROP:
      $delta = $this->calculateDeltaByQuickProp($keyNeuron, $neuron);
      break;

    case ANN_Network::ALGORITHM_RPROP:
      $delta = $this->calculateDeltaByRProp($keyNeuron, $neuron);
      break;
    }

		$neuron->setDelta($delta);
	}
}
	
// ****************************************************************************

/**
 * @param integer $keyNeuron
 * @return float
 * @uses ANN_Neuron::getOutput()
 * @uses ANN_Neuron::getDeltaWithMomentum()
 * @uses ANN_Neuron::getWeight()
 * @uses ANN_Layer::getNeurons()
 */

protected function calculateDeltaByBackpropagation($keyNeuron)
{
	$neuronsNextLayer = $this->nextLayer->getNeurons();

  $sum = 0;

	foreach ($neuronsNextLayer as $neuronNextLayer)
    $sum += $neuronNextLayer->getWeight($keyNeuron) * $neuronNextLayer->getDeltaWithMomentum();

  $output = $this->neurons[$keyNeuron]->getOutput();

  return $output * (1 - $output) * $sum;
}

// ****************************************************************************

/**
 * Quick propagation algorithm
 *
 * EXPERIMENTAL
 *
 * @param integer $keyNeuron
 * @param ANN_Neuron $neuron
 * @return float
 * @uses calculateDeltaByBackpropagation()
 * @uses ANN_Neuron::getDeltaWithMomentum()
 */

protected function calculateDeltaByQuickProp($keyNeuron, ANN_Neuron $neuron)
{
 if($this->network->firstLoopOfTraining)
    return $this->calculateDeltaByBackpropagation($keyNeuron, $neuron, $nextLayer);

  $deltaPrevious = $neuron->getDeltaWithMomentum();

  if(($deltaPrevious - $delta) != 0)
  {
    if(($deltaPrevious > 0 && $delta > 0) || ($deltaPrevious < 0 && $delta < 0))
    {
      $quickPropDelta = ($delta / ($deltaPrevious - $delta)) * $deltaPrevious;
    }
    else
    {
      return $delta;
    }

    $maxDelta = $this->network->quickPropMaxWeightChangeFactor * $deltaPrevious;

    if($quickPropDelta > $maxDelta)
      $quickPropDelta = $maxDelta;

    if($quickPropDelta < $delta)
      return $delta;

    if(is_nan($quickPropDelta))
      return $delta;

    return $quickPropDelta;
  }

  return $delta;
}

// ****************************************************************************

/**
 * Individual learning rate algorithm
 *
 * EXPERIMENTAL
 *
 * @param integer $keyNeuron
 * @param ANN_Neuron $neuron
 * @return float
 * @uses calculateDeltaByBackpropagation()
 * @uses ANN_Neuron::adjustLearningRateMinus()
 * @uses ANN_Neuron::adjustLearningRatePlus()
 * @uses ANN_Neuron::setErrorWeightDerivative()
 * @uses ANN_Neuron::getErrorWeightDerivative()
 */

protected function calculateDeltaByILR($keyNeuron, ANN_Neuron $neuron)
{
  $delta = $this->calculateDeltaByBackpropagation($keyNeuron, $neuron);

  if($this->network->firstEpochOfTraining)
  {
    $neuron->setErrorWeightDerivative($delta);

    return $delta * 0.5;
  }

  // $delta1 = $neuron->updateErrorWeightDerivative($delta);

  $delta1 = $neuron->getErrorWeightDerivative();

  $neuron->setErrorWeightDerivative($delta);

  if($delta * $delta1 > 0)
  {
    return $delta * $neuron->adjustLearningRatePlus();
  }
  else
  {
    return $delta * $neuron->adjustLearningRateMinus();
  }
}

// ****************************************************************************

/**
 * RProp algorithm
 *
 * EXPERIMENTAL
 *
 * @param integer $keyNeuron
 * @param ANN_Neuron $neuron
 * @return float
 * @uses ANN_Maths::sign()
 * @uses calculateDeltaByBackpropagation()
 * @uses ANN_Maths::sign()
 * @uses ANN_Neuron::getDeltaFactor()
 * @uses ANN_Neuron::getErrorWeightDerivative()
 * @uses ANN_Neuron::setDeltaFactor()
 * @uses ANN_Neuron::setErrorWeightDerivative()
 */

protected function calculateDeltaByRProp($keyNeuron, ANN_Neuron $neuron)
{
  $delta = $this->calculateDeltaByBackpropagation($keyNeuron, $neuron);

  /*
  if($this->network->firstEpochOfTraining)
  {
  $neuron->setErrorWeightDerivative($delta);

  return $delta;
  }
  */

  $debug = TRUE;

  $delta1 = $neuron->getErrorWeightDerivative();

  $neuron->setErrorWeightDerivative($delta);

  $deltaFactor = 0;

  $deltaFactor1 = $neuron->getDeltaFactor();

  $learningRatePlus = 1.2;
  $learningRateMinus = 0.5;

  $deltaMax = 50;
  $deltaMin = 0.000001;

  $delta_mul_delta1 = $delta * $delta1;

  if($debug)
    print "k = $keyNeuron, D = $delta, D(t-1) = $delta1, sign() = ". ANN_Maths::sign($delta_mul_delta1) .", DF(t-1) = $deltaFactor1";

  if($debug)
  {
    static $counter = 0;

    $counter++;
  }

  // ************************************

  if($delta_mul_delta1 > 0)
  {
    $deltaFactor = min($deltaFactor1 * $learningRatePlus, $deltaMax);

    $neuron->setDeltaFactor($deltaFactor);

    if($debug && $delta != 0)
      print ", D(t) = ". (ANN_maths::sign($delta) * abs($deltaFactor)). "<br>\n";

    if($debug && $delta == 0)
      print ", D(t) = 0<br>\n";

    if($debug)
      if($counter > 1000) exit;

    return ANN_maths::sign($delta) * abs($deltaFactor);
  }

  // ************************************

  if($delta_mul_delta1 < 0)
  {
    $deltaFactor = max($deltaFactor1 * $learningRateMinus, $deltaMin);

    $neuron->setDeltaFactor($deltaFactor);

    if($debug && $delta != 0)
      print ", D(t) = ". (ANN_maths::sign($delta) * abs($deltaFactor)) ."<br>\n";

    if($debug && $delta == 0)
      print ", D(t) = 0<br>\n";

    if($debug)
      if($counter > 1000) exit;

    return ANN_maths::sign($delta) * abs($deltaFactor);
  }

  // ************************************

  if($delta_mul_delta1 == 0)
  {
    $deltaFactor = $deltaFactor1;

    if($debug && $delta != 0)
      print ", D(t) = ". (ANN_maths::sign($delta) * abs($deltaFactor)). "<br>\n";

    if($debug && $delta == 0)
      print ", D(t) = 0<br>\n";

    if($debug)
      if($counter > 1000) exit;

    return ANN_maths::sign($delta) * abs($deltaFactor);
  }

  // ************************************
}

// ****************************************************************************

/**
 * @param array $desiredOutputs
 * @uses calculateOutputDeltaByBackpropagation()
 * @uses calculateOutputDeltaByRProp()
 * @uses calculateOutputDeltaByILR()
 * @uses calculateOutputDeltaByQuickProp()
 * @uses ANN_Neuron::setDelta()
 */

public function calculateOutputDeltas($desiredOutputs)
{
	foreach ($this->neurons as $keyNeuron => $neuron)
  {
    switch($this->network->backpropagationAlgorithm)
    {
    case ANN_Network::ALGORITHM_BACKPROPAGATION:
      $delta = $this->calculateOutputDeltaByBackpropagation($desiredOutputs[$keyNeuron], $neuron);
      break;

    case ANN_Network::ALGORITHM_ILR:
      $delta = $this->calculateOutputDeltaByILR($desiredOutputs[$keyNeuron], $neuron);
      break;

    case ANN_Network::ALGORITHM_RPROP:
      $delta = $this->calculateOutputDeltaByRProp($desiredOutputs[$keyNeuron], $neuron);
      break;

    case ANN_Network::ALGORITHM_QUICKPROP:
      $delta = $this->calculateOutputDeltaByQuickProp($desiredOutputs[$keyNeuron], $neuron);
      break;
    }

	  $neuron->setDelta($delta);
	}
}
	
// ****************************************************************************

/**
 * @param float $desiredOutput
 * @param ANN_Neuron $neuron
 * @return float
 * @uses calculateOutputDeltaByBackpropagation()
 * @uses ANN_Neuron::adjustLearningRateMinus()
 * @uses ANN_Neuron::adjustLearningRatePlus()
 * @uses ANN_Neuron::updateErrorWeightDerivative()
 */

protected function calculateOutputDeltaByILR($desiredOutput, ANN_Neuron $neuron)
{
  $delta = $this->calculateOutputDeltaByBackpropagation($desiredOutput, $neuron);

  if($this->network->firstEpochOfTraining)
  {
    $neuron->setErrorWeightDerivative($delta);

    return $delta * 0.5;
  }

  // $delta1 = $neuron->updateErrorWeightDerivative($delta);

  $delta1 = $neuron->getErrorWeightDerivative();

  $neuron->setErrorWeightDerivative($delta);

  if($delta * $delta1 > 0)
  {
    return $delta * $neuron->adjustLearningRatePlus();
  }
  else
  {
    return $delta * $neuron->adjustLearningRateMinus();
  }
}

// ****************************************************************************

/**
 * RProp algorithm
 *
 * EXPERIMENTAL
 *
 * @param float $desiredOutput
 * @param ANN_Neuron $neuron
 * @return float
 * @uses ANN_Maths::sign()
 * @uses calculateOutputDeltaByBackpropagation()
 * @uses ANN_Maths::sign()
 * @uses ANN_Neuron::getDeltaFactor()
 * @uses ANN_Neuron::getErrorWeightDerivative()
 * @uses ANN_Neuron::setDeltaFactor()
 * @uses ANN_Neuron::setErrorWeightDerivative()
 */

protected function calculateOutputDeltaByRProp($desiredOutput, ANN_Neuron $neuron)
{
  $delta = $this->calculateOutputDeltaByBackpropagation($desiredOutput, $neuron);

  if($this->network->firstEpochOfTraining)
  {
    $neuron->setErrorWeightDerivative($delta);

    return $delta;
  }

  $debug = FALSE;

  $delta1 = $neuron->getErrorWeightDerivative();

  $neuron->setErrorWeightDerivative($delta);

  $deltaFactor = 0;

  // $deltaFactor1 = $neuron->getDeltaFactor();

  $learningRatePlus = 1.2;
  $learningRateMinus = 0.5;

  $deltaMax = 50;
  $deltaMin = 0.000001;

  $delta_mul_delta1 = $delta * $delta1;

  if($debug)
    print "k = $keyNeuron, D = $delta, D(t-1) = $delta1, sign() = ". ANN_Maths::sign($delta_mul_delta1) .", DF(t-1) = $deltaFactor1";

  if($debug)
  {
    static $counter = 0;

    $counter++;
  }

  // ************************************

  if($delta_mul_delta1 > 0)
  {
    $deltaFactor = min($delta * $learningRatePlus, $deltaMax);

    // $neuron->setDeltaFactor($deltaFactor);

    if($debug && $delta != 0)
      print ", D(t) = ". (ANN_maths::sign($delta) * abs($deltaFactor)). "<br>\n";

    if($debug && $delta == 0)
      print ", D(t) = 0<br>\n";

    if($debug)
      if($counter > 1000) exit;

    return $deltaFactor;
  }

  // ************************************

  if($delta_mul_delta1 < 0)
  {
    $deltaFactor = max($delta * $learningRateMinus, $deltaMin);

    // $neuron->setDeltaFactor($deltaFactor);

    if($debug && $delta != 0)
      print ", D(t) = ". (ANN_maths::sign($delta) * abs($deltaFactor)) ."<br>\n";

    if($debug && $delta == 0)
      print ", D(t) = 0<br>\n";

    if($debug)
      if($counter > 1000) exit;

    return $deltaFactor;
  }

  // ************************************

  return 0;

  // ************************************
}

// ****************************************************************************

/**
 * @param float $desiredOutput
 * @uses ANN_Neuron::getOutput()
 * @uses ANN_Neuron::setDelta()
 * @uses ANN_Neuron::getDeltaWithMomentum()
 */

protected function calculateOutputDeltaByBackpropagation($desiredOutput, ANN_Neuron $neuron)
{
  $output = $neuron->getOutput();

	$delta = $output * ($desiredOutput - $output) * (1 - $output);

  if($this->network->weightDecayMode)
    $delta -= $neuron->getDeltaWithMomentum() * $this->network->weightDecay;

  return $delta;
}

// ****************************************************************************

/**
 * QuickProp algorithm
 *
 * EXPERIMENTAL
 *
 * @param float $desiredOutput
 * @param ANN_Neuron $neuron
 * @return float
 * @uses calculateOutputDeltaByBackpropagation()
 */

protected function calculateOutputDeltaByQuickProp($desiredOutput, ANN_Neuron $neuron)
{
  return $this->calculateOutputDeltaByBackpropagation($desiredOutput, $neuron);
}

// ****************************************************************************

/**
 * @uses ANN_Neuron::adjustWeights()
 */

public function adjustWeights()
{
	foreach ($this->neurons as $neuron)
		$neuron->adjustWeights();
}

// ****************************************************************************
}

?>
