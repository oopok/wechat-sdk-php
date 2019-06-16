<?php

namespace Yuanshe\WeChatSDK;

use Yuanshe\WeChatSDK\Exception\Exception;
use Yuanshe\WeChatSDK\Exception\ConfigException;
use Yuanshe\WeChatSDK\Exception\ModelException;
use Yuanshe\WeChatSDK\Exception\NotifyException;
use Yuanshe\WeChatSDK\Model\Comment;
use Yuanshe\WeChatSDK\Model\Core;
use Yuanshe\WeChatSDK\Model\CustomService;
use Yuanshe\WeChatSDK\Model\Media;
use Yuanshe\WeChatSDK\Model\Menu;
use Yuanshe\WeChatSDK\Model\OAuth;
use Yuanshe\WeChatSDK\Model\Tag;
use Yuanshe\WeChatSDK\Model\Template;
use Yuanshe\WeChatSDK\Model\User;

/**
 * Class WeChat
 * @package Yuanshe\WeChatSDK
 * @author Yuanshe <admin@ysboke.com>
 * @license LGPL-3.0-or-later
 * @property-read Core core
 * @property-read Menu menu
 * @property-read Media media
 * @property-read Comment comment
 * @property-read User user
 * @property-read Tag tag
 * @property-read Template template
 * @property-read OAuth oAuth
 * @property-read CustomService customService
 */
class WeChat
{
    protected $common;
    protected $models;

    /**
     * WeChat constructor.
     * @param array $config SDK配置
     * @param string $cacheClass 实现CacheInterface的类名称
     * @throws Exception
     */
    public function __construct(array $config, string $cacheClass)
    {
        if (!is_subclass_of($cacheClass, CacheInterface::class)) {
            throw new Exception('parameter 2 must implement \'' . CacheInterface::class . "', '$cacheClass' given");
        }
        $this->common = new Common($config, new $cacheClass($config['cache_prefix']));
    }

    public function __get(string $name)
    {
        $name = ucfirst($name);
        return $this->models[$name] ?? $this->model($name);
    }

    /**
     * 接收微信服务器通知消息
     * @param array $queries 通知请求URL中的query部分，以键值对的形式传入
     * @param string $body 通知请求实体，传入原始数据串
     * @return Notify|string 返回包含通知信息的Notify类或字符串(返回字符串时应将该字符串输出到响应)
     * @throws ConfigException
     * @throws NotifyException
     */
    public function notify(array $queries, string $body = '')
    {
        $this->common->checkConfig(array_fill_keys(['token', 'account'], ['required' => true, 'nonempty' => true]));
        $helper = new NotifyHelper($this->common);
        /***初步验证来源可靠性***/
        if (
            !isset($queries['timestamp'], $queries['nonce'], $queries['signature'])
            || NotifyHelper::sha1(
                $queries['timestamp'],
                $queries['nonce'],
                $this->common->config('token')
            ) != $queries['signature']
        ) {
            throw new NotifyException('Invalid signature');
        }
        /** @var bool 判断是否开启加密 */
        $isEncrypt = isset($queries['encrypt_type']) && (empty($data['MsgType']) || $this->common->config('encrypt'));
        if ($isEncrypt) {
            $this->common->checkConfig(['ase_key' => ['required' => true, 'nonempty' => true]]);
        }
        /***数据验证***/
        if ($body) {
            $data = NotifyHelper::xmlToArray($body);
            if (
                $data
                && $data['ToUserName'] == $this->common->config('account')
                && (
                    !$isEncrypt
                    || NotifyHelper::sha1(
                        $queries['timestamp'],
                        $queries['nonce'],
                        $this->common->config('token'),
                        $data['Encrypt']
                    ) == ($queries['msg_signature'] ?? null)
                    && $data = NotifyHelper::xmlToArray($helper->decrypt($data['Encrypt']))
                )
            ) {
                return new Notify($helper, $data, $isEncrypt);
            } else {
                throw new NotifyException('Invalid message body');
            }
        } elseif (!empty($queries['echostr'])) {
            return $queries['echostr'];
        } else {
            throw new NotifyException('The message body is empty');
        }
    }

    /**
     * 通过IP验证消息通知是否来自微信服务器
     * @param string $notifyIP 请求通知接口的IP
     * @return bool 验证通过返回true，不通过返回false
     * @throws Exception
     * @throws ModelException
     */
    public function checkNotifyIP(string $notifyIP)
    {
        if (!$ip_list = $this->common->cache()->get('wechat_ip_list')) {
            $ip_list = $this->core->getCallbackIP();
            $this->common->cache()->put('wechat_ip_list', $ip_list, 3600);
        }
        foreach ($ip_list as $ip) {
            $ip_info = explode('/', $ip, 2);
            if (
            count($ip_info) == 2
                ? !intdiv(ip2long($notifyIP) ^ ip2long($ip_info[0]), pow(2, 32 - $ip_info[1]))
                : $notifyIP == $ip_info[0]
            ) {
                return true;
            }
        }
        return false;
    }

    /**
     * 生成JS-SDK初始化所需的参数
     * @param string $pageURL 页面的URL，不包括hash(#符号及之后的部分)
     * @param array $jsApiList 需要使用的JS接口列表，也可在前端自行传入
     * @return array
     * @throws Exception
     * @throws ModelException
     */
    public function jsSDK(string $pageURL, array $jsApiList = [])
    {
        $nonce = '';
        for ($i = 0; $i < 4; $i++) {
            $nonce .= dechex(mt_rand());
        }
        $sign_params = [
            'noncestr' => $nonce,
            'jsapi_ticket' => $this->core->getTicket('jsapi'),
            'timestamp' => time(),
            'url' => $pageURL
        ];
        ksort($sign_params, SORT_STRING);
        $sign_data = [];
        foreach ($sign_params as $name => $value) {
            $sign_data[] = "$name=$value";
        }
        $sign = sha1(join('&', $sign_data));
        return [
            'appId' => $this->common->config('appid'),
            'timestamp' => $sign_params['timestamp'],
            'nonceStr' => $sign_params['noncestr'],
            'signature' => $sign,
            'jsApiList' => $jsApiList
        ];
    }

    /**
     * 载入一个模型实例
     * @param string $name 模型名称
     * @return ModelBase
     */
    private function model(string $name)
    {
        $class_name = __NAMESPACE__ . "\\Model\\$name";
        return $this->models[$name] = new $class_name($this->common);
    }
}
