<?php

namespace Controllers;

use Core\Database;
use Core\ErrorHandler;
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
				if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $post['user_id']) { 
					$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
					$postUrl = $scheme . '://' . $_SERVER['HTTP_HOST'] . '/uploads/' . $post['image_path'];
					$post['post_url'] = $postUrl;
					$post['sn_url'] = rawurlencode($postUrl);
					$tweetText = rawurlencode('Look at my picture on camagru !');
					$post['x_href'] = 'https://x.com/intent/tweet?text=' . $tweetText . '&url=' . rawurlencode($postUrl);
					$post['fb_href'] = 'https://www.facebook.com/sharer/sharer.php?u=' . rawurlencode($postUrl);
				}
			}
			unset($post);

			include __DIR__ . '/../Views/home.php';
		}
		catch (\Exception $e) {
			ErrorHandler::handleException($e);
		}
	}
}