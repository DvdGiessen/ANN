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
 * @access private
 */

final class Layer
{
	/**#@+
	 * @ignore
	 */
	

	/**
	 * @var array
	 */
	protected $arrNeurons   = array();

	/**
	 * @var array
	 */
	protected $arrOutputs   = array();
	
	/**
	 * @var Network
	 */
	protected $objNetwork   = null;
	
	/**
	 * @var Layer
	 */
	
	protected $objNextLayer = null;

	/**
	 * @var integer
	 */
	protected $intNumberOfNeurons = null;
	
	/**#@-*/
	
	/**
	 * @param Network $objNetwork
	 * @param integer $intNumberOfNeurons
	 * @param Layer $objNextLayer (Default: null)
	 * @uses createNeurons()
	 */
	
	public function __construct(Network $objNetwork, $intNumberOfNeurons, Layer $objNextLayer = null)
	{
	  $this->objNetwork = $objNetwork;
	
	  $this->objNextLayer = $objNextLayer;
	
	  $this->createNeurons($intNumberOfNeurons);
	  
	  $this->intNumberOfNeurons = $intNumberOfNeurons;
	}
		
	/**
	 * @param array &$arrInputs
	 * @uses Neuron::setInputs()
	 */
	
	public function setInputs(&$arrInputs)
	{
		foreach($this->arrNeurons as $objNeuron)
			$objNeuron->setInputs($arrInputs);
	}
		
	/**
	 * @return array
	 */
	
	public function getNeurons()
	{
		return $this->arrNeurons;
	}
	
	/**
	 * @return integer
	 */
	
	public function getNeuronsCount()
	{
		return $this->intNumberOfNeurons;
	}
	
	/**
	 * @return array
	 */
	
	public function getOutputs()
	{
		return $this->arrOutputs;
	}
	
	/**
	 * @return array
	 * @uses Maths::threshold()
	 */
	
	public function getThresholdOutputs()
	{
	  $arrReturnOutputs = array();
	
	  foreach($this->arrOutputs as $intKey => $floatOutput)
	    $arrReturnOutputs[$intKey] = Maths::threshold($floatOutput);
	  
	  return $arrReturnOutputs;
	}
	
	/**
	 * @param integer $intNumberOfNeurons
	 * @uses Neuron::__construct()
	 */
	
	protected function createNeurons($intNumberOfNeurons)
	{
		for($intIndex = 0; $intIndex < $intNumberOfNeurons; $intIndex++)
			$this->arrNeurons[] = new Neuron($this->objNetwork);
	}
		
	/**
	 * @uses Neuron::activate()
	 * @uses Neuron::getOutput()
	 * @uses Layer::setInputs()
	 * @uses Layer::activate()
	 */
	
	public function activate()
	{
		foreach($this->arrNeurons as $intKey => $objNeuron)
	  {
			$objNeuron->activate();
	
	  	$arrOutputs[$intKey] = $objNeuron->getOutput();
		}
	
		if($this->objNextLayer !== null)
		{
	  	$this->objNextLayer->setInputs($arrOutputs);
	
	  	$this->objNextLayer->activate();
		}
		
		$this->arrOutputs = $arrOutputs;
	}
		
	/**
	 * @uses Neuron::setDelta()
	 * @uses Neuron::getWeight()
	 * @uses Neuron::getDelta()
	 * @uses Neuron::getOutput()
	 * @uses getNeurons()
	 */
	
	public function calculateHiddenDeltas()
	{
		$floatDelta = 0;
	
	  $floatSum = 0;
	  
		$arrNeuronsNextLayer = $this->objNextLayer->getNeurons();
		
		/* @var $objNeuron Neuron */
		
		foreach($this->arrNeurons as $intKeyNeuron => $objNeuron)
	  {
	  	/* @var $objNeuronNextLayer Neuron */
	  	
	  	foreach($arrNeuronsNextLayer as $objNeuronNextLayer)
	    	$floatSum += $objNeuronNextLayer->getWeight($intKeyNeuron) * $objNeuronNextLayer->getDelta() * $this->objNetwork->floatMomentum;
	
	  	$floatOutput = $objNeuron->getOutput();
	
	  	$floatDelta = $floatOutput * (1 - $floatOutput) * $floatSum;
			
			$objNeuron->setDelta($floatDelta);
	  }
	}
		
	/**
	 * @param array $arrDesiredOutputs
	 * @uses Neuron::setDelta()
	 * @uses Neuron::getOutput()
	 */
	
	public function calculateOutputDeltas($arrDesiredOutputs)
	{
		foreach($this->arrNeurons as $intKeyNeuron => $objNeuron)
	  {
		  $floatOutput = $objNeuron->getOutput();
	
			$floatDelta = $floatOutput * ($arrDesiredOutputs[$intKeyNeuron] - $floatOutput) * (1 - $floatOutput);
		  
		  $objNeuron->setDelta($floatDelta);
		}
	}
		
	/**
	 * @uses Neuron::adjustWeights()
	 */
	
	public function adjustWeights()
	{
		foreach($this->arrNeurons as $objNeuron)
			$objNeuron->adjustWeights();
	}
}
