<?php

namespace Yuanshe\WeChatSDK\Model;

use Yuanshe\WeChatSDK\Exception\Exception;
use Yuanshe\WeChatSDK\Exception\ModelException;
use Yuanshe\WeChatSDK\ModelBase;

/**
 * Class Tag
 * @package Yuanshe\WeChatSDK\Model
 * @license LGPL-3.0-or-later
 * @author Yuanshe <admin@ysboke.com>
 * @see https://mp.weixin.qq.com/wiki?t=resource/res_main&id=mp1421140837
 */
class Tag extends ModelBase
{
    /**
     * 创建标签
     * @param string $name 标签名（30个字符以内）
     * @return int 返回创建后的标签ID
     * @throws Exception
     * @throws ModelException
     */
    public function create(string $name)
    {
        return $this->request('/cgi-bin/tags/create', ['tag' => ['name' => $name]])['tag']['id'] ?? 0;
    }

    /**
     * 编辑标签
     * @param int $id 标签ID
     * @param string $name 标签名
     * @throws Exception
     * @throws ModelException
     */
    public function update(int $id, string $name)
    {
        $this->request('/cgi-bin/tags/update', ['tag' => ['id' => $id, 'name' => $name]]);
    }

    /**
     * 删除标签
     * @param int $id 标签ID
     * @throws Exception
     * @throws ModelException]
     */
    public function delete(int $id)
    {
        $this->request('/cgi-bin/tags/delete', ['tag' => ['id' => $id]]);
    }

    /**
     * 获取公众号已创建的标签
     * @return array
     * @throws Exception
     * @throws ModelException
     */
    public function getList()
    {
        return $this->request('/tags/get')['tags'];
    }

    /**
     * 获取用户的标签列表
     * @param string $openid 用户标识
     * @return array 返回标签ID列表
     * @throws Exception
     * @throws ModelException
     */
    public function getListByUser(string $openid)
    {
        return $this->request('/cgi-bin/tags/getidlist', ['openid' => $openid])['tagid_list'];
    }

    /**
     * 批量为用户加上标签
     * @param array $openidList 用户openid列表
     * @param int $tagID 标签ID
     * @throws Exception
     * @throws ModelException
     */
    public function taggingUsers(array $openidList, int $tagID)
    {
        $this->request('/cgi-bin/tags/members/batchtagging', ['openid_list' => $openidList, 'tagid' => $tagID]);
    }

    /**
     * 批量为用户取消标签
     * @param array $openidList 用户openid列表
     * @param int $tagID 标签ID
     * @throws Exception
     * @throws ModelException
     */
    public function cancelUsersTag(array $openidList, int $tagID)
    {
        $this->request('/cgi-bin/tags/members/batchuntagging', ['openid_list' => $openidList, 'tagid' => $tagID]);
    }
}
