<?php

namespace Controllers;

use Core\Database;
use Core\ErrorHandler;
use Models\ImageModel;
use Models\UserModel;

class ImageController
{
	public function deleteImage()
	{
		try {
			if (!isset($_SESSION['user_id'])) {
				ErrorHandler::handleError(
					'You must be logged in to perform this action.',
					'/home',
					500,
					False
				);
			}

			$imageId = isset($_GET['image_id']) ? trim($_GET['image_id']) : '';

			if (empty($imageId)) {
				ErrorHandler::handleError(
					'All fields are required.',
					'/studio',
					400,
					False
				);
			}

			$pdo = Database::getConnection();
			$imageModel = new ImageModel($pdo);

			$image = $imageModel->findImage($imageId);

			if (!$image) {
				ErrorHandler::handleError(
					'Invalid or deleted image.',
					'/studio',
					400,
					False
				);
			}

			if ($_SESSION['user_id'] !== $image['user_id']) {
				ErrorHandler::handleError(
					'You are not allowed to perform this action',
					'/studio',
					403,
					False
				);
			}

			$pdo->beginTransaction();
			$imageModel->deleteImage($imageId);
			$pdo->commit();
			
			$_SESSION['success'] = "Image deleted successfully!";
			header('Location: /studio');
			exit();
		}
		catch (\Exception $e) {
			ErrorHandler::rollbackTransaction($pdo);
			ErrorHandler::handleException($e);
		}
	}
}