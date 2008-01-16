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
 * @author Thomas Wien <info_at_thwien_dot_de>
 * @version ANN Version 2.0 by Thomas Wien
 * @copyright Copyright (c) 2007 Thomas Wien
 * @package ANN
 */


/**
 * @package ANN
 * @access private
 */

class ANN_Logging
{
/**#@+
 * @ignore
 */

protected $filename;
protected $fileHandle;
protected $header = FALSE;

/**#@-*/

const SEPARATOR = ';';

// ****************************************************************************

/**
 * @param string $filename
 * @uses ANN_Exception::__construct()
 * @throws ANN_Exception
 */

public function setFilename($filename)
{
$this->filename = $filename;

$this->fileHandle = @fopen($filename, 'w+');

if(!is_resource($this->fileHandle))
  throw new ANN_Exception('File '. basename($filename). ' cannot be created');
}

// ****************************************************************************

/**
 * @param array $data
 * @uses isHeader()
 * @uses logHeader()
 */

public function logData($data)
{
if(!$this->isHeader())
  $this->logHeader($data);

$strData = implode(self::SEPARATOR, $data);

if(is_resource($this->fileHandle))
  @fwrite($this->fileHandle, $strData, strlen($strData));
  
@fwrite($this->fileHandle, "\r\n", strlen("\r\n"));
}

// ****************************************************************************

public function __destruct()
{
if(is_resource($this->fileHandle))
  @fclose($this->fileHandle);
}

// ****************************************************************************

/**
 * @return boolean
 */

protected function isHeader()
{
return $this->header;
}

// ****************************************************************************

/**
 * @param array $data
 */

protected function logHeader($data)
{
$strData = implode(self::SEPARATOR, array_keys($data));

if(is_resource($this->fileHandle))
  @fwrite($this->fileHandle, $strData, strlen($strData));

@fwrite($this->fileHandle, "\r\n", strlen("\r\n"));

$this->header = TRUE;
}

// ****************************************************************************
}

?>
