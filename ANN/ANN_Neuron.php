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
 * @version ANN Version 2.0 by Thomas Wien
 * @copyright Copyright (c) 2002 Eddy Young
 * @copyright Copyright (c) 2007 Thomas Wien
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

/**#@-*/

// ****************************************************************************

/**
 * @param ANN_Network $network
 * @param boolean $outputNeuron (Default:  FALSE)
 */

public function __construct(ANN_Network $network)
{
  $this->network = $network;
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
	$this->delta = $delta;
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

public function getDelta()
{
	return $this->network->momentum * $this->delta;
}
	
// ****************************************************************************

/**
 * @uses ANN_Maths::random()
 */

protected function initialiseWeights()
{
	foreach ($this->inputs as $k => $value)
		$this->weights[$k] = ANN_Maths::random(-1000, 1000) / 1000;
}
	
// ****************************************************************************

/**
 * @uses ANN_Maths::sigmoid()
 * @uses setOutput()
 */

public function activate()
{
	$sum = 0;
		
	foreach ($this->inputs as $k => $value)
		$sum += ($this->inputs[$k] * $this->weights[$k]);

  $this->setOutput(ANN_Maths::sigmoid($sum));
}
	
// ****************************************************************************

public function adjustWeights()
{
	foreach ($this->weights as $k => $value)
		$this->weights[$k] += ($this->network->learningRate * $this->inputs[$k] * $this->delta);
}

// ****************************************************************************
}

?>
