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
 * @author Thomas Wien <info_at_thwien_dot_de>
 * @version ANN Version 2.1 by Thomas Wien
 * @copyright Copyright (c) 2007-2011 by Thomas Wien
 * @package ANN
 */


/**
 * @package ANN
 * @access public
 * @since 2.0.6
 */

class ANN_Values extends ANN_Filesystem
{
	/**#@+
	 * @ignore
	 */
	
	protected $arrInputs  = array();
	protected $arrOutputs = array();
	protected $boolLastActionInput = FALSE;
	protected $boolTrain = FALSE;
	protected $intCountInputs = null;
	protected $intCountOutputs = null;
	
	/**#@-*/
	
	/**
	 * Input values
	 *
	 * List all input values comma separated
	 *
	 * <code>
	 * $objValues = new ANN_Values;
	 *
	 * $objValues->train()
	 *           ->input(0.12, 0.11, 0.15)
	 *           ->output(0.56);
	 * </code>
	 *
	 * <code>
	 * $objValues = new ANN_Values;
	 *
	 * $objValues->input(0.12, 0.11, 0.15)
	 *           ->input(0.13, 0.12, 0.16)
	 *           ->input(0.14, 0.13, 0.17);
	 * </code>
	 *
	 * @return ANN_Values
	 * @uses ANN_Exception::__construct()
	 * @throws ANN_Exception
	 */
	
	public function input()
	{
	  if($this->boolTrain && $this->boolLastActionInput)
	    throw new ANN_Exception('After calling input() method output() should be called');
	
	  $arrParameters = func_get_args();
	
	  $arrInputParameters = array();
	  
	  foreach($arrParameters as $mixedParameter)
		  if(is_array($mixedParameter))
		  {
				$arrInputParameters = array_merge($arrInputParameters, $mixedParameter);
		  }
		  elseif(is_numeric($mixedParameter))
		  {
		  	$arrInputParameters[] = $mixedParameter;
		  }
	  
	  $intCountParameters = func_num_args();
	  
	  foreach($arrInputParameters as $floatParameter)
	    if(!is_float($floatParameter) && !is_integer($floatParameter))
	      throw new ANN_Exception('Each parameter should be float');
	      
	  if($this->intCountInputs === null)
	    $this->intCountInputs =  $intCountParameters;
	    
	  if($this->intCountInputs != $intCountParameters)
	    throw new ANN_Exception('There should be '. $this->intCountInputs .' parameter values for input()');
	
	  $this->arrInputs[] = $arrInputParameters;
	  
	  $this->boolLastActionInput = TRUE;
	  
	  return $this;
	}
	
	/**
	 * Output values
	 *
	 * List all output values comma separated. Before you can call this method you
	 * have to call input(). After calling output() you cannot call the same method
	 * again. You have to call input() again first.
	 *
	 * <code>
	 * $objValues = new ANN_Values;
	 *
	 * $objValues->train()
	 *           ->input(0.12, 0.11, 0.15)
	 *           ->output(0.56);
	 * </code>
	 *
	 * @return ANN_Values
	 * @uses ANN_Exception::__construct()
	 * @throws ANN_Exception
	 */
	
	public function output()
	{
	  if(!$this->boolLastActionInput)
	    throw new ANN_Exception('After calling output() method input() should be called');
	
	  if(!$this->boolTrain)
	    throw new ANN_Exception('Calling output() is just allowed for training. Call train() if values for training.');
	
	  $arrParameters = func_get_args();
	
	  // If ANN_Classification is used
	  
	  if(isset($arrParameters[0]) && is_array($arrParameters[0]))
			$arrParameters = $arrParameters[0];
	  
		$intCountParameters = func_num_args();
	
	  foreach($arrParameters as $floatParameter)
	    if(!is_float($floatParameter) && !is_integer($floatParameter))
	      throw new ANN_Exception('Each parameter should be float');
	
	  if($this->intCountOutputs === null)
	    $this->intCountOutputs =  $intCountParameters;
	
	  if($this->intCountOutputs != $intCountParameters)
	    throw new ANN_Exception('There should be '. $this->intCountOutputs .' parameter values for output()');
	
	  $this->arrOutputs[] = $arrParameters;
	
	  $this->boolLastActionInput = FALSE;
	
	  return $this;
	}
	
	/**
	 * @return ANN_Values
	 */
	
	public function train()
	{
	  $this->boolTrain = TRUE;
	  
	  return $this;
	}
	
	/**
	 * Get internal saved input array
	 *
	 * Actually there is no reason to call this method in your application. This
	 * method is used by ANN_Network only.
	 *
	 * @return array
	 */
	
	public function getInputsArray()
	{
	  return $this->arrInputs;
	}
	
	/**
	 * Get internal saved output array
	 *
	 * Actually there is no reason to call this method in your application. This
	 * method is used by ANN_Network only.
	 *
	 * @return array
	 */
	
	public function getOutputsArray()
	{
	  return $this->arrOutputs;
	}
	
	/**
	 * Unserializing ANN_Values
	 *
	 * After calling unserialize the train mode is set to false. Therefore it is
	 * possible to use a saved object of ANN_Values to use inputs not for training
	 * purposes.
	 *
	 * You would not use unserialize in your application but you can call loadFromFile()
	 * to load the saved object to your application.
	 */
	
	public function __wakeup()
	{
	  $this->boolTrain = FALSE;
	}
	
	/**
	 * Reset saved input and output values
	 *
	 * All internal saved input and output values will be deleted after calling reset().
	 * If train() was called before, train state does not change by calling reset().
	 *
	 * <code>
	 * $objValues = new ANN_Values;
	 *
	 * $objValues->train()
	 *           ->input(0.12, 0.11, 0.15)
	 *           ->output(0.56)
	 *           ->reset()
	 *           ->input(0.12, 0.11, 0.15)
	 *           ->output(0.56);
	 * </code>
	 *
	 * @return ANN_Values
	 */
	
	public function reset()
	{
	  $this->arrInputs = array();
	
	  $this->arrOutputs = array();
	  
	  return $this;
	}
}
