<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * json 结果构造器
 * @param array $result
 * @param string $code
 * @param string $msg
 */
function jsonResultBuild($result = false, $code = '', $msg = ''){
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
function jsonOkReturn($result = []){
    jsonResultBuild($result);
    return true;
}

/**
 * 错误结果json输出
 * @param string $code
 * @param array $result
 * @return bool
 */
function jsonErrorReturn($code = '', $prepare = [], $result = false){
    jsonResultBuild($result, $code, codeStatus2String($code, $prepare));
    return false;
}

/**
 * 错误码转文字
 * @param string $code
 * @param array $prepare 格式化字符数组
 * @return string
 */
function codeStatus2String($code = '', $prepare=[]){
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

        //用户错误
        'USR001' => '该用户不存在',

        //账户错误
        'ACC001' => '暂未绑定账户',
        'ACC002' => '账户余额不足',
        'ACC003' => '一天只能提现一次哦~',
        'ACC004' => '该账户不存在',
        'ACC005' => '该账户已存在',
        'ACC007' => '账户暂不可提现',
        'ACC006' => '账户错误',

        //点滴错误
        'DIR001' => '该点滴不存在',

        //礼物相关
        'GFT001' => '该礼物不存在',
        'GFT002' => '礼物已下线',
        'GFT003' => '粉钻余额不足',
        'GFT004' => '订单创建失败',
        'GFT005' => '查询粉钻余额失败',
        'GFT006' => '扣减粉钻余额失败',
        'GFT007' => '增加账户余额失败',
        'GFT008' => '记录赠送礼物流水信息失败',
        'GFT009' => '不能给自己送礼物'

    ];
    $string = $map[$code];
    empty($string)  && $string = '错误码尚未定义';
    $prepare        && $string = vsprintf($string, $prepare);
    return $string;
}
