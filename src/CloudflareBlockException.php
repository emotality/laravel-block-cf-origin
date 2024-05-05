<?php

namespace Emotality\Cloudflare;

use Symfony\Component\HttpKernel\Exception\HttpException;

class CloudflareBlockException extends HttpException
{
    public function __construct(int $status = 403, string $message = 'Accessing the server directly is forbidden!', array $headers = [])
    {
        parent::__construct(
            statusCode: $status,
            message: $message,
            headers: $headers,
        );
    }
}
