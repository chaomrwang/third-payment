<?php
if(!defined('BASEPATH')) EXIT('No direct script asscess allowed'); 

class Httpresponse{ 

    /**
    *   请求粉粉平台
    */
    public function getHttpResponse($url, $params) {
        $time = time(); $token = 'no token';
        $hash = self::generateHttpHash($params['uid'], $time, $token);
        $params += array('time'=>$time, 'token'=>$token, 'hash'=>$hash);

        // 拼装get参数
        $url .= '?'.http_build_query($params);
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_HEADER, 0 ); // 过滤HTTP头
        curl_setopt($curl, CURLOPT_USERAGENT, "FFWEB_COIN_1.0-API");
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);// 显示输出结果
        curl_setopt($curl, CURLOPT_ENCODING, "");
        curl_setopt($curl, CURLOPT_HEADER, FALSE);
        curl_setopt($curl, CURLOPT_USERPWD, 'xxt:xxt123');
        $responseText = curl_exec($curl);
        $info = curl_getinfo($curl);
    
        // var_dump( curl_error($curl) );exit;//如果执行curl过程中出现异常，可打开此开关，以便查看异常内容
        curl_close($curl);
    
        if (empty($info['http_code']) or $info['http_code'] != 200 or empty($responseText)) {
            $code = $info['http_code'];
        }
    
        return $responseText;
    } 

    public static function generateHttpHash($uid, $time, $token) {
        $key = 'UserAuth';
        return sha1($key.$uid.$token.$time);
    }

    public function httpResponseGet($url) {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        $responseText = curl_exec($curl);
        curl_close($curl);
        return $responseText;
    }

    public function getHttpResponsePOST($url, $para, $input_charset = '') {
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_HEADER, 0 ); // 过滤HTTP头
        curl_setopt($curl,CURLOPT_POST,true); // post传输数据
        curl_setopt($curl,CURLOPT_POSTFIELDS,$para);// post传输数据
        curl_setopt($curl,CURLOPT_RETURNTRANSFER, 1);// 显示输出结果
        curl_setopt($curl, CURLOPT_USERAGENT, "FFWEB_1.0-API");
        curl_setopt($curl,CURLOPT_RETURNTRANSFER, 1);// 显示输出结果
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($curl, CURLOPT_ENCODING, "");
        curl_setopt($curl, CURLOPT_HEADER, FALSE);
        $responseText = curl_exec($curl);
    
        // var_dump( curl_error($curl) );exit;//如果执行curl过程中出现异常，可打开此开关，以便查看异常内容
        curl_close($curl);
        return $responseText;
    }
} 