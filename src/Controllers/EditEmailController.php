<?php

namespace Controllers;

use Core\Database;
use Models\EmailTokenModel;
use Models\UserModel;

class EditEmailController
{
	public function editEmail()
	{
		try {
			$pdo = Database::getConnection();
			$emailTokenModel = new EmailTokenModel($pdo);
			$userModel = new UserModel($pdo);

			$token = $_GET['token'] ?? '';

			$pdo->beginTransaction();

			$tokenData = $emailTokenModel->findValidToken($token);

			if (!$tokenData) {
				$_SESSION['error'] = 'Invalid or expired token.';
				header('Location: /home');
				exit();
			}

			$userModel->updateEmail($tokenData['new_email'], $tokenData['user_id']);
			$emailTokenModel->deleteToken($token);

			$pdo->commit();

			$_SESSION['success'] = 'Your email has been edited successfully.';
			header('Location: /home');
		}
		catch (Exception $e) {
			if (isset($pdo)) {
				$pdo->rollBack();
			}
			error_log($e->getMessage());
			$_SESSION['error'] = 'An error occurred while processing your request. Please try again later.';
			header('Location: /home');
			exit();
		}
	}
}