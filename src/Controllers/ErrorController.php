<?php

namespace Controllers;

use Core\ErrorHandler;

class ErrorController
{
	public function index()
	{
		if (isset($_SESSION['error'])) {
			$errorMessage = $_SESSION['error'];
			$errorCode = $_SESSION['error_code'] ?? 500;
			unset($_SESSION['error']);
			unset($_SESSION['error_code']);
		}
		else {
			header("Location: /home");
		}

		include __DIR__ . '/../Views/error.php';
	}
}