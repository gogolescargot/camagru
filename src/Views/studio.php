<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>Studio</title>
		<link rel="stylesheet" href="/css/style.css">
	</head>
	<body>
		<?php include __DIR__ . '/partials/navbar.php'; ?>
		<main>
			<h1>Studio</h1>
			<div id="studio-container">
				<div id="edit-container">
					<div id="preview-container">
						<canvas id="webcam-canvas"></canvas>
						<div id="image-container">
							<label for="image">Image:</label>
							<input type="file" id="image" name="image" accept="image/*">
							<button id="remove-image-button" style="display: none;">✖</button>
						</div>
						<input type="text" name="title" id="title">
						<button id="post-button">Post</button>
						</form>
					</div>
					<div id="stickers-container">
						<img src="/stickers/sticker_1.png" class="sticker" draggable="true"/>
						<img src="/stickers/sticker_2.png" class="sticker" draggable="true"/>
						<img src="/stickers/sticker_3.png" class="sticker" draggable="true"/>
						<img src="/stickers/sticker_4.png" class="sticker" draggable="true"/>
					</div>
				</div>
				<p id="studio-error"></p>
				<p id="studio-success"></p>
			</div>
		</main>
	</body>
	<script type="module" src="/js/studio.js"></script>
</html>