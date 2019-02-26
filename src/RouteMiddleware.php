<?php

namespace NiceRoute;

class RouteMiddleware
{
    /**
     * Determine to remove middleware of route after registered to Router.
     *
     * @var bool
     */
    public $toRemove;

    /**
     * The actual class name to middleware.
     *
     * @var string
     */
    public $accessClass;

    public function __construct(bool $toRemove, string $accessClass)
    {
        $this->toRemove = $toRemove;
        $this->accessClass = $accessClass;
    }
}
