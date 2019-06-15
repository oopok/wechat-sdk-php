<?php

namespace Yuanshe\WeChatSDK\Model;

use Yuanshe\WeChatSDK\Exception\Exception;
use Yuanshe\WeChatSDK\Exception\ModelException;
use Yuanshe\WeChatSDK\ModelBase;

/**
 * 图文消息留言管理接口
 * Class Comment
 * @package Yuanshe\WeChatSDK\Model
 * @license LGPL-3.0-or-later
 * @author Yuanshe <admin@ysboke.com>
 * @see https://mp.weixin.qq.com/wiki?t=resource/res_main&id=mp1494572718_WzHIY
 */
class Comment extends ModelBase
{
    const TYPE_ALL = 0; // 全部评论
    const TYPE_ORDINARY = 1; // 普通评论
    const TYPE_ELECT = 2; // 精选评论

    /**
     * 打开已群发文章评论
     * @param int $msgDataID 群发返回的msg_data_id
     * @param int $index 用来指定第几篇图文，从0开始
     * @throws Exception
     * @throws ModelException
     */
    public function open(int $msgDataID, int $index = 0)
    {
        $this->request('/cgi-bin/comment/open', ['msg_data_id' => $msgDataID, 'index' => $index]);
    }

    /**
     * 关闭已群发文章评论
     * @param int $msgDataID 群发返回的msg_data_id
     * @param int $index 用来指定第几篇图文，从0开始
     * @throws Exception
     * @throws ModelException
     */
    public function close(int $msgDataID, int $index = 0)
    {
        $this->request('/cgi-bin/comment/close', ['msg_data_id' => $msgDataID, 'index' => $index]);
    }

    /**
     * 获取指定文章的评论数据
     * @param int $msgDataID 群发返回的msg_data_id
     * @param int $begin 起始索引
     * @param int $count 获取数目（>=50会被拒绝）
     * @param int $type 评论类型，可以取类常量TYPE_*中的值
     * @param int $index 用来指定第几篇图文，从0开始
     * @return array
     * @throws Exception
     * @throws ModelException
     */
    public function getList(int $msgDataID, int $begin, int $count, int $type = self::TYPE_ALL, int $index = 0)
    {
        return $this->request('/cgi-bin/comment/list', [
            'msg_data_id' => $msgDataID,
            'index' => $index,
            'begin' => $begin,
            'count' => $count,
            'type' => $type
        ]);
    }

    /**
     * 将评论标记精选
     * @param int $msgDataID 群发返回的msg_data_id
     * @param int $userCommentID 用户评论id
     * @param int $index 用来指定第几篇图文，从0开始
     * @throws Exception
     * @throws ModelException
     */
    public function markElect(int $msgDataID, int $userCommentID, int $index = 0)
    {
        $this->request('/cgi-bin/comment/markelect', [
            'msg_data_id' => $msgDataID,
            'index' => $index,
            'user_comment_id' => $userCommentID
        ]);
    }

    /**
     * 将评论取消精选
     * @param int $msgDataID 群发返回的msg_data_id
     * @param int $userCommentID 用户评论id
     * @param int $index 用来指定第几篇图文，从0开始
     * @throws Exception
     * @throws ModelException
     */
    public function unmarkElect(int $msgDataID, int $userCommentID, int $index = 0)
    {
        $this->request('/cgi-bin/comment/unmarkelect', [
            'msg_data_id' => $msgDataID,
            'index' => $index,
            'user_comment_id' => $userCommentID
        ]);
    }

    /**
     * 删除评论
     * @param int $msgDataID 群发返回的msg_data_id
     * @param int $userCommentID 用户评论id
     * @param int $index 用来指定第几篇图文，从0开始
     * @throws Exception
     * @throws ModelException
     */
    public function delete(int $msgDataID, int $userCommentID, int $index = 0)
    {
        $this->request('/cgi-bin/comment/delete', [
            'msg_data_id' => $msgDataID,
            'index' => $index,
            'user_comment_id' => $userCommentID
        ]);
    }

    /**
     * 回复评论
     * @param int $msgDataID 群发返回的msg_data_id
     * @param int $userCommentID 用户评论id
     * @param string $content 回复内容
     * @param int $index 用来指定第几篇图文，从0开始
     * @throws Exception
     * @throws ModelException
     */
    public function addReply(int $msgDataID, int $userCommentID, string $content, int $index = 0)
    {
        $this->request('/cgi-bin/comment/delete', [
            'msg_data_id' => $msgDataID,
            'index' => $index,
            'user_comment_id' => $userCommentID,
            'content' => $content
        ]);
    }

    /**
     * 删除评论
     * @param int $msgDataID 群发返回的msg_data_id
     * @param int $userCommentID 用户评论id
     * @param int $index 用来指定第几篇图文，从0开始
     * @throws Exception
     * @throws ModelException
     */
    public function deleteReply(int $msgDataID, int $userCommentID, int $index = 0)
    {
        $this->request('/cgi-bin/comment/reply/delete', [
            'msg_data_id' => $msgDataID,
            'index' => $index,
            'user_comment_id' => $userCommentID
        ]);
    }
}
