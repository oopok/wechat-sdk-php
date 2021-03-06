<?php

use Yuanshe\WeChatSDK\Exception\ModelException;
use Yuanshe\WeChatSDK\Exception\NotifyException;
use Yuanshe\WeChatSDK\Notify;

$wechat = require __DIR__ . '/init.php';

// 校验IP白名单加强安全性
if (!$wechat->checkNotifyIP($_SERVER['REMOTE_ADDR'])) {
    http_response_code(403);
    echo 'Unknown request origin';
    return;
}

try {
    $notify = $wechat->notify($_GET, file_get_contents('php://input'));
    if ($notify instanceof Notify) {
        if ($notify->getType() == Notify::TYPE_MESSAGE) {
            switch ($notify->getSubType()) {
                case 'text':
                    try {
                        $wechat->customService->typing($notify->getFromUserName());
                        sleep(1); //延迟一秒模拟正在输入状态
                    } catch (ModelException $e) {
                        if ($e->getCode() != 45081) {
                            throw $e;
                        }
                    }
                    echo $notify->replyText('您输入了：' . $notify->getContent('Content'));
                    break;
                case 'image':
                    $wechat->customService->sendMessage($notify->getFromUserName(), 'text', [
                        'content' => '您发送了一张图片'
                    ]);
                    echo $notify->replyImage($notify->getContent('MediaId'));
                    break;
                default:
                    echo $notify->replyText('该消息类型未能处理');
            }
        } elseif ($notify->getType() == Notify::TYPE_EVENT) {
            switch ($notify->getSubType()) {
                case 'subscribe':
                    $scene = $notify->getContent('EventKey');
                    echo $notify->replyText('欢迎您关注公众号' . ($scene ? "，场景值：$scene" : ''));
                    break;
                case 'scan':
                    echo $notify->replyText('二维码场景值：' . $notify->getContent('EventKey'));
                    break;
                case 'location':
                    $notify_content = $notify->getContent();
                    echo $notify->replyText("您当前的坐标为：{$notify_content['Longitude']},{$notify_content['Latitude']}");
                    break;
                default:
                    echo 'success';
            }
        }
    } elseif (is_string($notify)) {
        echo $notify;
    }
} catch (NotifyException $e) {
    echo $e->getMessage();
}
