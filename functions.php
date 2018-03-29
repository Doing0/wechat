<?php
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
