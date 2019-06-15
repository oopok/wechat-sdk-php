<?php

namespace Yuanshe\WeChatSDK\Exception;

use Throwable;

class ModelException extends Exception
{
    protected $model;

    public function __construct(string $model, string $message = '', int $code = 0, Throwable $previous = null)
    {
        $split_pos = strrpos($model, '\\');
        $this->model = is_int($split_pos) ? substr($model, $split_pos + 1) : $model;
        parent::__construct("WeChat Model '$this->model' Error $message, Error code $code", $code, $previous);
    }

    /**
     * @return string
     */
    public function getModel(): string
    {
        return $this->model;
    }
}
