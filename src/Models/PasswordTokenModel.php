<?php

require_once __DIR__ . '/../Core/Database.php';

class PasswordTokenModel
{
	private $pdo;

	public function __construct()
	{
		$this->pdo = Database::getConnection();
	}

	public function createToken($email, $token, $expires_at)
	{
		$stmt = $this->pdo->prepare('INSERT INTO password_tokens (email, token, expires_at) VALUES (:email, :token, :expires_at)');
		return $stmt->execute([
			':email' => $email,
			':token' => $token,
			':expires_at' => $expires_at,
		]);
	}

	public function deleteToken($token)
	{
		$stmt = $this->pdo->prepare('DELETE FROM password_tokens WHERE token = :token');
		return $stmt->execute([':token' => $token]);
	}

	public function findValidToken($token)
	{
		$stmt = $this->pdo->prepare('SELECT * FROM password_tokens WHERE token = :token AND expires_at > NOW()');
		$stmt->execute([':token' => $token]);
		return $stmt->fetch(PDO::FETCH_ASSOC);
	}
}