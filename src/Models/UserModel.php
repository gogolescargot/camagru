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

	public function findByUsername($username)
	{
		$stmt = $this->pdo->prepare('SELECT * FROM users WHERE username = :username');
		$stmt->execute([':username' => $username]);
		return $stmt->fetch(PDO::FETCH_ASSOC);
	}

	public function findById($id)
	{
		$stmt = $this->pdo->prepare('SELECT * FROM users WHERE id = :id');
		$stmt->execute([':id' => $id]);
		return $stmt->fetch(PDO::FETCH_ASSOC);
	}

	public function updateUsername($username, $id)
	{
		$stmt = $this->pdo->prepare('UPDATE users SET username = :username WHERE id = :id');
		$stmt->execute([
			':username' => $username,
			':id' => $id,
		]);
	}

	public function updateEmail($email, $id)
	{
		$stmt = $this->pdo->prepare('UPDATE users SET email = :email WHERE id = :id');
		$stmt->execute([
			':email' => $email,
			':id' => $id,
		]);
	}

	public function updateVerify($verified, $id)
	{
		$stmt = $this->pdo->prepare('UPDATE users SET verified = :verified WHERE id = :id');
		$stmt->execute([
			':verified' => $verified,
			':id' => $id,
		]);
	}

	public function updatePassword($hashedPassword, $id)
	{
		$stmt = $this->pdo->prepare('UPDATE users SET password = :password WHERE id = :id');
		$stmt->execute([
			':password' => $hashedPassword,
			':id' => $id,
		]);
	}

	public function createUser($username, $email, $hashedPassword)
	{
		$stmt = $this->pdo->prepare('INSERT INTO users (username, email, password) VALUES (:username, :email, :password)');
		$stmt->execute([
			':username' => $username,
			':email' => $email,
			':password' => $hashedPassword,
		]);

		return $this->pdo->lastInsertId();
	}
}