<?php

namespace Controllers;

use Core\Database;
use Core\ErrorHandler;
use Models\ImageModel;

class WebcamController
{
	public function uploadWebcam()
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

			if (!isset($_FILES['webcam_image']) || $_FILES['webcam_image']['error'] !== UPLOAD_ERR_OK) {
				ErrorHandler::handleJsonResponse(
					$this->getUploadErrorMessage($_FILES['webcam_image']['error'] ?? null),
					True
				);
			}

			$fileTmpPath = $_FILES['webcam_image']['tmp_name'];
			$fileName = $_FILES['webcam_image']['name'];
			$fileSize = $_FILES['webcam_image']['size'];
			$fileType = $_FILES['webcam_image']['type'];
			$fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

			$allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
			if (!in_array($fileExtension, $allowedExtensions)) {
				ErrorHandler::handleJsonResponse(
					'Invalid file type. Only JPG, JPEG, PNG, and GIF are allowed.',
					False
				);
			}

			$maxFileSize = 20 * 1024 * 1024;
			if ($fileSize > $maxFileSize) {
				ErrorHandler::handleJsonResponse(
					'File size exceeds the maximum limit of 20MB.',
					False
				);
			}

			$uploadDir = '/var/www/html/uploads/';
			$uploadName = uniqid() . '.' . $fileExtension;
			$destPath = $uploadDir . $uploadName;

			if (!is_dir($uploadDir) || !is_writable($uploadDir)) {
				ErrorHandler::handleJsonResponse(
					"Upload directory does not exist or is not writable: $uploadDir.",
					True
				);
			}

			if (!move_uploaded_file($fileTmpPath, $destPath)) {
				ErrorHandler::handleJsonResponse(
					'There was an error moving the uploaded file.',
					True
				);
			}

			$pdo = Database::getConnection(); 
			$imageModel = new ImageModel($pdo);

			$pdo->beginTransaction();
			$imageModel->createImage($_SESSION["user_id"], $uploadName);
			$pdo->commit();

			header('Content-Type: application/json');
			echo json_encode([
				'success' => true,
				'message' => 'Image uploaded successfully!',
				'filename' => $uploadName
			]);
			exit();
		}
		catch (\Exception $e) {
			ErrorHandler::handleException($e);
		}
	}
}