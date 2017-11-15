<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * json 结果构造器
 * @param array $result
 * @param string $code
 * @param string $msg
 */
function jsonBuildResult($result = false, $code = '', $msg = ''){
    get_instance()->output->set_content_type('json')->set_output(json_encode([
        'resultData' => $result,
        'message' => $msg,
        'errorNo' => $code
    ]));
}

/**
 * 正确结果json输出
 * @param array $result
 * @return bool
 */
function jsonReturnOk($result = []){
    jsonBuildResult($result);
    return true;
}

/**
 * 错误结果json输出
 * @param string $code
 * @param array $result
 * @return bool
 */
function jsonReturnError($code = '', $prepare = [], $result = false){
    jsonBuildResult($result, $code, codeStatusString($code, $prepare));
    return false;
}

/**
 * 错误码转文字
 * @param string $code
 * @param array $prepare 格式化字符数组
 * @return string
 */
function codeStatusString($code = '', $prepare=[]){
    if (empty($code))
        return '未知错误';
    $map = [
        //通用错误
        'COM001' => '参数错误',
        'COM002' => '超出%s范围，应在 %s - %s 之间',
        'COM003' => '没有权限执行%s操作',
        'COM004' => '执行%s超时',
        'COM005' => '参数不完整',
        'COM006' => '%s失敗',
        'COM007' => '验证码错误',
        'COM008' => '%s不存在',
        'COM009' => '%s错误',
        'COM010' => '%s',
        'COM011' => '请求支付失败',

        'USR001' => '用户信息不合法',
        'USR002' => '该用户不是订阅号',
    ];
    $string = $map[$code];
    empty($string)  && $string = '错误码尚未定义';
    $prepare        && $string = vsprintf($string, $prepare);
    return $string;
}
