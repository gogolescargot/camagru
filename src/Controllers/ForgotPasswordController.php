<?php

namespace Controllers;

class ForgotPasswordController
{
	public function index()
	{
		if (isset($_SESSION['user_id'])) {
			header('Location: /home');
			exit();
		}

		include __DIR__ . '/../Views/forgot-password.php';
	}
}