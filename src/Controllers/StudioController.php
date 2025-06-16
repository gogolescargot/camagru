<?php

namespace Controllers;

use Core\ErrorHandler;

class StudioController
{
	public function index()
	{
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
}