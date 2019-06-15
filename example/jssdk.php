<?php

require __DIR__ . '/init.php';

$current_url = "http://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";
$jssdk_config = $wechat->jsSDK($current_url, ['getNetworkType', 'getLocation', 'scanQRCode']);

?>
<!doctype html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>微信JS-SDK演示</title>
    <style>
        #tips {
            text-align: center;
        }

        .button {
            padding: 20px;
            text-align: center;
        }
    </style>
    <script src="//res.wx.qq.com/open/js/jweixin-1.4.0.js"></script>
    <script>
      wx.config(<?php echo json_encode($jssdk_config); ?>)

      wx.ready(function () {
        document.querySelector('#content').style.display = 'block'
        document.querySelector('#tips').style.display = 'none'
      })

      function getNetworkType () {
        wx.getNetworkType({
          success: function (res) {
            alert('网络状态:' + res.networkType)
          }
        })
      }

      function getLocation () {
        wx.getLocation({
          type: 'wgs84',
          success: function (res) {
            alert(`经度:${res.latitude},纬度:${res.longitude},精度:${res.accuracy},速度:${res.speed}.`)
          }
        })
      }
    </script>
</head>
<body>
<div id="tips">请在微信中打开</div>
<div id="content" style="display:none">
    <div class="button">
        <button onclick="getNetworkType()">获取网络状态</button>
    </div>
    <div class="button">
        <button onclick="getLocation()">获取地理位置</button>
    </div>
    <div class="button">
        <button onclick="wx.scanQRCode()">扫一扫</button>
    </div>
    <div class="button">
        <button onclick="wx.closeWindow()">关闭窗口</button>
    </div>
</div>
</body>
</html>