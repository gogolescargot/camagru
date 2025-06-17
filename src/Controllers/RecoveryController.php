<?php

namespace Controllers;

use Core\Database;
use Core\ErrorHandler;
use Helpers\FormHelper;
use Models\TokenModel;
use Models\UserModel;

class RecoveryController
{
	public function sendPasswordReset()
	{
		try {
			
			$email = isset($_POST['email']) ? trim($_POST['email']) : '';

			if (empty($email)) {
				ErrorHandler::handleError(
					'All fields are required.',
					'/forgot-password',
					400,
					False
				);
			}

			$token = bin2hex(random_bytes(32));
			$expires_at = date('Y-m-d H:i:s', time() + 3600);

			$subject = "Password Reset Request";
			$message = "Click the link below to reset your password:\n\nhttp://localhost:8080/reset-password?token=$token";

			$pdo = Database::getConnection();

			$userModel = new UserModel($pdo);
			$tokenModel = new TokenModel($pdo);

			$user = $userModel->findByEmail($email);

			if (!$user) {
				$_SESSION['info'] = "If an account linked with this email exists, a password reset email has been sent successfully.";
				header('Location: /home');
				exit();
			}

			$pdo->beginTransaction();
			$tokenModel->createToken($user['id'], $token, 'password_reset', $expires_at);

			if (!mail($email, $subject, $message)) {
				throw new Exception('Failed to send email.');
			}

			$pdo->commit();

			$_SESSION['info'] = "If an account linked with this email exists, a password reset email has been sent successfully.";
			header('Location: /home');
			exit();
		}
		catch (\Exception $e) {
			ErrorHandler::handleException($e);
		}
	}

	public function resetPassword()
	{
		try {
			$token = isset($_POST['token']) ? trim($_POST['token']) : '';
			$newPassword = isset($_POST['password']) ? trim($_POST['password']) : '';
			$newConfirmPassword = isset($_POST['confirm-password']) ? trim($_POST['confirm-password']) : '';

			if (empty($newPassword) || empty($newConfirmPassword)) {
				ErrorHandler::handleError(
					'All fields are required.',
					"/reset-password?token=$token",
					400,
					False
				);
			}

			if ($newPassword !== $newConfirmPassword) {
				ErrorHandler::handleError(
					'Passwords do not match.',
					"/reset-password?token=$token",
					400,
					False
				);
			}

			$passwordErrors = FormHelper::validatePassword($newPassword);

			if (!empty($passwordErrors)) {
				ErrorHandler::handleError(
					$passwordErrors,
					"/reset-password?token=$token",
					400,
					False
				);
			}

			$pdo = Database::getConnection();

			$tokenModel = new TokenModel($pdo);
			$tokenData = $tokenModel->findValidToken($token);

			if (!$tokenData) {
				ErrorHandler::handleError(
					'Invalid or expired token.',
					'/forgot-password',
					400,
					False
				);
			}

			$hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

			$userModel = new UserModel($pdo);
			$user = $userModel->findById($tokenData['user_id']);

			$pdo->beginTransaction();
			$userModel->updatePassword($hashedPassword, $user['id']);
			$tokenModel->deleteToken($token);
			$pdo->commit();

			$_SESSION['success'] = 'Password updated successfully.';
			header('Location: /login');
			exit();
		}
		catch (\Exception $e) {
			ErrorHandler::rollbackTransaction($pdo);
			ErrorHandler::handleException($e);
		}
	}
}