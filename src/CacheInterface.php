<?php

namespace Yuanshe\WeChatSDK;

/**
 * Interface CacheInterface
 * @package Yuanshe\WeChatSDK
 * @author Yuanshe <admin@ysboke.com>
 * @license LGPL-3.0-or-later
 */
interface CacheInterface
{

    /**
     * CacheAbstract constructor.
     * @param string $prefix 缓存名称的前缀，请务必确保前缀的唯一性，避免与项目中其他缓存冲突
     */
    public function __construct(string $prefix = '');

    /**
     * 获取一条缓存数据
     * @param string $name 缓存名称
     * @return mixed 返回缓存数据，若缓存不存在或已过期则返回null
     */
    public function get(string $name);

    /**
     * 写入一条缓存
     * @param string $name 缓存名称
     * @param mixed $value 缓存内容
     * @param int $seconds 有效时长（秒）
     * @return bool 成功返回true，失败返回false
     */
    public function put(string $name, $value, int $seconds): bool;

    /**
     * 删除一条缓存
     * @param string $name 缓存名称
     * @return bool 成功返回true，失败返回false
     */
    public function del(string $name): bool;
}
