<?php

use \Yuanshe\WeChatSDK\Model\OAuth;

$wechat = require __DIR__ . '/init.php';

$state = 'some_tag';

if (empty($_GET['code'])) {
    $current_url = "http://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}?{$_SERVER['QUERY_STRING']}";
    header('Location: ' . $wechat->oAuth->codeURL(OAuth::SCOPE_USER_INFO, $current_url, $state));
    return;
}
if (empty($_GET['state']) || $_GET['state'] != $state) {
    header('Content-Type: text/plain');
    echo 'Incorrect state';
} else {
    $user_info = $wechat->oAuth->getUserInfoByCode($_GET['code']);
    header('Content-Type: application/json');
    echo json_encode($user_info);
}
