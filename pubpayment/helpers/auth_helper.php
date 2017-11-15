<?php
defined('BASEPATH') OR exit('No direct script access allowed');
    
/**
 *  支付权限校验
 */
function payAuth($params, $appSecret){
    @$sign = $params['sign'];
    unset($params['sign']);
    unset($params['appSecret']);
    $new_sign = generateHash($params, $appSecret);
    if (empty($sign) || $sign != $new_sign) {
        die(json_encode(['resultData' => false, 'message' => '签名错误', 'errorNo' => '10000']));
    }
}

 // 生成签名
function generateHash($params, $appSecret)
{
    ksort($params);
    $str = '';
    foreach ($params as $k => $val) {
        $str .= $k .'='. $val .'&';
    }
    $str .= 'appSecret='.$appSecret;
    return md5(strtoupper($str));
}


/**
    *  生成订单号
    *  $length 长度  (最好统一)
    */
function getRandChar( $length ) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $password = "";
    for ( $i = 0; $i < $length; $i++ )
    {
        $password .= $chars[ mt_rand(0, strlen($chars) - 1) ];
    }
    $password = substr(md5( $password.time() ), 0 , $length);
    return $password;
}