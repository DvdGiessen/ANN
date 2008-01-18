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

/**#@-*/

// ****************************************************************************

/**
 * @param ANN_Network $network
 * @param integer $numberOfNeurons
 * @param boolean $outputLayer (Default: FALSE)
 * @uses createNeurons()
 */

public function __construct(ANN_Network $network, $numberOfNeurons)
{
  $this->network = $network;

  $this->createNeurons($numberOfNeurons);
}
	
// ****************************************************************************

/**
 * @param array $inputs
 * @uses ANN_Neuron::setInputs()
 */

public function setInputs($inputs)
{
	foreach ($this->neurons as $k => $value)
		$this->neurons[$k]->setInputs($inputs);
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
	foreach ($this->neurons as $k => $value)
  {
		$this->neurons[$k]->activate();

  	$this->outputs[$k] = $this->neurons[$k]->getOutput();
	}
}
	
// ****************************************************************************

/**
 * @param ANN_Layer $nextLayer
 * @uses ANN_Neuron::getOutput()
 * @uses ANN_Neuron::getDeltaWithMomentum()
 * @uses ANN_Neuron::setDelta()
 * @uses ANN_Layer::getNeurons()
 */

public function calculateHiddenDeltas(ANN_Layer $nextLayer)
{
	$neurons = $nextLayer->getNeurons();

	foreach ($this->neurons as $k => $value)
  {
		$sum = 0;

		foreach ($neurons as $l => $value)
    {
			$weights = $neurons[$l]->getWeights();

    	$sum += ($weights[$k] * $neurons[$l]->getDeltaWithMomentum());
		}

    $output = $this->neurons[$k]->getOutput();

		$delta = $output * (1 - $output) * $sum;

    if($this->network->weightDecayMode)
      $delta -= $this->neurons[$k]->getDeltaWithMomentum() * $this->network->weightDecay;

		$this->neurons[$k]->setDelta($delta);
	}
}
	
// ****************************************************************************

/**
 * @param array $desiredOutputs
 * @uses ANN_Neuron::getOutput()
 * @uses ANN_Neuron::setDelta()
 */

public function calculateOutputDeltas($desiredOutputs)
{
	foreach ($this->neurons as $k => $value)
  {
    $output = $this->neurons[$k]->getOutput();
  
		$delta = $output * ($desiredOutputs[$k] - $output) * (1 - $output);
			
		$this->neurons[$k]->setDelta($delta);
	}
}
	
// ****************************************************************************

/**
 * @uses ANN_Neuron::adjustWeights()
 */

public function adjustWeights()
{
	foreach ($this->neurons as $k => $value)
		$this->neurons[$k]->adjustWeights();
}

// ****************************************************************************
}

?>
