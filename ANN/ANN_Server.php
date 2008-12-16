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
 * @copyright Copyright (c) 2007-08 by Thomas Wien
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

protected $login = FALSE;
protected $network = null;
protected $networkUnserialized = null;
protected $dir = '';

/**#@-*/

// ****************************************************************************

/**
 * @param string $dir (Default: 'networks')
 * @uses ANN_Exception::__construct()
 * @uses onPost()
 * @throws ANN_Exception
 */

public function __construct($dir = 'networks')
{
  if(!is_dir($dir) && is_writable($dir))
    throw new ANN_Exception('Directory '. $dir .' does not exists or has no writing permissions');

  $this->dir = $dir;

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

  $this->login = $this->checkLogin($_POST['username'], $_POST['password']);

  if(!$this->login)
    return;

  if(isset($_POST['mode']))
    switch($_POST['mode'])
    {
      case 'savetohost':

        $this->networkUnserialized = $_POST['network'];

        $this->saveToHost();

        break;

      case 'loadfromhost':

        $this->loadFromHost();

        break;

      case 'trainbyhost':

        $this->networkUnserialized = $_POST['network'];

        $this->trainByHost();

        break;
    }
}

// ****************************************************************************

/**
 * @param string $username
 * @param string $password
 * @return boolean
 */

protected function checkLogin($username, $password)
{
  return TRUE;
}

// ****************************************************************************

/**
 * @uses ANN_Network::saveToFile()
 */

protected function saveToHost()
{
  $this->network = unserialize($this->networkUnserialized);
  
  if($this->network instanceof ANN_Network)
    $this->network->saveToFile($this->dir .'/'. $_POST['username'] .'.dat');
}

// ****************************************************************************

/**
 * @uses ANN_Network::loadFromFile()
 */

protected function loadFromHost()
{
  $this->network = ANN_Network::loadFromFile($this->dir .'/'. $_POST['username'] .'.dat');
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

  if($this->network instanceof ANN_Network)
  {
    $this->network->saveToFile($this->dir .'/'. $_POST['username'] .'.dat');

    $this->network->train();
  }
}

// ****************************************************************************

protected function printNetwork()
{
  header('Content-Type: text/plain');

  print serialize($this->network);
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
