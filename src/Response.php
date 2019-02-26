<?php

namespace NiceRoute;

use Symfony\Component\HttpFoundation\Response as BaseResponse;

class Response extends BaseResponse
{
    /**
     * @param array|null $content
     * @param int $status
     * @return Response
     */
    public static function json(array $content = null, int $status = 200)
    {
        return static::create(json_encode($content), $status, ['Content-Type' => 'application/json']);
    }
}
