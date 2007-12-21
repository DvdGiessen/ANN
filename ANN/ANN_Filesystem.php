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

abstract class ANN_Filesystem
{
/**
 * @param string $filename (Default: null)
 * @uses ANN_Exception::__construct()
 * @throws ANN_Exception
 */

public function saveToFile($filename = null)
{
	try
	{
	  $serialized = serialize($this);

    file_put_contents($filename, $serialized);
  }
  catch(Exception $e)
	{
		throw new ANN_Exception("Could not open or create $filename!");
  }
}

// ****************************************************************************

/**
 * @param string $filename (Default: null)
 * @return ANN_Network
 * @uses ANN_Exception::__construct()
 * @throws ANN_Exception
 */

public static function loadFromFile($filename = null)
{
	if(is_file($filename) && is_readable($filename))
  {
    $serialized = file_get_contents($filename);

  	if (empty($serialized))
      throw new ANN_Exception('File '. basename($filename) .' couldn\'t be loaded (file has no object information stored)');

		$instance = unserialize($serialized);
		
		if(!($instance instanceof ANN_Network)
    && !($instance instanceof ANN_InputValue)
    && !($instance instanceof ANN_OutputValue))
      throw new ANN_Exception('File '. basename($filename) .' couldn\'t be opened (not ANN format)');
		
		return $instance;
	}

  throw new ANN_Exception('File '. basename($filename) .' couldn\'t be opened');
}

// ****************************************************************************
}

?>
