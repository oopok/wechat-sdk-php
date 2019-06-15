<?php

namespace Yuanshe\WeChatSDK\Exception;

use Throwable;

class NotifyException extends Exception
{
    public function __construct(string $message = '', int $code = 0, Throwable $previous = null)
    {
        parent::__construct("WeChat Notify Error \"$message\"", $code, $previous);
    }
}
