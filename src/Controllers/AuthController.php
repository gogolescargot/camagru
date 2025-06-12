<?php

require_once __DIR__ . '/../Models/UserModel.php';

class AuthController
{
	public function login()
	{

		if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
			$_SESSION['error'] = 'Invalid request method.';
			header('Location: /login');
			exit();
		}

		$email = trim($_POST['email'] ?? '');
		$password = trim($_POST['password'] ?? '');

		if (empty($email) || empty($password)) {
			$_SESSION['error'] = 'All fields are required.';
			header('Location: /login');
			exit();
		}

		try {
			$userModel = new UserModel();
			$user = $userModel->findByEmail($email);

			if ($user && password_verify($password, $user['password'])) {
				$_SESSION['user_id'] = $user['id'];
				$_SESSION['success'] = "You are logged in.";
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
		if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
			$_SESSION['error'] = 'Invalid request method.';
			header('Location: /register');
			exit();
		}

		$username = trim($_POST['username'] ?? '');
		$email = trim($_POST['email'] ?? '');
		$password = trim($_POST['password'] ?? '');

		if (empty($username) || empty($email) || empty($password)) {
			$_SESSION['error'] = 'All fields are required.';
			header('Location: /register');
			exit();
		}

		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			$_SESSION['error'] = 'Invalid email address.';
			header('Location: /register');
			exit();
		}

		$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

		try {
			$userModel = new UserModel();
			$userModel->createUser($username, $email, $hashedPassword);

			$_SESSION['success'] = 'Registration successful. You can now log in.';
			header('Location: /login');
			exit();
		}
		catch (Exception $e) {
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