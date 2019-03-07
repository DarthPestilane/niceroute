# NiceRoute - A simple and nice router for PHP

If you just want a minimal routing library for PHP instead of `laravel/lumen` or `slim` ...

Here you are!

## Features

- Http verb methods: `get` `post` `put` `patch` `delete`
- Placeholder in route pattern: `get /user/{user_id}/article/{article_id}`
- Request and Response based on Symfony
- Before/After Middleware supported
- Group routes supported

## Requirement

- php: ^7.1.3

## Usage

There are some example code in `example` directory.

### Basic one

We will registry all our routes in `\NiceRoute\Router` closure.
Each route we are going to define, should be like `$route->get('pattern', function() {})`.
We can use these methods to quickly define routes:

- get
- post
- put
- patch
- delete

Each method stands for the corresponding http method.

```php
<?php

require_once  './vendor/autoload.php';

// register routes
\NiceRoute\Router::registry(function(\NiceRoute\Route $route) {
    $route->get('/', function () {
        return \NiceRoute\Response::json(['msg' => 'nice to meet you.']);
    });
});

// dispatch routes
\NiceRoute\Router::run();
```

### Handle Request and placeholder

Placeholder in pattern should be wrapped by `{}`,

```php
<?php

\NiceRoute\Router::registry(function(\NiceRoute\Route $route) {
    $route->put('/user/{id}', function (\NiceRoute\Request $req) {
        $id = (int)$req->param('id');
        $body = $req->json->all();
        return \NiceRoute\Response::json([
            'msg' => 'nice to meet you.',
            'id' => $id,
            'body' => $body,
        ]);
    });
});
```

### Middleware

Routes can be bound with middleware.
All middleware must implement `\NiceRoute\Contracts\Middleware`.

```php
<?php

// Define middleware:
class ExampleMiddleware implements \NiceRoute\Contracts\Middleware
{
    public function handle(\NiceRoute\Request $req,\Closure $next) : \NiceRoute\Response{
        echo "before\n";
        
        /** * @var \NiceRoute\Response $resp */
        $resp = $next($req);

        echo "after\n";
        return $resp;
    }
}

// bind middleware to routes
\NiceRoute\Router::registry(function(\NiceRoute\Route $route) {
    $route->put('/profile', function (\NiceRoute\Request $req) {
        return \NiceRoute\Response::json([
            'request_body' => $req->json->all(),
        ]);
    }, ['middleware' => [ExampleMiddleware::class]]);
});
```

### Group routes

We can share a same prefix on a group of routes:

```php
<?php

\NiceRoute\Router::registry(function(\NiceRoute\Route $route) {
    $route->group(['prefix' => '/v1'], function(\NiceRoute\Route $route) {
        $route->get('/users', function() {
            return \NiceRoute\Response::json(['users' => []]);
        }); // which is defined like `GET /v1/users`
    });
});
```

We can also share same middleware as well:

```php
<?php

use NiceRoute\Router;
use NiceRoute\Route;
use NiceRoute\Response;

Router::registry(function(Route $route) {
    $route->group(['prefix' => '/admin', 'middleware' => [AuthMiddleware::class]], function(Route $route) {
        $route->get('/users', function() {
            return Response::json(['users' => []]);
        });
    });
});
```

### Using Controller

We can use controllers to handle our requests instead of `\Closure` in routes definition.

First we will specify a namespace prefix for our controller classes,
then we use 'Controller@method' to specify the method to be called.

```php
<?php

use NiceRoute\Router;
use NiceRoute\Route;

Router::registry(function(Route $route) {
    $route->group(['namespace' => 'App\Controllers',], function(Route $route) {
        $route->get('/users', 'UserContrller@list');
    });
});
```

And the controller should be like:

```php
<?php

namespace App\Controllers;

use NiceRoute\Request;
use NiceRoute\Response;

class UserController
{
    public function list(Request $request)
    {
        return Response::json([
            'list' => ['list data'],
            'request query string' => $request->query->all(),
        ]);
    }
}
```
