<?php

namespace Yuanshe\WeChatSDK\Model;

use Yuanshe\WeChatSDK\Exception\Exception;
use Yuanshe\WeChatSDK\ModelBase;

/**
 * 自定义菜单接口
 * @package Yuanshe\WeChatSDK\Model
 * @license LGPL-3.0-or-later
 * @author Yuanshe <admin@ysboke.com>
 * @see https://mp.weixin.qq.com/wiki?t=resource/res_main&id=mp1421141013
 */
class Menu extends ModelBase
{
    /**
     * 自定义菜单创建接口
     * @param array $data
     * @throws Exception
     */
    public function create(array $data)
    {
        $this->request('/cgi-bin/menu/create', $data);
    }

    /**
     * 自定义菜单查询接口
     * @return array
     * @throws Exception
     */
    public function get()
    {
        return $this->request('/cgi-bin/menu/get');
    }

    /**
     * 自定义菜单删除接口
     * @throws Exception
     */
    public function delete()
    {
        $this->request('/cgi-bin/menu/delete');
    }

    /**
     * 获取自定义菜单配置接口
     * @return array
     * @throws Exception
     */
    public function getCurrentSelfMenuInfo()
    {
        return $this->request('/cgi-bin/get_current_selfmenu_info');
    }

    /**
     * 创建个性化菜单
     * @param array $data
     * @return int 返回menuid
     * @throws Exception
     */
    public function addConditional(array $data)
    {
        return $this->request('/cgi-bin/menu/addconditional', $data)['menuid'] ?? '';
    }

    /**
     * 删除个性化菜单
     * @param int $menuId
     * @throws Exception
     */
    public function delConditional(int $menuId)
    {
        $this->request('/cgi-bin/menu/delete', ['menuid' => $menuId]);
    }

    /**
     * 测试个性化菜单匹配结果
     * @param string $userId 粉丝OpenID或微信号
     * @return array
     * @throws Exception
     */
    public function tryMatch(string $userId)
    {
        return $this->request('/cgi-bin/menu/trymatch', ['user_id' => $userId]);
    }
}
