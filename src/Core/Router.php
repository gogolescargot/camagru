<?php

namespace Core;

class Router
{
	private $routes = [];

	public function addRoute($method, $path, $controller, $action)
	{
		$this->routes[] = [
			'path' => $path,
			'controller' => $controller,
			'method' => $method,
			'action' => $action,
		];
	}

	public function handleRequest($uri)
	{
		$path = parse_url($uri, PHP_URL_PATH);
		$requestMethod = $_SERVER['REQUEST_METHOD'];

		foreach ($this->routes as $route) {
			if ($route['path'] === $path && $route['method'] === $requestMethod) {
				$controllerClass = 'Controllers\\' . $route['controller'];

				if (!class_exists($controllerClass)) {
					http_response_code(500);
					echo "Error 500: Controller '$controllerClass' not found.";
					return;
				}

				$controllerInstance = new $controllerClass();
				$action = $route['action'];

				if (method_exists($controllerInstance, $action)) {
					$controllerInstance->$action();
				}
				else {
					http_response_code(500);
					echo "Error 500: Action '$action' not found in controller '$controllerClass'.";
				}

				return;
			}
		}

		http_response_code(404);
		exit();
	}
}