<?php

namespace Models;

use PDO;
use PDOException;

class UserModel
{
	private $pdo;

	public function __construct($pdo)
	{
		$this->pdo = $pdo;
	}

	public function findByEmail($email)
	{
		try {
			$stmt = $this->pdo->prepare('SELECT * FROM users WHERE email = :email');
			$stmt->execute([':email' => $email]);
			return $stmt->fetch(PDO::FETCH_ASSOC);
		}
		catch (PDOException $e) {
			throw $e;
		}
	}

	public function findByUsername($username)
	{
		try {
			$stmt = $this->pdo->prepare('SELECT * FROM users WHERE username = :username');
			$stmt->execute([':username' => $username]);
			return $stmt->fetch(PDO::FETCH_ASSOC);
		}
		catch (PDOException $e) {
			throw $e;
		}
	}

	public function findById($id)
	{
		try {
			$stmt = $this->pdo->prepare('SELECT * FROM users WHERE id = :id');
			$stmt->execute([':id' => $id]);
			return $stmt->fetch(PDO::FETCH_ASSOC);
		}
		catch (PDOException $e) {
			throw $e;
		}
	}

	public function updateUsername($username, $id)
	{
		try {
			$stmt = $this->pdo->prepare('UPDATE users SET username = :username WHERE id = :id');
			$stmt->execute([
				':username' => $username,
				':id' => $id,
			]);
		}
		catch (PDOException $e) {
			throw $e;
		}
	}

	public function updateEmail($email, $id)
	{
		try {
			$stmt = $this->pdo->prepare('UPDATE users SET email = :email WHERE id = :id');
			$stmt->execute([
				':email' => $email,
				':id' => $id,
			]);
		}
		catch (PDOException $e) {
			throw $e;
		}
	}

	public function updateVerify($verified, $id)
	{
		try {
			$stmt = $this->pdo->prepare('UPDATE users SET verified = :verified WHERE id = :id');
			$stmt->execute([
				':verified' => $verified,
				':id' => $id,
			]);
		}
		catch (PDOException $e) {
			throw $e;
		}
	}

	public function updatePassword($hashedPassword, $id)
	{
		try {
			$stmt = $this->pdo->prepare('UPDATE users SET password = :password WHERE id = :id');
			$stmt->execute([
				':password' => $hashedPassword,
				':id' => $id,
			]);
		}
		catch (PDOException $e) {
			throw $e;
		}
	}

	public function createUser($username, $email, $hashedPassword)
	{
		try {
			$stmt = $this->pdo->prepare('INSERT INTO users (username, email, password) VALUES (:username, :email, :password)');
			$stmt->execute([
				':username' => $username,
				':email' => $email,
				':password' => $hashedPassword,
			]);

			return $this->pdo->lastInsertId();
		}
		catch (PDOException $e) {
			// header('Location: /home');
			throw $e;
		}
	}
}