<!DOCTYPE html>
<html>
<head>
    <title>Home</title>
    <link rel="stylesheet" href="/css/styles.css">
</head>
<body>
    <h1>Welcome to Home</h1>
</body>
</html>

<?php
$host = getenv('MYSQL_HOST');
$dbname = getenv('MYSQL_DATABASE');
$username = getenv('MYSQL_USER');
$password = getenv('MYSQL_PASSWORD');

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connected to the database successfully!";
}
catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
