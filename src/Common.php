<?php

namespace Yuanshe\WeChatSDK;

use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;
use Yuanshe\WeChatSDK\Exception\ConfigException;
use Yuanshe\WeChatSDK\Exception\Exception;

/**
 * Class Common
 * @package Yuanshe\WeChatSDK
 * @author Yuanshe <admin@ysboke.com>
 * @license LGPL-3.0-or-later
 */
class Common
{
    const DATA_TYPE_JSON = 'application/json'; // 以application/json格式提交数据
    const DATA_TYPE_MULTIPART = 'mutipart/form-data'; // 以mutipart/form-data格式提交数据

    private $configContents;
    private $cacheInstance;
    private $httpClient;

    /**
     * Common constructor.
     * @param array $config
     * @param CacheInterface $cacheInstance
     * @throws ConfigException
     */
    public function __construct(array $config, CacheInterface $cacheInstance)
    {
        $this->configContents = $config;
        $this->cacheInstance = $cacheInstance;
        $this->checkConfig([
            'appid' => [
                'type' => 'string',
                'required' => true,
                'nonempty' => true
            ],
            'app_secret' => [
                'type' => 'string',
                'required' => true,
                'nonempty' => true
            ],
            'account' => [
                'type' => 'string'
            ],
            'token' => [
                'type' => 'string'
            ],
            'encrypt' => [
                'type' => 'bool',
                'defaults' => true
            ],
            'ase_key' => [
                'type' => 'string'
            ],
            'api_domain' => [
                'type' => 'string',
                'required' => false,
                'nonempty' => true,
                'defaults' => 'api.weixin.qq.com'
            ],
            'cache_prefix' => [
                'type' => 'string',
                'required' => true
            ],
            'timeout' => [
                'type' => 'numeric',
                'defaults' => 0
            ],
            'ssl_verify' => [
                'type' => 'bool',
                'defaults' => true
            ]
        ]);
        $this->httpClient = new Client([
            'base_uri' => 'https://' . $this->config('api_domain'),
            'timeout' => $this->config('timeout'),
            'verify' => $this->config('ssl_verify')
        ]);
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function config(string $name)
    {
        return $this->configContents[$name] ?? null;
    }

    public function cache()
    {
        return $this->cacheInstance;
    }

    /**
     * 获取access_token
     * @return string 返回access_token
     * @throws Exception
     */
    public function getAccessToken()
    {
        return $this->cacheInstance->get('access_token') ?: $this->generateAccessToken();
    }

    /**
     * 生成新的access_token
     * @return string 返回新生成的access_token
     * @throws Exception
     */
    public function generateAccessToken()
    {
        $response = $this->httpClient->get('/cgi-bin/token', [
            'query' => [
                'grant_type' => 'client_credential',
                'appid' => $this->config('appid'),
                'secret' => $this->config('app_secret')
            ]
        ]);
        $token = json_decode($response->getBody(), true);
        if (!$token) {
            throw new Exception('Unknown data');
        } elseif (!empty($token['errcode'])) {
            throw new Exception($token['errmsg']);
        } else {
            $this->cacheInstance->put('access_token', $token['access_token'], $token['expires_in'] / 2);
            return $token['access_token'];
        }
    }

    /**
     * 发送一个请求到API并取得返回的数据
     * @param string $uri 请求的URI
     * @param array $data 附带传入的数据，如果不为空则使用POST的方式提交，否则使用GET的方式提交
     * @param array $options 选项(query, type, access_token)
     * @return ResponseInterface
     * @throws Exception
     */
    public function request(
        string $uri,
        array $data = [],
        array $options = []
    ) {
        $query = $options['query'] ?? [];
        if ($options['token'] ?? true) {
            $query['access_token'] = $this->getAccessToken();
        }
        $request_options = ['query' => $query];
        if ($data) {
            switch ($options['type'] ?? self::DATA_TYPE_JSON) {
                case self::DATA_TYPE_MULTIPART:
                    $post_type = 'multipart';
                    $post_data = [];
                    foreach ($data as $field => $contents) {
                        array_push(
                            $post_data,
                            ['name' => $field] + (is_array($contents) ? $contents : ['contents' => $contents])
                        );
                    }
                    break;
                case self::DATA_TYPE_JSON:
                default:
                    $post_type = 'body';
                    $post_data = json_encode($data, JSON_UNESCAPED_UNICODE);
                    $request_options['headers'] = ['Content-Type' => 'application/json'];
            }
            $request_options[$post_type] = $post_data;
            return $this->httpClient->post($uri, $request_options);
        } else {
            return $this->httpClient->get($uri, $request_options);
        }
    }

    /**
     * @param array $fieldRules
     * @throws ConfigException
     */
    public function checkConfig(array $fieldRules)
    {
        foreach ($fieldRules as $field => $rule) {
            if (isset($this->configContents[$field])) {
                if (!empty($rule['nonempty']) and empty($this->configContents[$field])) {
                    throw ConfigException::create($field, ConfigException::CODE_EMPTY);
                }
                if (!empty($rule['type'])) {
                    $type_func_name = "is_{$rule['type']}";
                    if (!function_exists($type_func_name) || !$type_func_name($this->configContents[$field])) {
                        throw ConfigException::create($field, ConfigException::CODE_TYPE_ERROR);
                    }
                }
            } elseif (isset($rule['defaults'])) {
                $this->configContents[$field] = $rule['defaults'];
            } elseif (!empty($rule['required'])) {
                throw ConfigException::create($field, ConfigException::CODE_MISSING);
            }
        }
    }
}
