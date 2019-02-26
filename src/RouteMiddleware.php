<?php

namespace NiceRoute;

class RouteMiddleware
{
    public $toRemove;

    public $accessClass;

    public function __construct(bool $toRemove, String $accessClass)
    {
        $this->toRemove = $toRemove;
        $this->accessClass = $accessClass;
    }
}
