<?php

$wechat = require __DIR__ . '/init.php';

$result = $wechat->user->getList();
header('Content-Type: application/json');
echo json_encode($result);
