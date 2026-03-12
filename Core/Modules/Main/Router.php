<?php

namespace Modules\Main;

class Router
{
    protected array $routes = [];

    public function addRoute(string $httpMethod, string $path, string $controller, string $action): void
    {
        [$regex, $paramNames] = $this->compilePath($path);

        $this->routes[] = [
            'method'     => strtoupper($httpMethod),
            'path'       => $path,
            'regex'      => $regex,
            'paramNames' => $paramNames,  // переименовал для ясности
            'controller' => $controller,
            'action'     => $action,
        ];
    }

    public function get(string $path, string $controller, string $action): void
    {
        $this->addRoute('GET', $path, $controller, $action);
    }

    public function post(string $path, string $controller, string $action): void
    {
        $this->addRoute('POST', $path, $controller, $action);
    }

    public function put(string $path, string $controller, string $action): void
    {
        $this->addRoute('PUT', $path, $controller, $action);
    }

    public function delete(string $path, string $controller, string $action): void
    {
        $this->addRoute('DELETE', $path, $controller, $action);
    }

    /**
     * Возвращает [$controllerClass, $action, $paramsAssoc] или null
     * Поддержка 0+ параметров из пути
     */
    public function dispatch(string $httpMethod, string $uri): ?array
    {
        $httpMethod = strtoupper($httpMethod);
        $path = parse_url($uri, PHP_URL_PATH) ?? '/';

        foreach ($this->routes as $route) {
            if ($route['method'] !== $httpMethod) {
                continue;
            }

            if (preg_match($route['regex'], $path, $matches)) {
                array_shift($matches); // убрать полное совпадение

                $paramsAssoc = [];
                foreach ($route['paramNames'] as $idx => $name) {
                    $paramsAssoc[$name] = $matches[$idx] ?? null;
                }

                // Возвращаем позиционно для удобства запуска:
                // [$controllerClass, $action, $paramsAssoc]
                return [
                    $route['controller'],
                    $route['action'],
                    $paramsAssoc,
                ];
            }
        }

        return null;
    }

    protected function compilePath(string $path): array
    {
        $paramNames = [];

        $regex = preg_replace_callback(
            '#\{([a-zA-Z_][a-zA-Z0-9_]*)\}#',
            function ($m) use (&$paramNames) {
                $paramNames[] = $m[1];
                return '([^/]+)';
            },
            $path
        );

        $regex = '#^' . $regex . '$#';

        return [$regex, $paramNames];
    }
}
