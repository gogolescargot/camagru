<?php

class Database
{
	private static $pdo = null;

	public static function getConnection()
	{
		if (self::$pdo === null) {
			$host = getenv('MYSQL_HOST');
			$dbname = getenv('MYSQL_DATABASE');
			$username = getenv('MYSQL_USER');
			$password = getenv('MYSQL_PASSWORD');

			try {
				self::$pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
				self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			} catch (PDOException $e) {
				die("Database connection failed: " . $e->getMessage());
			}
		}

		return self::$pdo;
	}
}