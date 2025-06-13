<?php

if (session_status() === PHP_SESSION_NONE) {
	session_start();
}

require_once __DIR__ . '/../Core/Router.php';

$router = new Router();

$router->addRoute('GET', '/', 'HomeController', 'index');
$router->addRoute('GET', '/home', 'HomeController', 'index');
$router->addRoute('GET', '/register', 'RegisterController', 'index');
$router->addRoute('GET', '/login', 'LoginController', 'index');
$router->addRoute('GET', '/forgot-password', 'ForgotPasswordController', 'index');
$router->addRoute('GET', '/reset-password', 'ResetPasswordController', 'index');
$router->addRoute('GET', '/verify-account', 'VerifyAccountController', 'verify');

$router->addRoute('POST', '/login', 'AuthController', 'login');
$router->addRoute('POST', '/register', 'AuthController', 'register');
$router->addRoute('GET', '/logout', 'AuthController', 'logout');

$router->addRoute('POST', '/send-password-reset', 'RecoveryController', 'sendPasswordReset');
$router->addRoute('POST', '/reset-password', 'RecoveryController', 'resetPassword');

$router->handleRequest($_SERVER['REQUEST_URI']);