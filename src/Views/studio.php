<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>Studio</title>
		<link rel="stylesheet" href="/css/style.css">
		<link rel="icon" type="image/x-icon" href="/favicon.ico">
	</head>
	<body>
		<?php include __DIR__ . '/partials/navbar.php'; ?>
		<main>
			<div id="studio-container">
				<div id="edit-container">
					<div id="preview-container">
						<div id="canvas-wrapper">
							<canvas id="webcam-canvas"></canvas>
							<div id="canvas-loading" aria-hidden="true">Loading...</div>
						</div>
						<div id="image-container">
							<label for="image">Background:</label>
							<input type="file" id="image" name="image" accept="image/*">
							<button id="remove-image-button" style="display: none;">✖</button>
						</div>
						<div id="post-container">
							<input type="text" name="title" id="title" maxlength="255" placeholder="Title of your post">
							<button id="post-button">Post</button>
							<button id="reset-sticker-button">Reset</button>
						</div>
						</form>
					</div>
					<div id="stickers-container">
						<img src="/stickers/sticker_1.png" class="sticker" draggable="true"/>
						<img src="/stickers/sticker_2.png" class="sticker" draggable="true"/>
						<img src="/stickers/sticker_3.png" class="sticker" draggable="true"/>
						<img src="/stickers/sticker_4.png" class="sticker" draggable="true"/>
					</div>
				</div>
				<div id="galleries-container">
					<?php if (empty($images)): ?>
						<p>No images yet.</p>
					<?php else: ?>
						<?php foreach ($images as $image): ?>
							<div class="gallery">
								<img class="image" src="/uploads/<?= htmlspecialchars($image['image_path']) ?>">
								<form method="post" action="/delete-post?post_id=<?= htmlspecialchars($image['id'])?>&redirect=/studio">
									<input type="hidden" name="image_id" value="<?= $image['id'] ?>">
									<button class="delete-button">Delete</button>
								</form>
							</div>
						<?php endforeach; ?>
					<?php endif; ?>
				</div>
			</div>
			<p id="studio-error"></p>
			<p id="studio-success"></p>
		</main>
		<?php include __DIR__ . '/partials/footer.php'; ?>
	</body>
	<script type="module" src="/js/studio.js"></script>
</html>