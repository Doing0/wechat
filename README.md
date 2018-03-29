---
style: summer
---
# 说明文档dev-master
## 关于我
> 1. 此composer包主要是集成关于微信的一些常用开发如：`微信公众号二次开发`，`微信支付`，`微信手机web的分享`等。由于包含众多功能现只上传了`微信手机web的分享`。后期会持续跟进和更新。
> 2. 您在用此包时已默认您已会并成功配置了相关公众号信息且会使用composer
> 3. 安装命令`composer require doing/wechat 版本号`
> 4. 此包只能集成于ThinkPHP5里面：原因是使用了它的缓存机制和异常处理机制，如果想使用于其他框架也很简单，只需要把缓存机制和异常机制做一个更换处理即可(主要是编程思想)

## 微信手机web分享(发送给朋友，朋友圈等)
### 配置公众号
1. 如果只做jssdk相关需求就只需要appid和appsecret,至于怎么去公众号获取可以百度很多。
2. 配置安全域名

   ![微信公众号Js安全域配置](https://s1.ax1x.com/2018/03/28/9XGFXR.png)
### 服务器端
> 1. 任务是获取分享时需要的appid和签名等参数
> 2. 大概流程:通过appid和appsecret->获取AccessToken->获取jsapi的ticket->再签名返回
> 3. 开发前请在doing/wechat/config/WechatConfig.php 配置appid和appsecret

写一个接口获取参数
```
public function info()
{
    #说明
    // 在tp5框架全局配置文件的config.php查下全局返回模式参数是否是json如果不是的话先转成json再return
    //总之返回给客户端建议用json格式方便后续操作
    //'default_ajax_return'    => 'json',
    //用json_encode转json时一定给第二个参数,否则中文乱码
    //json_encode($exp,JSON_UNESCAPED_UNICODE);   #获取转义的ur并反转义
    //客户端传递过来的参数fullurl一定是要通过js的encodeURIComponent转义了的

    $fullurl = urldecode(input('fullurl'));
    #获取权限信息(直接调用包内方法即可)：
    //类的顶上一定保证use wechat\auth\WxAuth;
  try
  {
        return WxAuth::instance()->getInfo($fullurl);
    }
    catch (\Exception $e)
    {
        $exp['msg'] = $e->getMessage();
        $exp['code'] = $e->getCode();
        #TP5的返回方式 异常都在客户端的error回掉函数内处理
        return json($exp, $e->getCode());
        #非tp5的php都兼容的返回模式
        //header("status: ".$e->getCode()." wechat error");
        //return $exp;
  }//-try
}//pf
```
返回成功的数据格式如下(满足客户端调用微信jssdk时需要的数据结构)
```
{
   appId: 'you appId',// 公众号的唯一标识
   timestamp:'12345678',// 生成签名的时间戳
   nonceStr: '12345678',// 生成签名的随机串
   signature: '12345678'// 签名
}
```
返回异常的数据格式如下(http状态码600在header体现)
```
{

   "msg": "微信错误码【40164】invalid ip 118.112.58.58, not in whitelist hint: [wgirhA04751512]",
   "code": 600
}
```


### 客户端(手机web)
1. 要确保加载了jquery和微信jsdk

```javascript
<script src="http://res.wx.qq.com/open/js/jweixin-1.2.0.js"></script>
```

2. 定义相应的url

```javascript
//BASE_URL_WX是你的服务器地址比如http://www.test.com防止以后更改:这个域名要和微信公众号配置的JS安全域名保持一致
//shareUrl是你要分享的页面的url如果后面有参数就?id=xx拼接
var shareUrl = BASE_URL_WX + "/listen/web/share/app";
//fullurl是去签名的url也就是嗲都用接口时客户端要的
//fullurl一定要这么写,且很多时候shareUrl和fullurl是相等的但有些特殊情况不相等要报错:这个问题调试了一天得出的结论
var fullurl = encodeURIComponent(location.href.split('#')[0]);
```
3. 通过ajax请求获取签名等参数同时写微信的逻辑  
> 说明:下面的imgUrl一定是直接可以访问的且在服务器上的全路径
```javascript
$.ajax({
        type: 'GET',
        url: SIGN_URL,
        data:{fullurl:fullurl},
        dataType: 'json',
        success: function (res) {
            wx.config({
                debug: false, // 开启调试模式,调用的所有api的返回值会在客户端alert出来，若要查看传入的参数，可以在pc端打开，参数信息会通过log打出，仅在pc端时才会打印。
                appId: res.appId, // 必填，公众号的唯一标识
                timestamp: res.timestamp, // 必填，生成签名的时间戳
                nonceStr: res.noncestr, // 必填，生成签名的随机串
                signature: res.signature,// 必填，签名
                jsApiList: [
                    'onMenuShareAppMessage',//分享给好友
                    'onMenuShareTimeline'//朋友圈
                ] // 必填，需要使用的JS接口列表
            });
            wx.ready(function () {
            	//发送给朋友
                wx.onMenuShareAppMessage({
                   title: title, // 分享标题
                   desc: desc, // 分享描述
                   link: shareUrl, // 分享链接
                   imgUrl:shareImg, // 分享图标
                   type: 'link', // 分享类型,music、video或link，不填默认为link
                   dataUrl: '', // 如果type是music或video，则要提供数据链接，默认为空
                   success: function () {
                    
                   },
                   cancel: function () {}
                }),
				//分享到朋友圈
                wx.onMenuShareTimeline({
                   title: title, // 分享标题
                   link: shareUrl, // 分享链接
                   imgUrl:shareImg, // 分享图标
                })

            });
        },
        error: function (res) {
            alert("微信服务器异常");
        }
    });
```


