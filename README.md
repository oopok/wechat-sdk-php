足够简洁的微信公众平台接口封装，只需要简单的配置即可轻松使用公众平台接口

**目前项目处于开发阶段，部分功能未开发完成，且未经全面测试，生产环境及商业项目请谨慎使用！**

_PS: 使用此工具前需要对公众号开发流程有一定了解，或结合微信官方文档使用_

## 运行环境
- PHP 7.0+
- OpenSSL扩展
- SimpleXML扩展
- libxml扩展
- Composer

> PHP7已全面流行，PHP官方也不再提供PHP5的支持，因此项目不会考虑PHP7以下版本的兼容

## 安装
```
composer require yuanshe/wechat-sdk @dev
```

## 基本示例
示例代码在`example`目录下，使用方式参考 [example说明](example/README.md)

## 开始
*Tips: 项目主命名空间为`Yuanshe\WeChatSDK`，说明中出现的类名称均省略命名空间*

### 初始化

使用任何功能前，要实例化一个`WeChat`对象，所有功能都是通过这个对象调用

```php
<?php

use Yuanshe\WeChatSDK\WeChat;

$wechat = new WeChat($config, $cacheClass);

```
- **config** array类型。公众平台配置，配置项参考 [配置](#config-explain)
- **cacheClass** string类型。缓存类类名。由于使用微信公众平台接口时，需要对access token等数据进行缓存。开发者需要编写一个实现`CacheInterface`接口的类，并将完整类名传入到该参数中，以供内部调用。参考 [缓存类](#cache-explain)

### <span id="config-explain">配置</span>
配置参数以键值对数组的形式传入，以下为配置项列表:

|参数|类型|必填|默认值|说明|
|:---:|:---:|:---:|:---:|:---:|
|appid|string|是|无|可在公众平台查看|
|appsecret|string|是|无|可在公众平台生成|
|account|string|消息通知必填|无|公众平台"公众号设置"中的"微信号"或"原始ID"|
|token|string|消息通知必填|无|与公众平台"服务器配置"中的Token一致即可|
|encrypt|bool|否|true|是否开启消息加密(公众平台安全模式为兼容模式时此选项才有效，否则根据公众平台自身设置决定是否加密)|
|ase_key|string|开启消息加密时必填|无|消息加解密密钥|
|cache_prefix|string|是|无|缓存前缀。请务必确保前缀在项目中的唯一性，防止与项目中其他缓存冲突|
|timeout|int|否|0|接口请求超时时间，0为永不超时|
|ssl_verify|bool|否|true|是否启用SSL证书验证，生产环境下建议开启|
|api_domain|string|否|api.weixin.qq.com|公众平台接口域名。使用默认配置即可，也可根据微信官方文档所列出的节点填写|

### <span id="cache-explain">缓存类</span>
开发者需要编写一个实现`CacheInterface`接口的类，可参考`example/Cache` _(该文件仅供参考，实际开发时应尽量利用项目、框架中现有的缓存功能)_

#### 需要实现的方法:

- `__construct(string $prefix = '')` _构造函数_
  - **prefix** 缓存前缀。传入的值为config中的cache_prefix配置项，在实现缓存功能时请务必使用该前缀，确保缓存的唯一性，防止与项目中其它缓存重名
<br/><br/>
- `get(string $name)` _获取一条缓存数据_
  - **name** 缓存名称
  - **@return** 返回缓存数据，若缓存不存在或已过期则返回null
<br/><br/>
- `put(string $name, $value, int $seconds): bool` _写入一条缓存_
  - **name** 缓存名称
  - **value** 缓存内容
  - **seconds** 有效时长（秒）
  - **@return** 成功返回true，失败返回false
<br/><br/>
- `del(string $name): bool` _删除一条缓存_
  - **name** 缓存名称
  - **@return** 成功返回true，失败返回false

## 接口调用
### 基本用法
接口以Model Class的形式封装，通过`WeChat`对象以属性的形式直接调用。_注意：Model名称区分大小写，类名以大写开头，调用时需以小写开头_

#### Demo
获取关注用户列表:
```php
$user_list = $wechat->user->getList();
```
获取公众号菜单内容:
```php
$menu = $wechat->menu->get();
```
### Throws
如果公众平台接口返回错误码，程序会抛出`ModelException`异常，可以通过`getCode`和`getMessage`方法获取错误码和错误消息，`getModel`方法可以获取到Model名称

### Model列表
- `Core` 核心公共接口
- `Menu` 自定义菜单
- `Material` 素材管理
- `Comment` 图文消息留言管理
- `User` 用户管理
- `Tag` 用户标签管理
- `Template` 模板消息
- `OAuth` 微信网页OAuth2.0授权
- `CustomService` 客服系统
- `MassMessage` 群发消息

更多接口正在完善中...

## 消息通知
用于接收公众号的事件推送以及用户消息，并可以自动回复用户
### 验证通知来源
为确保进一步安全，可验证消息来源IP是否在微信服务器列表中。此步骤非必须
```php
<?php

if (!$wechat->checkNotifyIP($notifyIP)) {
    // 校验不通过
}

```
- **notifyIP** 消息通知来源IP(即请求的客户端IP),通常为`$_SERVER['REMOTE_ADDR']`
- **@return** 验证通过返回true，不通过返回false

### 接收消息
```php
<?php

use Yuanshe\WeChatSDK\Notify;

$notify = $wechat->notify($queries, $body);
if ($notify instanceof Notify) {
    // ...
} elseif (is_string($notify)) {
    echo $notify;
}

```
- **queries** array类型。请求URL的query部分，以数组键值对的形式传入。通常传入`$_GET`即可
- **body** string类型。请求体的原始数据，通常传入`file_get_contents('php://input')`即可
- **@return** 返回值有两种情况。返回string类型时，通知仅用作平台校验，将返回值原样输出即可。否则返回`Notify`对象，包含了本次消息的所有信息
- **@throws:**
  - **NotifyException** 消息验证不通过时会抛出该异常
  - **ConfigException** 配置参数出错时抛出该异常

### 处理消息
`Notify`对象中包含消息的所有信息，可用如下方法获取:
- `getType(): int` 消息的类型。为`Notify::TYPE_MESSAGE`(消息) 或 `Notify::TYPE_EVENT`(事件)的值
- `getSubType(): string` 通知的子类型，例如消息中的text、image，事件中的subscribe、scan等。所有值均为小写
- `getContent(string $name = '')` 传入`name`时返回消息对应字段的值，默认返回消息全部内容的数组
- `getFromUserName(): string` 获取通知发送者用户名。一般为用户的OpenID
- `getToUserName(): string` 获取通知接收者用户名。一般为公众号帐号，与account配置项一致
- `getCreateTime(): string` 获取消息的创建时间

### 回复消息
`Notify`类已封装消息回复方法，方法将数据处理成字符串后返回，开发者将其输出即可

方法用法及列表如下
```php
<?php

use Yuanshe\WeChatSDK\Notify;

if (
    $notify->getType() == Notify::TYPE_MESSAGE
    && $notify->getSubType() == 'text'
) {
    echo $notify->replyText('您输入了：' . $notify->getContent('Content'));
}

```
- `replyText(string $content)` 回复文本消息。传入消息文本
- `replyImage(string $mediaID)` 回复图片消息。传入图片素材的media_id
- `replyVoice(string $mediaID)` 回复语音消息。传入语音素材的media_id
- `replyVideo(string $mediaID, string $title = '', string $description = '')` 回复视频消息
  - **mediaID** 视频素材的media_id
  - **title** 视频标题
  - **description** 视频描述
- `replyNews(array $articles)` 回复图文消息。传入articles结构，格式请参考微信官方文档。(一次最多发送8篇文章)
- `replyMusic(string $title, string $description, string $musicURL, string $hqMusicURL, string $thumbMediaID = '')` 回复音乐消息
  - **title** 音乐标题
  - **description** 音乐描述
  - **musicURL** 音乐链接
  - **hqMusicURL** 高质量音乐链接，WIFI环境优先使用该链接播放音乐
  - **thumbMediaID** 缩略图的素材id，通过素材管理接口上传thumb类型素材，得到的id
- `replyTransferCustomerService(string $kfAccount = '')` 将消息转发到客服。传入客服账号。请参考客服消息文档
- `reply(string $msgType, array $content = [])` 消息回复通用方法。用于后期扩展，一般情况无需使用

## 授权登录
参考`example/oauth.php`

## JS-SDK
调用`$wechat->jsSdk`方法，将返回值转换为JS对象，前端传参给`wx.config`即可

参考`example/jssdk.php`

## 异常处理
此项目异常类的命名空间为`Yuanshe\WeChatSDK\Exception`，以下列出项目中定义的所有异常类，开发者可根据需要自行捕获处理

- **Exception** 其它异常类的基类
  - **ConfigException** 配置参数不正确时会抛出该异常
  - **NotifyException** 通知校验未通过时抛出该异常
  - **ModelException** 公众平台接口返回错误码时会抛出该异常。可以通过`getCode`和`getMessage`方法获取错误码和错误消息，`getModel`方法可以获取到Model名称

## License
LGPL