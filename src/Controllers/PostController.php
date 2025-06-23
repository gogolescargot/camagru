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

			if (!isset($_FILES['image-post']) || $_FILES['image-post']['error'] !== UPLOAD_ERR_OK) {
				ErrorHandler::handleJsonResponse(
					ImageHelper::getUploadErrorMessage($_FILES['image-post']['error'] ?? null),
					True
				);
			}

			$fileTmpPath = $_FILES['image-post']['tmp_name'];
			$fileName = $_FILES['image-post']['name'];
			$fileSize = $_FILES['image-post']['size'];
			$fileType = $_FILES['image-post']['type'];
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
			$postModel = new PostModel($pdo);

			$pdo->beginTransaction();
			$postModel->createPost($_SESSION["user_id"], $title, $uploadName);
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

			$pdo->beginTransaction();
			$postModel->deletePost($postId);
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