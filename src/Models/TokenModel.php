<?php

namespace Models;

use PDO;
use PDOException;

class TokenModel
{
	private $pdo;

	public function __construct($pdo)
	{
		$this->pdo = $pdo;
	}

	public function createToken($user_id, $token, $type, $expires_at = NULL, $new_email = NULL)
	{
		try {
			$stmt = $this->pdo->prepare('INSERT INTO tokens (user_id, token, type, new_email, expires_at) VALUES (:user_id, :token, :type, :new_email, :expires_at)');
			$stmt->execute([
				':user_id' => $user_id,
				':token' => $token,
				':type' => $type,
				':new_email' => $new_email,
				':expires_at' => $expires_at,
			]);
		}
		catch (PDOException $e) {
			throw $e;
		}
	}

	public function deleteToken($token)
	{
		try {
			$stmt = $this->pdo->prepare('DELETE FROM tokens WHERE token = :token');
			$stmt->execute([':token' => $token]);
		}
		catch (PDOException $e) {
			throw $e;
		}
	}

	public function findValidToken($token)
	{
		try {
			$stmt = $this->pdo->prepare('SELECT * FROM tokens WHERE token = :token AND (expires_at IS NULL OR expires_at > NOW())');
			$stmt->execute([':token' => $token]);
			return $stmt->fetch(PDO::FETCH_ASSOC);
		}
		catch (PDOException $e) {
			throw $e;
		}
	}

	public function cleanOldEmailToken($user_id)
	{
		try {
			$stmt = $this->pdo->prepare('DELETE FROM tokens WHERE user_id = :user_id AND type = "email_change"');
			$stmt->execute([':user_id' => $user_id]);
		}
		catch (PDOException $e) {
			throw $e;
		}
	}
}