<?php

namespace Controllers;

use Core\ErrorHandler;

class LoginController
{
	public function index()
	{
		if (isset($_SESSION['user_id'])) {
			ErrorHandler::handleError(
				'You are already logged in.',
				'/home',
				403,
				False
			);
		}

		include __DIR__ . '/../Views/login.php';
	}
}