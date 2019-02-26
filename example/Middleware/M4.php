<?php

namespace Example\Middleware;

use NiceRoute\Contracts\Middleware;
use NiceRoute\Request;
use NiceRoute\Response;

class M4 implements Middleware
{
    public function handle(Request $req, \Closure $next): Response
    {
        echo "m4-before\n";
        $resp = $next($req);
        echo "m4-after\n";
        return $resp;
    }
}
