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
 * @copyright Copyright (c) 2007-2010 by Thomas Wien
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
	 
	protected $arrInputs = null;
	protected $arrWeights = null;
	protected $floatOutput = null;
	protected $floatDelta = 0;
	
	/**
	 * @var ANN_Network
	 */
	
	protected $objNetwork = null;
	protected $floatLearningRate = 0;
	
	/**#@-*/
	
	/**
	 * @param ANN_Network $objNetwork
	 */
	
	public function __construct(ANN_Network $objNetwork)
	{
	  $this->objNetwork = $objNetwork;
	
	  $this->floatDelta = ANN_Maths::random();
	  
	  $this->floatLearningRate = $this->objNetwork->floatLearningRate;
	}
	
	/**
	 * @param array &$arrInputs
	 * @uses initializeWeights()
	 */
	
	public function setInputs(&$arrInputs)
	{
		$this->arrInputs = $arrInputs;
	
		$this->arrInputs[] = 1; // bias
		
		if($this->arrWeights === null)
			$this->initializeWeights();
	}
		
	/**
	 * @param float $floatDelta
	 */
	
	public function setDelta($floatDelta)
	{
		$this->floatDelta = $floatDelta;
	}
		
	/**
	 * @return array
	 */
	
	public function getWeights()
	{
		return $this->arrWeights;
	}
	
	/**
	 * @param integer $intKeyNeuron
	 * @return float
	 */
	
	public function getWeight($intKeyNeuron)
	{
		return $this->arrWeights[$intKeyNeuron];
	}
	
	/**
	 * @return float
	 */
	
	public function getOutput()
	{
		return $this->floatOutput;
	}
	
	/**
	 * @return float
	 */
	
	public function getDelta()
	{
		return $this->floatDelta;
	}
	
	/**
	 * @uses ANN_Maths::random()
	 */
	
	protected function initializeWeights()
	{
		foreach($this->arrInputs as $intKey => $floatInput)
			$this->arrWeights[$intKey] = ANN_Maths::random(-50000, 50000) / 100000;
	}
		
	/**
	 * @uses ANN_Maths::sigmoid()
	 */
	
	public function activate()
	{
		$floatSum = 0;
			
		foreach($this->arrInputs as $intKey => $floatInput)
			$floatSum += $floatInput * $this->arrWeights[$intKey];
	
		$this->floatOutput = ANN_Maths::sigmoid($floatSum);
	}
		
	public function adjustWeights()
	{
	  switch($this->objNetwork->intOutputType)
	  {
	    case ANN_Network::OUTPUT_LINEAR:
	
	      $floatDelta = 0;
	
	      $floatLearningRateDeltaFactor = $this->floatLearningRate * $this->floatDelta;
	      
	      foreach($this->arrInputs as $floatInput)
	        $floatDelta += $floatLearningRateDeltaFactor * $floatInput;
	
	    	foreach ($this->arrWeights as $intKey => $floatWeight)
	    		$this->arrWeights[$intKey] += $floatDelta;
	
	      break;
	
	    case ANN_Network::OUTPUT_BINARY:
	
	      $floatLearningRateDeltaFactor = $this->floatLearningRate * $this->floatDelta;
	    	
	      foreach ($this->arrWeights as $intKey => $floatWeight)
	    		$this->arrWeights[$intKey] += $this->arrInputs[$intKey] * $floatLearningRateDeltaFactor;
	
	      break;
	  }
	}
}
