<?php

namespace Yuanshe\WeChatSDK\Model;

use Yuanshe\WeChatSDK\Exception\Exception;
use Yuanshe\WeChatSDK\Exception\ModelException;
use Yuanshe\WeChatSDK\ModelBase;

/**
 * 核心接口
 * @package Yuanshe\WeChatSDK\Model
 * @license LGPL-3.0-or-later
 * @author Yuanshe <admin@ysboke.com>
 */
class Core extends ModelBase
{

    /**
     * 消息通知URL网络检测
     * @see https://mp.weixin.qq.com/wiki?t=resource/res_main&id=21541575776DtsuT
     * @param string $action 执行的检测动作，允许的值：dns（域名解析）、ping（ping检测）、all（dns和ping都做）
     * @param string $checkOperator 指定平台从某个运营商进行检测，允许的值：CHINANET（电信）、UNICOM（联通）、CAP（腾讯自建）、DEFAULT（自动）
     * @return array 返回检测的结果
     * @throws Exception
     * @throws ModelException
     */
    public function callbackCheck(string $action = 'all', string $checkOperator = 'DEFAULT')
    {
        return $this->request('/cgi-bin/callback/check', [
            'action' => $action,
            'check_operator' => $checkOperator
        ]);
    }

    /**
     * 对所有api调用次数进行清零
     * @see https://mp.weixin.qq.com/wiki?t=resource/res_main&id=mp1433744592
     * @throws Exception
     * @throws ModelException
     */
    public function clearQuota()
    {
        $this->request('/cgi-bin/clear_quota', ['appid' => $this->common->config('appid')]);
    }

    /**
     * 获取微信服务器IP地址列表
     * 可用作消息通知接口的白名单校验
     * @see https://mp.weixin.qq.com/wiki?t=resource/res_main&id=mp1421140187
     * @return array 返回IP地址或IP网段列表
     * @throws Exception
     * @throws ModelException
     */
    public function getCallbackIP()
    {
        return $this->request('/cgi-bin/getcallbackip')['ip_list'];
    }

    /**
     * 获取公众号的自动回复规则
     * @see https://mp.weixin.qq.com/wiki?t=resource/res_main&id=mp1433751299
     * @return array
     * @throws Exception
     * @throws ModelException
     */
    public function getCurrentAutoReplyInfo()
    {
        return $this->request('/cgi-bin/get_current_autoreply_info');
    }

    /**
     * 长链接转短链接
     * @see https://mp.weixin.qq.com/wiki?t=resource/res_main&id=mp1443433600
     * @param string $originalURL 原始链接，支持"http://"、"https://"、"weixin://wxpay"几种协议
     * @return string
     * @throws Exception
     * @throws ModelException
     */
    public function shortURL(string $originalURL)
    {
        $data = ['action' => 'long2short', 'long_url' => $originalURL];
        return $this->request('/cgi-bin/shorturl', $data)['short_url'] ?? '';
    }

    /**
     * 生成临时凭证
     * @param string $type 凭证类型，可传入jsapi、wx_card
     * @return array 返回生成的ticket与有效时长
     * @throws Exception
     * @throws ModelException
     */
    public function generateTicket(string $type)
    {
        return $this->request('/cgi-bin/ticket/getticket', [], ['query' => ['type' => $type]]);
    }

    /**
     * 获取临时凭证
     * 该函数已将ticket进行缓存
     * @param string $type 凭证类型，可传入jsapi、wx_card
     * @return string 返回生成的ticket
     * @throws Exception
     * @throws ModelException
     */
    public function getTicket(string $type)
    {
        $ticket = $this->common->cache()->get("{$type}_ticket");
        if ($ticket) {
            return $ticket;
        } else {
            $result = $this->generateTicket($type);
            $this->common->cache()->put("{$type}_ticket", $result['ticket'], $result['expires_in'] / 2);
            return $result['ticket'];
        }
    }

    /**
     * 生成带参数的二维码（场景值二维码）
     * @see https://mp.weixin.qq.com/wiki?t=resource/res_main&id=mp1443433542
     * @param mixed $scene 场景值。可以为int或string，string的最大长度不能超过64
     * @param int $seconds 二维码有效时长（秒），最大不超过2592000（30天），默认为30秒。值为0时生成永久二维码。
     * @return array 返回二维码的ticket、qrcode_url（二维码图片地址）、url（二维码数据中的地址）
     * @throws Exception
     * @throws ModelException
     */
    public function createQRCode($scene, int $seconds = 30)
    {
        $is_string = (int)is_string($scene);
        $is_limit = (int)($seconds > 0);
        $data = [
            'action_name' => [
                ['QR_SCENE', 'QR_STR_SCENE'],
                ['QR_LIMIT_SCENE', 'QR_LIMIT_STR_SCENE']
            ][$is_limit][$is_string],
            'action_info' => ['scene' => [$is_string ? 'scene_str' : 'scene_id' => $scene]]
        ];
        if ($is_limit) {
            $data['expire_seconds'] = $seconds;
        }
        $result = $this->request('/cgi-bin/qrcode/create', $data);
        $result['qrcode_url'] = "https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket={$result['ticket']}";
        return $result;
    }
}
