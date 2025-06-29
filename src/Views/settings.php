<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>Settings</title>
		<link rel="stylesheet" href="/css/style.css">
		<link rel="icon" type="image/x-icon" href="/favicon.ico">
	</head>
	<body>
		<?php include __DIR__ . '/partials/navbar.php'; ?>
		<main>
			<h1>Settings</h1>

			<h3>Edit</h3>
			<form action="/edit-username" method="POST">
				<label for="username">Username:</label>
				<input type="text" id="username" name="username" value="<?php echo htmlspecialchars($username); ?>" placeholder="Enter new username">

				<button type="submit">Submit</button>
			</form>

			<form action="/edit-email" method="POST">
				<label for="email">Email:</label>
				<input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" placeholder="Enter new email">

				<button type="submit">Submit</button>
			</form>

			<form action="/edit-password" method="POST">
				<label for="password">Password:</label>
				<input type="password" id="password" name="password" placeholder="Enter new password">

				<label for="password">Confirm password:</label>
				<input type="password" id="confirm-password" name="confirm-password" placeholder="Confirm new password">

				<button type="submit">Submit</button>
			</form>

			<h3>Preferences</h3>
			<form action="/edit-preferences" method="POST">
				<label for="email-notifications">
					<input type="checkbox" id="email-notifications" name="email-notifications" <?php echo $emailNotifications ? 'checked' : ''; ?>>
					Send email notifications
				</label>

				<button type="submit">Submit</button>
			</form>
		</main>
	</body>
</html>