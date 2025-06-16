<?php

namespace Controllers;

use Core\Database;
use Models\UserModel;
use Models\VerifyTokenModel;

class VerifyAccountController
{
	public function verifyAccount()
	{
		try {
			$token = $_GET['token'] ?? '';

			$pdo = Database::getConnection();
			$pdo->beginTransaction();

			$verifyTokenModel = new VerifyTokenModel($pdo);
			$tokenData = $verifyTokenModel->findValidToken($token);

			if (!$tokenData) {
				$_SESSION['error'] = 'Invalid or expired token.';
				header('Location: /login');
				exit();
			}

			$userModel = new UserModel($pdo);
			$userModel->updateVerify(TRUE, $tokenData['user_id']);

			$verifyTokenModel->deleteToken($token);

			$pdo->commit();

			$_SESSION['success'] = 'Your account has been verified successfully. You can now log in.';
			header('Location: /home');
			exit();
		}
		catch (Exception $e) {
			if (isset($pdo)) {
				$pdo->rollBack();
			}
			error_log($e->getMessage());
			$_SESSION['error'] = 'An error occurred while processing your request. Please try again later.';
			header('Location: /login');
			exit();
		}

	}
}