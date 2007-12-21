<?php

/**
 * ANN Version 1.0
 *
 * Copyrighth (c) 2002 Eddy Young
 * 
 * This source file is freely re-distributable, with or without, modifications
 * provided the following conditions are met:
 * 
 * 1.	The source file must retain the above copyright notice, this list of
 *		conditions and the following disclaimer.
 *
 * 2.	The name of the author must not be used to endorse or promote products
 *		derived from this source file without prior written permission. For 
 *		written permission, plase contact me.
 *
 * DISCLAIMER
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
 * Author: 	Eddy Young <jeyoung@priscimon.com>
 *
 * $Id: NN.php,v 1.00 2002/01/06 11:54:49 jeyoung Exp $
 *
 * Artificial Neural Network
 *
 */

class Maths {

	function &sigmoid($x) {
		return 1 / (1 + exp(-1 * $x) );
	}
	
	function &random($min = 0, $max = 10) {
		mt_srand( (double) microtime() * 1234567890);
		return mt_rand($min, $max);
	}
}

class Neuron {
	
	var $inputs;
	var $weights;
	var $output;
	var $delta;
	
	function Neuron() {
	}
	
	function setInputs($inputs) {
		$inputs[] = 1; // bias
		
		$this->inputs = $inputs;
		
		if (count($this->weights) == 0) {
			$this->initialiseWeights();
		}
	}
	
	function setOutput($output) {
		$this->output = $output;
	}
	
	function setDelta($delta) {
		$this->delta = $delta;
	}
	
	function getInputs() {
		return $this->inputs;
	}
	
	function getWeights() {
		return $this->weights;
	}

	function getOutput() {
		return $this->output;
	}
	
	function getDelta() {
		return $this->delta;
	}
	
	function initialiseWeights() {
		foreach (array_keys($this->inputs) as $k) {
			$this->weights[$k] = Maths::random(-1000, 1000) / 1000;
		}
	}
	
	function activate() {
		$sum = 0;
		
		foreach (array_keys($this->inputs) as $k) {
			$sum += ($this->inputs[$k] * $this->weights[$k]);
		}
		$this->setOutput(Maths::sigmoid($sum) );		
	}
	
	function adjustWeights($learningRate) {
		foreach (array_keys($this->weights) as $k) {
			$this->weights[$k] += ($learningRate * $this->inputs[$k] * $this->getDelta() );
		}
	}
}
	
class Layer {
	
	var $neurons;
	var $outputs;
	
	function Layer($numberOfNeurons) {
		$this->createNeurons($numberOfNeurons);
	}
	
	function setInputs($inputs) {
		foreach (array_keys($this->neurons) as $k) {
			$this->neurons[$k]->setInputs($inputs);
		}
	}
	
	function getNeurons() {
		return $this->neurons;
	}
	
	function getInputs() {
		return $this->inputs;
	}
	
	function getOutputs() {
		return $this->outputs;
	}
	
	function createNeurons($numberOfNeurons) {
		for ($i = 0; $i < $numberOfNeurons; $i++) {
			$this->neurons[] =& new Neuron();		
		}
	}
	
	function activate() {
		foreach (array_keys($this->neurons) as $k) {
			$this->neurons[$k]->activate();
			$this->outputs[$k] = $this->neurons[$k]->getOutput();
		}
	}
	
	function calculateHiddenDeltas($nextLayer) {
		$neurons = $nextLayer->getNeurons();	
		
		foreach (array_keys($this->neurons) as $k) {
			$sum = 0;
			foreach (array_keys($neurons) as $l) {
				$weights = $neurons[$l]->getWeights();
				$sum += ($weights[$k] * $neurons[$l]->getDelta() );
			}

			$delta = $this->neurons[$k]->getOutput() * (1 - $this->neurons[$k]->getOutput() ) * $sum;
			$this->neurons[$k]->setDelta($delta);
		}
	}
	
	function calculateOutputDeltas($desiredOutputs) {
		foreach (array_keys($this->neurons) as $k) {
			$delta = $this->neurons[$k]->getOutput() * ($desiredOutputs[$k] - $this->neurons[$k]->getOutput() ) * 
				(1 - $this->neurons[$k]->getOutput() );
			$this->neurons[$k]->setDelta($delta);
		}
	}
	
	function adjustWeights($learningRate) {
		foreach (array_keys($this->neurons) as $k) {
			$this->neurons[$k]->adjustWeights($learningRate);
		}
	}
}

class Network {

	var $outputLayer;
	var $hiddenLayers;
	
	function Network($numberOfHiddenLayers, $numberOfNeuronsPerLayer, $numberOfOutputs) {
		$this->createHiddenLayers($numberOfHiddenLayers, $numberOfNeuronsPerLayer);
		$this->createOutputLayer($numberOfOutputs);
	}
	
	function setInputs($inputs) {
		foreach (array_keys($this->hiddenLayers) as $k) {
			$this->hiddenLayers[$k]->setInputs($inputs);
		}
	}
	
	function getOutputs() {
		return $this->outputLayer->getOutputs();	
	}
	
	function createHiddenLayers($numberOfHiddenLayers, $numberOfNeuronsPerLayer) {
		for ($i = 0; $i < $numberOfHiddenLayers; $i++) {
			$this->hiddenLayers[] =& new Layer($numberOfNeuronsPerLayer);
		}
	}
	
	function createOutputLayer($numberOfOutputs) {
		$this->outputLayer =& new Layer($numberOfOutputs);
	}
	
	function activate() {
		for ($i = 0; $i < count($this->hiddenLayers) - 1; $i++) {
			$this->hiddenLayers[$i]->activate();
			
			$this->hiddenLayers[$i + 1]->setInputs($this->hiddenLayers[$i]->getOutputs() );
		}
		$this->hiddenLayers[$i]->activate();
		
		$this->outputLayer->setInputs($this->hiddenLayers[$i]->getOutputs() );
		$this->outputLayer->activate();
	}
	
	function train($learningRate, $outputs) {
		$this->activate();
		
		$this->outputLayer->calculateOutputDeltas($outputs);
		$this->hiddenLayers[count($this->hiddenLayers) - 1]->calculateHiddenDeltas($this->outputLayer);
		for ($i = count($this->hiddenLayers) - 1; $i > 1; $i--) {
			$this->hiddenLayers[$i - 1]->calculateHiddenDeltas($this->hiddenLayers[$i]);
		}
		
		$this->outputLayer->adjustWeights($learningRate);
		for ($i = count($this->hiddenLayers); $i > 0; $i--) {
			$this->hiddenLayers[$i - 1]->adjustWeights($learningRate);	
		}
	}
	
	function save($filename) {
		$serialised = serialize($this);
		
		$f = fopen($filename, "w+");
		if (!$f)  {
			echo "\nCould not open $filename!";
			return;
		}
		
		$size = fwrite($f, $serialised);
		fclose($f);
	}
	
	function &load($filename) {
		if (file_exists($filename) ) {
			$f = fopen($filename, "r");
			$serialised = fread($f, filesize($filename) );
			fclose($f);
			
			if ($serialised < 0) {
				return null;
			}
			return unserialize($serialised);
		}
		return null;
	}	
}

?>

