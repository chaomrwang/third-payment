<?php
defined('BASEPATH') OR exit('No direct script access allowed');

 class ManageAuth
{
    private $CI;
    public function __construct()
    {
        $this->CI = &get_instance();  //获取CI对象
    }

    //权限认证
    public function auth()
    {
        $params = $_GET;
        @$sign = $params['sign'];
        unset($params['sign']);
        unset($params['appSecret']);
        $new_sign = $this->generateHash($params);
        if (empty($sign) || $sign != $new_sign) {
            die(json_encode(['resultData' => false, 'message' => '签名错误', 'errorNo' => '10000']));
        }
    }

    // 生成签名
    private function generateHash($params)
    {
        ksort($params);
        $str = '';
        foreach ($params as $k => $val) {
            $str .= $k .'='. $val .'&';
        }
        if(array_key_exists($params['appKey'], SA::$signKey)){
            $appSecret = SA::$signKey[$params['appKey']]['appSecret'];
        }else{
            $appSecret = '';
        }
        $str .= 'appSecret='.$appSecret;
        return md5(strtoupper($str));
    }
}
