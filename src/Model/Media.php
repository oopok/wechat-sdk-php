<?php

namespace Yuanshe\WeChatSDK\Model;

use Psr\Http\Message\StreamInterface;
use Yuanshe\WeChatSDK\Common;
use Yuanshe\WeChatSDK\Exception\Exception;
use Yuanshe\WeChatSDK\Exception\ModelException;
use Yuanshe\WeChatSDK\ModelBase;

/**
 * 素材管理接口
 * @package Yuanshe\WeChatSDK\Model
 * @license LGPL-3.0-or-later
 * @author Yuanshe <admin@ysboke.com>
 * @see https://mp.weixin.qq.com/wiki?t=resource/res_main&id=mp1444738726
 */
class Media extends ModelBase
{
    /***素材格式类型***/
    const TYPE_IMAGE = 'image'; //max:2MB; type:PNG,JPEG,JPG,GIF
    const TYPE_VOICE = 'voice'; //max:2MB,6s; type:AMR,MP3
    const TYPE_VIDEO = 'video'; //max:10MB; type:MP4
    const TYPE_THUMB = 'thumb'; //max:64KB; type:JPG
    const TYPE_NEWS = 'news'; //array

    /**
     * 上传临时素材
     * 临时素材只保存三天
     * @param string $type 素材类型（不能为news类型）
     * @param string|resource|StreamInterface $content 素材的内容。可以是：二进制内容|fopen返回的资源|StreamInterface实例
     * @return string 返回media_id
     * @throws Exception
     * @throws ModelException
     */
    public function upload(string $type, $content)
    {
        $data = ['media' => $content];
        $result = $this->request('/cgi-bin/media/upload', $data, [
            'query' => ['type' => $type],
            'type' => Common::DATA_TYPE_MULTIPART
        ]);
        return $result['media_id'] ?? $result['thumb_media_id'] ?? '';
    }

    /**
     * 获取临时素材
     * @param string $mediaID 待获取素材的media_id
     * @return StreamInterface|string video类型素材返回资源URL，其它类型素材返回StreamInterface实例
     * @throws Exception
     * @throws ModelException
     */
    public function get(string $mediaID)
    {
        $result = $this->request("/cgi-bin/media/get", [], ['query' => ['media_id' => $mediaID]]);
        if (is_array($result) && isset($result['video_url'])) {
            return $result['video_url'];
        } else {
            return $result;
        }
    }

    /**
     * 上传图文素材
     * @see https://mp.weixin.qq.com/wiki?t=resource/res_main&id=mp1481187827_i0l21
     * @param array $articles 图文素材articles结构组
     * @return string 返回media_id
     * @throws Exception
     * @throws ModelException
     */
    public function uploadNews(array $articles)
    {
        return $this->request('/cgi-bin/media/uploadnews', ['articles' => $articles])['media_id'] ?? '';
    }

    /**
     * 上传图文消息内的图片
     * 仅支持jpg/png格式的文件
     * @param string|resource|StreamInterface $content 素材的内容。可以是：二进制内容|fopen返回的资源|StreamInterface实例
     * @return string 返回图片的URL
     * @throws Exception
     * @throws ModelException
     */
    public function uploadImg($content)
    {
        return $this->request(
                '/cgi-bin/media/uploadimg',
                ['media' => $content],
                ['type' => Common::DATA_TYPE_MULTIPART]
            )['url'] ?? '';
    }

    /**
     * 上传视频素材（用于群发消息）
     * @param string $mediaID 从已有素材中选择一个video类型的素材id传入
     * @param string $title 视频标题
     * @param string $description 视频描述
     * @return string 返回生成的素材ID
     * @throws Exception
     * @throws ModelException
     */
    public function uploadVideo(string $mediaID, string $title, string $description)
    {
        return $this->request('/cgi-bin/media/uploadvideo', [
                'media_id' => $mediaID,
                'title' => $title,
                'description' => $description
            ])['media_id'] ?? '';
    }
    
    /**
     * 获取JSSDK高清语音素材
     * @param string $mediaID 待获取素材的media_id
     * @return StreamInterface
     * @throws Exception
     * @throws ModelException
     */
    public function getJSSDK(string $mediaID)
    {
        return $this->request("/cgi-bin/media/get/jssdk", [], ['query' => ['media_id' => $mediaID]]);
    }

    /**
     * 上传永久素材
     * 视频、图文素材不使用此接口，请分别使用addVideo、addNews接口上传
     * @param string $type 素材类型
     * @param string|resource|StreamInterface $content 素材的内容。可以是：二进制内容|fopen返回的资源|StreamInterface实例
     * @param string &$imageUrl image素材上传后的URL，此参数只有素材类型为image时才有效
     * @return string|bool 成功返回media_id，失败返回false
     * @throws Exception
     * @throws ModelException
     */
    public function addMaterial(string $type, $content, &$imageUrl = null)
    {
        $result = $this->request('/cgi-bin/material/add_material', ['media' => $content], [
            'query' => ['type' => $type],
            'type' => Common::DATA_TYPE_MULTIPART
        ]);
        empty($result['url']) or $imageUrl = $result['url'];
        return $result['media_id'] ?? '';
    }

    /**
     * 新增永久图文素材
     * @see https://mp.weixin.qq.com/wiki?t=resource/res_main&id=mp1444738729
     * @param array $articles 图文素材articles结构组
     * @return string 返回media_id
     * @throws Exception
     * @throws ModelException
     */
    public function addNews(array $articles)
    {
        return $this->request('/cgi-bin/material/add_news', ['articles' => $articles])['media_id'] ?? '';
    }

    /**
     * 修改永久图文素材
     * @see https://mp.weixin.qq.com/wiki?t=resource/res_main&id=mp1444738732
     * @param string $mediaID 图文素材的media_id
     * @param int $index 要修改的文章在图文素材中的索引，0为素材中第一篇文章
     * @param array $article 图文素材article结构
     * @throws Exception
     * @throws ModelException
     */
    public function updateNews(string $mediaID, int $index, array $article)
    {
        $this->request('/cgi-bin/material/update_news', [
            'media_id' => $mediaID,
            'index' => $index,
            'articles' => $article
        ]);
    }

    /**
     * 上传永久视频素材
     * @param string|resource|StreamInterface $content 素材的内容。可以是：二进制内容|fopen返回的资源|StreamInterface实例
     * @param string $title 视频标题
     * @param string $introduction 视频描述
     * @return string media_id
     * @throws Exception
     * @throws ModelException
     */
    public function addVideo($content, string $title, string $introduction)
    {
        $data = [
            'media' => $content,
            'description' => json_encode(['title' => $title, 'introduction' => $introduction])
        ];
        return $this->request('/cgi-bin/material/add_material', $data, [
                'query' => ['type' => 'video'],
                'type' => Common::DATA_TYPE_MULTIPART
            ])['media_id'] ?? '';
    }

    /**
     * 获取永久素材
     * @param string $mediaID 待获取素材的media_id
     * @return array|StreamInterface 图文素材与视频素材返回相应结构，其它类型素材返回StreamInterface实例
     * @throws Exception
     * @throws ModelException
     */
    public function getMaterial(string $mediaID)
    {
        return $this->request('/cgi-bin/material/get_material', ['media_id' => $mediaID]);
    }

    /**
     * 删除永久素材
     * 请谨慎操作本接口，因为它可以删除公众号在公众平台官网素材管理模块中新建的图文消息、语音、视频等素材
     * @param string $mediaID 待删除素材的media_id
     * @throws Exception
     * @throws ModelException
     */
    public function delMaterial(string $mediaID)
    {
        $this->request('/cgi-bin/material/del_material', ['media_id' => $mediaID]);
    }

    /**
     * 获取永久素材的总数
     * @return array 返回一个数组，分别包含语音、视频、图片和图文素材的总数
     * @throws Exception
     * @throws ModelException
     */
    public function getMaterialCount()
    {
        return $this->request('/cgi-bin/material/get_materialcount');
    }

    /**
     * 获取永久素材列表
     * @param string $type 素材类型，支持image,video,voice,news
     * @param int $offset 获取素材的偏移，0表示从第一个素材开始获取
     * @param int $count 获取素材的数量,支持1-20之间的整数
     * @return array 成功返回一个包含素材列表的数组
     * @throws Exception
     * @throws ModelException
     */
    public function getMaterialList($type, $offset, $count)
    {
        return $this->request('/cgi-bin/material/batchget_material', [
            'type' => $type,
            'offset' => $offset,
            'count' => $count
        ]);
    }
}
