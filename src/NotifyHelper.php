<?php

namespace Yuanshe\WeChatSDK;

use stdClass;

/**
 * Class NotifyHelper
 * @package Yuanshe\WeChatSDK
 * @author Yuanshe <admin@ysboke.com>
 * @license LGPL-3.0-or-later
 */
class NotifyHelper
{
    protected $common;

    public function __construct(Common $common)
    {
        $this->common = $common;
    }

    /**
     * 将XML字符串转换为关联数组
     * @param string $xml XML原文
     * @return array|bool 成功返回转换后的数组，失败返回false
     */
    public static function xmlToArray(string $xml)
    {
        $data = @simplexml_load_string($xml, null, LIBXML_NOCDATA);
        return $data ? self::objectToArray($data) : false;
    }

    /**
     * 将数组转换为XML字符串
     * @param array $params
     * @param string $rootElement 文档根元素
     * @return string 返回转换后的XML
     */
    public static function arrayToXml(array $params, string $rootElement = 'xml')
    {
        $method = __METHOD__;
        array_walk($params, function ($v, $k) use (&$xml, $method) {
            is_int($k) and $k = 'item';
            if (is_array($v)) {
                $xml .= $method($v, $k);
            } else {
                $v = htmlspecialchars($v, ENT_XML1);
                $xml .= "<$k>$v</$k>";
            }
        });
        return "<$rootElement>$xml</$rootElement>";
    }

    /**
     * 将对象转换为数组
     * @param $data
     * @return array|bool
     */
    public static function objectToArray($data)
    {
        static $method = __METHOD__;
        if (!is_object($data)) {
            return false;
        }
        $result = (array)$data;
        foreach ($result as &$value) {
            $value instanceof stdClass and $value = $method($value);
        }
        return $result;
    }

    /**
     * 将多个字符串自然排序后拼接使用sha1算法加密
     * @param string ...$strs
     * @return string
     */
    public static function sha1(string ...$strs)
    {
        sort($strs, SORT_STRING);
        return sha1(implode($strs));
    }

    /**
     * 加密消息
     * @param string $plainText 消息明文
     * @return string 返回加密后的字符串
     */
    public function encrypt(string $plainText)
    {
        //得到随机字符串
        $random = '';
        for ($i = 0; $i < 16; $i++) {
            $random .= chr(mt_rand(1, 255));
        }
        //拼接随机字符串、网络字节序、消息文本与APPID
        $data = $random . pack('N', strlen($plainText)) . $plainText . $this->common->config('appid');
        //填充补位字符
        $fill_length = 32 - (strlen($data) % 32);
        $fill_length or $fill_length = 32;
        $data .= str_repeat(chr($fill_length), $fill_length);
        //加密数据
        $key = base64_decode($this->common->config('ase_key') . '=');
        return openssl_encrypt($data, 'AES-256-CBC', substr($key, 0, 32), OPENSSL_ZERO_PADDING, substr($key, 0, 16));
    }

    /**
     * 解密消息并验证消息的可靠性
     * @param string $cipherText 消息密文
     * @return string|bool 验证成功返回解密后的字符串，失败返回false
     */
    public function decrypt(string $cipherText)
    {
        // 解密数据
        $key = base64_decode($this->common->config('ase_key') . '=');
        $data = openssl_decrypt($cipherText, 'AES-256-CBC', $key, OPENSSL_ZERO_PADDING, substr($key, 0, 16));
        $pad = ord(substr($data, -1));
        // 删除补位字节
        if ($pad < 1 || $pad > 32) {
            $pad = 0;
        }
        $data = substr($data, 0, strlen($data) - $pad);
        // 去除16位随机字符串
        if (strlen($data) < 16) {
            return false;
        }
        $data = substr($data, 16);
        // 获取网络字节序中储存的XML长度，用于截取XML主体与APPID
        $xml_length = unpack("N", substr($data, 0, 4))[1];
        if ($this->common->config('appid') != substr($data, $xml_length + 4)) {
            return false;
        }
        return substr($data, 4, $xml_length);
    }

    /**
     * 打包加密数据为XML
     * @param string $cipherText 要打包的加密文本
     * @return string
     */
    public function packingEncrypt(string $cipherText)
    {
        $time = time();
        $nonce = substr(md5(mt_rand(0, mt_getrandmax())), mt_rand(0, 20), 10); //生成随机字符串
        $signature = self::sha1($cipherText, $this->common->config('token'), $time, $nonce);
        return /** @lang XML */ "<xml>
<Encrypt><![CDATA[$cipherText]]></Encrypt>
<MsgSignature><![CDATA[$signature]]></MsgSignature>
<TimeStamp>$time</TimeStamp>
<Nonce><![CDATA[$nonce]]></Nonce>
</xml>";
    }
}
