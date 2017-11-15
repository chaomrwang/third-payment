<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once(APPPATH.'libraries/pingpp-php/init.php');
class Payment extends CI_Controller {

    private $redis;

	public function __construct()
    {
        header('Access-Control-Allow-Origin: *');
        parent::__construct();
        $this->load->model('PaymentModel');
        $this->load->model('SubmanageModel');
        $this->redis = $this->cache->redis->getRedis();
    }

    /**
	 *  请求支付
     */
    public function pay(){
        $uid            = $this->input->post_get('uid');       //用户id
    	$channel 	 	= $this->input->post_get('channel');   //支付渠道
        $order_sn       = $this->input->post_get('out_trade_no');    //商户订单号
    	$order_amount 	= $this->input->post_get('total_fee'); //订单金额(单位：分)
        $goodId         = $this->input->post_get('good_id');   //商品id
        $body           = $this->input->post_get('body');      //商品描述
        $detail         = $this->input->post_get('detail');    //商品详情
        $business       = $this->input->post_get('business');  //业务类型
        $source         = $this->input->post_get('source');    //来源
        $platform       = $this->input->post_get('platform');  //支付平台
        $redirect       = $this->input->post_get('notify_url');//回调地址
        $appKey         = $this->input->post_get('appKey');    //秘钥
        $ip             = $_SERVER['REMOTE_ADDR'];
        empty($ip) && $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        empty($ip) && $ip = $_SERVER['HTTP_CLIENT_IP'];

    	if(intval($uid) < 1 || empty($channel) || empty($order_sn) || intval($order_amount)<1 || empty($body) || empty($detail) || empty($redirect)|| empty($appKey)){
            jsonReturnError("COM001");
            return false;
        }
        $str=array(" ","　","\t","\n","\r");  
        $detail = str_replace($str, '', $detail);

        //查询开发者是否存在
        $developer = $this->SubmanageModel->getDeveloperByAppId($appKey);
        if(!$developer){
            jsonReturnError("USR001");
            return false;
        }

        payAuth($_GET, $developer['app_secret']);  //签名校验
        $app_id      = SA::$appId;
        $api_key     = SA::$apiKeyP;
        //身份校验
    	$userInfo = $this->PaymentModel->getUserInfo($uid);
    	if(!$userInfo){
            jsonReturnError("USR001");
			return false;
		}

        //记录支付流水
        $params = array(
            'uid' => $uid,
            'order_sn' => $order_sn,
            'channel' => $channel,
            'order_amount' => $order_amount / 100,
            'good_amount' => $order_amount / 100,
            'business' => $business,
            'source' => $source,
            'platform' => intval($platform),
            'good_id' => intval($goodId),
            'good_name' => $body,
            'str1' => $detail,
            'create_time' => SA::$serverTime,
            'update_time' => SA::$serverTime,
            'ip' => $ip,
        );
        $this->createOrdreInfo($params);
        if($channel == 'apple'){
            jsonOkReturn(['orderInfo'=>$params]);
            return;
        }

        $successUrl = "https://www.fenfenriji.com/success.html";
        $extra   = array();
         switch ($channel) {
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

        $metadata = array('uid' => $uid, 'redirect'=>$redirect);
        \Pingpp\Pingpp::setApiKey($api_key);
        try {
            $ch = \Pingpp\Charge::create(
                array(
                    'order_no'  =>  $order_sn,
                    'amount'    =>  $order_amount,
                    'app'       =>  array('id' => $app_id),
                    'channel'   =>  $channel,
                    'currency'  => 'cny',
                    'client_ip' =>  $ip,
                    'subject'   =>  $body,
                    'body'      =>  $detail,
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
     * 创建订单
     */
    public function createOrdre()
    {
        $params = [];
        $params['uid']          = $this->input->post_get('uid');      
        $params['channel']      = $this->input->post_get('channel');            //支付渠道
        $params['business']     = $this->input->post_get('business');           //订单业务
        $params['good_id']      = $this->input->post_get('good_id');            //商品id
        $params['order_amount'] = $this->input->post_get('total_fee');          //订单价格(单位：分)
        $params['source']       = trim($this->input->post_get('source'));       //页面跳转来源
        $params['order_sn']     = trim($this->input->post_get('out_trade_no')); //订单号
        $params['good_name']    = $this->input->post_get('body');               //商品描述
        $params['str1']         = $this->input->post_get('detail');             //商品详情
        if(intval($params['uid']) < 1 || empty($params['channel']) || !isset($params['good_id']) || intval($params['order_amount']) < 1 || !isset($params['business']) || empty($params['order_sn'])){
            jsonReturnError("COM001");
            return false;
        }
        payAuth($_GET);  //签名校验
        $recordId = $this->createOrdreInfo($params);
        jsonOkReturn(['recordId'=>$recordId]);
    }

    /**
     * 创建预支付订单
     */
    private function createOrdreInfo($params)
    {
        $recordId = $this->PaymentModel->addPayRecord($params);
        $isSuccess = "成功";
        if(!$recordId){
            $isSuccess = "失败";
        }
        recordLog('thirdPayBack_'.date('Ymd').'.txt','用户:'.$params['uid'].',支付记录添加'.$isSuccess.',订单号:'.$params['order_sn']);
        return $recordId;
    }

    /**
     * 支付成功后请求的回调
     */
    public function payBack()
    {
        $input_data = json_decode(file_get_contents('php://input'), true);
        $type = $input_data['type'];
        $obj = $input_data['data']['object'];
        switch ($type) {
            case "charge.succeeded":
                // 获取回调信息
                list($sn, $third_order, $paid, $amount, $uid, $redirect) = array($obj['order_no'], $obj['id'], $obj['time_paid'], $obj['amount'], $obj['metadata']['uid'], $obj['metadata']['redirect']);
                recordLog('thirdPayBack_'.date('Ymd').'.txt','Pingpp-thirdPay-webhooks :pa=>'.json_encode($input_data));
                
                $this->PaymentModel->updatePayInfo($uid, $sn, $third_order, $paid);
                if(SA::$errorNo){
                    recordLog('thirdPayBack_'.date('Ymd').'.txt','用户:'.$uid.',支付记录更新失败,订单号:'.$sn);
                }
                //请求回调
                $params = array(
                    'uid' => $uid,
                    'order_sn'  => $sn,
                    'third_sn' => $third_order,
                    'update_time' => $paid,
                    'money_paid' => $amount / 100,
                );
                
                $this->PaymentModel->webhook($redirect, array('uid'=>$uid,'data'=>json_encode($params)));
                http_response_code(200);
                jsonOkReturn(true);
                break;
            default:
                break;
        }
        http_response_code(500);
    }

    /**
     *  支付流水查询
     */
    public function getPayRecords(){
        $uid      = $this->input->post_get('uid');       //用户id
        $order_sn = $this->input->post_get('out_trade_no');  //订单号
        $start    = $this->input->post_get('start');  //开始位置
        $length   = $this->input->post_get('length');  //每页条数
        $start    = $start ? $start : SA::$start;
        $length   = $length ? $length : SA::$length;

        if(intval($uid) < 1){
            jsonReturnError("COM001");
            return false;
        }

        //身份校验
        $userInfo = $this->PaymentModel->getUserInfo($uid);
        if(!$userInfo){
            jsonReturnError("USR001");
            return false;
        }
        $fields = 'uid, order_sn, good_name, order_amount, channel, pay_status, pay_time';
        $order = ' create_time desc';
        $recordList = $this->PaymentModel->getRecordList($uid, $order_sn, $fields, $order, $start, $length);
        jsonReturnOk($recordList);
    }
}
