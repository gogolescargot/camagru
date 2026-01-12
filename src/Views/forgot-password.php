<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>Forgot Password</title>
		<link rel="stylesheet" href="/css/style.css">
		<link rel="icon" type="image/x-icon" href="/favicon.ico">
	</head>
	<body>
		<?php include __DIR__ . '/partials/navbar.php'; ?>
		<main>
			<div class="auth-container">
				<h1 class="text-center">Forgot Password</h1>
				<form action="/send-password-reset" method="POST">
					<label for="email">Email:</label>
					<input type="email" id="email" name="email" required>
					<button type="submit">Send</button>
				</form>
			</div>
		</main>
		<?php include __DIR__ . '/partials/footer.php'; ?>
	</body>
</html>