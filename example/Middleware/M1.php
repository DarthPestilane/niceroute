<?php

namespace Example\Middleware;

use NiceRoute\Contracts\Middleware;
use NiceRoute\Request;
use NiceRoute\Response;

class M1 implements Middleware
{
    public function handle(Request $req, \Closure $next): Response
    {
        echo "m1-before\n";

        /**
         * @var Response $resp
         */
        $resp = $next($req);
        $resp->headers->add(['M1' => '']);

        echo "m1-after\n";

        return $resp;
    }
}
