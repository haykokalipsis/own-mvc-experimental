<?php

namespace Core;

class Router
{
    protected $getRoutes = [];
    protected $postRoutes = [];

    protected $params = [];
    protected $variables = [];

    public function filter($route)
    {
        $route = preg_replace('~\/~', '\\/', $route);
        $route = preg_replace('~\{([a-z]+):([^\}]+)\}~', '(?P<\1>\2)', $route);
        $route = '~^' . $route . '$~i';

        return $route;
//        $this->routes[$route] = $params;
    }

    public function get($route, $params)
    {
        $route = $this->filter($route);
        $this->getRoutes[$route] = $params;
    }

    public function post($route, $params)
    {
        $route = $this->filter($route);
        $this->postRoutes[$route] = $params;
    }

    public function getRoutes()
    {
        return array_merge($this->getRoutes, $this->postRoutes);
    }

    public function getParams()
    {
        return $this->params;
    }

    public function oldMatch($url)
    {
        foreach ($this->routes as $route => $params) {
            if ($url == $route) {
                $this->params = $params;
                return true;
            }
        }

        return false;
    }

    public function match($url, $method)
    {
        switch ($method) {
            case 'GET' : $routes = $this->getRoutes; break;
            case 'POST' : $routes = $this->postRoutes; break;
        }

        foreach ($routes as $route => $params) {
            if (preg_match($route, $url, $matches) ) {
                foreach ($matches as $key => $match) {
                    if (is_string($key) ) {
                        $variables[$key] = $match;
                    }
                }

                $this->params = $params;
                $this->variables = $variables ?? [];

                return true;
            }
        }

        return false;
    }

    public function dispatch($url, $method)
    {
        $url = $this->removeQueryStringVariables($url);

        if ($this->match($url, $method) ) {
            $controller = $this->params['controller'];
            $controller = $this->convertToStudlyCaps($controller);
//            $controller = "App\Controllers\\$controller";
            $controller = 'App\\Controllers\\' . $controller;

            if (class_exists($controller)) {
//                $methodsParameters = $this->excludeControllerAndAction($this->params);
                $controller_object = new $controller($this->params, $this->variables);
                $action = $this->params['action'];
                $action = $this->convertToCamelCase($action);

                if (is_callable([$controller_object, $action])) {
//                    $controller_object->$action($this->variables);
                    call_user_func_array(array($controller_object, $action), $this->variables);
                } else {
                    echo "Method {$action} in (in controller {$controller}) not found";
                }
            } else {
                echo "Controller class {$controller} not found";
            }
        } else {
            echo "No route matched";
        }
    }

    public function removeQueryStringVariables($url)
    {
        if ($url != '') {
            $parts = explode('&', $url, 2);

            if (strpos($parts[0], '=') === false)
                $url = $parts[0];
            else
                $url = '=';
        }

        return $url;
    }


    public function convertToStudlyCaps($string)
    {
        return str_replace(' ', '', ucwords(str_replace('-', ' ', $string) ) );
    }

    public function convertToCamelCase($string)
    {
        return lcfirst($this->convertToStudlyCaps($string) );
    }

    public function excludeControllerAndAction($arr)
    {
        foreach ($arr as $key => $value) {
            if ($key == 'controller')
                unset ($arr[$key]);
            if ($key == 'action')
                unset ($arr[$key]);
        }

        return $arr;
    }
}