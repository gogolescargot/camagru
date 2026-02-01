<?php

require_once __DIR__ . '/../Core/autoload.php';

use Core\Router;

if (session_status() === PHP_SESSION_NONE) {
	session_start();
}

$router = new Router();

$router->addRoute('GET', '/', 'HomeController', 'index');
$router->addRoute('GET', '/home', 'HomeController', 'index');
$router->addRoute('GET', '/register', 'RegisterController', 'index');
$router->addRoute('GET', '/login', 'LoginController', 'index');
$router->addRoute('GET', '/settings', 'SettingsController', 'index');
$router->addRoute('GET', '/studio', 'StudioController', 'index');
$router->addRoute('GET', '/forgot-password', 'ForgotPasswordController', 'index');
$router->addRoute('GET', '/reset-password', 'ResetPasswordController', 'index');
$router->addRoute('GET', '/verify-account', 'VerifyAccountController', 'verifyAccount');
$router->addRoute('GET', '/edit-email', 'EditEmailController', 'editEmail');
$router->addRoute('GET', '/error', 'ErrorController', 'index');

$router->addRoute('POST', '/login', 'AuthController', 'login');
$router->addRoute('POST', '/register', 'AuthController', 'register');
$router->addRoute('GET', '/logout', 'AuthController', 'logout');

$router->addRoute('POST', '/send-password-reset', 'RecoveryController', 'sendPasswordReset');
$router->addRoute('POST', '/reset-password', 'RecoveryController', 'resetPassword');
$router->addRoute('POST', '/edit-username', 'EditAccountController', 'editUsername');
$router->addRoute('POST', '/edit-email', 'EditAccountController', 'editEmail');
$router->addRoute('POST', '/edit-password', 'EditAccountController', 'editPassword');
$router->addRoute('POST', '/edit-preferences', 'EditAccountController', 'editEmailNotifications');


$router->addRoute('POST', '/like', 'PostController', 'likePost');

$router->addRoute('POST', '/comment', 'PostController', 'createCommentPost');
$router->addRoute('POST', '/delete-comment', 'PostController', 'deleteCommentPost');

$router->addRoute('POST', '/create-post', 'PostController', 'createPost');
$router->addRoute('POST', '/delete-post', 'PostController', 'deletePost');

$router->addRoute('POST', '/delete-image', 'ImageController', 'deleteImage');

$router->addRoute('GET', '/gallery', 'StudioController', 'gallery');

$router->handleRequest($_SERVER['REQUEST_URI']);