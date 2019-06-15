<?php

namespace Yuanshe\WeChatSDK\Model;

use Yuanshe\WeChatSDK\Exception\Exception;
use Yuanshe\WeChatSDK\Exception\ModelException;
use Yuanshe\WeChatSDK\ModelBase;

/**
 * 用户管理接口
 * Class User
 * @package Yuanshe\WeChatSDK\Model
 * @license LGPL-3.0-or-later
 * @author Yuanshe <admin@ysboke.com>
 */
class User extends ModelBase
{
    /**
     * 获取用户基本信息
     * @param string $openid 普通用户的标识，对当前公众号唯一
     * @param string $lang 返回国家地区语言版本，zh_CN 简体，zh_TW 繁体，en 英语
     * @return array
     * @throws Exception
     * @throws ModelException
     */
    public function getInfo(string $openid, string $lang = 'zh_CN')
    {
        return $this->request('/cgi-bin/user/info', [], ['query' => ['openid' => $openid, 'lang' => $lang]]);
    }

    /**
     * 批量获取用户基本信息
     * @param array $userList 要查询的用户列表，列表的每一项包含openid和lang字段
     * @return array
     * @throws Exception
     * @throws ModelException
     */
    public function getInfoList(array $userList)
    {
        return $this->request('/cgi-bin/user/info/batchget', ['user_list' => $userList]);
    }

    /**
     * 设置用户备注名
     * @param string $openid 用户标识
     * @param string $remark 新的备注名，长度必须小于30字符
     * @throws Exception
     * @throws ModelException
     */
    public function updateRemark(string $openid, string $remark)
    {
        $this->request('/cgi-bin/user/info/updateremark', ['openid' => $openid, 'remark' => $remark]);
    }

    /**
     * 获取用户列表
     * @param string $nextOpenid 第一个拉取的openid，不填默认从头开始拉取
     * @return array 返回用户的openid列表及接口附加信息
     * @throws Exception
     * @throws ModelException
     */
    public function getList(string $nextOpenid = '')
    {
        return $this->request('/cgi-bin/user/get', [], ['query' => $nextOpenid ? ['next_openid' => $nextOpenid] : []]);
    }

    /**
     * 获取指定标签下的用户列表
     * @param int $tagID 标签ID
     * @param string $nextOpenid 第一个拉取的OPENID，不填默认从头开始拉取
     * @return array 返回用户的openid列表及接口附加信息
     * @throws Exception
     * @throws ModelException
     */
    public function getListByTag(int $tagID, string $nextOpenid = '')
    {
        $data = ['tagid' => $tagID];
        $nextOpenid and $data['next_openid'] = $nextOpenid;
        return $this->request('/cgi-bin/user/tag/get', $data);
    }

    /**
     * 拉黑用户
     * @param array $openidList 要拉黑用户的openid列表
     * @throws Exception
     * @throws ModelException
     */
    public function blacklistUsers(array $openidList)
    {
        $this->request('/cgi-bin/tags/members/batchblacklist', ['openid_list' => $openidList]);
    }

    /**
     * 取消拉黑用户
     * @param array $openidList 要拉黑用户的openid列表
     * @throws Exception
     * @throws ModelException
     */
    public function cancelBlacklistUsers(array $openidList)
    {
        $this->request('/cgi-bin/tags/members/batchunblacklist', ['openid_list' => $openidList]);
    }

    /**
     * 获取黑名单列表
     * @param string $beginOpenid 起始的openid，可以将上一次调用得到的返回中的next_openid的值，传入下一次调用的$beginOpenid
     * @return array
     * @throws Exception
     * @throws ModelException
     */
    public function getBlacklist(string $beginOpenid = '')
    {
        return $this->request(
            '/cgi-bin/tags/members/getblacklist',
            $beginOpenid ? ['begin_openid' => $beginOpenid] : []
        );
    }
}
