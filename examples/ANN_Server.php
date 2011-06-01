<?php

namespace App;

require_once '../ANN/Loader.php';

use ANN\Server;

class MyServer extends Server
{
	/**
	 * @param string $username
	 * @param string $password
	 * @return boolean
	 */

	protected function checkLogin($username, $password)
	{
		return ($username == 'username' && $password == 'password');
	}
}

$objServer = new MyServer;
