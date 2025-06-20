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
				<?php include 'partials/gallery.php'; ?>
			</div>
			<button id="switch-studio">Canvas</button>
			<div id="studio-container">
				<div id="webcam-container">
					<video id="webcam" autoplay playsinline></video>
					<canvas id="webcam-canvas"></canvas>
					<button id="webcam-button">Snap</button>
					<p id="webcam-error"></p>
					<p id="webcam-success"></p>
				</div>
				<div id="canvas-container" style="display: none;">
					<canvas id="canvas-frame" width="500" height="500"></canvas>
					<button id="canvas-save">Save</button>
					<p id="canvas-error"></p>
					<p id="canvas-success"></p>
					<!-- Canvas Edit -->
					<!-- Upload Post with Title -->
				</div>
			</div>
		</main>
	</body>
	<script>
		const switchStudio = document.getElementById('switch-studio');
		const webcamContainer = document.getElementById('webcam-container');
		const canvasContainer = document.getElementById('canvas-container');
		const webcamButton = document.getElementById('webcam-button');
		const webcamError = document.getElementById('webcam-error');
		const webcamSuccess = document.getElementById('webcam-success');

		let mode = 'webcam';

		function updateView() {
			const isWebcam = mode === 'webcam';
			switchStudio.textContent = isWebcam ? 'Canvas' : 'Webcam';
			if (webcamContainer) webcamContainer.style.display = isWebcam ? '' : 'none';
			if (canvasContainer) canvasContainer.style.display = isWebcam ? 'none' : '';
		}

		function b64toBlob(b64Data, contentType='', sliceSize=512) {
			const byteCharacters = atob(b64Data);
			const byteArrays = [];

			for (let offset = 0; offset < byteCharacters.length; offset += sliceSize) {
				const slice = byteCharacters.slice(offset, offset + sliceSize);

				const byteNumbers = new Array(slice.length);
				for (let i = 0; i < slice.length; i++) {
					byteNumbers[i] = slice.charCodeAt(i);
				}

				const byteArray = new Uint8Array(byteNumbers);
				byteArrays.push(byteArray);
			}
				
			const blob = new Blob(byteArrays, {type: contentType});
			return blob;
		}

		function refreshGallery() {
			fetch('/gallery', {
				headers: {
					'X-Requested-With': 'XMLHttpRequest'
				}
			})
			.then(response => response.text())
			.then(html => {
				document.getElementById('galleries-container').innerHTML = html;
			});
		}

		switchStudio.addEventListener('click', () => {
			mode = mode === 'webcam' ? 'canvas' : 'webcam';
			updateView();
		});

		const video = document.getElementById('webcam');
		navigator.mediaDevices.getUserMedia({ video: true })
		.then(stream => {
			video.srcObject = stream;
			if (webcamButton) {
				webcamButton.style.display = 'block';
			}
		})
		.catch(err => {
			if (webcamError) {
				webcamError.style.display = 'block';
				webcamError.textContent = "Unable to access the webcam.";
			}
		});

		const webcamCanvas = document.getElementById('webcam-canvas');

		webcamButton.addEventListener('click', () => {
			webcamCanvas.width = video.videoWidth;
			webcamCanvas.height = video.videoHeight;
			webcamCanvas.getContext('2d').drawImage(video, 0, 0, webcamCanvas.width, webcamCanvas.height);

			const imageData = webcamCanvas.toDataURL('image/png');

			const blob = b64toBlob(imageData.split(',')[1], 'image/png');

			const formData = new FormData();
			formData.append('webcam_image', blob, 'webcam.png');

			fetch('/upload-webcam', {
				method: 'POST',
				body: formData
			})
			.then(response => response.json())
			.then(data => {
				if (data.success) {
					if (webcamSuccess) {
						webcamSuccess.style.display = 'block';
						webcamSuccess.textContent = data.message;
						refreshGallery();
					}
				}
				else {
					if (webcamError) {
						webcamError.style.display = 'block';
						webcamError.textContent = data.message;
					}
				}
			})
			.catch(error => {
				alert('Network or Server error');
			});
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

		document.addEventListener('DOMContentLoaded', () => {
			const canvas = document.getElementById('canvas-frame');
			const ctx = canvas.getContext('2d');

			document.body.addEventListener('dragstart', function(e) {
				if (e.target.classList.contains('gallery-img')) {
					e.dataTransfer.setData('text/plain', e.target.getAttribute('data-src'));
				}
			});

			canvas.addEventListener('dragover', function(e) {
				e.preventDefault();
			});

			canvas.addEventListener('drop', function(e) {
				e.preventDefault();
				const imgSrc = e.dataTransfer.getData('text/plain');
				if (imgSrc) {
					const img = new window.Image();
					img.crossOrigin = "anonymous";
					img.onload = function() {
						ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
					};
					img.src = imgSrc;
				}
			});

			document.getElementById('canvas-save').addEventListener('click', function() {
				// Upload to DB
			});
		});
	</script>
</html>