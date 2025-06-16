<?php

namespace Controllers;

use Core\Database;
use Helpers\FormHelper;
use Models\UserModel;
use Models\VerifyTokenModel;

class AuthController
{
	public function login()
	{
		$username = trim($_POST['username'] ?? '');
		$password = trim($_POST['password'] ?? '');

		if (empty($username) || empty($password)) {
			$_SESSION['error'] = 'All fields are required.';
			header('Location: /login');
			exit();
		}

		try {
			$pdo = Database::getConnection();
			$userModel = new UserModel($pdo);

			$user = $userModel->findByUsername($username);

			if ($user && password_verify($password, $user['password'])) {

				if ($user["verified"] == FALSE) {
					$_SESSION['error'] = 'Your account is not verified. Please check your email to verify your account.';
					header('Location: /login');
					exit();
				}

				$_SESSION['user_id'] = $user['id'];
				$_SESSION['success'] = "Login successful. Welcome back!";
				header('Location: /home');
				exit();
			}
			else {
				$_SESSION['error'] = 'Invalid email or password.';
				header('Location: /login');
				exit();
			}
		}
		catch (Exception $e) {
			error_log($e->getMessage());
			$_SESSION['error'] = 'An error occurred while logging in. Please try again.';
			header('Location: /login');
			exit();
		}
	}

	public function register()
	{
		$username = trim($_POST['username'] ?? '');
		$email = trim($_POST['email'] ?? '');
		$password = trim($_POST['password'] ?? '');
		$confirmPassword = trim($_POST['confirm-password'] ?? '');

		if (empty($username) || empty($email) || empty($password) || empty($confirmPassword)) {
			$_SESSION['error'] = 'All fields are required.';
			header('Location: /register');
			exit();
		}

		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			$_SESSION['error'] = 'Invalid email address.';
			header('Location: /register');
			exit();
		}

		$usernameErrors = FormHelper::validateUsername($username);

		if (!empty($usernameErrors)) {
			$_SESSION['error'] = $usernameErrors;
			header('Location: /register');
			exit();
		}

		if ($password !== $confirmPassword) {
			$_SESSION['error'] = 'Passwords do not match.';
			header('Location: /register');
			exit();
		}

		$passwordErrors = FormHelper::validatePassword($password);

		if (!empty($passwordErrors)) {
			$_SESSION['error'] = $passwordErrors;
			header('Location: /register');
			exit();
		}

		$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

		$token = bin2hex(random_bytes(32));

		$subject = "Account Verification";
		$message = "Click the link below to verify your account:\n\nhttp://localhost:8080/verify-account?token=$token";

		try {
			$pdo = Database::getConnection();
			$userModel = new UserModel($pdo);
			$verifyTokenModel = new VerifyTokenModel($pdo);

			$pdo->beginTransaction();

			$user_id = $userModel->createUser($username, $email, $hashedPassword);
			$tokenData = $verifyTokenModel->createToken($user_id, $token);

			if (!mail($email, $subject, $message)) {
				throw new Exception('Failed to send email.');
			}

			$pdo->commit();

			$_SESSION['info'] = 'A confirmation email has been sent to verify your account.';
			header('Location: /login');
			exit();
		}
		catch (Exception $e) {
			if (isset($pdo)) {
				$pdo->rollBack();
			}
			error_log($e->getMessage());
			$_SESSION['error'] = 'An error occurred while registering. Please try again.';
			header('Location: /register');
			exit();
		}
	}

	public function logout()
	{
		session_unset();
		session_destroy();

		header('Location: /home');
		exit();
	}
}