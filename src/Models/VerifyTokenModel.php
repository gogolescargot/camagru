<?php

namespace Models;

use PDO;

class VerifyTokenModel
{
	private $pdo;

	public function __construct($pdo)
	{
		$this->pdo = $pdo;
	}

	public function createToken($user_id, $token)
	{
		$stmt = $this->pdo->prepare('INSERT INTO verify_tokens (user_id, token) VALUES (:user_id, :token)');
		return $stmt->execute([
			':user_id' => $user_id,
			':token' => $token,
		]);
	}

	public function deleteToken($token)
	{
		$stmt = $this->pdo->prepare('DELETE FROM verify_tokens WHERE token = :token');
		return $stmt->execute([':token' => $token]);
	}

	public function findValidToken($token)
	{
		$stmt = $this->pdo->prepare('SELECT * FROM verify_tokens WHERE token = :token');
		$stmt->execute([':token' => $token]);
		return $stmt->fetch(PDO::FETCH_ASSOC);
	}
}