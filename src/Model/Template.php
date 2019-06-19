<?php

namespace Yuanshe\WeChatSDK\Model;

use Yuanshe\WeChatSDK\Exception\Exception;
use Yuanshe\WeChatSDK\Exception\ModelException;
use Yuanshe\WeChatSDK\ModelBase;

/**
 * 模板消息接口
 * Class Template
 * @package Yuanshe\WeChatSDK\Model
 * @license LGPL-3.0-or-later
 * @author Yuanshe <admin@ysboke.com>
 * @see https://mp.weixin.qq.com/wiki?t=resource/res_main&id=mp1433751277
 */
class Template extends ModelBase
{
    /**
     * 设置所属行业
     * @param int $primaryIndustryID 主营行业
     * @param int $secondaryIndustryID 副营行业
     * @throws Exception
     * @throws ModelException
     */
    public function setIndustry(int $primaryIndustryID, int $secondaryIndustryID)
    {
        $this->request('/cgi-bin/template/api_set_industry', [
            'industry_id1' => $primaryIndustryID,
            'industry_id2' => $secondaryIndustryID
        ]);
    }

    /**
     * 获取设置的行业信息
     * @return array
     * @throws Exception
     * @throws ModelException
     */
    public function getIndustry()
    {
        return $this->request('/cgi-bin/template/get_industry');
    }

    /**
     * 获得模板ID
     * 从行业模板库选择模板到帐号后台
     * @param string $templateIDShort 模板库中模板的编号
     * @return string
     * @throws Exception
     * @throws ModelException
     */
    public function addTemplate(string $templateIDShort)
    {
        return $this->request('/cgi-bin/template/api_add_template', [
                'template_id_short' => $templateIDShort
            ])['template_id'] ?? '';
    }

    /**
     * 获取模板列表
     * 获取已添加至帐号下所有模板列表
     * @return array
     * @throws Exception
     * @throws ModelException
     */
    public function getAllPrivateTemplate()
    {
        return $this->request('/cgi-bin/template/get_all_private_template');
    }

    /**
     * 删除模板
     * @param string $templateID 公众帐号下模板消息ID
     * @throws Exception
     * @throws ModelException
     */
    public function delPrivateTemplate(string $templateID)
    {
        $this->request('/cgi-bin/template/del_private_template', ['template_id' => $templateID]);
    }

    /**
     * 发送模板消息
     * $url和$miniProgram都是非必填字段，若都不传则模板无跳转；若都传，会优先跳转至小程序
     * @param string $toUser 接收者openid
     * @param string $templateID 模板ID
     * @param array $data 消息数据
     * @param string $url 消息跳转链接
     * @param array $miniProgram 跳小程序所需数据
     * @return string
     * @throws Exception
     * @throws ModelException
     */
    public function send(string $toUser, string $templateID, array $data, string $url = '', array $miniProgram = [])
    {
        $data = [
            'touser' => $toUser,
            'template_id' => $templateID,
            'data' => $data
        ];
        $url and $data['url'] = $url;
        $miniProgram and $data['miniprogram'] = $miniProgram;
        return $this->request('/cgi-bin/message/template/send', $data)['msgid'] ?? '';
    }

    /**
     * 推送一次性订阅模板消息
     * @param string $toUser 接收者openid
     * @param string $templateID 模板ID
     * @param int $scene 订阅场景值
     * @param string $title 消息标题。15字以内
     * @param array $data 消息数据
     * @param string $url 消息跳转链接
     * @param array $miniProgram 跳小程序所需数据
     * @throws Exception
     * @throws ModelException
     */
    public function sendSubscribe(
        string $toUser,
        string $templateID,
        int $scene,
        string $title,
        array $data,
        string $url = '',
        array $miniProgram = []
    ) {
        $data = [
            'touser' => $toUser,
            'template_id' => $templateID,
            'scene' => $scene,
            'title' => $title,
            'data' => $data
        ];
        $url and $data['url'] = $url;
        $miniProgram and $data['miniprogram'] = $miniProgram;
        $this->request('/cgi-bin/message/template/subscribe', $data)['msgid'];
    }

    /**
     * 生成一次性订阅消息的授权链接
     * @param string $templateID 订阅消息模板ID
     * @param int $scene 订阅场景值。重定向后会带上scene参数，开发者可以填0-10000的整形值，用来标识订阅场景值
     * @param string $redirect 授权后重定向的回调地址
     * @param string $reserved 用于保持请求和回调的状态，授权请后原样带回。开发者可以填写a-zA-Z0-9的参数值，最多128字节
     * @return string 返回生成的URL，使用微信客户端访问
     */
    public function subscribeURL(string $templateID, int $scene, string $redirect, string $reserved = '')
    {
        $query = [
            'action' => 'get_confirm',
            'appid' => $this->common->config('appid'),
            'scene' => $scene,
            'template_id' => $templateID,
            'redirect_url' => $redirect,
        ];
        $reserved and $query['reserved'] = $reserved;
        return 'https://mp.weixin.qq.com/mp/subscribemsg' . http_build_query($reserved) . '#wechat_redirect';
    }
}
