<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>Login</title>
		<link rel="stylesheet" href="/css/style.css">
		<link rel="icon" type="image/x-icon" href="/favicon.ico">
	</head>
	<body>
		<?php include __DIR__ . '/partials/navbar.php'; ?>
		<main>
			<div class="auth-container">
				<h1>Login</h1>
				<form action="/login" method="POST" autocomplete="on">
					<label for="username">Username</label>
					<input type="text" id="username" name="username" required autofocus>

					<label for="password">Password</label>
					<input type="password" id="password" name="password" required>

					<button type="submit">Login</button>
				</form>
				<a class="auth-link" href="/forgot-password">Forgot Password?</a>
				<a class="auth-link" href="/register">Don't have an account? Register</a>
			</div>
		</main>
	</body>
</html>