<?php

namespace Controllers;

use Core\Database;
use Core\ErrorHandler;
use Models\TokenModel;
use Models\UserModel;

class EditEmailController
{
	public function editEmail()
	{
		try {
			$pdo = Database::getConnection();
			$tokenModel = new TokenModel($pdo);
			$userModel = new UserModel($pdo);

			$token = $_GET['token'] ?? '';

			$tokenData = $tokenModel->findValidToken($token);

			if (!$tokenData) {
				ErrorHandler::handleError(
					'Invalid or expired token.',
					'/home',
					400,
					False
				);
			}

			$pdo->beginTransaction();
			$userModel->updateEmail($tokenData['new_email'], $tokenData['user_id']);
			$tokenModel->deleteToken($token);
			$pdo->commit();

			$_SESSION['success'] = 'Your email has been edited successfully.';
			header('Location: /home');
			exit();
		}
		catch (\Exception $e) {
			ErrorHandler::rollbackTransaction($pdo);
			ErrorHandler::handleException($e);
		}
	}
}