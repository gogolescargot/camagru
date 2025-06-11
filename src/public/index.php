<?php

require_once __DIR__ . '/../Core/Router.php';

$router = new Router();
$router->handleRequest($_SERVER['REQUEST_URI']);
