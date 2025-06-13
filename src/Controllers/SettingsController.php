<?php

require_once __DIR__ . '/../Models/UserModel.php';

class SettingsController
{
	public function index()
	{
		if (!isset($_SESSION['user_id'])) {
			header('Location: /home');
			exit();
		}

		try {
			$userModel = new UserModel();
			$user = $userModel->findById($_SESSION['user_id']);

			if (!$user) {
				$_SESSION['error'] = 'An error occurred while processing your request. Please contact an administrator.';
				header('Location: /settings');
				exit();
			}

			$username = htmlspecialchars($user['username']);
			$email = htmlspecialchars($user['email']);

			$emailNotifications = TRUE;
		}
		catch (Exception $e) {
			error_log($e->getMessage());
			$_SESSION['error'] = 'An error occurred while processing your request. Please try again later.';
			header('Location: /settings');
			exit();
		}

		include __DIR__ . '/../Views/settings.php';
	}
}