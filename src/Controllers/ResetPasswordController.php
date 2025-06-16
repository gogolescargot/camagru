<?php

namespace Controllers;

use Core\Database;
use Core\ErrorHandler;
use Models\TokenModel;

class ResetPasswordController
{
	public function index()
	{
		try {
			if (isset($_SESSION['user_id'])) {
				ErrorHandler::handleError(
					'You must not be logged in to perform this action.',
					'/home',
					500,
					False
				);
			}

			$token = $_GET['token'] ?? '';

			$pdo = Database::getConnection();
			$tokenModel = new TokenModel($pdo);
			$tokenData = $tokenModel->findValidToken($token);

			if (!$tokenData) {
				ErrorHandler::handleError(
					'Invalid or expired token.',
					'/forgot-password',
					500,
					False
				);
			}

			include __DIR__ . '/../Views/reset-password.php';
		}
		catch (\Exception $e) {
			ErrorHandler::handleException($e);
		}
	}
}