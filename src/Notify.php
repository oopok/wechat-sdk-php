<?php

namespace Yuanshe\WeChatSDK;

/**
 * Class Notify
 * @package Yuanshe\WeChatSDK
 * @author Yuanshe <admin@ysboke.com>
 * @license LGPL-3.0-or-later
 */
class Notify
{
    const TYPE_MESSAGE = 0;
    const TYPE_EVENT = 1;

    protected $helper;
    private $enableEncrypt;

    protected $toUserName;
    protected $fromUserName;
    protected $createTime;
    protected $type;
    protected $subType;
    protected $content;

    /**
     * Message constructor.
     * @param NotifyHelper $helper
     * @param array $message 微信服务器传入的消息实体
     * @param bool $enableEncrypt 输出的消息是否加密
     */
    public function __construct(NotifyHelper $helper, array $message, bool $enableEncrypt)
    {
        $this->helper = $helper;
        $this->enableEncrypt = $enableEncrypt;
        $this->toUserName = $message['ToUserName'];
        $this->fromUserName = $message['FromUserName'];
        $this->createTime = $message['CreateTime'];
        if ($message['MsgType'] == 'event') {
            $this->type = self::TYPE_EVENT;
            $this->subType = strtolower($message['Event']);
            unset($message['Event']);
        } else {
            $this->type = self::TYPE_MESSAGE;
            $this->subType = $message['MsgType'];
        }
        unset($message['ToUserName'], $message['FromUserName'], $message['CreateTime'], $message['MsgType']);
        $this->content = $message;
    }

    /**
     * 获取通知类型
     * @return int 返回当前类常量TYPE_*中的值
     */
    public function getType(): int
    {
        return $this->type;
    }

    /**
     * 获取通知子类型
     * @return string
     */
    public function getSubType(): string
    {
        return $this->subType;
    }

    /**
     * 获取消息内容
     * @param string $name 字段名，留空则返回全部
     * @return array|string
     */
    public function getContent(string $name = '')
    {
        return $name ? $this->content[$name] : $this->content;
    }

    /**
     * 获取发送者用户名
     * @return string
     */
    public function getFromUserName(): string
    {
        return $this->fromUserName;
    }

    /**
     * 获取接受者用户名
     * @return string
     */
    public function getToUserName(): string
    {
        return $this->toUserName;
    }

    /**
     * 获取消息创建时间
     * @return string
     */
    public function getCreateTime(): string
    {
        return $this->createTime;
    }

    /**
     * 回复文本消息
     * @param string $content 文本内容
     * @return string
     */
    public function replyText(string $content)
    {
        return $this->reply('text', [
            'Content' => $content
        ]);
    }

    /**
     * 回复图片消息
     * @param string $mediaID 图片素材media_id
     * @return string
     */
    public function replyImage(string $mediaID)
    {
        return $this->reply('image', [
            'Image' => ['MediaId' => $mediaID]
        ]);
    }

    /**
     * 回复语音消息
     * @param string $mediaID 语音素材media_id
     * @return string
     */
    public function replyVoice(string $mediaID)
    {
        return $this->reply('voice', [
            'Voice' => ['MediaId' => $mediaID]
        ]);
    }

    /**
     * 回复视频消息
     * @param string $mediaID 视频素材media_id
     * @param string $title 视频标题
     * @param string $description 视频描述
     * @return string
     */
    public function replyVideo(string $mediaID, string $title = '', string $description = '')
    {
        $message = [
            'Video' => ['MediaId' => $mediaID]
        ];
        $title and $message['Video']['Title'] = $title;
        $description and $message['Video']['Description'] = $description;
        return $this->reply('video', $message);
    }

    /**
     * 回复图文消息，一次最多发送8篇文章
     * @param array $articles 文章列表
     * @return string
     */
    public function replyNews(array $articles)
    {
        return $this->reply('news', [
            'ArticleCount' => count($articles),
            'Articles' => $articles
        ]);
    }

    /**
     * 回复音乐消息
     * @param string $title 音乐标题
     * @param string $description 音乐描述
     * @param string $musicURL 音乐链接
     * @param string $hqMusicURL 高质量音乐链接，WIFI环境优先使用该链接播放音乐
     * @param string $thumbMediaID 缩略图的素材id，通过素材管理接口上传thumb类型素材，得到的id
     * @return string
     */
    public function replyMusic(
        string $title,
        string $description,
        string $musicURL,
        string $hqMusicURL,
        string $thumbMediaID = ''
    ) {
        $message = [
            'Music' => [
                'Title' => $title,
                'Description' => $description,
                'MusicUrl' => $musicURL,
                'HQMusicUrl' => $hqMusicURL
            ]
        ];
        $thumbMediaID and $message['Music']['ThumbMediaId'] = $thumbMediaID;
        return $this->reply('music', $message);
    }

    /**
     * 将消息转发到客服
     * @param string $kfAccount 指定会话接入的客服账号
     * @return string
     */
    public function replyTransferCustomerService(string $kfAccount = '')
    {
        return $this->reply(
            'transfer_customer_service',
            $kfAccount ? ['TransInfo' => ['KfAccount' => $kfAccount]] : []
        );
    }

    /**
     * 消息回复通用方法
     * @param string $msgType
     * @param array $content
     * @return string
     */
    public function reply(string $msgType, array $content = []): string
    {
        $content = [
                'MsgType' => $msgType,
                'ToUserName' => $this->fromUserName,
                'FromUserName' => $this->toUserName,
                'CreateTime' => time()
            ] + $content;
        $xml = NotifyHelper::arrayToXml($content);
        return $this->enableEncrypt ? $this->helper->packingEncrypt($this->helper->encrypt($xml)) : $xml;
    }
}
