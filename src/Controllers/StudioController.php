<?php

namespace Controllers;

use Core\Database;
use Core\ErrorHandler;
use Models\ImageModel;
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
			$imageModel = new ImageModel($pdo);

			$images = $imageModel->getImages($_SESSION['user_id']);

			include __DIR__ . '/../Views/studio.php';
		}
		catch (\Exception $e) {
			ErrorHandler::handleException($e);
		}
	}

	public function gallery()
	{
		if (!isset($_SESSION['user_id'])) {
			ErrorHandler::handleError(
				'You must be logged in to perform this action.',
				'/home',
				403,
				False
			);
		}

		if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
			http_response_code(404);
			exit();
		}

		$pdo = Database::getConnection();
		$imageModel = new ImageModel($pdo);
		$images = $imageModel->getImages($_SESSION['user_id']);
		include __DIR__ . '/../Views/partials/gallery.php';
	}
}