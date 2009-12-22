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

class ANN_Logging
{
/**#@+
 * @ignore
 */

protected $strFilename;
protected $handleFile;
protected $boolHeader = FALSE;

/**#@-*/

const SEPARATOR = ';';

// ****************************************************************************

/**
 * @param string $strFilename
 * @uses ANN_Exception::__construct()
 * @throws ANN_Exception
 */

public function setFilename($strFilename)
{
  $this->strFilename = $strFilename;

  $this->handleFile = @fopen($strFilename, 'w+');

  if(!is_resource($this->handleFile))
    throw new ANN_Exception('File '. basename($strFilename). ' cannot be created');
}

// ****************************************************************************

/**
 * @param array $arrData
 * @uses isHeader()
 * @uses logHeader()
 */

public function logData($arrData)
{
  if(!$this->isHeader())
    $this->logHeader($arrData);

  $strData = implode(self::SEPARATOR, $arrData);

  if(is_resource($this->handleFile))
    @fwrite($this->handleFile, $strData, strlen($strData));

  @fwrite($this->handleFile, "\r\n", strlen("\r\n"));
}

// ****************************************************************************

public function __destruct()
{
  if(is_resource($this->handleFile))
    @fclose($this->handleFile);
}

// ****************************************************************************

/**
 * @return boolean
 */

protected function isHeader()
{
  return $this->boolHeader;
}

// ****************************************************************************

/**
 * @param array $arrData
 */

protected function logHeader($arrData)
{
  $strData = implode(self::SEPARATOR, array_keys($arrData));

  if(is_resource($this->handleFile))
    @fwrite($this->handleFile, $strData, strlen($strData));

  @fwrite($this->handleFile, "\r\n", strlen("\r\n"));

  $this->boolHeader = TRUE;
}

// ****************************************************************************
}
