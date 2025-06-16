<?php

namespace Controllers;

use Core\Database;
use Core\ErrorHandler;
use Models\UserModel;
use Models\TokenModel;

class VerifyAccountController
{
	public function verifyAccount()
	{
		try {
			$token = $_GET['token'] ?? '';

			$pdo = Database::getConnection();

			$tokenModel = new TokenModel($pdo);
			$userModel = new UserModel($pdo);

			$tokenData = $tokenModel->findValidToken($token);

			if (!$tokenData) {
				ErrorHandler::handleError(
					'Invalid or expired token.',
					'/login',
					500,
					False
				);
			}

			$pdo->beginTransaction();
			$userModel->updateVerify(TRUE, $tokenData['user_id']);
			$tokenModel->deleteToken($token);
			$pdo->commit();

			$_SESSION['success'] = 'Your account has been verified successfully. You can now log in.';
			header('Location: /home');
			exit();
		}
		catch (\Exception $e) {
			ErrorHandler::rollbackTransaction($pdo);
			ErrorHandler::handleException($e);
		}
	}
}