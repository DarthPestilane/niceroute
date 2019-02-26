<?php

namespace NiceRoute;


/**
 * Class Route
 * @package NiceRoute
 *
 * @method get(String $pattern, \Closure $handler, array $attributes = [])
 * @method post(String $pattern, \Closure $handler, array $attributes = [])
 * @method put(String $pattern, \Closure $handler, array $attributes = [])
 * @method patch(String $pattern, \Closure $handler, array $attributes = [])
 * @method delete(String $pattern, \Closure $handler, array $attributes = [])
 */
class Route
{
    /**
     * @var String
     */
    public $method;

    /**
     * @var String
     */
    public $pattern;

    /**
     * @var String
     */
    public $regex;

    /**
     * @var \Closure
     */
    public $handler;

    /**
     * @var String
     */
    public $name;

    /**
     * @var array
     */
    private $middlewareStack = [];

    /**
     * @var array
     */
    private $prefixStack = [];

    /**
     * Get middleware stack.
     *
     * @return array
     */
    public function middlewareStack()
    {
        return $this->middlewareStack;
    }

    /**
     * Starting group route registry.
     *
     * @param array $attributes
     * @param \Closure $handler
     */
    public function group(array $attributes, \Closure $handler)
    {
        $this->updateAttributes($attributes, true);

        $handler($this->duplicate());

        array_pop($this->prefixStack);
        $length = count($this->middlewareStack);
        for ($i = 0; $i < $length; $i++) {
            array_shift($this->middlewareStack);
        }
    }

    private function duplicate()
    {
        return clone $this;
    }

    private function mergePrefix(String $uri = '')
    {
        $uri = trim($uri, '/');

        $p = '';
        foreach ($this->prefixStack as $prefix) {
            $p .= '/' . trim($prefix, '/');
        }

        return '/' . trim($p . '/' . $uri, '/');
    }

    private function add(String $method, String $pattern, \Closure $handler, array $attributes = [])
    {
        $this->method = $method;
        $this->pattern = $this->mergePrefix($pattern);
        $this->handler = $handler;
        $this->name = $this->method . '-' . $this->pattern;
        $this->regex = $this->buildRegex();

        // parse $attributes
        $this->updateAttributes($attributes);

        // add route to router
        Router::addRoute($this->name, $this->duplicate());


        // restore $this for next call.
        $this->clean();
    }

    private function clean()
    {
        $this->method = null;
        $this->pattern = null;
        $this->handler = null;
        $this->name = null;
        $this->regex = null;
        foreach ($this->middlewareStack as $key => $middleware) {
            if ($middleware->toRemove) {
                unset($this->middlewareStack[$key]);
            }
        }
    }

    private function updateAttributes(array $attributes = [], bool $fromGroup = false)
    {
        $this->updateMiddlewareStack($attributes, $fromGroup);
        if ($fromGroup) {
            $this->updatePrefixStack($attributes);
        }
    }

    private function updatePrefixStack(array $attributes = [])
    {
        $prefix = $attributes['prefix'] ?? '';
        if ($prefix !== '') {
            $this->prefixStack[] = $prefix;
        }
    }

    private function updateMiddlewareStack(array $attributes = [], bool $fromGroup = false)
    {
        $middlewareClasses = $attributes['middleware'] ?? [];
        foreach ($middlewareClasses as $class) {
            $this->middlewareStack[] = new RouteMiddleware(!$fromGroup, $class);
        }
    }

    /**
     * Match regex with given $uri.
     *
     * @param String $uri
     * @return array
     */
    public function matches(String $uri): array
    {
        $regex = $this->buildRegex();
        $find = !!preg_match($regex, rawurldecode($uri), $matches);
        if ($find) {
            array_shift($matches);
        }
        return [$find, $matches];
    }

    /**
     * Get array of placeholders in pattern.
     *
     * @return array
     */
    public function listPlaceHolders(): array
    {
        $find = !!preg_match_all('/\{(\w+?)\}/', $this->pattern, $matches);
        if (!$find) {
            return [];
        }
        return $matches[1];
    }

    private function buildRegex()
    {
        $reg = preg_replace('/\{\w+?\}/', '(\w+?)', $this->pattern);
        return "#^{$reg}$#";
    }

    public function __call($method, $arguments)
    {
        $httpMethods = ['get', 'post', 'put', 'patch', 'delete'];
        if (in_array($method, $httpMethods)) {
            $this->add(strtoupper($method), ...$arguments);
            return;
        }
        throw new \InvalidArgumentException("method: '{$method}' not found.");
    }
}
