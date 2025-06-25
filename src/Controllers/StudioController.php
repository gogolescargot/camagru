<?php

namespace Controllers;

use Core\Database;
use Models\PostModel;
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

			$pdo = Database::getConnection();
			$postModel = new PostModel($pdo);

			$images = $postModel->getLastUserPosts(6, $_SESSION['user_id']);

			include __DIR__ . '/../Views/studio.php';
		}
		catch (\Exception $e) {
			ErrorHandler::handleException($e);
		}
	}
}