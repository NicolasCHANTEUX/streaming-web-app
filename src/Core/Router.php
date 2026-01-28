<?php

namespace App\Core;

class Router
{
    private array $routes = [];

    public function get(string $path, string $controller, string $method): void
    {
        $this->addRoute('GET', $path, $controller, $method);
    }

    public function post(string $path, string $controller, string $method): void
    {
        $this->addRoute('POST', $path, $controller, $method);
    }

    public function put(string $path, string $controller, string $method): void
    {
        $this->addRoute('PUT', $path, $controller, $method);
    }

    public function delete(string $path, string $controller, string $method): void
    {
        $this->addRoute('DELETE', $path, $controller, $method);
    }

    private function addRoute(string $httpMethod, string $path, string $controller, string $method): void
    {
        $this->routes[] = [
            'http_method' => $httpMethod,
            'path' => $path,
            'controller' => $controller,
            'method' => $method
        ];
    }

    public function dispatch(): void
    {
        $requestMethod = $_SERVER['REQUEST_METHOD'];
        $requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        // Remove trailing slash except for root
        if ($requestUri !== '/' && str_ends_with($requestUri, '/')) {
            $requestUri = rtrim($requestUri, '/');
        }

        foreach ($this->routes as $route) {
            // Convert route path to regex pattern
            $pattern = $this->convertToRegex($route['path']);

            if ($route['http_method'] === $requestMethod && preg_match($pattern, $requestUri, $matches)) {
                // Remove full match
                array_shift($matches);

                // Instantiate controller and call method
                $controllerClass = "App\\Controllers\\" . $route['controller'];
                
                if (!class_exists($controllerClass)) {
                    $this->notFound();
                    return;
                }

                $controller = new $controllerClass();
                $method = $route['method'];

                if (!method_exists($controller, $method)) {
                    $this->notFound();
                    return;
                }

                call_user_func_array([$controller, $method], $matches);
                return;
            }
        }

        $this->notFound();
    }

    private function convertToRegex(string $path): string
    {
        // Convert /path/{id} to regex pattern
        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '([a-zA-Z0-9_-]+)', $path);
        return '#^' . $pattern . '$#';
    }

    private function notFound(): void
    {
        http_response_code(404);
        view('pages/404');
    }
}
