<?php

namespace NiceRoute\Facade;

/**
 * Class Route
 * @package NiceRoute\Facade
 *
 * @method static \NiceRoute\Route get(String $pattern, \Closure $handler)
 * @method static \NiceRoute\Route post(String $pattern, \Closure $handler)
 * @method static \NiceRoute\Route put(String $pattern, \Closure $handler)
 * @method static \NiceRoute\Route patch(String $pattern, \Closure $handler)
 * @method static \NiceRoute\Route group(String $prefix, \Closure $handler)
 * @method static \NiceRoute\Route middleware(\Closure $handler)
 */
class Route
{
    /**
     * @return \NiceRoute\Route
     */
    private static function getInstance()
    {
        return new \NiceRoute\Route();
    }

    /**
     * Handle dynamic, static calls to the object.
     *
     * @param  string $method
     * @param  array $args
     * @return mixed
     *
     * @throws \RuntimeException
     */
    public static function __callStatic($method, $args)
    {
        $instance = static::getInstance();

        if (!$instance) {
            throw new \RuntimeException('A facade root has not been set.');
        }

        return $instance->$method(...$args);
    }
}
