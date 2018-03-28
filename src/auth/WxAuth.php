<?php
/**
 * Created by PhpStorm
 * PROJECT:微信相关包
 * User: Doing <vip.dulin@gmail.com>
 * Desc:获取微信相关权限:access_token签名等
 */

namespace wechat\auth;

use think\Cache;
use wechat\config\WechatConfig;

class WxAuth {
    //对象
    public static $instance;
    //微信的appid
    private $appid;
    //微信的钥匙
    private $appsecret;
    //签名需要的参数
    private $params;
    //签名
    private $signature;

    /**
     * 初始化数据
     */
    public function __construct()
    {
        $this->appid = WechatConfig::APPID;
        $this->appsecret = WechatConfig::APPSECRET;
    }

    /**入口实例化对象
     *
     * @param array $options
     *
     * @return array|static
     */

    public static function instance()
    {
        if (is_null(self::$instance))
        {
            self::$instance = new static();
        }
        return self::$instance;
    }

    /** 简述:主调方法:获取签名和其他信息
     * @params  $fullurl 反转义后的地址
     */
    public function getInfo($fullurl)
    {
        #获取签名需要的参数
        $this->makeParamsWithSign();
        #签名
        $this->doSign($fullurl);
        #返回参数
        return $this->makeReturnData();
    }//pf

    /** 简述:拼接返回参数
     *
     */
    private function makeReturnData()
    {
        $data['appId'] = $this->appid;
        $data['timestamp'] = $this->params['timestamp'];
        $data['noncestr'] = $this->params['noncestr'];
        $data['signature'] = $this->signature;
        return $data;
    }//pf

    /** 简述:生成签名需要的参数
     */
    private function makeParamsWithSign()
    {
        //随机字符串
        $this->params['noncestr'] = getRandChar(16);
        //ticket
        $this->params['jsapi_ticket'] = $this->getJsapi();
        //时间戳
        $this->params['timestamp'] = time();
    }//pf

    /** 简述:签名
     *
     * @params
     *
     */
    private function doSign($fullurl)
    {
        //字典序
        ksort($this->params);
        //拼接string
        $valur = http_build_query($this->params);
        $string = $valur . "&url=" . $fullurl;
        //签名
        $this->signature = sha1($string);
    }//pf

    /** 简述:获取getJsapi
     */
    private function getJsapi()
    {
        #根据accessToken获取ticket
        return $this->createTicket($this->getAccessToken());

    }//pf

    /** 简述:生成Jsapi的ticket
     */
    private function createTicket($access_token)
    {
        #生成Jsapi
        $url = sprintf(WechatConfig::GET_JSAPI_TICKET_URL, $access_token);
        $res = json_decode(postCurl($url, 'GET'), true);
        #验证请求
        if (0 != $res['errcode'])
        {
            throw New \Exception($this->makeWxErrorString($res));
        }
        return $res['ticket'];
    }//pf

    /** 简述:获取AccessToken
     */
    private function getAccessToken()
    {
        #读取缓存的数据
        $accessToken = Cache::get('accessToken');
        #验证 微信是7200我们设置7000秒
        $res = $this->checkAccessToken($accessToken);
        if ($res === true) return $accessToken['access_token'];
        return $this->updateAccessToken();


    }//pf

    /** 简述:验证accesstoken是否过期
     */
    private function checkAccessToken($accessToken)
    {
        if (!$accessToken) return false;
        return true;
    }//pf

    /** 简述:更新AccessToken
     * 异常1:生成accessToken失败
     * return:array
     */
    private function updateAccessToken()
    {
        #生成
        $url = sprintf(WechatConfig::GET_ACCESS_TOKEN_URL, $this->appid, $this->appsecret);

        $res = json_decode(postCurl($url, 'GET'), true);
        if (is_null($res)) throw New \Exception('请检查您的配置的appid等参数是否正确');
        //微信端错误 80002生成accesstoken错误
        if (array_key_exists('errmsg', $res))
        {
            throw New \Exception($this->makeWxErrorString($res));
        }
        #写缓存
        $access_token = $res['access_token'];
        $this->createCacheAccessToken($access_token);
        #返回
        return $access_token;
    }//pf

    /** 简述:写accesstoken
     *
     * @params access_token
     *
     */
    private function createCacheAccessToken($access_token)
    {
        $data['access_token'] = $access_token;
        $data['tm'] = time();
        Cache::set('accessToken', $data, WechatConfig::EXPIRE_ACCESS_TOKEN);
        return true;
    }//pf

    /** 简述:微信异常信息拼接
     */
    private function makeWxErrorString($res)
    {
        return "微信错误码【" . $res['errcode'] . "】" . $res['errmsg'];
    }//pf


}//class

