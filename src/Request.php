<?php

namespace NiceRoute;

use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request as BaseRequest;

class Request extends BaseRequest
{
    /**
     * Parameters in route pattern
     *
     * @var array
     */
    private $params = [];

    /**
     * @var ParameterBag
     */
    public $json;

    /**
     * @return Request
     */
    public static function init()
    {
        $request = parent::createFromGlobals();

        $req = (new static)->duplicate(
            $request->query->all(), $request->request->all(), $request->attributes->all(),
            $request->cookies->all(), $request->files->all(), $request->server->all()
        );

        // adds up json parameter
        if ($req->getContentType() === 'json') {
            $req->json = new ParameterBag(json_decode($req->getContent(), true));
        }
        return $req;
    }

    /**
     * {@inheritdoc}
     */
    public function duplicate(array $query = null, array $request = null, array $attributes = null, array $cookies = null, array $files = null, array $server = null)
    {
        return parent::duplicate($query, $request, $attributes, $cookies, $files, $server);
    }

    /**
     * Retrieve request params.
     *
     * @param string|null $key
     * @param null $default
     * @return mixed
     */
    public function input(string $key = null, $default = null)
    {
        if ($this->json !== null) {
            return $this->json->get($key, $default);
        }
        return $this->get($key, $default);
    }

    /**
     * Retrieve route parameter.
     *
     * @param string $key
     * @return string
     */
    public function param(string $key): string
    {
        return $this->params[$key];
    }

    /**
     * Retrieve all route parameters
     *
     * @return array
     */
    public function params(): array
    {
        return $this->params;
    }

    /**
     * Set route parameters.
     *
     * @param array $params
     */
    public function setParams(array $params = [])
    {
        $this->params = $params;
    }
}
