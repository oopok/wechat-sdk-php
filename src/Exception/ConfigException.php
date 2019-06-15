<?php

namespace Yuanshe\WeChatSDK\Exception;

use Throwable;

class ConfigException extends Exception
{
    const CODE_OTHER = 0;
    const CODE_MISSING = 1;
    const CODE_TYPE_ERROR = 2;
    const CODE_EMPTY = 3;
    const CODE_FORMAT_ERROR = 4;
    const CODE_OVERFLOW = 5;

    protected $field;

    private static $messageMap = [
        self::CODE_OTHER => 'Other errors',
        self::CODE_MISSING => 'Missing field',
        self::CODE_TYPE_ERROR => 'Error type',
        self::CODE_EMPTY => 'Value cannot be empty',
        self::CODE_FORMAT_ERROR => 'Format error',
        self::CODE_OVERFLOW => 'The value is overflowed'
    ];

    public function __construct(string $field, string $message = '', int $code = 0, Throwable $previous = null)
    {
        $this->field = $field;
        parent::__construct("WeChat Config '$this->field' Error \"$message\"", $code, $previous);
    }

    /**
     * @return string
     */
    public function getField(): string
    {
        return $this->field;
    }

    /**
     * @param string $field
     * @param int $type
     * @param Throwable|null $previous
     * @return ConfigException
     */
    public static function create(string $field, int $type = self::CODE_OTHER, Throwable $previous = null): self
    {
        return new self($field, self::$messageMap[$type] ?? self::$messageMap[self::CODE_OTHER], $type, $previous);
    }
}
