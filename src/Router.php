<?php

namespace NiceRoute;

use NiceRoute\Contracts\Middleware;

class Router
{
    /**
     * Map of Routes
     *
     * @var array
     */
    public static $routeMap = [];

    /**
     * Incoming request
     * @var Request
     */
    private $request;

    /**
     * Custom 404 handler
     *
     * @var \Closure function(Request $req): Response
     */
    private static $notFoundHandler;

    /**
     * Custom 405 handler
     *
     * @var \Closure function(Request $req): Response
     */
    private static $notAllowedHandler;

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
            if ($route === Response::HTTP_NOT_FOUND && static::$notFoundHandler !== null) {
                $router->response((static::$notFoundHandler)($router->request), 404);
                return;
            }
            if ($route === Response::HTTP_METHOD_NOT_ALLOWED && static::$notAllowedHandler !== null) {
                $router->response((static::$notAllowedHandler)($router->request), 405);
                return;
            }
            Response::create('', $route)->send();
            return;
        }

        $router->handleRoute($route);
    }

    /**
     * Set custom handler for 404 error
     *
     * @param \Closure $handler function(Request $req): Response
     */
    public static function setNotFoundHandler(\Closure $handler)
    {
        static::$notFoundHandler = $handler;
    }

    /**
     * Set custom handler for 405 error
     *
     * @param \Closure $handler function(Request $req): Response
     */
    public static function setNotAllowedHandler(\Closure $handler)
    {
        static::$notAllowedHandler = $handler;
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
        $this->response($next($this->request));
    }

    /**
     * Perform a response with given content and http status code.
     *
     * @param mixed $content
     * @param int $code
     */
    private function response($content = '', $code = 200)
    {
        if ($content instanceof Response) {
            $content->send();
            return;
        }
        Response::create($content, $code)->send();
    }

    /**
     * Match route with incoming request.
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
