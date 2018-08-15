<?php
/**
 * 
 */
namespace conf;

class errDesc
{ 
    static $Errors = array(
        0=>"OK",
        1000=>"服务器错误",
        2001=>"请求包格式错误",
        2002=>"缺少必要参数",
        //账号相关
        3001=>"创建账号失败",
        3002=>"账号不存在",
        3003=>"公钥不合法",
     
    );

    static public function getErrorDesc($code)
    {
        return isset(self::$Errors[$code]) ? self::$Errors[$code] : "undefined error code";
    }
}