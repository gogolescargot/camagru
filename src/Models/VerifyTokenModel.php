<?php

require_once __DIR__ . '/../Core/Database.php';

class VerifyTokenModel
{
	private $pdo;

	public function __construct()
	{
		$this->pdo = Database::getConnection();
	}

	public function createToken($email, $token)
	{
		$stmt = $this->pdo->prepare('INSERT INTO verify_tokens (email, token) VALUES (:email, :token)');
		return $stmt->execute([
			':email' => $email,
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