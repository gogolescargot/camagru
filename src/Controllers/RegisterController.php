<?php

namespace Controllers;

use Core\ErrorHandler;

class RegisterController
{
	public function index()
	{
		if (isset($_SESSION['user_id'])) {
			ErrorHandler::handleError(
				'You are already logged in.',
				'/home',
				500,
				False
			);
		}

		include __DIR__ . '/../Views/register.php';
	}
}