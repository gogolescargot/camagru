<?php

namespace Models;

use PDO;
use PDOException;

class PostModel
{
	private $pdo;

	public function __construct($pdo)
	{
		$this->pdo = $pdo;
	}

	public function createPost($user_id, $title, $image_path)
	{
		try {
			$stmt = $this->pdo->prepare('INSERT INTO posts (user_id, title, image_path) VALUES (:user_id, :title, :image_path)');
			$stmt->execute([
				':user_id' => $user_id,
                ':title' => $title,
				':image_path' => $image_path,
			]);
            
            return $this->pdo->lastInsertId();
		}
		catch (PDOException $e) {
			throw $e;
		}
	}

	public function deletePost($id)
	{
		try {
			$stmt = $this->pdo->prepare('DELETE FROM posts WHERE id = :id');
			$stmt->execute([':id' => $id]);
		}
		catch (PDOException $e) {
			throw $e;
		}
	}

	public function findPost($post_id)
	{
		try {
			$stmt = $this->pdo->prepare('SELECT * FROM posts WHERE id = :id');
			$stmt->execute([':id' => $post_id]);
			return $stmt->fetch(PDO::FETCH_ASSOC);
		}
		catch (PDOException $e) {
			throw $e;
		}
	}

	public function findLikePost($post_id, $user_id)
	{
		try {
			$stmt = $this->pdo->prepare('SELECT id FROM likes WHERE post_id = :post_id AND user_id = :user_id');
			$stmt->execute([
				':post_id' => $post_id,
				':user_id' => $user_id,
			]);
			return $stmt->fetch(PDO::FETCH_ASSOC);
		}
		catch (PDOException $e) {
			throw $e;
		}
	}

	public function likePost($post_id, $user_id)
	{
		try {
			$stmt = $this->pdo->prepare('INSERT INTO likes (post_id, user_id) VALUES (:post_id, :user_id)');
			$stmt->execute([
				':post_id' => $post_id,
				':user_id' => $user_id,
			]);
		}
		catch (PDOException $e) {
			throw $e;
		}
	}

	public function unlikePost($post_id, $user_id)
	{
		try {
			$stmt = $this->pdo->prepare('DELETE FROM likes WHERE post_id = :post_id AND user_id = :user_id');
			$stmt->execute([
				':post_id' => $post_id,
				':user_id' => $user_id,
			]);
		}
		catch (PDOException $e) {
			throw $e;
		}
	}

	public function createCommentPost($post_id, $user_id, $content)
	{
		try {
			$stmt = $this->pdo->prepare('INSERT INTO comments (post_id, user_id, content) VALUES (:post_id, :user_id, :content)');
			$stmt->execute([
				':post_id' => $post_id,
				':user_id' => $user_id,
				':content' => $content,
			]);
		}
		catch (PDOException $e) {
			throw $e;
		}
	}

	public function deleteCommentPost($id)
	{
		try {
			$stmt = $this->pdo->prepare('DELETE FROM comments WHERE id = :id');
			$stmt->execute([
				':id' => $id,
			]);
		}
		catch (PDOException $e) {
			throw $e;
		}
	}

	public function findCommentPost($id)
	{
		try {
			$stmt = $this->pdo->prepare('SELECT user_id FROM comments WHERE id = :id');
			$stmt->execute([':id' => $id]);
			return $stmt->fetch(PDO::FETCH_ASSOC);
		}
		catch (PDOException $e) {
			throw $e;
		}
	}

	public function getPostsPaginated($limit, $page)
	{
		try {
			$stmt = $this->pdo->prepare('SELECT * FROM posts ORDER BY created_at DESC LIMIT :limit OFFSET :offset');
			$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
			$stmt->bindValue(':offset', ($page - 1) * 5, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		}
		catch (PDOException $e) {
			throw $e;
		}
	}

	public function getLastUserPosts($user_id)
	{
		try {
			$stmt = $this->pdo->prepare('SELECT * FROM posts WHERE user_id = :user_id ORDER BY created_at DESC');
			$stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		}
		catch (PDOException $e) {
			throw $e;
		}
	}

	public function getLikesForPostIds($postIds)
		{
			if (empty($postIds)) {
				return [];
			}
			try {
				$placeholders = implode(',', array_fill(0, count($postIds), '?'));
				$stmt = $this->pdo->prepare("
					SELECT post_id, COUNT(id) as like_count 
					FROM likes 
					WHERE post_id IN ($placeholders) 
					GROUP BY post_id
				");
				$stmt->execute($postIds);
				$likes = $stmt->fetchAll(PDO::FETCH_ASSOC);

				$likesByPostId = [];
				foreach ($likes as $like) {
					$likesByPostId[$like['post_id']] = (int)$like['like_count'];
				}
				return $likesByPostId;
			}
			catch (PDOException $e) {
				throw $e;
			}
		}

		public function getCommentsForPostIds(array $postIds)
		{
			if (empty($postIds)) {
				return [];
			}
			try {
				$placeholders = implode(',', array_fill(0, count($postIds), '?'));
				$stmt = $this->pdo->prepare("
					SELECT c.*, u.username 
					FROM comments c
					JOIN users u ON c.user_id = u.id
					WHERE c.post_id IN ($placeholders) 
					ORDER BY c.created_at ASC
				");
				$stmt->execute($postIds);
				$comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

				$commentsByPostId = [];
				foreach ($postIds as $postId) {
					$commentsByPostId[$postId] = [];
				}
				foreach ($comments as $comment) {
					$commentsByPostId[$comment['post_id']][] = $comment;
				}
				return $commentsByPostId;
			}
			catch (PDOException $e) {
				throw $e;
			}
		}
}