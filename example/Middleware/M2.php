<?php

namespace Example\Middleware;

use NiceRoute\Contracts\Middleware;
use NiceRoute\Request;
use NiceRoute\Response;

class M2 implements Middleware
{
    public function handle(Request $req, \Closure $next): Response
    {
        echo "m2-before\n";

        /**
         * @var Response $resp
         */
        $resp = $next($req);
        //        $resp->headers->add(['M2' => '']);

        echo "m2-after\n";

        return $resp;
    }
}
