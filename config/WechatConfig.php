<?php
/**
 * Created by PhpStorm
 * PROJECT:微信相关包
 * User: Doing <vip.dulin@gmail.com>
 * Desc:配置文件
 */

namespace wechat\config;
class WechatConfig {
    //微信公众号的Appid
    CONST APPID = 'you appid';
    //微信公众号appsecret
    CONST APPSECRET = 'you appsecret';
    //获取access_token的url
    CONST GET_ACCESS_TOKEN_URL = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=%s&secret=%s";
    //获取jsapi的ticket的url
    CONST GET_JSAPI_TICKET_URL = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=%s&type=jsapi";
    //access_token过期时间:7000建议不要修改
    CONST EXPIRE_ACCESS_TOKEN = '7000';
}//class


