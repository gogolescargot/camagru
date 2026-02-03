<?php

namespace Controllers;

use Core\Database;
use Core\ErrorHandler;
use Helpers\FormHelper;
use Models\TokenModel;
use Models\UserModel;

class EditAccountController
{
	public function editUsername()
	{
		try {
			if (!isset($_SESSION['user_id'])) {
				ErrorHandler::handleError(
					'You must be logged in to perform this action.',
					'/login',
					403,
					False
				);
			}

			$pdo = Database::getConnection();

			$userModel = new UserModel($pdo);

			$user = $userModel->findById($_SESSION['user_id']);

			$newUsername = isset($_POST['username']) ? trim($_POST['username']) : '';

			if ($newUsername === $user['username']) {
				header('Location: /settings');
				exit();
			}

			$usernameErrors = FormHelper::validateUsername($newUsername);

			if (!empty($usernameErrors)) {
				ErrorHandler::handleError(
					$usernameErrors,
					'/settings',
					400,
					False
				);
			}

			if (!empty($userModel->findByUsername($newUsername))) {
				ErrorHandler::handleError(
					'This username is already taken.',
					'/settings',
					400,
					False
				);
			}

			$pdo->beginTransaction();
			$userModel->updateUsername($newUsername, $user['id']);
			$pdo->commit();

			$_SESSION['success'] = 'Your username has been successfully updated.';
			header('Location: /settings');
			exit();
		}
		catch (\Exception $e) {
			ErrorHandler::rollbackTransaction($pdo);
			ErrorHandler::handleException($e);
		}
	}

	public function editEmail()
	{
		try {
			if (!isset($_SESSION['user_id'])) {
				ErrorHandler::handleError(
					'You must be logged in to perform this action.',
					'/login',
					403,
					False
				);
			}

			$pdo = Database::getConnection();

			$userModel = new UserModel($pdo);
			$tokenModel = new TokenModel($pdo);

			$user = $userModel->findById($_SESSION['user_id']);

			$newEmail = isset($_POST['email']) ? trim($_POST['email']) : '';

			if ($newEmail === $user['email']) {
				header('Location: /settings');
				exit();
			}
			
			if (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
				ErrorHandler::handleError(
					'Invalid email format.',
					'/settings',
					400,
					False
				);
			}

			if (!empty($userModel->findByEmail($newEmail))) {
				ErrorHandler::handleError(
					'This email is already registered.',
					'/settings',
					400,
					False
				);
			}

			$token = bin2hex(random_bytes(32));
			$subject = "Email Verification";
			$message = "Click the link below to verify your email:\n\nhttp://localhost:8080/edit-email?token=$token";

			$expires_at = date('Y-m-d H:i:s', time() + 3600);


			$pdo->beginTransaction();
			$tokenModel->cleanOldEmailToken($user['id']);
			$tokenModel->createToken($user['id'], $token, 'email_change', $expires_at, $newEmail);

			if (!mail($newEmail, $subject, $message)) {
				throw new Exception('Failed to send email.');
			}

			$pdo->commit();

			$_SESSION['info'] = 'A confirmation email has been sent to verify your email.';
			header('Location: /settings');
			exit();
		}
		catch (\Exception $e) {
			ErrorHandler::rollbackTransaction($pdo);
			ErrorHandler::handleException($e);
		}
	}

	public function editPassword()
	{
		try {
			if (!isset($_SESSION['user_id'])) {
				ErrorHandler::handleError(
					'You must be logged in to perform this action.',
					'/login',
					403,
					False
				);
			}

			$pdo = Database::getConnection();

			$userModel = new UserModel($pdo);
			$user = $userModel->findById($_SESSION['user_id']);

			$currentPassword = isset($_POST['current-password']) ? trim($_POST['current-password']) : '';
			$newPassword = isset($_POST['new-password']) ? trim($_POST['new-password']) : '';
			$newConfirmPassword = isset($_POST['confirm-new-password']) ? trim($_POST['confirm-new-password']) : '';

			if ($newPassword !== $newConfirmPassword) {
				ErrorHandler::handleError(
					'New passwords do not match.',
					'/settings',
					400,
					False
				);
			}

			if (!password_verify($currentPassword, $user['password'])) {
				ErrorHandler::handleError(
					'Current password is incorrect.',
					'/settings',
					400,
					False
				);
			}

			$passwordErrors = FormHelper::validatePassword($newPassword);

			if (!empty($passwordErrors)) {
				ErrorHandler::handleError(
					$passwordErrors,
					'/settings',
					400,
					False
				);
			}

			$hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

			$pdo->beginTransaction();
			$userModel->updatePassword($hashedPassword, $user['id']);
			$pdo->commit();

			$_SESSION['success'] = 'Your password has been successfully updated.';
			header('Location: /settings');
			exit();
		}
		catch (\Exception $e) {
			ErrorHandler::rollbackTransaction($pdo);
			ErrorHandler::handleException($e);
		}
	}

	public function editEmailNotifications()
	{
		try {
			if (!isset($_SESSION['user_id'])) {
				ErrorHandler::handleError(
					'You must be logged in to perform this action.',
					'/login',
					403,
					False
				);
			}

			$pdo = Database::getConnection();

			$userModel = new UserModel($pdo);

			$emailNotifications = isset($_POST['email-notifications']);

			$pdo->beginTransaction();
			$userModel->updateEmailNotifications((int)$emailNotifications, $_SESSION['user_id']);
			$pdo->commit();

			$_SESSION['success'] = 'Your notification preferences have been successfully updated.';
			header('Location: /settings');
			exit();
		}
		catch (\Exception $e) {
			ErrorHandler::rollbackTransaction($pdo);
			ErrorHandler::handleException($e);
		}
	}
}