<?php

require_once __DIR__ . '/../Models/UserModel.php';
require_once __DIR__ . '/../Models/PasswordTokenModel.php';
require_once __DIR__ . '/../Helpers/FormHelper.php';

class RecoveryController
{
	public function sendPasswordReset()
	{
		$email = trim($_POST['email'] ?? '');

		if (empty($email)) {
			$_SESSION['error'] = 'All fields are required.';
			header('Location: /forgot-password');
			exit();
		}

		$token = bin2hex(random_bytes(32));
		$expires_at = date('Y-m-d H:i:s', time() + 3600);

		$subject = "Password Reset Request";
		$message = "Click the link below to reset your password:\n\nhttp://localhost:8080/reset-password?token=$token";

		try {
			$userModel = new UserModel();
			$user = $userModel->findByEmail($email);

			if (!$user) {
				header('Location: /home');
				exit();
			}

			$passwordTokenModel = new PasswordTokenModel();
			$passwordTokenModel->createToken($user['id'], $token, $expires_at);

			if (!mail($email, $subject, $message)) {
				throw new Exception('Failed to send email.');
			}

			$_SESSION['info'] = "If an account linked with this email exists, a password reset email has been sent successfully.";
			header('Location: /home');
			exit();
		}
		catch (Exception $e) {
			error_log($e->getMessage());
			$_SESSION['error'] = 'An error occurred while processing your request. Please try again later.';
			header('Location: /forgot-password');
			exit();
		}
	}

	public function resetPassword()
	{
		$token = $_POST['token'] ?? '';
		$newPassword = $_POST['password'] ?? '';

		if (empty($newPassword)) {
			$_SESSION['error'] = 'All fields are required.';
			header("Location: /reset-password?token=$token");
			exit();
		}

		$passwordErrors = FormHelper::validatePassword($newPassword);

		if (!empty($passwordErrors)) {
			$_SESSION['error'] = $passwordErrors;
			header("Location: /reset-password?token=$token");
			exit();
		}

		try {
			$passwordTokenModel = new PasswordTokenModel();
			$tokenData = $passwordTokenModel->findValidToken($token);

			if (!$tokenData) {
				$_SESSION['error'] = 'Invalid or expired token.';
				header('Location: /forgot-password');
				exit();
			}

			$hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

			$userModel = new UserModel();
			$user = $userModel->findById($tokenData['user_id']);
			$userModel->updatePassword($hashedPassword, $user['id']);

			$passwordTokenModel->deleteToken($token);

			$_SESSION['success'] = 'Password updated successfully.';
			header('Location: /login');
			exit();
		}
		catch (Exception $e) {
			error_log($e->getMessage());
			$_SESSION['error'] = 'An error occurred while processing your request. Please try again later.';
			header("Location: /reset-password?token=$token");
			exit();
		}
	}
}