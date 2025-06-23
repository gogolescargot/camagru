import { b64toBlob } from './b64toBlob.js';

const MAX_SIZE = 20 * 1024 * 1024;
const studioError = document.getElementById('studio-error');
const studioSuccess = document.getElementById('studio-success');
const webcamCanvas = document.getElementById('webcam-canvas');
const postButton = document.getElementById('post-button');
const fileInput = document.getElementById('image');
const galleriesContainer = document.getElementById('galleries-container');
const removeImageButton = document.getElementById('remove-image-button');

let video = document.createElement('video');
video.setAttribute('autoplay', true);
video.setAttribute('playsinline', true);

let stickersOnCanvas = [];
let draggedStickerSrc = null;
let isUsingWebcam = true;

function displayError(message) {
	if (studioError) {
		studioError.style.display = 'block';
		studioError.textContent = message;
	}
}

function displaySuccess(message) {
	if (studioSuccess) {
		studioSuccess.style.display = 'block';
		studioSuccess.textContent = message;
	}
}

function initializeWebcam() {
	navigator.mediaDevices
		.getUserMedia({
			video: {
				width: { ideal: 720 },
				height: { ideal: 720 },
				aspectRatio: 1,
			},
		})
		.then((stream) => {
			video.srcObject = stream;
			if (postButton) postButton.style.display = 'block';
			isUsingWebcam = true;
			drawWebcam();
		})
		.catch(() => {
			displayError('Unable to access the webcam.');
		});
}

function handlePostButtonClick() {
	if (stickersOnCanvas.length === 0) {
		displayError('You need to add at least one sticker to snap an image.');
		return;
	}

	if (isUsingWebcam) {
		webcamCanvas.width = video.videoWidth;
		webcamCanvas.height = video.videoHeight;
		const ctx = webcamCanvas.getContext('2d');
		ctx.drawImage(video, 0, 0, webcamCanvas.width, webcamCanvas.height);
	}

	drawStickers();

	const imageData = webcamCanvas.toDataURL('image/png');
	const blob = b64toBlob(imageData, 'image/png');

	const formData = new FormData();
	formData.append('image-post', blob, 'image.png');

	const titleInput = document.getElementById('title');
	if (titleInput) {
		formData.append('title', titleInput.value);
	}

	fetch('/create-post', {
		method: 'POST',
		body: formData,
	})
		.then((response) => response.json())
		.then((data) => {
			if (data.success) {
				displaySuccess('Post created successfully!');
				window.location.href = data.redirect;
			} else {
				displayError(data.message || 'An error occurred.');
			}
		})
		.catch((error) => {
			displayError('Failed to upload the post.');
		});
}

function handleFileInputChange(e) {
	const file = e.target.files[0];
	if (!file) {
		displayError('No file selected.');
		return;
	}

	if (file.size > MAX_SIZE) {
		displayError('The file is too large (max 20 MB).');
		e.target.value = '';
	} else {
		studioError.style.display = 'none';
		isUsingWebcam = false;
		stickersOnCanvas = [];
		drawImage(file);
		removeImageButton.style.display = 'block';
	}
}

function handleStickerDragStart(e) {
	draggedStickerSrc = e.target.src;
}

function handleCanvasDragOver(e) {
	e.preventDefault();
}

function handleCanvasDrop(e) {
	e.preventDefault();
	if (!draggedStickerSrc) return;

	const rect = webcamCanvas.getBoundingClientRect();
	const x = e.clientX - rect.left;
	const y = e.clientY - rect.top;

	stickersOnCanvas.push({ src: draggedStickerSrc, x, y });
	drawStickers();
}

function handleRemoveImage() {
	isUsingWebcam = true;
	removeImageButton.style.display = 'none';
	fileInput.value = '';
	stickersOnCanvas = [];
	initializeWebcam();
}

function drawWebcam() {
	if (!isUsingWebcam) return;

	if (video.readyState === video.HAVE_ENOUGH_DATA) {
		webcamCanvas.width = 720;
		webcamCanvas.height = 720;
		const ctx = webcamCanvas.getContext('2d');
		ctx.drawImage(video, 0, 0, 720, 720);

		drawStickers();
	}
	requestAnimationFrame(drawWebcam);
}

function drawImage(file) {
	const reader = new FileReader();
	reader.onload = (event) => {
		const img = new Image();
		img.src = event.target.result;
		img.onload = () => {
			webcamCanvas.width = 720;
			webcamCanvas.height = 720;

			const ctx = webcamCanvas.getContext('2d');
			ctx.clearRect(0, 0, webcamCanvas.width, webcamCanvas.height);

			const scale = Math.min(720 / img.width, 720 / img.height);
			const width = img.width * scale;
			const height = img.height * scale;
			const x = (720 - width) / 2;
			const y = (720 - height) / 2;

			ctx.drawImage(img, x, y, width, height);

			drawStickers();
		};
	};
	reader.readAsDataURL(file);
}

function drawStickers() {
	const ctx = webcamCanvas.getContext('2d');
	stickersOnCanvas.forEach((sticker) => {
		const img = new Image();
		img.src = sticker.src;
		img.onload = () => {
			ctx.drawImage(img, sticker.x - 75, sticker.y - 75, 150, 150);
		};
		if (img.complete) {
			ctx.drawImage(img, sticker.x - 75, sticker.y - 75, 150, 150);
		}
	});
}

if (removeImageButton) {
	removeImageButton.addEventListener('click', handleRemoveImage);
}

if (postButton) {
	postButton.addEventListener('click', handlePostButtonClick);
}

if (fileInput) {
	fileInput.addEventListener('change', handleFileInputChange);
}

document.querySelectorAll('.sticker').forEach((img) => {
	img.addEventListener('dragstart', handleStickerDragStart);
});

webcamCanvas.addEventListener('dragover', handleCanvasDragOver);
webcamCanvas.addEventListener('drop', handleCanvasDrop);

initializeWebcam();