<?php

/**
 * @copyright (c)1999-2008 by thwien.de
 * @author Thomas Wien
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

  spl_autoload_register(array($this, 'splAutoload'));
}

// ****************************************************************************

public function __destruct()
{
  spl_autoload_unregister(array($this, 'splAutoload'));
}

// ****************************************************************************

/**
 * @param string $strClassname
 * @return boolean
 */

public function splAutoload($strClassname)
{
  settype($strClassname, 'string');

  if(!preg_match('/^ANN/', $strClassname))
    return FALSE;

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

?>
