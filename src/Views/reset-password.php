<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>Reset password</title>
		<link rel="stylesheet" href="/css/style.css">
		<link rel="icon" type="image/x-icon" href="/favicon.ico">
	</head>
	<body>
		<?php include __DIR__ . '/partials/navbar.php'; ?>
		<main>
			<div class="auth-container">
				<h1>Reset password</h1>
				<form action="/reset-password" method="POST">
					<input type="hidden" name="token" value="<?php echo htmlspecialchars($_GET['token'] ?? ''); ?>">

					<label for="new-password">New password:</label>
					<input type="password" id="new-password" name="new-password" maxlength="255" required>

					<label for="confirm-new-password">Confirm new password:</label>
					<input type="password" id="confirm-new-password" name="confirm-new-password" maxlength="255" required>
					<button type="submit">Reset password</button>
				</form>
			</div>
		</main>
		<?php include __DIR__ . '/partials/footer.php'; ?>
	</body>
</html>