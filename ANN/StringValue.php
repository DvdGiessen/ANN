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
 */

final class ANN_StringValue extends ANN_Filesystem
{
	/**#@+
	 * @ignore
	 */

	/**
	 * @var integer
	 */
	protected $intMaxStringLength;

	/**
	 * @var boolean
	 */
	protected $boolOverride = FALSE;

	/**
	 * @var array
	 */
	protected $arrMapping = array();
	
	/**#@-*/
	
	/**
	 * @param integer $intMaxStringLength
	 * @param boolean $boolOverride (Default: FALSE)
	 * If $boolOverride is FALSE, an exception will be thrown if getInputValue() will
	 * be called with outranged values. If $boolOverride is TRUE, no exception will be
	 * thrown in this case, but lower values are replaced by $floatMin and upper values
	 * are replaced by $floatMax.
	 * @uses createMapping()
	 * @throws ANN_Exception
	 */
	
	public function __construct($intMaxStringLength, $boolOverride = FALSE)
	{
		mb_internal_encoding('UTF-8');
		
	  if(!is_integer($intMaxStringLength) || $intMaxStringLength <= 0)
	    throw new ANN_Exception('Constraints: $intMaxStringLength should be a positive integer number');
	
	  if(!is_bool($boolOverride))
	    throw new ANN_Exception('Constraints: $boolOverride should be boolean');
	
	  $this->intMaxStringLength = $intMaxStringLength;
	  
	  $this->boolOverride = $boolOverride;
	  
	  $this->createMapping();
	}
	
	/**
	 * @param string $strValue
	 * @return array
	 * @uses calculateInputValues()
	 * @uses removeSpecialCharacters()
	 * @throws ANN_Exception
	 */
	
	public function getInputValue($strValue)
	{
		if(!is_string($strValue))
			throw new ANN_Exception('$strValue should be string');
			
		if(!$this->boolOverride && mb_strlen($strValue) > $this->intMaxStringLength)
			throw new ANN_Exception('$strValue is longer than max string length');
			
		substr($strValue, 0, $this->intMaxStringLength);
		
		$strValue = mb_strtolower($strValue);
		
		$strValue = $this->removeSpecialCharacters($strValue);
		
	  return $this->calculateInputValues($strValue);
	}
	
	/**
	 * @param string $strValue
	 * @return string
	 */
	
	protected function removeSpecialCharacters($strValue)
	{
		$strValue = preg_replace('/ /u', '', $strValue);
		$strValue = preg_replace('/[§\$%&)(=}{?!]/u', '', $strValue);
		
		return $strValue;
	}
	
	/**
	 * @param string $strValue
	 * @return array
	 * @uses getMapping()
	 */
	
	protected function calculateInputValues($strValue)
	{
		$arrReturn = array();
		
		$intStringLength = mb_strlen($strValue);
		
		for($intIndex = 0; $intIndex < $intStringLength; $intIndex++)
		{
	  	$strCharacter = mb_substr($strValue, $intIndex, 1);
	  	
	  	$arrReturn[] = $this->getMapping($strCharacter); 
		}
		
		for(; $intIndex < $this->intMaxStringLength; $intIndex++)
		{
			$arrReturn[] = 0;
		}
		
		return $arrReturn;
	}
	
	/**
	 * @param string $strCharacter
	 * @return float
	 * @throws ANN_Exception
	 */
	
	protected function getMapping($strCharacter)
	{
		if(!isset($this->arrMapping[$strCharacter]))
			throw new ANN_Exception('Not convertable character '. $strCharacter);
		
		return $this->arrMapping[$strCharacter];	
	}
	
	/**
	 * @uses ordUTF8() 
	 * @uses createSimilarityMapping()
	 * @throws ANN_Exception
	 */
	
	protected function createMapping()
	{
		$arrCharacters = array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z',
		 											 ' ', ',', ';', '.', ':', '-', '_', '!', '?');
		
		foreach($arrCharacters as $strCharacter)
		{
			$this->arrMapping[$strCharacter] = $this->ordUTF8($strCharacter) / 1000;
			
			if($this->arrMapping[$strCharacter] > 1)
				throw new ANN_Exception('Mapping exception');
		}
		
		$this->createSimilarityMapping();
	}
	
	protected function createSimilarityMapping()
	{
		$this->arrMapping['á'] = $this->arrMapping['a']; 	
		$this->arrMapping['à'] = $this->arrMapping['a']; 	
		$this->arrMapping['â'] = $this->arrMapping['a']; 	
	
		$this->arrMapping['é'] = $this->arrMapping['e']; 	
		$this->arrMapping['è'] = $this->arrMapping['e']; 	
		$this->arrMapping['ê'] = $this->arrMapping['e']; 	
	
		$this->arrMapping['í'] = $this->arrMapping['i']; 	
		$this->arrMapping['ì'] = $this->arrMapping['i']; 	
		$this->arrMapping['î'] = $this->arrMapping['i']; 	
	
		$this->arrMapping['ó'] = $this->arrMapping['o']; 	
		$this->arrMapping['ò'] = $this->arrMapping['o']; 	
		$this->arrMapping['ô'] = $this->arrMapping['o']; 	
	
		$this->arrMapping['ú'] = $this->arrMapping['u']; 	
		$this->arrMapping['ù'] = $this->arrMapping['u']; 	
		$this->arrMapping['û'] = $this->arrMapping['u']; 	
	
		$this->arrMapping['ß'] = $this->arrMapping['s']; 	
		$this->arrMapping['ö'] = $this->arrMapping['o']; 	
		$this->arrMapping['ü'] = $this->arrMapping['u']; 	
		$this->arrMapping['ä'] = $this->arrMapping['a']; 	
	}
	
	/**
	 * @param string $strCharacter
	 * @return integer
	 * @throws ANN_Exception
	 * @author kerry at shetline dot com
	 * @author Thomas Wien
	 */
	
	protected function ordUTF8($strCharacter)
	{
	  if(!is_string($strCharacter))
	  	throw new ANN_Exception('$strCharacter should be string');
	  
	  if(mb_strlen($strCharacter) == 0)
	  	throw new ANN_Exception('$strCharacter should be exact one character (1)');
	  
	 	if(mb_strlen($strCharacter) > 1)
	  	throw new ANN_Exception('$strCharacter should be exact one character (2)');
	  
	  $strOrd = ord($strCharacter{0});
	
	  if($strOrd <= 0x7F)
	  {
	    return $strOrd;
	  }
	  elseif($strOrd < 0xC2)
	  {
	    throw new ANN_Exception('Cannot convert string to number');
	  }
	  elseif($strOrd <= 0xDF)
	  {
	    return ($strOrd & 0x1F) <<  6
	           | (ord($strCharacter{1}) & 0x3F);
	  }
	  elseif($strOrd <= 0xEF)
	  {
	    return ($strOrd & 0x0F) << 12
	    			 | (ord($strCharacter{1}) & 0x3F) << 6
	           | (ord($strCharacter{2}) & 0x3F);
	  }          
	  elseif($strOrd <= 0xF4)
	  {
	    return ($strOrd & 0x0F) << 18
	    	| (ord($strCharacter{1}) & 0x3F) << 12
	      | (ord($strCharacter{2}) & 0x3F) << 6
	      | (ord($strCharacter{3}) & 0x3F);
	  }
	
	  throw new ANN_Exception('Cannot convert string to number');
	}
	
	public function __wakeup()
	{
		mb_internal_encoding('UTF-8');
	}
	
	/**
	 * @param string $strValue
	 * @return array
	 * @uses getInputValue()
	 */
	
	public function __invoke($strValue)
	{
		return $this->getInputValue($strValue);	
	}
}
