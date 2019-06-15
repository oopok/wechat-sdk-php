<?php

namespace Yuanshe\WeChatSDK;

use Psr\Http\Message\StreamInterface;
use Yuanshe\WeChatSDK\Exception\ModelException;

/**
 * Class ModelBase
 * @package Yuanshe\WeChatSDK
 * @author Yuanshe <admin@ysboke.com>
 * @license LGPL-3.0-or-later
 */
class ModelBase
{
    protected $common;

    public function __construct(Common $common)
    {
        $this->common = $common;
    }

    /**
     * @param string $uri
     * @param array $data
     * @param array $options
     * @return array|StreamInterface
     * @throws Exception\Exception
     * @throws ModelException
     */
    protected function request(string $uri, array $data = [], array $options = [])
    {
        $response = $this->common->request($uri, $data, $options);
        $data = $response->getBody();
        if (
            in_array(explode(';', $response->getHeaderLine('Content-Type'), 2)[0], ['application/json', 'text/plain'])
            and $result = json_decode($data, true)
        ) {
            if (!is_array($result)) {
                throw new ModelException(static::class, 'Unknown response');
            }
            if (empty($result['errcode'])) {
                return $result;
            } else {
                throw new ModelException(static::class, $result['errmsg'], $result['errcode']);
            }
        } else {
            return $data;
        }
    }
}
