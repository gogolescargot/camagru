<?php

require_once __DIR__ . '/../Models/UserModel.php';
require_once __DIR__ . '/../Models/EmailTokenModel.php';
require_once __DIR__ . '/../Models/VerifyTokenModel.php';
require_once __DIR__ . '/../Helpers/FormHelper.php';

class EditAccountController
{
	public function editAccount()
	{
		try {
			if (!isset($_SESSION['user_id'])) {
				$_SESSION['error'] = 'You must be logged in to perform this action.';
				header('Location: /home');
				exit();
			}

			$userModel = new UserModel();
			$user = $userModel->findById($_SESSION['user_id']);

			$newUsername = isset($_POST['username']) ? trim($_POST['username']) : '';
			$newEmail = isset($_POST['email']) ? trim($_POST['email']) : '';
			$newPassword = isset($_POST['password']) ? trim($_POST['password']) : '';
			$newConfirmPassword = isset($_POST['confirm-password']) ? trim($_POST['confirm-password']) : '';

			if ($newUsername != $user['username'] && !empty($newUsername)) {
				$usernameErrors = FormHelper::validateUsername($newUsername);

				if (!empty($usernameErrors)) {
					$_SESSION['error'] = $usernameErrors;
					header('Location: /settings');
					exit();
				}

				if (!empty($userModel->findByUsername($newUsername)))
				{
					$_SESSION['error'] = 'An error occurred while processing your request. Please try again later.';
					header('Location: /settings');
					exit();
				}

				$userModel->updateUsername($newUsername, $user['id']);
			}

			if ($newEmail != $user['email'] && !empty($newEmail)) {

				if (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
					$_SESSION['error'] = 'Invalid email address.';
					header('Location: /settings');
					exit();
				}

				if (!empty($userModel->findByEmail($newEmail))) {
					$_SESSION['error'] = 'An error occurred while processing your request. Please try again later.';
					header('Location: /settings');
					exit();
				}

				

				$token = bin2hex(random_bytes(32));
				$subject = "Email Verification";
				$message = "Click the link below to verify your email:\n\nhttp://localhost:8080/edit-email?token=$token";

				$emailTokenModel = new EmailTokenModel();
				$emailTokenModel->cleanOldToken($user['id']);
				$tokenData = $emailTokenModel->createToken($user['id'], $newEmail, $token);

				if (!mail($newEmail, $subject, $message)) {
					throw new Exception('Failed to send email.');
				}

				$_SESSION['info'] = 'A confirmation email has been sent to verify your email.';
				header('Location: /settings');
				exit();
			}

			if (!empty($newPassword) || !empty($newConfirmPassword)) {

				if ($newPassword !== $newConfirmPassword) {
					$_SESSION['error'] = 'Passwords do not match.';
					header('Location: /settings');
					exit();
				}

				$passwordErrors = FormHelper::validatePassword($newPassword);

				if (!empty($passwordErrors)) {
					$_SESSION['error'] = $passwordErrors;
					header('Location: /settings');
					exit();
				}

				$hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
				
				$userModel->updatePassword($hashedPassword, $user['id']);
			}

			$_SESSION['success'] = 'Your profile has been successfully updated.';
			header('Location: /settings');
			exit();
		}
		catch (Exception $e) {
			error_log($e->getMessage());
			$_SESSION['error'] = 'An error occurred while processing your request. Please try again later.';
			header('Location: /settings');
			exit();
		}
	}
}