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

#### 写在接口之前 在application/common.php里面添加两个方法
```
/**生成指定长度的随机字符串
  * @param $ length 指定字符串长度
  * @return null|string
 */ 
function getRandChar($length)
{
    $str = null;
    $strPol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
    $max = strlen($strPol) - 1;
    for ($i = 0; $i < $length; $i++)
    {
        $str .= $strPol[rand(0, $max)];
    }
    return $str;
}//fun

/**最全的模拟请求方法
 * @param string $url 请求地址
  * @param string $type请求方式
  * @param string $data请求数据
  * @param bool $header头部数据
  * @return mixed
 */ 
function postCurl($url = '', $type = "POST", $data = '', $header = false)
{
    #1.创建一个curl资源
  $ch = curl_init();
    #2.设置URL和相应的选项
  //2.1设置url
  curl_setopt($ch, CURLOPT_URL, $url);
    //2.2设置头部信息
  //array_push($header, 'Accept:application/json');
 //array_push($header,'Content-Type:application/json'); //array_push($header, 'http:multipart/form-data'); //设置为false,只会获得响应的正文(true的话会连响应头一并获取到)
  curl_setopt($ch, CURLOPT_HEADER, 0);
    //curl_setopt ( $ch, CURLOPT_TIMEOUT,5); // 设置超时限制防止死循环
  //设置发起连接前的等待时间，如果设置为0，则无限等待。
  curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    //将curl_exec()获取的信息以文件流的形式返回，而不是直接输出。
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    #3设置请求参数
  if ($data)
    {
        //全部数据使用HTTP协议中的"POST"操作来发送。
  curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    }
    //3)设置提交方式
  switch($type){
        case "GET":
  curl_setopt($ch,CURLOPT_HTTPGET,true);
            break;
        case "POST":
  curl_setopt($ch,CURLOPT_POST,true);
            break;
        case "PUT"://使用一个自定义的请求信息来代替"GET"或"HEAD"作为HTTP请求。这对于执行"DELETE" 或者其他更隐蔽的HTT
  curl_setopt($ch,CURLOPT_CUSTOMREQUEST,"PUT");
            break;
        case "DELETE":
  curl_setopt($ch,CURLOPT_CUSTOMREQUEST,"DELETE");
            break;
    }
    //4.设置请求头 如果有才设置
  if ($header)
    {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    }
    #5.上传文件相关设置
  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_MAXREDIRS, 3);
    // 对认证证书来源的检查
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    // 从证书中检查SSL加密算
  curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
    #6.在HTTP请求中包含一个"User-Agent: "头的字符串。-----必设
  //curl_setopt($ch, CURLOPT_USERAGENT, 'SSTS Browser/1.0');
 //curl_setopt($ch, CURLOPT_ENCODING, 'gzip'); //6.2=1模拟用户使用的浏览器
  curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.0; Trident/4.0)');
    #7.抓取URL并把它传递给浏览器
  $result = curl_exec($ch);
    #8关闭curl资源，并且释放系统资源
  curl_close($ch);
    return $result;
}//fun

```

#### 写一个接口获取参数
```
public function info()
{
  
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


