<?php

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
				require_once __DIR__ . '/../Controllers/' . $route['controller'] . '.php';

				$controllerClass = new $route['controller']();
				$action = $route['action'];

				if (method_exists($controllerClass, $action)) {
					$controllerClass->$action();
				}
				else {
					http_response_code(500);
					echo "Error 500: Action '$action' not found in controller '{$route['controller']}'";
				}

				return;
			}
		}

		http_response_code(404);
		echo "Error 404: Page not Found";
	}
}