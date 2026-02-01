<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>Error</title>
		<link rel="stylesheet" href="/css/style.css">
		<link rel="icon" type="image/x-icon" href="/favicon.ico">
	</head>
	<body>
		<?php include __DIR__ . '/partials/navbar.php'; ?>
		<main>
            <div class="error-page">
                <div class="error-message">
                    <h1>Error <?= htmlspecialchars($errorCode) ?></h1>
                    <?php if ($errorMessage !== null): ?>
                        <p class="error"><?= htmlspecialchars($errorMessage) ?></p>
                    <?php else: ?>
                        <p>An error occurred.</p>
                    <?php endif; ?>
                </div>
            </div>
        </main>
		<?php include __DIR__ . '/partials/footer.php'; ?>
	</body>
</html>