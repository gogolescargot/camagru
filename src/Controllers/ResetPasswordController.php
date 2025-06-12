<?php

require_once __DIR__ . '/../Models/PasswordTokenModel.php';

class ResetPasswordController
{
	public function index()
	{
		if (isset($_SESSION['user_id'])) {
			header('Location: /home');
			exit();
		}

		$token = $_GET['token'] ?? '';

		try {
			$passwordTokenModel = new PasswordTokenModel();
			$tokenData = $passwordTokenModel->findValidToken($token);

			if (!$tokenData) {
				$_SESSION['error'] = 'Invalid or expired token.';
				header('Location: /forgot-password');
				exit();
			}
		}
		catch (PDOException $e) {
			error_log($e->getMessage());
			$_SESSION['error'] = 'An error occurred while processing your request. Please try again later.';
			header('Location: /forgot-password');
			exit();
		}

		include __DIR__ . '/../Views/reset-password.php';
	}
}