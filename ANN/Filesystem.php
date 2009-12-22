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
 * @access private
 */

abstract class ANN_Filesystem
{
/**
 * @param string $strFilename (Default: null)
 * @uses ANN_Exception::__construct()
 * @throws ANN_Exception
 */

public function saveToFile($strFilename = null)
{
  settype($strFilename, 'string');

  if(empty($strFilename))
		throw new ANN_Exception('Paramter $strFilename should be a filename');

  $strDir = dirname($strFilename);
  
  if(empty($strDir))
    $strDir = '.';
    
  if(!is_dir($strDir))
		throw new ANN_Exception("Directory $strDir does not exist");

  if(!is_writable($strDir))
		throw new ANN_Exception("Directory $strDir has no writing permission");
		
  if(is_file($strFilename) && !is_writable($strFilename))
		throw new ANN_Exception("File $strFilename does exist but has no writing permission");

	try
	{
	  $strSerialized = serialize($this);

    file_put_contents($strFilename, $strSerialized);
  }
  catch(Exception $e)
	{
		throw new ANN_Exception("Could not open or create $strFilename!");
  }
}

// ****************************************************************************

/**
 * @param string $strFilename (Default: null)
 * @return ANN_Network|ANN_InputValue|ANN_OutputValue|ANN_Values
 * @uses ANN_Exception::__construct()
 * @throws ANN_Exception
 */

public static function loadFromFile($strFilename = null)
{
	if(is_file($strFilename) && is_readable($strFilename))
  {
    $strSerialized = file_get_contents($strFilename);

  	if (empty($strSerialized))
      throw new ANN_Exception('File '. basename($strFilename) .' could not be loaded (file has no object information stored)');

		$objInstance = unserialize($strSerialized);
		
		if(!($objInstance instanceof ANN_Network)
    && !($objInstance instanceof ANN_Values)
    && !($objInstance instanceof ANN_InputValue)
    && !($objInstance instanceof ANN_OutputValue))
      throw new ANN_Exception('File '. basename($strFilename) .' could not be opened (no ANN format)');
		
		return $objInstance;
	}

  throw new ANN_Exception('File '. basename($strFilename) .' could not be opened');
}

// ****************************************************************************
}
