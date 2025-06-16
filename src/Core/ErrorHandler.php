<?php

namespace Core;

class ErrorHandler
{

	public static function rollbackTransaction($pdo)
	{
		if (isset($pdo) && $pdo->inTransaction()) {
			$pdo->rollBack();
			error_log("Transaction rolled back.");
		}
	}

	public static function handleError($message, $redirect, $code, $isCritical)
	{
		$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';

		if ($isCritical && !$isAdmin) {
			$_SESSION['error'] = 'An error occurred while processing your request. Please try again later.';
		}
		else {
			$_SESSION['error'] = $message;
		}

		error_log("[User ID: " . ($_SESSION['user_id'] ?? 'guest') . "] " . $message);
		header("Location: $redirect");
		exit();
	}

	public static function handleException($e)
	{
		$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';

		if (!$isAdmin)
		{
			$_SESSION['error'] = 'An error occurred while processing your request. Please try again later.';
		}
		else
		{
			$_SESSION['error'] = $e->getMessage();;
		}

		error_log("[User ID: " . ($_SESSION['user_id'] ?? 'guest') . "] " . $e->getMessage());
		header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '/'));

		exit();
	}
}