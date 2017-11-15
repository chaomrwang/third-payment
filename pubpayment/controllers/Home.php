<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once(APPPATH.'libraries/pingpp-php/init.php');
class Home extends CI_Controller {

    private $redis;
	public function __construct()
    {
        header('Access-Control-Allow-Origin: *');
        parent::__construct();
        $this->load->model('PaymentModel');
        $this->load->model('SubmanageModel');
        $this->httpresponse = new Httpresponse();
        $this->cookie = new Cookie();
        $this->redis = $this->cache->redis->getRedis();
    }

    /**
	 *  跳转到目标页面
     */
    public function target(){
        $uid            = $this->input->post_get('uid');       //用户id
    	$redirect    	= $this->input->post_get('redirect');  //回调地址

    	/*if(intval($uid) < 1){
            jsonReturnError("COM001");
            return false;
        }*/
        $uid = intval($uid);
        //身份校验
        $userInfo = $this->PaymentModel->getUserInfo($uid);
        /*if(!$userInfo){
            jsonReturnError("USR001");
            return false;
        }*/
        $userName = $userInfo ? $userInfo['nickname'] : '游客';
        $url = $this->buildCreditAutoLoginRequest($uid, $userName, $redirect);
        header("Location: {$url}"); 
    }

    /**
     * 生成自动登录地址
     * 通过此方法生成的地址，可以让用户免登录，进入书城
     * @param unknown $uid
     * @param unknown $appKey
     * @return string
     */
    private function buildCreditAutoLoginRequest($uid, $userName, $redirect = ''){
        $url = "http://qd.xiang5.com/autologin/1010?";
        //$url = "http://form.tj.boetech.cn/autologin/1010?";
        $timestamp = time() * 1000 . "";
        $array = array("uid" => $uid, "appKey" => SA::$appKey, "userName" => $userName, "timestamp" => $timestamp, "redirect" => $redirect);
        $sign  = generateHash($array, '3gFCA2QGUb6BozNKL1a6SyKqx7gy');
        $array['sign'] = $sign;
        $url .= http_build_query($array);
        return $url;
    }

    /**
     *  登录引导
     */
    public function presentLogin(){
        $redirect = $this->input->post_get('redirect');  //回调地址
        $data = array('redirect' => $redirect);
        $this->load->view('login', $data);
    }

    /**
     *  进入支付页面
     */
    public function getPayPage(){
        $uid            = $this->input->post_get('uid');       //用户id
        $xiang_sn       = $this->input->post_get('out_trade_no');    //商户订单号
        $order_amount   = $this->input->post_get('total_fee'); //订单金额(单位：分)
        $channel        = $this->input->post_get('channel');   //支付方式
        $goodId         = $this->input->post_get('good_id');   //商品id
        $goodId         = $goodId ? $goodId : 0;               //商品id
        $body           = $this->input->post_get('body');      //商品描述
        $detail         = $this->input->post_get('detail');    //商品详情
        $notify_url     = $this->input->post_get('notify_url');//服务器回调地址
        $redirect       = $this->input->post_get('redirect');  //页面跳转地址
        $appKey         = $this->input->post_get('appKey');    //秘钥
        if(intval($uid) < 1 || empty($xiang_sn) || intval($order_amount)<1 || empty($redirect) || empty($channel)){
            jsonReturnError("COM001");
            return false;
        }

        $developer = $this->SubmanageModel->getDeveloperByAppId($appKey);
        if(!$developer){
            jsonReturnError("USR001");
            return false;
        }

        payAuth($_GET, $developer['app_secret']);  //签名校验

        empty($body) && $body = '商品描述';
        empty($detail) && $detail = '商品详情';
        //身份校验
        $userInfo = $this->PaymentModel->getUserInfo($uid);
        if(!$userInfo){
            jsonReturnError("USR001");
            return false;
        }
        $params = array(
            'uid' => $uid,
            'channel' => $channel,
            'order_amount' => $order_amount,
            'goodId' => $goodId,
            'body' => $body,
            'detail' => $detail,
            'business' => 9,
            'source' => 'book',
            'appKey' => $appKey,
            'notify_url' => $notify_url
        );
        $this->redis->set('sn:'.$xiang_sn, json_encode($params));
        $data = array('out_trade_no' => $xiang_sn, 'redirect' => $redirect);
        $this->load->view('resultPage', $data);
    }

    /**
     *  请求支付
     */
    public function xiangPay(){
        $xiang_sn       = $this->input->post_get('out_trade_no');    //商户订单号
        $ip             = $_SERVER['REMOTE_ADDR'];
        empty($ip) && $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        empty($ip) && $ip = $_SERVER['HTTP_CLIENT_IP'];

        if(empty($xiang_sn)){
            jsonReturnError("COM005");
            return false;
        }

        $payParams = $this->redis->get('sn:'.$xiang_sn);
        $payParams = json_decode($payParams, true);
        if(empty($payParams)){
            jsonReturnError("COM001");
            return false;
        }
        $this->redis->del('sn:'.$xiang_sn);
        
        //payAuth($_GET);  //签名校验
        $app_id      = SA::$appId;
        $api_key     = SA::$apiKeyP;
        //身份校验
        $userInfo = $this->PaymentModel->getUserInfo($payParams['uid']);
        if(!$userInfo){
            jsonReturnError("USR001");
            return false;
        }

        //记录支付流水
        $params = array(
            'uid' => $payParams['uid'],
            'order_sn' => 'xiangsh'.getRandChar(15),
            'channel' => $payParams['channel'],
            'order_amount' => $payParams['order_amount'] / 100,
            'good_amount' => $payParams['order_amount'] / 100,
            'business' => $payParams['business'],
            'source' => $payParams['source'],
            'good_id' => intval($payParams['goodId']),
            'good_name' => $payParams['body'],
            'str1' => $payParams['detail'],
            'str2' => $xiang_sn,
            'create_time' => SA::$serverTime,
            'update_time' => SA::$serverTime,
            'ip' => $ip,
        );
        $recordId = $this->PaymentModel->addPayRecord($params);
        $isSuccess = "成功";
        if(!$recordId){
            $isSuccess = "失败";
        }
        recordLog('thirdPayBack_'.date('Ymd').'.txt','用户:'.$params['uid'].',支付记录添加'.$isSuccess.',粉粉订单号:'.$params['order_sn'].',商户订单号:'.$xiang_sn);

        $successUrl = "https://www.fenfenriji.com/success.html";
        $extra   = array();
         switch ($payParams['channel']) {
            case 'alipay_wap':   //支付宝手机网页支付
                $extra = array('success_url' => $successUrl);
                break;
            case 'alipay_pc_direct':   //支付宝手机网页支付
                $extra = array('success_url' => $successUrl);
                break;
            case 'wx_pub':   //微信公众号支付
                $extra = array('open_id' => 'wxede9a654ad6a9cc7');
                break;
            case 'qpay':  //QQ钱包支付
                $extra = array('device' => 'android');
                break;
        }

        $metadata = array('uid' => $payParams['uid'], 'redirect'=>$payParams['notify_url'], 'out_trade_no'=>$xiang_sn, 'appKey'=>$payParams['appKey']);
        \Pingpp\Pingpp::setApiKey($api_key);
        try {
            $ch = \Pingpp\Charge::create(
                array(
                    'order_no'  =>  $params['order_sn'],
                    'amount'    =>  $payParams['order_amount'],
                    'app'       =>  array('id' => $app_id),
                    'channel'   =>  $payParams['channel'],
                    'currency'  => 'cny',
                    'client_ip' =>  $ip,
                    'subject'   =>  $payParams['body'],
                    'body'      =>  $payParams['detail'],
                    'extra'     =>  $extra,
                    'metadata'  =>  $metadata,
                )
            );
        } catch (\Pingpp\Error\Base $e) {
            jsonOkReturn($e);return;
        }
        
         $ch = json_decode($ch, true);
         $third_order = $ch['id'];
         if (!$third_order){
            //请求失败
             jsonReturnError("COM011");
            return false;
         }
         $ch = json_encode($ch);
         get_instance()->output->set_content_type('json')->set_output($ch);
    }

    /**
     *  模拟第三方支付
     */
    public function payTest(){
        $order_sn       = $this->input->post_get('out_trade_no');    //商户订单号
        $ip             = $_SERVER['REMOTE_ADDR'];
        empty($ip) && $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        empty($ip) && $ip = $_SERVER['HTTP_CLIENT_IP'];
        
        if(empty($order_sn)){
            jsonReturnError("COM005");
            return false;
        }
        
        $payParams = $this->redis->get('sn:'.$order_sn);
        $payParams = json_decode($payParams, true);
        if(empty($payParams)){
            jsonReturnError("COM001");
            return false;
        }
        $this->redis->del('sn:'.$order_sn);
        //payAuth($_GET);  //签名校验
        //身份校验
        $userInfo = $this->PaymentModel->getUserInfo($payParams['uid']);
        if(!$userInfo){
            jsonReturnError("USR001");
            return false;
        }

        //记录支付流水
        $params = array(
            'uid' => $payParams['uid'],
            'order_sn' => $order_sn,
            'channel' => $payParams['channel'],
            'order_amount' => $payParams['order_amount'] / 100,
            'good_amount' => $payParams['order_amount'] / 100,
            'business' => $payParams['business'],
            'source' => $payParams['source'],
            'good_id' => intval($payParams['goodId']),
            'good_name' => $payParams['body'],
            'str1' => $payParams['detail'],
            'create_time' => SA::$serverTime,
            'update_time' => SA::$serverTime,
            'ip' => $ip,
        );
        
        $recordId = $this->PaymentModel->addPayRecord($params);

        $responseArr = array(
            'uid' => $payParams['uid'],
            'out_trade_no' => $order_sn,
            'channel' => $payParams['channel'],
            'total_fee' => $payParams['order_amount'],
            'out_trade_status' => $recordId ? 0 : 1,
            'third_no' => 'xiangsh'.getRandChar(15),
            'appKey' => $payParams['appKey']
        );
        
        $notify_url = $payParams['notify_url'].'?';
        $responseArr['sign'] = generateHash($responseArr);
        $notify_url .= http_build_query($responseArr);
        $result = $this->httpresponse->httpResponseGet($notify_url);
        recordLog('thirdPayBack_'.date('Ymd').'.txt','payTest=>'.$result);
        jsonReturnOK(true);
    }

    /**
     * 支付成功后请求的回调
     */
    public function xiangBack()
    {
        $input_data = json_decode(file_get_contents('php://input'), true);
        $type = $input_data['type'];
        $obj = $input_data['data']['object'];
        switch ($type) {
            case "charge.succeeded":
                // 获取回调信息
                list($sn, $third_order, $paid, $amount, $channel, $uid, $redirect, $out_trade_no, $appKey) = array($obj['order_no'], $obj['id'], $obj['time_paid'], $obj['amount'], $obj['channel'], $obj['metadata']['uid'], $obj['metadata']['redirect'], $obj['metadata']['out_trade_no'], $obj['metadata']['appKey']);
                recordLog('thirdPayBack_'.date('Ymd').'.txt','Pingpp-thirdPay-webhooks :pa=>'.json_encode($input_data));
                
                $this->PaymentModel->updatePayInfo($uid, $sn, $third_order, $paid);
                if(SA::$errorNo){
                    recordLog('thirdPayBack_'.date('Ymd').'.txt','用户:'.$uid.',支付记录更新失败,订单号:'.$sn.',错误信息:'.SA::$errorMsg);
                }
                //请求回调
                $params = array(
                    'uid' => $uid,
                    'out_trade_no'  => $out_trade_no,
                    'third_no' => $third_order,
                    'total_fee' => $amount / 100,
                    'out_trade_status' => 0,
                    'channel' => $channel,
                    'appKey' => $appKey
                );
                $redirect = $redirect.'?';
                $params['sign'] = generateHash($params);
                $redirect .= http_build_query($params);
                recordLog('thirdPayBack_'.date('Ymd').'.txt','xiangBackUrl=>'.$redirect);
                $result = $this->httpresponse->httpResponseGet($redirect);
                http_response_code(200);
                jsonOkReturn($result);
                break;
            default:
                break;
        }
        http_response_code(500);
    }
}
