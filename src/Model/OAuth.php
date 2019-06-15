<?php

namespace Yuanshe\WeChatSDK\Model;

use Yuanshe\WeChatSDK\Exception\Exception;
use Yuanshe\WeChatSDK\Exception\ModelException;
use Yuanshe\WeChatSDK\ModelBase;

/**
 * 微信网页授权接口
 * @package Yuanshe\WeChatSDK\Model
 * @license LGPL-3.0-or-later
 * @author Yuanshe <admin@ysboke.com>
 * @see https://mp.weixin.qq.com/wiki?t=resource/res_main&id=mp1421140842
 */
class OAuth extends ModelBase
{

    const SCOPE_BASE = 'snsapi_base ';
    const SCOPE_USER_INFO = 'snsapi_userinfo';

    /**
     * 生成请求用户授权的链接，用于获取code
     * @param string $scope 应用授权作用域，snsapi_base
     * @param string $redirect 授权后重定向的回调链接地址
     * @param string $state 重定向后会带上state参数，开发者可以填写a-zA-Z0-9的参数值，最多128字节
     * @return string 返回生成的URL，使用微信客户端访问
     */
    public function codeURL(string $scope, string $redirect, string $state = '')
    {
        $query = [
            'appid' => $this->common->config('appid'),
            'redirect_uri' => $redirect,
            'response_type' => 'code',
            'scope' => $scope
        ];
        $state and $query['state'] = $state;
        return 'https://open.weixin.qq.com/connect/oauth2/authorize?' . http_build_query($query) . '#wechat_redirect';
    }

    /**
     * 通过code拉取用户信息
     * @param string $code 获取到的code
     * @param string $lang 返回国家地区语言版本，zh_CN 简体，zh_TW 繁体，en 英语
     * @return array
     * @throws Exception
     * @throws ModelException
     */
    public function getUserInfoByCode(string $code, string $lang = 'zh_CN')
    {
        $access_result = $this->getAccessToken($code);
        return $this->getUserInfo($access_result['openid'], $access_result['access_token'], $lang);
    }

    /**
     * @param string $refreshToken 通过getAccessToken获取到的refresh_token参数
     * @param string $lang 返回国家地区语言版本，zh_CN 简体，zh_TW 繁体，en 英语
     * @return array
     * @throws Exception
     * @throws ModelException
     */
    public function getUserInfoByRefreshToken(string $refreshToken, string $lang = 'zh_CN')
    {
        $access_result = $this->refreshAccessToken($refreshToken);
        return $this->getUserInfo($access_result['openid'], $access_result['access_token'], $lang);
    }

    /**
     * 拉取用户信息
     * @param string $openid 用户的唯一标识
     * @param string $accessToken 使用getAccessToken或refreshAccessToken中的值
     * @param string $lang 返回国家地区语言版本，zh_CN 简体，zh_TW 繁体，en 英语
     * @return array
     * @throws Exception
     * @throws ModelException
     */
    public function getUserInfo(string $openid, string $accessToken, string $lang = 'zh_CN')
    {
        return $this->request('/sns/userinfo', [], [
            'token' => false,
            'query' => [
                'openid' => $openid,
                'access_token' => $accessToken,
                'lang' => $lang
            ]
        ]);
    }

    /**
     * 获取OAuth2.0 access_token
     * @param string $code 获取到的code
     * @return array 返回access_token及附带数据
     * @throws Exception
     * @throws ModelException
     */
    public function getAccessToken(string $code)
    {
        return $this->request('/sns/oauth2/access_token', [], [
            'token' => false,
            'query' => [
                'appid' => $this->common->config('appid'),
                'secret' => $this->common->config('app_secret'),
                'code' => $code,
                'grant_type' => 'authorization_code'
            ]
        ]);
    }

    /**
     * 刷新access_token
     * @param string $refreshToken 通过getAccessToken获取到的refresh_token参数
     * @return array 返回access_token及附带数据
     * @throws Exception
     * @throws ModelException
     */
    public function refreshAccessToken(string $refreshToken)
    {
        return $this->request('/sns/oauth2/refresh_token', [], [
            'token' => false,
            'query' => [
                'appid' => $this->common->config('appid'),
                'grant_type' => 'refresh_token',
                'refresh_token' => $refreshToken
            ]
        ]);
    }

    /**
     * 校验access_token是否有效
     * @param string $openid
     * @param string $accessToken
     * @throws Exception
     * @throws ModelException
     */
    public function checkAccessToken(string $openid, string $accessToken)
    {
        $this->request('/sns/auth', [], [
            'token' => false,
            'query' => [
                'openid' => $openid,
                'access_token' => $accessToken
            ]
        ]);
    }
}
