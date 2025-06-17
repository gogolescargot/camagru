<?php

namespace Controllers;

use Core\Database;
use Models\PostModel;
use Models\UserModel;

class HomeController
{
	public function index()
	{
		try {
			$pdo = Database::getConnection();
			$postModel = new PostModel($pdo);
			$userModel = new UserModel($pdo);

			$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
			$limit = 5;

			$posts = $postModel->getPostsPaginated($limit, $page);
			$postIds = array_column($posts, 'id');

			$likesByPostId = [];
			$commentsByPostId = [];

			if (!empty($postIds)) {
				$likesByPostId = $postModel->getLikesForPostIds($postIds);
				$commentsByPostId = $postModel->getCommentsForPostIds($postIds);
			}

			$current_user_id = $_SESSION['user_id'] ?? null;

			foreach ($posts as &$post) {
				$post['username'] = $userModel->findById($post["user_id"])["username"];
				$post['like_count'] = $likesByPostId[$post['id']] ?? 0;
				$post['comments'] = $commentsByPostId[$post['id']] ?? [];
				$post['liked'] = $current_user_id ? !empty($postModel->findLikePost($post['id'], $current_user_id)) : false;
			}
			unset($post);

			include __DIR__ . '/../Views/home.php';
		}
		catch (\Exception $e) {
			ErrorHandler::handleException($e);
		}
	}
}