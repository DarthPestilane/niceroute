<?php

namespace NiceRoute\Contracts;

use NiceRoute\Request;
use NiceRoute\Response;

interface Middleware
{
    public function handle(Request $req, \Closure $next): Response;
}
