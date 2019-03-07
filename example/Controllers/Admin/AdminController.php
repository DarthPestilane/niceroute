<?php

namespace Example\Controllers\Admin;

use NiceRoute\Response;

class AdminController
{
    public function list()
    {
        return Response::json(["admin list"]);
    }
}
