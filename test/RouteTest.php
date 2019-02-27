<?php

namespace Test;

use Example\Middleware\M1;
use NiceRoute\Route;
use NiceRoute\RouteMiddleware;
use NiceRoute\Router;
use PHPUnit\Framework\TestCase;

final class RouteTest extends TestCase
{
    public function testListPlaceholders()
    {
        $route = new Route();
        $route->pattern = '/foo/{foo}/bar/{bar}';
        $ph = $route->listPlaceHolders();
        $this->assertEquals(['foo', 'bar'], $ph);
    }

    public function testRegistry()
    {
        $route = new Route();
        $route->get('/foo/bar', function () {
        });
        $registered = array_pop(Router::$routeMap);
        $this->assertEquals('/foo/bar', $registered->pattern);
    }

    public function testMatches()
    {
        $route = new Route();
        $route->get('/foo/bar', function () {
        });

        /** @var Route $route */
        $route = array_pop(Router::$routeMap);

        $uri = '/foo/bar';
        list($find,) = $route->matches($uri);
        $this->assertEquals(true, $find);
    }

    public function testMatchesPlaceholders()
    {
        $route = new Route();
        $route->get('/foo/{foo}/bar/{bar}', function () {
        });

        /** @var Route $route */
        $route = array_pop(Router::$routeMap);

        $uri = '/foo/foo_id/bar/bar_id';
        list($find, $matches) = $route->matches($uri);
        $this->assertEquals(true, $find);
        $this->assertEquals(['foo_id', 'bar_id'], $matches);
    }

    public function testGroupPrefix()
    {
        $route = new Route();
        $route->group(['prefix' => '/prefix'], function (Route $route) {
            $route->get('/foo', function () {
            });
        });
        /** @var Route $route */
        $route = array_pop(Router::$routeMap);
        $this->assertEquals('/prefix/foo', $route->pattern);
    }

    public function testGroupPrefixNested()
    {
        $route = new Route();
        $route->group(['prefix' => '/foo'], function (Route $route) {
            $route->group(['prefix' => '/bar'], function (Route $route) {
                $route->get('/', function () {
                });
            });
        });
        /** @var Route $route */
        $route = array_pop(Router::$routeMap);
        $this->assertEquals('/foo/bar', $route->pattern);
    }

    public function testMiddleware()
    {
        $route = new Route();
        $route->get('/foo', function () {
        }, ['middleware' => [M1::class]]);

        /** @var Route $route */
        $route = array_pop(Router::$routeMap);

        /** @var RouteMiddleware $middleware */
        $middleware = $route->middlewareStack()[0];
        $this->assertEquals(true, $middleware->toRemove);
        $this->assertEquals(M1::class, $middleware->accessClass);
    }

    public function testGroupMiddleware()
    {
        $route = new Route();
        $route->group(['middleware' => [M1::class]], function (Route $route) {
            $route->get('/foo', function () {
            });
        });
        /** @var Route $route */
        $route = array_pop(Router::$routeMap);

        /** @var RouteMiddleware $middleware */
        $middleware = $route->middlewareStack()[0];
        $this->assertEquals(false, $middleware->toRemove);
        $this->assertEquals(M1::class, $middleware->accessClass);
    }
}
