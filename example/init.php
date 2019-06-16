<?php

use Yuanshe\WeChatSDK\WeChat;
use Yuanshe\WeChatSDK\Example\Cache;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/Cache.php';

$config = include __DIR__ . '/config.php';

return new WeChat($config, Cache::class);
