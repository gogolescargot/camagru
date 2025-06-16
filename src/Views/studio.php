<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>Studio</title>
		<link rel="stylesheet" href="/css/styles.css">
	</head>
	<body>
		<?php include __DIR__ . '/partials/navbar.php'; ?>
		<main>
			<h1>Studio</h1>
			<form action="/upload" method="POST" enctype="multipart/form-data">
				<label for="image">Image:</label>
				<input type="file" id="image" name="image" accept="image/*" required>

				<button type="submit">Upload</button>
			</form>
		<main>
	</body>
</html>