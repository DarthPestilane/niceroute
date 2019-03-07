<?php

namespace Example\Controllers;

use NiceRoute\Request;
use NiceRoute\Response;

class UserController
{
    public function list(Request $req)
    {
        return Response::json([
            'list' => [],
            'req'  => $req->query->all(),
        ]);
    }
}
