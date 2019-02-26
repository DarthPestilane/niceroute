<?php

namespace Example\Middleware;

use NiceRoute\Contracts\Middleware;
use NiceRoute\Request;
use NiceRoute\Response;

class M5 implements Middleware
{
    public function handle(Request $req, \Closure $next): Response
    {
        echo "m5-before\n";
        $resp = $next($req);
        echo "m5-after\n";
        return $resp;
    }
}
