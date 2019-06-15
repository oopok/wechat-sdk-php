<?php

namespace Yuanshe\WeChatSDK\Example;

use Yuanshe\WeChatSDK\CacheInterface;

class Cache implements CacheInterface
{
    const PATH = __DIR__ . '/cache';
    protected $prefix;

    public function __construct(string $prefix = '')
    {
        $this->prefix = $prefix;
    }

    public function get(string $name)
    {
        $file_name = $this->getFilePathByName($name);
        if (is_file($file_name)) {
            $data = unserialize(file_get_contents($file_name));
            if ($data['deadline'] < time()) {
                return null;
            } else {
                return $data['content'];
            }
        } else {
            return null;
        }
    }

    public function put(string $name, $value, int $seconds): bool
    {
        $put_data = serialize([
            'deadline' => time() + $seconds,
            'content' => $value
        ]);
        return file_put_contents($this->getFilePathByName($name), $put_data);
    }

    public function del(string $name): bool
    {
        return unlink($this->getFilePathByName($name));
    }

    private function getFilePathByName($name)
    {
        return self::PATH . "/$this->prefix.$name.cache";
    }
}