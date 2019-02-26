<?php

namespace Example;

require_once __DIR__ . '/../vendor/autoload.php';

use NiceRoute\Router;
use NiceRoute\Route;
use NiceRoute\Request;
use NiceRoute\Response;
use Example\Middleware\M1;
use Example\Middleware\M2;
use Example\Middleware\M3;
use Example\Middleware\M4;
use Example\Middleware\M5;

Router::registry(function (Route $route) {

    $route->get('/user', function () {
        echo "user list\n";
        return Response::json(['fuck']);
    }, ['middleware' => [M1::class, M2::class]]);

    $route->group(['prefix' => '/api', 'middleware' => [M3::class]], function (Route $route) {

        $route->get('/user', function () {
            echo "api user\n";
            return Response::json(['api user']);
        }, ['middleware' => [M4::class]]);

        $route->get('/article', function () use ($route) {
            echo "api article\n";
            return Response::json(['api article']);
        });

        $route->group(['prefix' => '/v1', 'middleware' => [M5::class]], function (Route $route) {
            $route->get('/user', function () {
                echo "api v1 user\n";
                return Response::json(['api v1 user']);
            });
        });
    });

    $route->get('/article/{article_id}/comment/{comment_id}', function (Request $req) {
        return Response::json([
            'path_params' => $req->params(),
        ]);
    });
});

Router::run();
