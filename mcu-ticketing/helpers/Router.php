<?php

class Router {
    private $routes = [];

    public function add($page, $controller, $method = 'index') {
        $this->routes[$page] = ['controller' => $controller, 'method' => $method];
    }

    public function dispatch($page) {
        if (array_key_exists($page, $this->routes)) {
            $route = $this->routes[$page];
            $controllerName = $route['controller'];
            $methodName = $route['method'];

            // Adjust path based on where Router is called from (assuming index.php)
            $controllerPath = __DIR__ . '/../controllers/' . $controllerName . '.php';

            if (file_exists($controllerPath)) {
                include_once $controllerPath;
                if (class_exists($controllerName)) {
                    $controller = new $controllerName();
                    if (method_exists($controller, $methodName)) {
                        $controller->$methodName();
                        return;
                    } else {
                        $this->handleError("Method '$methodName' not found in '$controllerName'");
                    }
                } else {
                    $this->handleError("Class '$controllerName' not found");
                }
            } else {
                $this->handleError("Controller file '$controllerPath' not found");
            }
        } else {
            $this->handle404($page);
        }
    }

    private function handleError($message) {
        echo "<h1>System Error</h1><p>$message</p>";
    }

    private function handle404($page) {
        echo "<h1>404 Page Not Found</h1>";
        echo "<p>The requested page '" . htmlspecialchars($page) . "' does not exist.</p>";
        echo "<a href='index.php?page=dashboard'>Go to Dashboard</a>";
    }
}
