<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>Reset Password</title>
		<link rel="stylesheet" href="/css/styles.css">
	</head>
	<body>
		<?php include __DIR__ . '/partials/navbar.php'; ?>
		<main>
			<h1>Reset Password</h1>
			<form action="/reset-password" method="POST">
				<input type="hidden" name="token" value="<?php echo htmlspecialchars($_GET['token'] ?? ''); ?>">
				<label for="password">New Password:</label>
				<input type="password" id="password" name="password" required>
				<button type="submit">Reset Password</button>
			</form>
			<?php
			if (isset($_SESSION['error'])) {
				echo '<p style="color: red;">' . htmlspecialchars($_SESSION['error']) . '</p>';
				unset($_SESSION['error']);
			}
			?>
		<main>
	</body>
</html>