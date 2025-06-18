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
			<div id="upload-container">
				<form action="/upload" method="POST" enctype="multipart/form-data">
					<label for="image">Image:</label>
					<input type="file" id="image" name="image" accept="image/*" required>
					<p id="input-error"><p>

					<button type="submit">Upload</button>
				</form>
			</div>
			<div id="galleries-container">
				<?php if (empty($images)): ?>
					<p>No images yet.</p>
				<?php else: ?>
					<?php foreach ($images as $image): ?>
						<div class="gallery">
							<img class="image" src="/uploads/<?= htmlspecialchars($image['path']) ?>">

							<?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $image['user_id']):?>
								<form method="post" action="/delete-image?image_id=<?= htmlspecialchars($image['id'])?>">
									<button type="submit" class="delete-button">Delete Image</button>
								</form>
							<?php endif; ?>
						</div>
					<?php endforeach; ?>
				<?php endif; ?>
			</div>
			<!-- Drag and Drop -->
			<button id="switch-studio">Canva</button>
			<div id="studio-container">
				<div id="webcam-container">
					<video id="webcam" autoplay playsinline></video>
					<button id="webcam-button">Snap</button>
					<p id="webcam-error"></p>
					
				</div>
				<div id="canva-container">
					<!-- Canva Edit -->
				</div>
				<!-- Upload Post with Title -->
			</div>
		<main>
	</body>
	<script>
		const switchStudio = document.getElementById('switch-studio');
		const webcamContainer = document.getElementById('webcam-container');
		const canvaContainer = document.getElementById('canva-container');
		let mode = 'webcam';

		function updateView() {
			const isWebcam = mode === 'webcam';
			switchStudio.textContent = isWebcam ? 'Canva' : 'Webcam';
			if (webcamContainer) webcamContainer.style.display = isWebcam ? 'block' : 'none';
			if (canvaContainer) canvaContainer.style.display = isWebcam ? 'none' : 'block';
		}

		switchStudio.addEventListener('click', () => {
			mode = mode === 'webcam' ? 'canva' : 'webcam';
			updateView();
		});

		const video = document.getElementById('webcam');
		navigator.mediaDevices.getUserMedia({ video: true })
		.then(stream => {
			video.srcObject = stream;
			const webcamButton = document.getElementById('webcam-button');
			if (webcamButton) {
				webcamButton.style.display = 'block';
			}
		})
		.catch(err => {
			const webcamError = document.getElementById('webcam-error');
			if (webcamError) {
				webcamError.style.display = 'block';
				webcamError.textContent = "Unable to access the webcam.";
			}
		});

		const MAX_SIZE = 20 * 1024 * 1024;
		const fileInput = document.getElementById('image');

		fileInput.addEventListener('change', (e) => {
			const file = e.target.files[0];
			if (file && file.size > MAX_SIZE) {
				const inputError = document.getElementById('input-error');
				inputError.style.display = 'block';
				inputError.textContent = "The file is too large (max 20 MB).";
				e.target.value = '';
			}
		});
	</script>
</html>