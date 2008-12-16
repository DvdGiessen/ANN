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

final class ANN_Neuron
{
/**#@+
 * @ignore
 */
 
protected $inputs = null;
protected $weights = null;
protected $output = null;
protected $delta = 0;
protected $network = null;
protected $deltaFactor = 0.1;
protected $errorWeightDerivative = 0;
protected $learningRate = 0;

/**#@-*/

// ****************************************************************************

/**
 * @param ANN_Network $network
 */

public function __construct(ANN_Network $network)
{
  $this->network = $network;
  
  $this->learningRate = ANN_Maths::random(400, 600) / 1000;
}

// ****************************************************************************

/**
 * @param array $inputs
 * @uses initialiseWeights()
 */

public function setInputs($inputs)
{
	$inputs[] = 1; // bias
		
	$this->inputs = $inputs;

	if(!$this->weights)
		$this->initialiseWeights();
}
	
// ****************************************************************************

/**
 * @param array $output
 */

protected function setOutput($output)
{
  $this->output = $output;
}
	
// ****************************************************************************

/**
 * @param float $delta
 */

public function setDelta($delta)
{
	$this->delta = round($delta, ANN_Maths::PRECISION);
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

public function getWeights()
{
	return $this->weights;
}

// ****************************************************************************

/**
 * @param integer $keyNeuron
 * @return float
 */

public function getWeight($keyNeuron)
{
	return $this->weights[$keyNeuron];
}

// ****************************************************************************

/**
 * @return array
 */

public function getOutput()
{
	return $this->output;
}

// ****************************************************************************

/**
 * @return float
 */

public function getDeltaWithMomentum()
{
	return $this->network->momentum * $this->delta;
}

// ****************************************************************************

/**
 * @return float
 */

public function getDelta()
{
	return $this->delta;
}

// ****************************************************************************

/**
 * @uses ANN_Maths::random()
 */

protected function initialiseWeights()
{
	foreach ($this->inputs as $k => $input)
		$this->weights[$k] = ANN_Maths::random(-500, 500) / 1000;
}
	
// ****************************************************************************

/**
 * @uses ANN_Maths::linearSaturated01()
 * @uses ANN_Maths::sigmoid()
 * @uses ANN_Maths::tangensHyperbolicus01()
 * @uses setOutput()
 */

public function activate()
{
	$sum = 0;
		
	foreach ($this->inputs as $k => $input)
		$sum += $input * $this->weights[$k];

//  $this->setOutput(ANN_tanh_1_2($sum));
  $this->setOutput(ANN_Maths::sigmoid($sum));
//  $this->setOutput(ANN_Maths::tangensHyperbolicus01($sum));
//  $this->setOutput(ANN_Maths::linearSaturated01($sum));
}
	
// ****************************************************************************

public function adjustWeights()
{
	foreach ($this->weights as $k => $weight)
		$this->weights[$k] += round($this->inputs[$k] * $this->delta, ANN_Maths::PRECISION);
}

// ****************************************************************************

/**
 * @param float $deltaFactor
 */

public function setDeltaFactor($deltaFactor)
{
  $this->deltaFactor = $deltaFactor;
}

// ****************************************************************************

/**
 * @return float
 */

public function getDeltaFactor()
{
  return $this->deltaFactor;
}

// ****************************************************************************

/**
 * @param float $errorWeightDerivative dE/dw(t)
 * @return float dE/dw(t-1)
 */

public function updateErrorWeightDerivative($errorWeightDerivative)
{
  $return = $this->errorWeightDerivative;
  
  $this->errorWeightDerivative = $errorWeightDerivative;
  
  return $this->errorWeightDerivative;
}

// ****************************************************************************

/**
 * @param float $errorWeightDerivative
 */

public function setErrorWeightDerivative($errorWeightDerivative)
{
  $this->errorWeightDerivative = $errorWeightDerivative;
}

// ****************************************************************************

/**
 * @return float
 */

public function getErrorWeightDerivative()
{
  return $this->errorWeightDerivative;
}

// ****************************************************************************

/**
 * @return float
 */

public function adjustLearningRatePlus()
{
  $learningRate = $this->learningRate + 0.02;
  
  if($learningRate < 0.6)
    $this->learningRate = $learningRate;

  return $this->learningRate;
}

// ****************************************************************************

/**
 * @return float
 */

public function adjustLearningRateMinus()
{
  $learningRate = $this->learningRate - 0.02;

  if($learningRate > 0.4)
    $this->learningRate = $learningRate;

  return $this->learningRate;
}

// ****************************************************************************

/**
 * @return float
 */

public function getLearningRate()
{
  return $this->learningRate;
}

// ****************************************************************************
}

?>
