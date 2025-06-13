<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>Reset password</title>
		<link rel="stylesheet" href="/css/styles.css">
	</head>
	<body>
		<?php include __DIR__ . '/partials/navbar.php'; ?>
		<main>
			<h1>Reset password</h1>
			<form action="/reset-password" method="POST">
				<input type="hidden" name="token" value="<?php echo htmlspecialchars($_GET['token'] ?? ''); ?>">
				<label for="password">New password:</label>
				<input type="password" id="password" name="password" required>
				<label for="password">Confirm new password:</label>
				<input type="password" id="confirm-password" name="confirm-password" required>
				<button type="submit">Reset password</button>
			</form>
		<main>
	</body>
</html>