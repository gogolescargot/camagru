<?php

require_once __DIR__ . '/../Core/Database.php';

class UserModel
{
	private $pdo;

	public function __construct()
	{
		$this->pdo = Database::getConnection();
	}

	public function findByEmail($email)
	{
		$stmt = $this->pdo->prepare('SELECT * FROM users WHERE email = :email');
		$stmt->execute([':email' => $email]);
		return $stmt->fetch(PDO::FETCH_ASSOC);
	}

	public function updatePassword($email, $hashedPassword)
	{
		$stmt = $this->pdo->prepare('UPDATE users SET password = :password WHERE email = :email');
		return $stmt->execute([
			':password' => $hashedPassword,
			':email' => $email,
		]);
	}

	public function verifyUser($email)
	{
		$stmt = $this->pdo->prepare('UPDATE users SET verified = TRUE WHERE email = :email');
		return $stmt->execute([
			':email' => $email,
		]);
	}

	public function createUser($username, $email, $hashedPassword)
	{
		$stmt = $this->pdo->prepare('INSERT INTO users (username, email, password) VALUES (:username, :email, :password)');
		return $stmt->execute([
			':username' => $username,
			':email' => $email,
			':password' => $hashedPassword,
		]);
	}
}