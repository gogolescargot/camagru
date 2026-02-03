import { b64toBlob } from './b64toBlob.js';

const MAX_SIZE = 20 * 1024 * 1024;
const CANVA_SIZE = 720;
const STICKER_SIZE = 150;
const MAX_STICKERS = 10;
const studioError = document.getElementById('studio-error');
const studioSuccess = document.getElementById('studio-success');
const webcamCanvas = document.getElementById('webcam-canvas');
const canvasLoading = document.getElementById('canvas-loading');
const postButton = document.getElementById('post-button');
const resetStickerButton = document.getElementById('reset-sticker-button');
const fileInput = document.getElementById('image');
const removeImageButton = document.getElementById('remove-image-button');

let video = document.createElement('video');
video.setAttribute('autoplay', true);
video.setAttribute('playsinline', true);

let stickersOnCanvas = [];
let draggedStickerSrc = null;
let isUsingWebcam = true;
let webcamFirstFrameRendered = false;
const stickerCache = new Map();
const baseCanvas = document.createElement('canvas');

function showCanvasLoading() {
	if (!canvasLoading) return;
	canvasLoading.classList.add('show');
	canvasLoading.setAttribute('aria-hidden', 'false');
}

function hideCanvasLoading() {
	if (!canvasLoading) return;
	canvasLoading.classList.remove('show');
	canvasLoading.setAttribute('aria-hidden', 'true');
}

function updatePostButtonState() {
	if (!postButton) {
		return;
	}
	postButton.disabled = stickersOnCanvas.length === 0;
	postButton.title = stickersOnCanvas.length === 0 ? 'You need to add at least one sticker to post an image.' : '';
}

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
	webcamFirstFrameRendered = false;
	showCanvasLoading();
	navigator.mediaDevices
		.getUserMedia({
			video: {
				width: { ideal: CANVA_SIZE },
				height: { ideal: CANVA_SIZE },
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
			hideCanvasLoading();
			displayError('Unable to access the webcam.');
		});
}

function handlePostButtonClick() {
	if (stickersOnCanvas.length === 0) {
		displayError('You need to add at least one sticker to post an image.');
		return;
	}

	if (isUsingWebcam) {
		// force square crop capture
		baseCanvas.width = CANVA_SIZE;
		baseCanvas.height = CANVA_SIZE;
		const baseCtx = baseCanvas.getContext('2d');
		drawVideoToCanvas(video, baseCtx, baseCanvas.width, baseCanvas.height);
	}

	const imageData = baseCanvas.toDataURL('image/png');
	const blob = b64toBlob(imageData, 'image/png');

	const formData = new FormData();
	formData.append('image-post', blob, 'image.png');

	const titleInput = document.getElementById('title');
	if (titleInput) {
		formData.append('title', titleInput.value);
	}

	const getIdFromSrc = (src) => {
		try {
			const parts = src.split('/');
			const name = parts[parts.length - 1];
			return name.split('.').slice(0, -1).join('.');
		} catch (err) {
			return src;
		}
	};

	const stickersPayload = stickersOnCanvas.map((s) => ({
		id: getIdFromSrc(s.src),
		src: s.src,
		x: s.x,
		y: s.y,
		width: STICKER_SIZE,
		height: STICKER_SIZE,
	}));

	formData.append('stickers', JSON.stringify(stickersPayload));

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
			// displayError("An error occurred while creating the post.");
		});
}

function handleResetStickerButtonClick() {
	stickersOnCanvas = [];
	drawStickers();
	updatePostButtonState();
}

function handleFileInputChange(e) {
	const file = e.target.files[0];
	if (!file) {
		displayError('No file selected.');
		return;
	}

	if (!file.type || !file.type.startsWith('image/')) {
		displayError('Failed to load the selected image.');
		e.target.value = '';
		return;
	}

	if (file.size == 0) {
		displayError('Failed to load the selected image.');
		e.target.value = '';
		return;
	}

	const allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
	const fileExtension = file.name.split('.').pop().toLowerCase();
	if (!allowedExtensions.includes(fileExtension)) {
		displayError('Invalid file type. Only JPG, JPEG, PNG, and GIF are allowed.');
		e.target.value = '';
		return;
	}

	if (file.size > MAX_SIZE) {
		displayError('The file is too large (max 20 MB).');
		e.target.value = '';
	} else {
		studioError.style.display = 'none';
		isUsingWebcam = false;
		stickersOnCanvas = [];
		updatePostButtonState();
		showCanvasLoading();
		drawImage(file);
		removeImageButton.style.display = 'block';
	}
}

function handleStickerDragStart(e) {
	draggedStickerSrc = e.target.src;
	draggedStickerImg = e.target;
}

function handleCanvasDragOver(e) {
	e.preventDefault();
}

function handleCanvasDrop(e) {
	e.preventDefault();
	if (stickersOnCanvas.length >= MAX_STICKERS) {
		displayError(`You can only add up to ${MAX_STICKERS} stickers.`);
		return;
	}

	if (!draggedStickerSrc) return;

	const rect = webcamCanvas.getBoundingClientRect();
	const ratio = CANVA_SIZE / rect.width;

	const x = (e.clientX - rect.left) * ratio;
	const y = (e.clientY - rect.top) * ratio;

	stickersOnCanvas.push({ src: draggedStickerSrc, x, y });
	drawStickers();
	updatePostButtonState();
}

function handleRemoveImage() {
	isUsingWebcam = true;
	removeImageButton.style.display = 'none';
	fileInput.value = '';
	stickersOnCanvas = [];
	updatePostButtonState();
	initializeWebcam();
}

function drawVideoToCanvas(srcVideo, destCtx, destW, destH) {
	const vw = srcVideo.videoWidth || srcVideo.width || 0;
	const vh = srcVideo.videoHeight || srcVideo.height || 0;
	if (!vw || !vh) return false;
	const side = Math.min(vw, vh);
	const sx = Math.floor((vw - side) / 2);
	const sy = Math.floor((vh - side) / 2);
	destCtx.drawImage(srcVideo, sx, sy, side, side, 0, 0, destW, destH);
	return true;
}

function drawWebcam() {
	if (!isUsingWebcam) return;

	if (video.readyState === video.HAVE_ENOUGH_DATA) {
		// Keep a base canvas with the background-only (no stickers)
		baseCanvas.width = CANVA_SIZE;
		baseCanvas.height = CANVA_SIZE;
		const baseCtx = baseCanvas.getContext('2d');
		drawVideoToCanvas(video, baseCtx, CANVA_SIZE, CANVA_SIZE);

		webcamCanvas.width = CANVA_SIZE;
		webcamCanvas.height = CANVA_SIZE;
		const ctx = webcamCanvas.getContext('2d');
		drawVideoToCanvas(video, ctx, CANVA_SIZE, CANVA_SIZE);

		drawStickers();

		if (!webcamFirstFrameRendered) {
			webcamFirstFrameRendered = true;
			hideCanvasLoading();
		}
	}
	requestAnimationFrame(drawWebcam);
}

function drawImage(file) {
	const reader = new FileReader();
	reader.onload = (event) => {
		const img = new Image();
		img.src = event.target.result;
		img.onload = () => {
			webcamCanvas.width = CANVA_SIZE;
			webcamCanvas.height = CANVA_SIZE;

			const ctx = webcamCanvas.getContext('2d');
			ctx.clearRect(0, 0, webcamCanvas.width, webcamCanvas.height);

			const scale = Math.min(CANVA_SIZE / img.width, CANVA_SIZE / img.height);
			const width = img.width * scale;
			const height = img.height * scale;
			const x = (CANVA_SIZE - width) / 2;
			const y = (CANVA_SIZE - height) / 2;

			ctx.drawImage(img, x, y, width, height);

			// Also draw to baseCanvas so we have a background-only version
			baseCanvas.width = CANVA_SIZE;
			baseCanvas.height = CANVA_SIZE;
			const baseCtx = baseCanvas.getContext('2d');
			baseCtx.clearRect(0, 0, baseCanvas.width, baseCanvas.height);
			baseCtx.drawImage(img, x, y, width, height);

			drawStickers();
			hideCanvasLoading();
		};
	};
	reader.readAsDataURL(file);
}

function drawStickers() {
	const ctx = webcamCanvas.getContext('2d');
	stickersOnCanvas.forEach((sticker) => {
		let img = stickerCache.get(sticker.src);
		if (!img) {
			img = new Image();
			img.src = sticker.src;
			stickerCache.set(sticker.src, img);
		}
		const draw = () => {
			try {
				ctx.drawImage(img, sticker.x - (STICKER_SIZE / 2), sticker.y - (STICKER_SIZE / 2), STICKER_SIZE, STICKER_SIZE);
			} catch (err) { }
		};
		if (img.complete) {
			draw();
		} else {
			img.addEventListener('load', draw, { once: true });
		}
	});
}

if (removeImageButton) {
	removeImageButton.addEventListener('click', handleRemoveImage);
}

if (postButton) {
	postButton.addEventListener('click', handlePostButtonClick);
}

if (resetStickerButton) {
	resetStickerButton.addEventListener('click', handleResetStickerButtonClick);
}

if (fileInput) {
	fileInput.addEventListener('change', handleFileInputChange);
}

function preloadStickers() {
	document.querySelectorAll('.sticker').forEach((el) => {
		const img = new Image();
		img.src = el.src;
		stickerCache.set(el.src, img);
		el.addEventListener('dragstart', handleStickerDragStart);
	});
}

webcamCanvas.addEventListener('dragover', handleCanvasDragOver);
webcamCanvas.addEventListener('drop', handleCanvasDrop);

preloadStickers();
initializeWebcam();
updatePostButtonState();