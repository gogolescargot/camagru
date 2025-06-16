<?php

namespace Controllers;

class LoginController
{
	public function index()
	{
		if (isset($_SESSION['user_id'])) {
			header('Location: /home');
			exit();
		}

		include __DIR__ . '/../Views/login.php';
	}
}