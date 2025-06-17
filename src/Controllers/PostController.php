<?php

namespace Controllers;

use Core\Database;
use Core\ErrorHandler;
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