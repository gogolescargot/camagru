<?php

namespace Controllers;

use Core\Database;
use Core\ErrorHandler;
use Models\ImageModel;

class UploadImageController
{
	public function uploadImage()
	{
		try {
			if (!isset($_SESSION['user_id'])) {
				ErrorHandler::ErrorHandler::handleError(
					'You must be logged in to perform this action.',
					'/home',
					403,
					False
				);
			}

			if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
				ErrorHandler::handleError(
					$this->getUploadErrorMessage($_FILES['image']['error'] ?? null),
					'/studio',
					500,
					True
				);
			}

			$fileTmpPath = $_FILES['image']['tmp_name'];
			$fileName = $_FILES['image']['name'];
			$fileSize = $_FILES['image']['size'];
			$fileType = $_FILES['image']['type'];
			$fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

			$allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
			if (!in_array($fileExtension, $allowedExtensions)) {
				ErrorHandler::handleError(
					'Invalid file type. Only JPG, JPEG, PNG, and GIF are allowed.',
					'/studio',
					500,
					False
				);
			}

			$maxFileSize = 20 * 1024 * 1024;
			if ($fileSize > $maxFileSize) {
				ErrorHandler::handleError(
					'File size exceeds the maximum limit of 20MB.',
					'/studio',
					413,
					False
				);
			}

			$uploadDir = '/var/www/html/uploads/';
			$uploadName = uniqid() . '.' . $fileExtension;
			$destPath = $uploadDir . $uploadName;

			if (!is_dir($uploadDir) || !is_writable($uploadDir)) {
				ErrorHandler::handleError(
					"Upload directory does not exist or is not writable: $uploadDir.",
					'/studio',
					500,
					True
				);
			}

			if (!move_uploaded_file($fileTmpPath, $destPath)) {
				ErrorHandler::handleError(
					'There was an error moving the uploaded file.',
					'/studio',
					500,
					True
				);
			}

			$pdo = Database::getConnection(); 
			$imageModel = new ImageModel($pdo);

			$pdo->beginTransaction();
			$imageModel->createImage($_SESSION["user_id"], $uploadName);
			$pdo->commit();

			$_SESSION['success'] = "Image uploaded successfully!";
			header('Location: /studio');
			exit();
		}
		catch (\Exception $e) {
			ErrorHandler::handleException($e);
		}
	}

	private function getUploadErrorMessage($errorCode)
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
}