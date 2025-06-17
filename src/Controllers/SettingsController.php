<?php

namespace Controllers;

use Core\Database;
use Core\ErrorHandler;
use Models\UserModel;

class SettingsController
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
			$userModel = new UserModel($pdo);
			$user = $userModel->findById($_SESSION['user_id']);

			if (!$user) {
				ErrorHandler::handleError(
					'User not found in the database. Please check the system integrity.',
					'/home',
					500,
					True
				);
			}

			$username = htmlspecialchars($user['username']);
			$email = htmlspecialchars($user['email']);

			$emailNotifications = $user['email_notifications'];

			include __DIR__ . '/../Views/settings.php';
		}
		catch (\Exception $e) {
			ErrorHandler::handleException($e);
		}
	}
}