<?php

class Router
{
    public function handleRequest($uri)
    {
        $path = parse_url($uri, PHP_URL_PATH);

        switch ($path) {
            case '/':
                header('Location: /home');
                break;
            case '/home':
                require_once __DIR__ . '/../Controllers/HomeController.php';
                $controller = new HomeController();
                $controller->index();
                break;

            default:
                http_response_code(404);
                echo "Error 404: Page not Found";
                break;
        }
    }
}
