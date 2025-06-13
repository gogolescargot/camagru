<?php

require_once __DIR__ . '/../Models/UserModel.php';
require_once __DIR__ . '/../Models/EmailTokenModel.php';

class EditEmailController
{
	public function editEmail()
	{
		try {
			$token = $_GET['token'] ?? '';

			$emailTokenModel = new EmailTokenModel();
			$tokenData = $emailTokenModel->findValidToken($token);

			if (!$tokenData) {
				$_SESSION['error'] = 'Invalid or expired token.';
				header('Location: /home');
				exit();
			}

			$userModel = new UserModel();
			$userModel->updateEmail($tokenData['new_email'], $tokenData['user_id']);

			$emailTokenModel->deleteToken($token);

			$_SESSION['success'] = 'Your email has been edited successfully.';
			header('Location: /home');
		}
		catch (Exception $e) {
			error_log($e->getMessage());
			$_SESSION['error'] = 'An error occurred while processing your request. Please try again later.';
			header('Location: /home');
			exit();
		}

	}
}