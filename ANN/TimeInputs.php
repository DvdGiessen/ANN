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

final class ANN_TimeInputs
{
/**#@+
 * @ignore
 */

	/**
	 * @var string
	 */
	protected $strTime = null;

/**#@-*/

// ****************************************************************************

/**
 * @param string $strTime (Default: null)
 * @uses checkTimeFormat()
 * @throws ANN_Exception
 */
	
public function __construct($strTime = null)
{
	if($strTime && !$this->checkTimeFormat($strTime))
		throw new ANN_Exception('Constraints: $strTime should be HH:MM format');
	
	$this->strTime = $strTime;
}

// ****************************************************************************

/**
 * @param string $strTime
 * @uses checkTimeFormat()
 * @throws ANN_Exception
 */

public function setDefaultTime($strTime)
{
	if(!$this->checkTimeFormat($strTime))
		throw new ANN_Exception('Constraints: $strTime should be HH:MM format');
	
	$this->strTime = $strTime;
}

// ****************************************************************************

/**
 * @param string $strTime (Default: null)
 * @return array
 * @uses checkTimeFormat()
 * @throws ANN_Exception
 */

public function getTimeOfDay($strTime = null)
{
	if(!$strTime)
		$strTime = $this->getDefaultTime();
	
	if(!$this->checkTimeFormat($strTime))
		throw new ANN_Exception('Constraints: $strTime should be HH:MM format');
	
	$arrReturn = array();

	$intHour = date('G', strtotime($strTime));
	
	$arrReturn[0] = ($intHour < 6) ? 1 : 0;
	
	$arrReturn[1] = ($intHour >= 6 && $intHour < 12) ? 1 : 0;
	
	$arrReturn[2] = ($intHour >= 12 && $intHour < 18) ? 1 : 0;
	
	$arrReturn[3] = ($intHour >= 18) ? 1 : 0;
	
	return $arrReturn;
}

// ****************************************************************************

/**
 * @param string $strTime (Default: null)
 * @return array
 * @uses checkTimeFormat()
 * @throws ANN_Exception
 */

public function getHour($strTime = null)
{
	if(!$strTime)
		$strTime = $this->getDefaultTime();
	
	if(!$this->checkTimeFormat($strTime))
		throw new ANN_Exception('Constraints: $strTime should be HH:MM format');
	
	for($intHour = 0; $intHour <= 23; $intHour++)
		$arrReturn[$intHour] = 0;
		
	$intHour = date('G', strtotime($strTime));
	
	$arrReturn[$intHour] = 1;
	
	return $arrReturn;
}

// ****************************************************************************

/**
 * @return string
 */

protected function getDefaultTime()
{
	if(!$this->strTime)
		return date('H:i');
		
	return $this->strTime;
}

// ****************************************************************************

/**
 * @param string $strTime
 * @return boolean
 */

protected function checkTimeFormat($strTime)
{
	return preg_match('/^[0-2][0-9]:[0-5][0-9]$/', $strTime);	
}

// ****************************************************************************
}
