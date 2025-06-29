<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>Register</title>
		<link rel="stylesheet" href="/css/style.css">
		<link rel="icon" type="image/x-icon" href="/favicon.ico">
	</head>
	<body>
		<?php include __DIR__ . '/partials/navbar.php'; ?>
		<main>
			<h1>Register</h1>
			<form action="/register" method="POST">
				<label for="username">Username:</label>
				<input type="text" id="username" name="username" required>

				<label for="email">Email:</label>
				<input type="email" id="email" name="email" required>

				<label for="password">Password:</label>
				<input type="password" id="password" name="password" required>

				<label for="password">Confirm password:</label>
				<input type="password" id="confirm-password" name="confirm-password" required>

				<button type="submit">Register</button>
			</form>
		</main>
	</body>
</html>