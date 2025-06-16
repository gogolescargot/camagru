<?php

namespace Models;

use PDO;

class EmailTokenModel
{
	private $pdo;

	public function __construct($pdo)
	{
		$this->pdo = $pdo;
	}

	public function createToken($user_id, $new_email, $token)
	{
		$stmt = $this->pdo->prepare('INSERT INTO email_tokens (user_id, new_email, token) VALUES (:user_id, :new_email, :token)');
		$stmt->execute([
			':user_id' => $user_id,
			':new_email' => $new_email,
			':token' => $token,
		]);
	}

	public function deleteToken($token)
	{
		$stmt = $this->pdo->prepare('DELETE FROM email_tokens WHERE token = :token');
		$stmt->execute([':token' => $token]);
	}

	public function findValidToken($token)
	{
		$stmt = $this->pdo->prepare('SELECT * FROM email_tokens WHERE token = :token');
		$stmt->execute([':token' => $token]);
		return $stmt->fetch(PDO::FETCH_ASSOC);
	}

	public function cleanOldToken($user_id)
	{
		$stmt = $this->pdo->prepare('DELETE FROM email_tokens WHERE user_id = :user_id');
		$stmt->execute([':user_id' => $user_id]);
	}
}