<?php

namespace Controllers;

use Core\Database;
use Core\ErrorHandler;
use Helpers\FormHelper;
use Models\UserModel;
use Models\TokenModel;

class AuthController
{
	public function login()
	{
		try {
			$username = isset($_POST['username']) ? trim($_POST['username']) : '';
			$password = isset($_POST['password']) ? trim($_POST['password']) : '';

			if (empty($username) || empty($password)) {
				ErrorHandler::handleError(
					'All fields are required.',
					'/login',
					400,
					False
				);
			}

			$pdo = Database::getConnection();
			$userModel = new UserModel($pdo);

			$user = $userModel->findByUsername($username);

			if ($user && password_verify($password, $user['password'])) {
				if ($user["verified"] == False) {
					ErrorHandler::handleError(
						'Your account is not verified. Please check your email to verify your account.',
						'/login',
						403,
						False
					);
				}

				$_SESSION['user_id'] = $user['id'];

				// if ($username == "test")
				// {
				// 	$_SESSION['role'] = "admin";
				// }

				$_SESSION['success'] = "Login successful. Welcome back!";
				header('Location: /home');
				exit();
			}
			else {
				ErrorHandler::handleError(
					'Invalid username or password.',
					'/login',
					400,
					False
				);
			}
		}
		catch (\Exception $e) {
			ErrorHandler::handleException($e);
		}
	}

	public function register()
	{
		try {
			$username = isset($_POST['username']) ? trim($_POST['username']) : '';
			$email = isset($_POST['email']) ? trim($_POST['email']) : '';
			$password = isset($_POST['password']) ? trim($_POST['password']) : '';
			$confirmPassword = isset($_POST['confirm-password']) ? trim($_POST['confirm-password']) : '';

			if (empty($username) || empty($email) || empty($password) || empty($confirmPassword)) {
				ErrorHandler::handleError(
					'All fields are required.',
					'/register',
					400,
					False
				);
			}

			if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
				ErrorHandler::handleError(
					'Invalid email address.',
					'/register',
					400,
					False
				);
			}

			$usernameErrors = FormHelper::validateUsername($username);

			if (!empty($usernameErrors)) {
				ErrorHandler::handleError(
					$usernameErrors,
					'/register',
					400,
					False
				);
			}

			if ($password !== $confirmPassword) {
				ErrorHandler::handleError(
					'Passwords do not match.',
					'/register',
					400,
					False
				);
			}

			$passwordErrors = FormHelper::validatePassword($password);

			if (!empty($passwordErrors)) {
				ErrorHandler::handleError(
					$passwordErrors,
					'/register',
					400,
					False
				);
			}

			$hashedPassword = password_hash($password, PASSWORD_DEFAULT);
			$token = bin2hex(random_bytes(32));

			$subject = "Account Verification";
			$message = "Click the link below to verify your account:\n\nhttp://localhost:8080/verify-account?token=$token";

			$pdo = Database::getConnection();
			$userModel = new UserModel($pdo);
			$tokenModel = new TokenModel($pdo);

			if ($userModel->findByUsername($username)) {
				ErrorHandler::handleError(
					'This username is already taken.',
					'/register',
					400,
					False
				);
			}

			if ($userModel->findByEmail($email)) {
				ErrorHandler::handleError(
					'This email is already registered.',
					'/register',
					400,
					False
				);
			}

			$pdo->beginTransaction();
			$user_id = $userModel->createUser($username, $email, $hashedPassword);
			$tokenModel->createToken($user_id, $token, "email_verification", NULL);

			if (!mail($email, $subject, $message)) {
				throw new Exception('Failed to send email.');
			}

			$pdo->commit();

			$_SESSION['info'] = 'A confirmation email has been sent to verify your account.';
			header('Location: /login');
			exit();
		}
		catch (\Exception $e) {
			ErrorHandler::rollbackTransaction($pdo);
			ErrorHandler::handleException($e);
		}
	}

	public function logout()
	{
		try {
			session_unset();
			session_destroy();

			header('Location: /home');
			exit();
		}
		catch (\Exception $e) {
			ErrorHandler::handleException($e);
		}
	}
}