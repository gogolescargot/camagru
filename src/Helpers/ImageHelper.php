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

        foreach ($stickers as $st) {
            $filename = basename($st['src'] ?? '');
            if ($filename === '') continue;

            $stickerPath = $stickersDir . $filename;
            if (!file_exists($stickerPath)) {
                if (isset($destPath) && file_exists($destPath)) @unlink($destPath);
                ErrorHandler::handleJsonResponse("Sticker not found: $filename", True);
            }

            $stContents = @file_get_contents($stickerPath);
            $stImgCheck = @imagecreatefromstring($stContents);
            if ($stImgCheck === false) {
                if (isset($destPath) && file_exists($destPath)) @unlink($destPath);
                ErrorHandler::handleJsonResponse("Failed to load sticker: $filename", True);
            }
            imagedestroy($stImgCheck);
        }

        $baseContents = @file_get_contents($destPath);
        $baseImg = @imagecreatefromstring($baseContents);
        if ($baseImg === false) {
            return;
        }

        imagesavealpha($baseImg, true);
        imagealphablending($baseImg, true);

        foreach ($stickers as $st) {
            $filename = basename($st['src'] ?? '');
            if ($filename === '') {
                if (isset($destPath) && file_exists($destPath)) @unlink($destPath);
                imagedestroy($baseImg);
                ErrorHandler::handleJsonResponse("Invalid sticker data.", True);
            }

            $stickerPath = $stickersDir . $filename;
            $stContents = @file_get_contents($stickerPath);
            $stImg = @imagecreatefromstring($stContents);
            if ($stImg === false) {
                if (isset($destPath) && file_exists($destPath)) @unlink($destPath);
                imagedestroy($baseImg);
                ErrorHandler::handleJsonResponse("Failed to load sticker: $filename", True);
            }

            $stWidth = isset($st['width']) ? (int)$st['width'] : $STICKER_SIZE;
            $stHeight = isset($st['height']) ? (int)$st['height'] : $STICKER_SIZE;

            $tmp = imagecreatetruecolor($stWidth, $stHeight);
            imagesavealpha($tmp, true);
            $trans = imagecolorallocatealpha($tmp, 0, 0, 0, 127);
            imagefill($tmp, 0, 0, $trans);

            $resampled = imagecopyresampled(
                $tmp,
                $stImg,
                0,
                0,
                0,
                0,
                $stWidth,
                $stHeight,
                imagesx($stImg),
                imagesy($stImg)
            );
            if ($resampled === false) {
                if (isset($destPath) && file_exists($destPath)) @unlink($destPath);
                imagedestroy($tmp);
                imagedestroy($stImg);
                imagedestroy($baseImg);
                ErrorHandler::handleJsonResponse("Failed to resize sticker: $filename", True);
            }

            $destX = isset($st['x']) ? (int)round($st['x'] - ($stWidth / 2)) : 0;
            $destY = isset($st['y']) ? (int)round($st['y'] - ($stHeight / 2)) : 0;

            $copied = imagecopy($baseImg, $tmp, $destX, $destY, 0, 0, $stWidth, $stHeight);
            if ($copied === false) {
                if (isset($destPath) && file_exists($destPath)) @unlink($destPath);
                imagedestroy($tmp);
                imagedestroy($stImg);
                imagedestroy($baseImg);
                ErrorHandler::handleJsonResponse("Failed to merge sticker onto base image: $filename", True);
            }

            imagedestroy($tmp);
            imagedestroy($stImg);
        }

        $imageInfo = @getimagesize($destPath);
        $mime = $imageInfo['mime'] ?? 'image/png';
        switch ($mime) {
            case 'image/png':
                imagepng($baseImg, $destPath);
                break;
            case 'image/gif':
                imagegif($baseImg, $destPath);
                break;
            default:
                imagejpeg($baseImg, $destPath, 90);
                break;
        }

        imagedestroy($baseImg);
    }
}