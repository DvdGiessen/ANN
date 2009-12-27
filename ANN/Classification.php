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
 * @copyright Copyright (c) 2007-09 by Thomas Wien
 * @package ANN
 */


/**
 * @package ANN
 * @access public
 */

final class ANN_Classification extends ANN_Filesystem
{
/**#@+
 * @ignore
 */

protected $intMaxClassifiers;
protected $arrClassifiers = array();

/**#@-*/

// ****************************************************************************

/**
 * @param integer $intMaxClassifiers
 * @throws ANN_Exception
 */

public function __construct($intMaxClassifiers)
{
  if(!is_integer($intMaxClassifiers) || $intMaxClassifiers <= 0)
    throw new ANN_Exception('Constraints: $intMaxClassifiers should be a positive integer number');

  $this->intMaxClassifiers = $intMaxClassifiers;
}

// ****************************************************************************

/**
 * @param string $strValue
 * @throws ANN_Exception
 * @uses existsClassifier()
 */

public function addClassifier($strValue)
{
	if(count($this->arrClassifiers) == $this->intMaxClassifiers)
		throw new ANN_Exception('Maximal count of classifiers reached');
		
	if($this->existsClassifier($strValue))
		throw new ANN_Exception('Classifier "'. $strValue .'" does already exist');
	
	$this->arrClassifiers[] = $strValue;
}

// ****************************************************************************

/**
 * @param string $strValue
 * @return boolean
 */

protected function existsClassifier($strValue)
{
	foreach($this->arrClassifiers as $strClassifier)
	{
		if(strtolower($strClassifier) == strtolower($strValue))
			return TRUE;
	}

	return FALSE;
}

// ****************************************************************************

/**
 * @param string|array $mixedValues
 * @return array
 * @uses calculateOutputValues()
 * @throws ANN_Exception
 */

public function getOutputValue($mixedValues)
{
	if(!is_string($mixedValues) && !is_array($mixedValues))
		throw new ANN_Exception('$mixedValues should be either string or array');
		
	$arrValues = array();
		
	if(is_string($mixedValues))
	{
		$arrValues = array($mixedValues);
	}
	else
	{
		$arrValues = $mixedValues;
	}
	
  return $this->calculateOutputValues($arrValues);
}

// ****************************************************************************

/**
 * @param array $arrValues
 * @return array
 * @throws ANN_Exception
 */

protected function calculateOutputValues($arrValues)
{
	$arrReturn = array();
	
	$boolFound = FALSE;
	
	foreach($this->arrClassifiers as $intKey => $strClassifier)
	{
		$arrReturn[$intKey] = (in_array($strClassifier, $arrValues)) ? 1 : 0;
		
		if($arrReturn[$intKey] == 1)
			$boolFound = TRUE;
	}
	
	if(!$boolFound)
		throw new ANN_Exception('Classifier(s) "'. implode(', ', $arrValues) .'" not found');
	
	$intCountRemainingOutputs = $this->intMaxClassifiers - count($arrReturn);

	for($intIndex = 0; $intIndex < $intCountRemainingOutputs; $intIndex++)
	{
		$arrReturn[] = 0;
	}
	
	return $arrReturn;
}

// ****************************************************************************

/**
 * @param string|array $mixedValues
 * @return array
 * @uses getOutputValue()
 */

public function __invoke($mixedValues)
{
	return $this->getOutputValue($mixedValues);
}

// ****************************************************************************

/**
 * @param array $arrValues
 * @return array
 */

public function getRealOutputValue($arrValues)
{
	$arrReturn = array();

	foreach($this->arrClassifiers as $intKey => $strClassifier)
	{
		if($arrValues[$intKey] == 1)
			$arrReturn[] = $strClassifier;
	}
	
	return $arrReturn;
}

// ****************************************************************************
}
