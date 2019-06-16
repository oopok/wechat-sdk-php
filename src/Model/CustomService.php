<?php

namespace Yuanshe\WeChatSDK\Model;

use Psr\Http\Message\StreamInterface;
use Yuanshe\WeChatSDK\Common;
use Yuanshe\WeChatSDK\Exception\ConfigException;
use Yuanshe\WeChatSDK\Exception\Exception;
use Yuanshe\WeChatSDK\Exception\ModelException;
use Yuanshe\WeChatSDK\ModelBase;

/**
 * 客服管理接口
 * @package Yuanshe\WeChatSDK\Model
 * @license LGPL-3.0-or-later
 * @author Yuanshe <admin@ysboke.com>
 * @see https://mp.weixin.qq.com/wiki?t=resource/res_main&id=mp1458044813
 */
class CustomService extends ModelBase
{
    /**
     * CustomService constructor.
     * @param Common $common
     * @throws ConfigException
     */
    public function __construct(Common $common)
    {
        parent::__construct($common);
        $common->checkConfig(['account' => ['required' => true, 'nonempty' => true]]);
    }

    /**
     * 发送消息
     * @param string $toUser 用户openid
     * @param string $type 消息类型
     * @param array $content 消息内容
     * @throws Exception
     * @throws ModelException
     */
    public function sendMessage(string $toUser, string $type, array $content)
    {
        $this->request('/cgi-bin/message/custom/send', [
            'touser' => $toUser,
            'msgtype' => $type,
            $type => $content
        ]);
    }

    /**
     * 改变公众号窗口输入状态
     * @param string $toUser 用户openid
     * @param bool $typingStatus 改变的状态。true为正在输入，false为取消正在输入
     * @throws Exception
     * @throws ModelException
     */
    public function typing(string $toUser, bool $typingStatus = true)
    {
        $this->request('/cgi-bin/message/custom/typing', [
            'touser' => $toUser,
            'command' => $typingStatus ? 'Typing' : 'CancelTyping'
        ]);
    }

    /**
     * 添加客服帐号
     * @param string $account 客服帐号。必须是英文、数字或下划线，最多10个字符
     * @param string $nickname 客服昵称
     * @throws Exception
     * @throws ModelException
     */
    public function addAccount(string $account, string $nickname)
    {
        $this->request('/customservice/kfaccount/add', [
            'kf_account' => $this->getFullAccountName($account),
            'nickname' => $nickname
        ]);
    }

    /**
     * 微信邀请绑定客服帐号
     * @param string $account 客服帐号
     * @param string $inviteWX 接收绑定邀请的客服微信号
     * @throws Exception
     * @throws ModelException
     */
    public function inviteWorker(string $account, string $inviteWX)
    {
        $this->request('/customservice/kfaccount/inviteworker', [
            'kf_account' => $this->getFullAccountName($account),
            'invite_wx' => $inviteWX
        ]);
    }

    /**
     * 添加客服帐号并邀请绑定
     * @param string $account 客服帐号
     * @param string $nickname 客服昵称
     * @param string $inviteWX 接收绑定邀请的客服微信号
     * @throws Exception
     * @throws ModelException
     */
    public function addAccountForInvite(string $account, string $nickname, string $inviteWX)
    {
        $this->addAccount($account, $nickname);
        $this->inviteWorker($account, $inviteWX);
    }

    /**
     * 更新客服信息
     * @param string $account 客服帐号
     * @param string $nickname 要更新的昵称
     * @throws Exception
     * @throws ModelException
     */
    public function updateAccount(string $account, string $nickname)
    {
        $this->request('/customservice/kfaccount/update', [
            'kf_account' => $this->getFullAccountName($account),
            'nickname' => $nickname
        ]);
    }

    /**
     * @param string $account 客服帐号
     * @param string|resource|StreamInterface $media 素材的内容。可以是：二进制内容|fopen返回的资源|StreamInterface实例
     * @throws Exception
     * @throws ModelException
     */
    public function uploadAccountHeadImage(string $account, $media)
    {
        $this->request('/customservice/kfaccount/uploadheadimg', ['media' => $media], [
            'type' => Common::DATA_TYPE_MULTIPART,
            'query' => ['kf_account' => $this->getFullAccountName($account)]
        ]);
    }

    /**
     * 删除客服帐号
     * @param string $account 客服帐号
     * @throws Exception
     * @throws ModelException
     */
    public function delAccount(string $account)
    {
        $this->request('/customservice/kfaccount/del', [], [
            'query' => ['kf_account' => $this->getFullAccountName($account)]
        ]);
    }

    /**
     * 获取客服列表
     * @return array 返回客服信息列表
     * @throws Exception
     * @throws ModelException
     */
    public function getAccountList()
    {
        return $this->request('/cgi-bin/customservice/getkflist')['kf_list'] ?? [];
    }

    /**
     * 获取在线客服列表
     * @return array 返回在线客服列表
     * @throws Exception
     * @throws ModelException
     */
    public function getOnlineAccountList()
    {
        return $this->request('/cgi-bin/customservice/getkflist')['kf_online_list'] ?? [];
    }

    /**
     * 创建会话
     * 在客服和用户之间创建一个会话，指定的客服帐号必须已经绑定微信号且在线
     * @param string $csAccount 客服帐号
     * @param string $openid 用户openid
     * @throws Exception
     * @throws ModelException
     */
    public function createSession(string $csAccount, string $openid)
    {
        $this->request('/customservice/kfsession/create', [
            'kf_account' => $csAccount,
            'openid' => $openid
        ]);
    }

    /**
     * 关闭会话
     * @param string $csAccount 客服帐号
     * @param string $openid 用户openid
     * @throws Exception
     * @throws ModelException
     */
    public function closeSession(string $csAccount, string $openid)
    {
        $this->request('/customservice/kfsession/close', [
            'kf_account' => $csAccount,
            'openid' => $openid
        ]);
    }

    /**
     * 获取用户当前会话
     * @param string $openid 用户openid
     * @return array 返回用户当前会话的客服帐号与创建时间，若用户当前无会话，则客服帐号字段为空
     * @throws Exception
     * @throws ModelException
     */
    public function getSessionByUser(string $openid)
    {
        return $this->request('/customservice/kfsession/getsession', [], ['query' => ['openid' => $openid]]);
    }

    /**
     * 获取客服当前会话列表
     * @param string $csAccount 客服帐号
     * @return array
     * @throws Exception
     * @throws ModelException
     */
    public function getSessionListByCS(string $csAccount)
    {
        return $this->request('/customservice/kfsession/getsession', [], [
                'query' => ['kf_account' => $csAccount]
            ])['sessionlist'] ?? [];
    }

    /**
     * 获取未接入会话列表
     * @return array
     * @throws Exception
     * @throws ModelException
     */
    public function getWaitCase()
    {
        return $this->request('/customservice/kfsession/getwaitcase');
    }

    /**
     * 获取聊天记录
     * @param int $startTime 起始时间戳
     * @param int $endTime 结束时间戳
     * @param int $messageID 消息id顺序从小到大，从1开始
     * @param int $number 每次获取条数，最多10000条
     * @return array
     * @throws Exception
     * @throws ModelException
     */
    public function getMessageRecord(int $startTime, int $endTime, int $messageID, int $number)
    {
        return $this->request('/customservice/msgrecord/getmsglist', [
            'starttime' => $startTime,
            'endtime' => $endTime,
            'msgid' => $messageID,
            'number' => $number
        ]);
    }

    /**
     * 获取完整客服帐号(即客服帐号拼接上"@公众号帐号")
     * @param string $accountName 客服帐号
     * @return string
     */
    public function getFullAccountName(string $accountName)
    {
        return $accountName . '@' . $this->common->config('account');
    }
}
