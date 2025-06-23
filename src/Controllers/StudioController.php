<?php

namespace Controllers;

use Core\Database;
use Core\ErrorHandler;
use Models\UserModel;

class StudioController
{
	public function index()
	{
		try {
			if (!isset($_SESSION['user_id'])) {
				ErrorHandler::handleError(
					'You must be logged in to perform this action.',
					'/home',
					403,
					False
				);
			}

			include __DIR__ . '/../Views/studio.php';
		}
		catch (\Exception $e) {
			ErrorHandler::handleException($e);
		}
	}
}