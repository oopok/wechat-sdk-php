<?php

namespace Yuanshe\WeChatSDK\Model;

use Yuanshe\WeChatSDK\Exception\Exception;
use Yuanshe\WeChatSDK\Exception\ModelException;
use Yuanshe\WeChatSDK\ModelBase;

/**
 * 群发消息接口
 * @package Yuanshe\WeChatSDK\Model
 * @license LGPL-3.0-or-later
 * @author Yuanshe <admin@ysboke.com>
 * @see https://mp.weixin.qq.com/wiki?t=resource/res_main&id=mp1433751277
 */
class MassMessage extends ModelBase
{
    /**
     * 根据标签群发
     * @param string $type 消息类型
     * @param array $content 消息内容
     * @param int $tagID 标签ID。为0时发送到全部用户
     * @param bool $ignoreReprint 图文消息被判定为转载时，是否继续群发。 1为继续群发（转载），0为停止群发
     * @param string $customID 自定义ID。用于防止重复发送消息，长度不得超过64 byte。默认以数据摘要作为自定义ID
     * @return array 返回消息ID等信息
     * @throws Exception
     * @throws ModelException
     */
    public function sendByTag(
        string $type,
        array $content,
        int $tagID = 0,
        bool $ignoreReprint = true,
        string $customID = ''
    ) {
        $data = [
            'msgtype' => $type,
            $type => $content,
            'filter' => [
                'is_to_all' => !$tagID
            ]
        ];
        $tagID and $data['filter']['tag_id'] = $tagID;
        $ignoreReprint and $data['send_ignore_reprint'] = 1;
        $customID and $data['clientmsgid'] = $customID;
        return $this->request('/cgi-bin/message/mass/sendall', $data);
    }

    /**
     * 根据openid列表群发
     * @param string $type 消息类型
     * @param array $content 消息内容
     * @param array $openidList 接收消息用户的openid
     * @param bool $ignoreReprint 图文消息被判定为转载时，是否继续群发。 1为继续群发（转载），0为停止群发
     * @param string $customID 自定义ID。用于防止重复发送消息，长度不得超过64 byte。默认以数据摘要作为自定义ID
     * @return array 返回消息ID等信息
     * @throws Exception
     * @throws ModelException
     */
    public function sendByOpenidList(
        string $type,
        array $content,
        array $openidList,
        bool $ignoreReprint = true,
        string $customID = ''
    ) {
        $data = [
            'msgtype' => $type,
            $type => $content,
            'touser' => $openidList
        ];
        $ignoreReprint and $data['send_ignore_reprint'] = 1;
        $customID and $data['clientmsgid'] = $customID;
        return $this->request('/cgi-bin/message/mass/send', $data);
    }

    /**
     * 发送群发消息预览
     * @param string $type 消息类型
     * @param array $content 消息内容
     * @param string $toUser 接收消息预览用户的openid
     * @throws Exception
     * @throws ModelException
     */
    public function sendPreview(string $type, array $content, string $toUser)
    {
        $this->request('/cgi-bin/message/mass/preview', [
            'msgtype' => $type,
            $type => $content,
            'touser' => $toUser
        ]);
    }

    /**
     * 删除群发
     * @param int $id 发送出去的消息ID。只能传入图文消息和视频消息ID
     * @param int $articleIndex 要删除的文章在图文消息中的位置，第一篇编号为1，该字段不填或填0会删除全部文章
     * @throws Exception
     * @throws ModelException
     */
    public function delete(int $id, int $articleIndex = 0)
    {
        $this->request('/cgi-bin/message/mass/delete', [
            'msg_id' => $id,
            'article_idx' => $articleIndex
        ]);
    }

    /**
     * 查询群发消息发送状态
     * @param int $id 群发消息后返回的消息id
     * @return string 返回消息的发送状态。SEND_SUCCESS表示发送成功，SENDING表示发送中，SEND_FAIL表示发送失败，DELETE表示已删除
     * @throws Exception
     * @throws ModelException
     */
    public function getStatus(int $id)
    {
        return $this->request('/cgi-bin/message/mass/get', ['msg_id' => $id])['msg_status'] ?? '';
    }

    /**
     * 设置群发速度
     * @param int $speed 群发速度的级别。传入0~4之间的整数，数字越大表示群发速度越慢
     * @throws Exception
     * @throws ModelException
     */
    public function setSpeed(int $speed)
    {
        $this->request('/cgi-bin/message/mass/speed/set', ['speed' => $speed]);
    }

    /**
     * 获取当前群发速度
     * @return array 返回群发速度级别以及对应的速度值(万/分钟)
     * @throws Exception
     * @throws ModelException
     */
    public function getSpeed()
    {
        return $this->request('/cgi-bin/message/mass/speed/get');
    }
}
