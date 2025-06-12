<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>Login</title>
		<link rel="stylesheet" href="/css/styles.css">
	</head>
	<body>
		<?php include __DIR__ . '/partials/navbar.php'; ?>
		<main>
			<h1>Login</h1>
			<form action="/login" method="POST">
				<label for="email">Email:</label>
				<input type="email" id="email" name="email" required>

				<label for="password">Password:</label>
				<input type="password" id="password" name="password" required>

				<button type="submit">Login</button>
			</form>
			<a href="/forgot-password">Forgot Password</a>
			<?php
			if (isset($_SESSION['error'])) {
				echo '<p style="color: red;">' . htmlspecialchars($_SESSION['error']) . '</p>';
				unset($_SESSION['error']);
			}
			?>
		<main>
	</body>
</html>