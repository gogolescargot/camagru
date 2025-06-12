<?php

class RegisterController
{
	public function index()
	{
		if (isset($_SESSION['user_id'])) {
			header('Location: /home');
			exit();
		}

		include __DIR__ . '/../Views/register.php';
	}
}