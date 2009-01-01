<?php

/**
 * Artificial Neural Network - Version 2.0
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
 * @copyright Copyright (c) 2007-09 by Thomas Wien
 * @package ANN
 */


/**
 * @package ANN
 * @access public
 */

class ANN_Server
{
/**#@+
 * @ignore
 */

protected $boolLogin = FALSE;
protected $objNetwork = null;
protected $objNetworkUnserialized = null;
protected $strDir = '';

/**#@-*/

// ****************************************************************************

/**
 * @param string $strDir (Default: 'networks')
 * @uses ANN_Exception::__construct()
 * @uses onPost()
 * @throws ANN_Exception
 */

public function __construct($strDir = 'networks')
{
  if(!is_dir($strDir) && is_writable($strDir))
    throw new ANN_Exception('Directory '. $strDir .' does not exists or has no writing permissions');

  $this->strDir = $strDir;

  if(isset($_POST) && count($_POST))
    $this->OnPost();
}

// ****************************************************************************

/**
 * @uses loadFromHost()
 * @uses checkLogin()
 * @uses saveToHost()
 * @uses trainByHost()
 */

protected function onPost()
{
  if(!isset($_POST['username']))
    $_POST['username'] = '';

  if(!isset($_POST['password']))
    $_POST['password'] = '';
    
  settype($_POST['username'], 'string');

  settype($_POST['password'], 'string');

  $this->boolLogin = $this->checkLogin($_POST['username'], $_POST['password']);

  if(!$this->boolLogin)
    return;

  if(isset($_POST['mode']))
    switch($_POST['mode'])
    {
      case 'savetohost':

        $this->objNetworkUnserialized = $_POST['network'];

        $this->saveToHost();

        break;

      case 'loadfromhost':

        $this->loadFromHost();

        break;

      case 'trainbyhost':

        $this->objNetworkUnserialized = $_POST['network'];

        $this->trainByHost();

        break;
    }
}

// ****************************************************************************

/**
 * @param string $strUsername
 * @param string $strPassword
 * @return boolean
 */

protected function checkLogin($strUsername, $strPassword)
{
  return TRUE;
}

// ****************************************************************************

/**
 * @uses ANN_Network::saveToFile()
 */

protected function saveToHost()
{
  $this->objNetwork = unserialize($this->objNetworkUnserialized);
  
  if($this->objNetwork instanceof ANN_Network)
    $this->objNetwork->saveToFile($this->strDir .'/'. $_POST['username'] .'.dat');
}

// ****************************************************************************

/**
 * @uses ANN_Network::loadFromFile()
 */

protected function loadFromHost()
{
  $this->objNetwork = ANN_Network::loadFromFile($this->strDir .'/'. $_POST['username'] .'.dat');
}

// ****************************************************************************

/**
 * @uses ANN_Network::saveToFile()
 * @uses ANN_Network::train()
 * @uses saveToHost()
 */

protected function trainByHost()
{
  $this->saveToHost();

  if($this->objNetwork instanceof ANN_Network)
  {
    $this->objNetwork->saveToFile($this->strDir .'/'. $_POST['username'] .'.dat');

    $this->objNetwork->train();
  }
}

// ****************************************************************************

protected function printNetwork()
{
  header('Content-Type: text/plain');

  print serialize($this->objNetwork);
}

// ****************************************************************************

/**
 * @uses printNetwork()
 */

public function __destruct()
{
  if(isset($_POST['mode']))
    switch($_POST['mode'])
    {
      case 'loadfromhost':
      case 'trainbyhost':
        $this->printNetwork();
    }
}

// ****************************************************************************
}

?>
