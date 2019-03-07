<?php

namespace NiceRoute;

use NiceRoute\Contracts\Middleware;

class Router
{
    /**
     * @var array
     * map of Route
     */
    public static $routeMap = [];

    /**
     * @var Request
     */
    public $request;

    /**
     * Registry routes.
     *
     * @param \Closure $routesFunc
     */
    public static function registry(\Closure $routesFunc)
    {
        $routesFunc(new Route());
    }

    /**
     * Add route.
     *
     * @param string $name
     * @param Route $route
     */
    public static function addRoute(string $name, Route $route)
    {
        static::$routeMap[$name] = $route;
    }

    /**
     * Start handling routes.
     */
    public static function run()
    {
        $router = new static();

        $router->request = Request::init();

        $route = $router->match();

        if (is_int($route)) {
            if ($route === 404) {
                Response::create('not found', 404)->send();
                return;
            }
            Response::create('method not allowed', 405)->send();
            return;
        }

        $router->handleRoute($route);
    }

    /**
     * Handle single route.
     *
     * @param Route $route
     */
    private function handleRoute(Route $route)
    {
        $next = $route->handler;

        // if is controller
        if (!($next instanceof \Closure)) {
            list($ctrlClass, $ctrlMethod) = explode('@', $next);
            $next = [new $ctrlClass, $ctrlMethod];
        }

        // handle middleware [..., m2, m1]
        $middlewareStack = array_reverse($route->middlewareStack());
        foreach ($middlewareStack as $middleware) {
            $next = function (Request $req) use ($middleware, $next) {
                /** @var RouteMiddleware $middleware */
                /** @var Middleware $realMiddleware */
                $realMiddleware = new $middleware->accessClass;
                $resp = $realMiddleware->handle($req, $next);
                if ($resp instanceof Response) {
                    return $resp;
                }
                return Response::create($resp);
            };
        }

        $resp = $next($this->request);
        if ($resp instanceof Response) {
            $resp->send();
            return;
        }
        Response::create($resp)->send();
    }

    /**
     * To match route with requested uri.
     *
     * @return Route|int
     */
    private function match()
    {
        $method = $this->request->getMethod();
        $uri = '/' . trim($this->request->getPathInfo(), '/'); // without query string

        // completely match
        $name = $method . '-' . $uri;
        if (isset(static::$routeMap[$name])) {
            return static::$routeMap[$name];
        }

        // match placeholders
        foreach (static::$routeMap as $name => $route) {
            /** @var $route Route */
            list($_method,) = explode('-', $name);
            if ($method === $_method) {
                $matchResult = $route->matches($uri);
                if (!$matchResult[0]) {
                    // not match
                    continue;
                }
                $vars = $matchResult[1];
                $holders = $route->listPlaceHolders();
                $params = array_combine($holders, $vars);
                $this->request->setParams($params);
                return $route;
            }
        }

        // 405
        foreach (static::$routeMap as $name => $route) {
            list(, $pattern) = explode('-', $name);
            if ($pattern === $uri) {
                return Response::HTTP_METHOD_NOT_ALLOWED;
            }
        }

        // 404
        return Response::HTTP_NOT_FOUND;
    }
}
