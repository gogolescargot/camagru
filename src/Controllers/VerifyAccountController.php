<?php

require_once __DIR__ . '/../Models/VerifyTokenModel.php';
require_once __DIR__ . '/../Models/UserModel.php';

class VerifyAccountController
{
	public function verifyAccount()
	{
		try {
			$token = $_GET['token'] ?? '';

			$verifyTokenModel = new VerifyTokenModel();
			$tokenData = $verifyTokenModel->findValidToken($token);

			if (!$tokenData) {
				$_SESSION['error'] = 'Invalid or expired token.';
				header('Location: /login');
				exit();
			}

			$userModel = new UserModel();
			$userModel->updateVerify(TRUE, $tokenData['user_id']);

			$verifyTokenModel->deleteToken($token);

			$_SESSION['success'] = 'Your account has been verified successfully. You can now log in.';
			header('Location: /home');
		}
		catch (Exception $e) {
			error_log($e->getMessage());
			$_SESSION['error'] = 'An error occurred while processing your request. Please try again later.';
			header('Location: /login');
			exit();
		}

	}
}