<?php

namespace Models;

use PDO;
use PDOException;

class ImageModel
{
	private $pdo;

	public function __construct($pdo)
	{
		$this->pdo = $pdo;
	}

	public function createImage($user_id, $path)
	{
		try {
			$stmt = $this->pdo->prepare('INSERT INTO images (user_id, path) VALUES (:user_id, :path)');
			$stmt->execute([
				':user_id' => $user_id,
				':path' => $path,
			]);
		}
		catch (PDOException $e) {
			throw $e;
		}
	}

	public function deleteImage($id)
	{
		try {
			$stmt = $this->pdo->prepare('DELETE FROM images WHERE id = :id');
			$stmt->execute([':id' => $id]);
		}
		catch (PDOException $e) {
			throw $e;
		}
	}

    public function findImage($image_id)
	{
		try {
			$stmt = $this->pdo->prepare('SELECT * FROM images WHERE id = :id');
			$stmt->execute([':id' => $image_id]);
			return $stmt->fetch(PDO::FETCH_ASSOC);
		}
		catch (PDOException $e) {
			throw $e;
		}
	}

    public function getImages($user_id)
	{
		try {
			$stmt = $this->pdo->prepare('SELECT * FROM images WHERE user_id = :user_id ORDER BY created_at');
			$stmt->execute([':user_id' => $user_id]);
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		}
		catch (PDOException $e) {
			throw $e;
		}
	}
}