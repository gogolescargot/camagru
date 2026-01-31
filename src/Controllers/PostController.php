<?php

namespace Controllers;

use Core\Database;
use Core\ErrorHandler;
use Helpers\ImageHelper;
use Models\PostModel;
use Models\UserModel;

class PostController
{
	public function likePost()
	{
		try {
			if (!isset($_SESSION['user_id'])) {
				ErrorHandler::handleError(
					'You must be logged in to perform this action.',
					'/home',
					500,
					False
				);
			}

			$postId = isset($_GET['post_id']) ? trim($_GET['post_id']) : '';

			$pdo = Database::getConnection();
			$postModel = new PostModel($pdo);

			if (!$postModel->findPost($postId)) {
				ErrorHandler::handleError(
					'Invalid or deleted post.',
					'/home',
					400,
					False
				);
			}

			$pdo->beginTransaction();
			if (!$postModel->findLikePost($postId, $_SESSION['user_id'])) {
				$postModel->likePost($postId, $_SESSION['user_id']);
			}
			else {
				$postModel->unlikePost($postId, $_SESSION['user_id']);
			}
			$pdo->commit();
			
			header('Location: /home');
			exit();
		}
		catch (\Exception $e) {
			ErrorHandler::rollbackTransaction($pdo);
			ErrorHandler::handleException($e);
		}
	}

	public function createCommentPost()
	{
		try {
			if (!isset($_SESSION['user_id'])) {
				ErrorHandler::handleError(
					'You must be logged in to perform this action.',
					'/home',
					500,
					False
				);
			}

			$postId = isset($_GET['post_id']) ? trim($_GET['post_id']) : '';
			$content = isset($_POST['content']) ? trim($_POST['content']) : '';

			if (empty($postId)) {
				ErrorHandler::handleError(
					'All fields are required.',
					'/home',
					400,
					False
				);
			}

			if (empty($content)) {
				ErrorHandler::handleError(
					'Empty comment.',
					'/home',
					400,
					False
				);
			}

			if (strlen($content) > 500) {
				ErrorHandler::handleError(
					'Comment must not exceed 500 characters.',
					'/home',
					400,
					False
				);
			}

			$pdo = Database::getConnection();
			$postModel = new PostModel($pdo);
			$userModel = new UserModel($pdo);

			$post = $postModel->findPost($postId);

			if (!$post) {
				ErrorHandler::handleError(
					'Invalid or deleted post.',
					'/home',
					400,
					False
				);
			}

			$receiver = $userModel->findById($post['user_id']);
			$sender = $userModel->findById($_SESSION['user_id']);

			$email = $receiver['email'];
			$subject = 'New comment on your post';
			$message = $sender['username'] . " has commented on one of your post. Check it out on Camagru!";

			$pdo->beginTransaction();
			$postModel->createCommentPost($postId, $_SESSION['user_id'], $content);

			if ($receiver['email_notifications'] && !mail($email, $subject, $message)) {
				throw new Exception('Failed to send email.');
			}

			$pdo->commit();
			
			header('Location: /home');
			exit();
		}
		catch (\Exception $e) {
			ErrorHandler::rollbackTransaction($pdo);
			ErrorHandler::handleException($e);
		}
	}

	public function deleteCommentPost()
	{
		try {
			if (!isset($_SESSION['user_id'])) {
				ErrorHandler::handleError(
					'You must be logged in to perform this action.',
					'/home',
					500,
					False
				);
			}

			$commentId = isset($_GET['comment_id']) ? trim($_GET['comment_id']) : '';

			if (empty($commentId)) {
				ErrorHandler::handleError(
					'All fields are required.',
					'/home',
					400,
					False
				);
			}

			$pdo = Database::getConnection();
			$postModel = new PostModel($pdo);

			$comment = $postModel->findCommentPost($commentId);

			if (!$comment) {
				ErrorHandler::handleError(
					'Invalid or deleted comment.',
					'/home',
					400,
					False
				);
			}

			if ($_SESSION['user_id'] !== $comment['user_id']) {
				ErrorHandler::handleError(
					'You are not allowed to perform this action',
					'/home',
					403,
					False
				);
			}

			$pdo->beginTransaction();
			$postModel->deleteCommentPost($commentId);
			$pdo->commit();
			
			$_SESSION['success'] = "Comment deleted successfully!";
			header('Location: /home');
			exit();
		}
		catch (\Exception $e) {
			ErrorHandler::rollbackTransaction($pdo);
			ErrorHandler::handleException($e);
		}
	}

	public function createPost()
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

			$title = isset($_POST['title']) ? trim($_POST['title']) : '';

			if (strlen($title) > 255) {
				ErrorHandler::handleJsonResponse('Title must not exceed 255 characters.', False);
			}

			if (!isset($_FILES['image-post']) || !is_array($_FILES['image-post'])) {
				ErrorHandler::handleJsonResponse('No file uploaded.', True);
			}

			$file = $_FILES['image-post'];
			$error = $file['error'] ?? UPLOAD_ERR_NO_FILE;

			if ($error !== UPLOAD_ERR_OK) {
				ErrorHandler::handleJsonResponse(
					ImageHelper::getUploadErrorMessage($error),
					True
				);
			}

			$fileTmpPath = $file['tmp_name'] ?? '';
			if ($fileTmpPath === '' || !is_uploaded_file($fileTmpPath)) {
				ErrorHandler::handleJsonResponse('Invalid upload.', True);
			}

			$fileSize = (int)($file['size'] ?? 0);
			$maxFileSize = 20 * 1024 * 1024;
			if ($fileSize <= 0 || $fileSize > $maxFileSize) {
				ErrorHandler::handleJsonResponse('File size exceeds the maximum limit of 20MB.', False);
			}

			$finfo = new \finfo(FILEINFO_MIME_TYPE);
			$mimeType = $finfo->file($fileTmpPath);

			$allowedMimes = [
				'image/jpeg' => 'jpg',
				'image/png'  => 'png',
				'image/gif'  => 'gif',
			];

			if (!isset($allowedMimes[$mimeType])) {
				ErrorHandler::handleJsonResponse(
					'Invalid file type. Only JPG, PNG, and GIF are allowed.',
					False
				);
			}

			$imageInfo = @getimagesize($fileTmpPath);
			if ($imageInfo === false) {
				ErrorHandler::handleJsonResponse('Invalid image file.', False);
			}

			$width = (int)($imageInfo[0] ?? 0);
			$height = (int)($imageInfo[1] ?? 0);
			$maxPixels = 25_000_000;

			if ($width <= 0 || $height <= 0 || ($width * $height) > $maxPixels) {
				ErrorHandler::handleJsonResponse('Image dimensions are too large.', False);
			}

			$uploadDir = '/var/www/uploads';
			if (!is_dir($uploadDir) || !is_writable($uploadDir)) {
				ErrorHandler::handleJsonResponse(
					"Upload directory does not exist or is not writable: $uploadDir.",
					True
				);
			}

			$uploadDir = rtrim($uploadDir, '/') . '/';
			$fileExtension = $allowedMimes[$mimeType];

			do {
				$uploadName = bin2hex(random_bytes(16)) . '.' . $fileExtension;
				$destPath = $uploadDir . $uploadName;
			} while (file_exists($destPath));

			if (!move_uploaded_file($fileTmpPath, $destPath)) {
				ErrorHandler::handleJsonResponse(
					'There was an error moving the uploaded file.',
					True
				);
			}

			$pdo = Database::getConnection();
			$postModel = new PostModel($pdo);

			$pdo->beginTransaction();
			$postModel->createPost($_SESSION['user_id'], $title, $uploadName);
			$pdo->commit();

			$_SESSION['success'] = 'Post created successfully!';
			header('Content-Type: application/json');
			echo json_encode([
				'success' => true,
				'message' => 'Image uploaded successfully!',
				'redirect' => '/home',
			]);
			exit();
		}
		catch (\Exception $e) {
			ErrorHandler::rollbackTransaction($pdo);
			ErrorHandler::handleException($e);
		}
	}

	public function deletePost()
	{
		try {
			if (!isset($_SESSION['user_id'])) {
				ErrorHandler::handleError(
					'You must be logged in to perform this action.',
					'/home',
					500,
					False
				);
			}

			$postId = isset($_GET['post_id']) ? trim($_GET['post_id']) : '';

			if (empty($postId)) {
				ErrorHandler::handleError(
					'All fields are required.',
					'/home',
					400,
					False
				);
			}

			$pdo = Database::getConnection();
			$postModel = new PostModel($pdo);

			$post = $postModel->findPost($postId);

			if (!$post) {
				ErrorHandler::handleError(
					'Invalid or deleted post.',
					'/home',
					400,
					False
				);
			}

			if ($_SESSION['user_id'] !== $post['user_id']) {
				ErrorHandler::handleError(
					'You are not allowed to perform this action',
					'/home',
					403,
					False
				);
			}

			$filePath = '/var/www/uploads/' . $post['image_path'];

			$pdo->beginTransaction();
			$postModel->deletePost($postId);
			if (file_exists($filePath)) {
				unlink($filePath);
			}
			$pdo->commit();
			
			$_SESSION['success'] = "Post deleted successfully!";
			header('Location: /home');
			exit();
		}
		catch (\Exception $e) {
			ErrorHandler::rollbackTransaction($pdo);
			ErrorHandler::handleException($e);
		}
	}
}