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

class ANN_Loader
{
/**#@+
 * @ignore
 */

protected $strDir = '';

/**#@-*/

// ****************************************************************************

public function __construct()
{
  $this->strDir = dirname(__FILE__);

  spl_autoload_register(array($this, 'autoload'));
}

// ****************************************************************************

public function __destruct()
{
  spl_autoload_unregister(array($this, 'autoload'));
}

// ****************************************************************************

/**
 * @param string $strClassname
 * @return boolean
 */

public function autoload($strClassname)
{
  settype($strClassname, 'string');

  if(!preg_match('/^ANN/', $strClassname))
    return FALSE;

  $strClassname = preg_replace('/^ANN_/', '', $strClassname);

  $strFilename = $this->strDir . "/$strClassname.php";

  if(is_file($strFilename))
  {
    require_once($strFilename);
    
    return TRUE;
  }

  return FALSE;
}

// ****************************************************************************
}

$objANNLoader = new ANN_Loader;
