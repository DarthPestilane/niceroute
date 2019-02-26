<?php

namespace Example\Middleware;

use NiceRoute\Contracts\Middleware;
use NiceRoute\Request;
use NiceRoute\Response;

class M3 implements Middleware
{
    public function handle(Request $req, \Closure $next): Response
    {
        echo "m3-before\n";
        $resp = $next($req);
        echo "m3-after\n";
        return $resp;
    }
}
