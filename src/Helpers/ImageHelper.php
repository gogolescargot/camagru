<?php

namespace Helpers;

use Core\ErrorHandler;

class ImageHelper
{
	public static function getUploadErrorMessage($errorCode)
	{
		$errors = [
			UPLOAD_ERR_INI_SIZE => 'The uploaded file exceeds the upload_max_filesize directive in php.ini.',
			UPLOAD_ERR_FORM_SIZE => 'The uploaded file exceeds the MAX_FILE_SIZE directive specified in the HTML form.',
			UPLOAD_ERR_PARTIAL => 'The uploaded file was only partially uploaded.',
			UPLOAD_ERR_NO_FILE => 'No file was uploaded.',
			UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder.',
			UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
			UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload.',
		];

		return $errors[$errorCode] ?? 'Unknown upload error.';
	}

	public static function applyStickersToImage(string $destPath, array $stickers, int $STICKER_SIZE): void
	{
		$projectRoot = realpath(__DIR__ . '/../../');
		$stickersDir = $projectRoot . '/html/public/stickers/';

		if (!file_exists($destPath) || !is_readable($destPath)) {
			self::cleanupAndError('Base image not found or unreadable.', $destPath);
		}

		$baseContents = @file_get_contents($destPath);
		$baseImg = @imagecreatefromstring($baseContents);
		if ($baseImg === false) {
			self::cleanupAndError('Failed to load base image.', $destPath);
		}

		// Configure alpha handling on the base image
		imagesavealpha($baseImg, true);
		imagealphablending($baseImg, true);

		// For each sticker, validate, load, resize into a transparent buffer, then copy onto base.
		foreach ($stickers as $st) {
			$filename = basename($st['src'] ?? '');
			if ($filename === '') {
				self::cleanupAndError('Invalid sticker data.', $destPath, [$baseImg]);
			}

			$stickerPath = $stickersDir . $filename;
			if (!file_exists($stickerPath) || !is_readable($stickerPath)) {
				self::cleanupAndError("Sticker not found: $filename", $destPath, [$baseImg]);
			}

			$stContents = @file_get_contents($stickerPath);
			$stImg = @imagecreatefromstring($stContents);
			if ($stImg === false) {
				self::cleanupAndError("Failed to load sticker: $filename", $destPath, [$baseImg]);
			}

			$stWidth = max(1, isset($st['width']) ? (int)$st['width'] : $STICKER_SIZE);
			$stHeight = max(1, isset($st['height']) ? (int)$st['height'] : $STICKER_SIZE);

			// Create a transparent truecolor buffer for the resized sticker
			$tmp = imagecreatetruecolor($stWidth, $stHeight);
			imagesavealpha($tmp, true);
			$trans = imagecolorallocatealpha($tmp, 0, 0, 0, 127);
			imagefill($tmp, 0, 0, $trans);

			// Resample (resize) the sticker into the transparent buffer
			$resampled = @imagecopyresampled(
				$tmp,
				$stImg,
				0,
				0,
				0,
				0,
				$stWidth,
				$stHeight,
				@imagesx($stImg) ?: 0,
				@imagesy($stImg) ?: 0
			);
			if ($resampled === false) {
				@imagedestroy($tmp);
				@imagedestroy($stImg);
				self::cleanupAndError("Failed to resize sticker: $filename", $destPath, [$baseImg]);
			}

			// Compute destination coordinates (center sticker on provided x,y)
			$destX = isset($st['x']) ? (int)round($st['x'] - ($stWidth / 2)) : 0;
			$destY = isset($st['y']) ? (int)round($st['y'] - ($stHeight / 2)) : 0;

			// Copy the sticker buffer onto the base image
			$copied = @imagecopy($baseImg, $tmp, $destX, $destY, 0, 0, $stWidth, $stHeight);
			if ($copied === false) {
				@imagedestroy($tmp);
				@imagedestroy($stImg);
				self::cleanupAndError("Failed to merge sticker onto base image: $filename", $destPath, [$baseImg]);
			}

			@imagedestroy($tmp);
			@imagedestroy($stImg);
		}

		$imageInfo = @getimagesize($destPath);
		$mime = $imageInfo['mime'] ?? 'image/png';
		$saved = false;
		switch ($mime) {
			case 'image/png':
				$saved = @imagepng($baseImg, $destPath);
				break;
			case 'image/gif':
				$saved = @imagegif($baseImg, $destPath);
				break;
			default:
				$saved = @imagejpeg($baseImg, $destPath, 90);
				break;
		}

		@imagedestroy($baseImg);

		if ($saved === false) {
			self::cleanupAndError('Failed to save final image.', $destPath);
		}
	}

	private static function cleanupAndError(string $msg, string $destPath, array $resources = []): void
	{
		foreach ($resources as $res) {
			if (is_resource($res) || (is_object($res) && ($res instanceof \GdImage))) {
				@imagedestroy($res);
			}
		}
		if (isset($destPath) && file_exists($destPath)) {
			@unlink($destPath);
		}
		ErrorHandler::handleJsonResponse($msg, true);
	}
}