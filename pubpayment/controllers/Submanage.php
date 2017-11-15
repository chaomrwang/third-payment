<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once(APPPATH.'libraries/pingpp-php/init.php');
/**
*   订阅号开发者中心
*/
class Submanage extends CI_Controller {

    private $redis;
    private $uid;

	public function __construct()
    {
        header('Access-Control-Allow-Origin: *');
        parent::__construct();
        $this->load->model('PaymentModel');
        $this->load->model('SubmanageModel');
        $this->redis = $this->cache->redis->getRedis();

        $this->uid = $this->input->post_get('uid');
        //身份校验
        $userInfo = $this->PaymentModel->getUserInfo($this->uid);
        if(!$userInfo){
            jsonReturnError("USR001");
            return false;
        }
    }

    /**
	 *  基本信息展示
     */
    public function getBaseInfo(){
        $baseInfo = $this->SubmanageModel->getSubscriptionInfo($this->uid);
        jsonOkReturn($baseInfo); 
    }

    /**
     *  获取开发者信息
     */
    public function getDeveloper(){
        $developerInfo = $this->SubmanageModel->getDeveloper($this->uid);
        if(!$developerInfo){
            jsonReturnError("COM009", "用户信息");
            return false;
        }
        jsonOkReturn($developerInfo); 
    }

    /**
     *  开发者申请(分配appId)
     */
    public function applyForDeveloper(){
        $developer = $this->SubmanageModel->getDeveloper($this->uid);
        if(!$developer){
            jsonReturnError("COM006", "申请");
            return false;
        }
        $developerInfo = array(
            'uid' => $this->uid,
            'create_time' => SA::$serverTime,
            'update_time' => SA::$serverTime
        );
        $appId = $this->SubmanageModel->addDeveloper($developerInfo);
        jsonOkReturn(['appKey' => $appId]); 
    }

    /**
     *  生成appSecret
     */
    public function getAppSecret(){
        $developerInfo = $this->SubmanageModel->getDeveloper($this->uid);
        if(!$developerInfo){
            jsonReturnError("COM009", "用户信息");
            return false;
        }
        $appSecret = getRandChar(25);
        $set = ['app_secret' => $appSecret];
        $where = ['id' => $developerInfo['id']];
        $this->SubmanageModel->updateDeveloper($set, $where);
        $this->SubmanageModel->updateOauthClient(['secret' => $appSecret], $where);
        jsonOkReturn(['appSecret' => $appSecret]); 
    }

    /**
     *  根据功能码查询开发者功能状态
     */
    public function getFunctionInfo(){
        $code = $this->input->post_get('functionCode');
        if(!$code){
            jsonReturnError("COM001");
            return false;
        }

        $developerInfo = $this->SubmanageModel->getDeveloper($this->uid);
        if(!$developerInfo){
            jsonReturnError("COM009", "用户信息");
            return false;
        }
        $funcCodes = [];
        !empty($developerInfo['func_codes']) && $funcCodes = explode(',', $developerInfo['func_codes']);

        $status = 0;
        if(in_array($code, $funcCodes)){
            $status = 1;
        }
        
        jsonOkReturn(['status' => $status]); 
    }

    /**
     *  获取开发者功能权限状态
     */
    public function getDevelperAuth(){
        $developerInfo = $this->SubmanageModel->getDeveloper($this->uid);
        if(!$developerInfo){
            jsonReturnError("COM009", "用户信息");
            return false;
        }
        $funcCodes = [];
        !empty($developerInfo['func_codes']) && $funcCodes = explode(',', $developerInfo['func_codes']);

        $authCodes = $this->SubmanageModel->getAuthCodes();
        $dataCodes = [];
        if($authCodes){
            foreach ($authCodes as $key => $val) {
                $dataCodes[$key]['code'] = $val['code'];
                $dataCodes[$key]['name'] = $val['name'];
                $dataCodes[$key]['desc'] = $val['description'];
                if(in_array($val['code'], $funcCodes)){
                    $dataCodes[$key]['status'] = 1;
                }else{
                    $dataCodes[$key]['status'] = 0;
                }
            }
        }
        jsonOkReturn($dataCodes); 
    }

    /**
     *  开通功能权限
     */
    public function addFunctionAuth(){
        $code = $this->input->post_get('functionCode');
        if(!$code){
            jsonReturnError("COM001");
            return false;
        }
        $developerInfo = $this->SubmanageModel->getDeveloper($this->uid);
        if(!$developerInfo){
            jsonReturnError("COM009", "用户信息");
            return false;
        }
        $funcCodes = [];
        !empty($developerInfo['func_codes']) && $funcCodes = explode(',', $developerInfo['func_codes']);
        array_push($funcCodes, $code);
        $funcCodes = implode(',', $funcCodes);
        $set = ['func_codes' => $funcCodes];
        $where = ['id' => $developerInfo['id']];
        $result = $this->SubmanageModel->updateDeveloper($set, $where);
        $result = $result ? true : false;
        jsonOkReturn($result); 
    }

    /**
     *  删除功能权限
     */
    public function removeFunctionAuth(){
        $code = $this->input->post_get('functionCode');
        if(!$code){
            jsonReturnError("COM001");
            return false;
        }
        $developerInfo = $this->SubmanageModel->getDeveloper($this->uid);
        if(empty($developerInfo['func_codes'])){
            jsonReturnError("COM006", "操作");
            return false;
        }
        $funcCodes = explode(',', $developerInfo['func_codes']);
        if(!in_array($code, $funcCodes)){
            jsonReturnError("COM010", "暂未开通该权限");
            return false;
        }
        $funcCodes = array_flip($funcCodes);
        unset($funcCodes[$code]);
        $funcCodes = array_flip($funcCodes);
        $funcCodes = implode(',', $funcCodes);
        $set = ['func_codes' => $funcCodes];
        $where = ['id' => $developerInfo['id']];
        $result = $this->SubmanageModel->updateDeveloper($set, $where);
        $result = $result ? true : false;
        jsonOkReturn($result);  
    }

    /**
     *  获取域名列表
     */
    public function getDomainList(){
        $code = $this->input->post_get('functionCode');
        if(!$code){
            jsonReturnError("COM001");
            return false;
        }
        $domainList = $this->SubmanageModel->getDomainList($this->uid, $code);
        jsonOkReturn($domainList);  
    }

    /**
     *  域名配置
     */
    public function addDomain(){
        $domain = $this->input->post_get('host');
        $code = $this->input->post_get('functionCode');
        if(!$domain || !$code){
            jsonReturnError("COM001");
            return false;
        }
        $developerInfo = $this->SubmanageModel->getDeveloper($this->uid);
        if(!$developerInfo){
            jsonReturnError("COM009", "用户信息");
            return false;
        }
        //查看用户是否开通该功能
        $funcCodes = [];
        !empty($developerInfo['func_codes']) && $funcCodes = explode(',', $developerInfo['func_codes']);
        if(!in_array($code, $funcCodes)){
            jsonReturnError("COM010", "暂未开通该功能");
            return false;
        }

        $domainInfo = $this->SubmanageModel->getDomainInfo($this->uid, $domain, $code);
        if($domainInfo){
            jsonReturnError("COM010", "域名重复");
            return false;
        }

        $data = array(
            'uid' => $this->uid,
            'app_id' => $developerInfo['id'],
            'domain' => $domain,
            'func_code' => $code,
            'create_time' => SA::$serverTime,
            'update_time' => SA::$serverTime
        );
        $result = $this->SubmanageModel->addDomainConf($data);
        $result = $result ? true : false;
        jsonOkReturn($result);
    }

    /**
     *  域名删除
     */
    public function removeDomain(){
        $domain = $this->input->post_get('host');
        $code = $this->input->post_get('functionCode');
        if(!$domain || !$code){
            jsonReturnError("COM001");
            return false;
        }
        
        $domainInfo = $this->SubmanageModel->getDomainInfo($this->uid, $domain, $code);
        if(!$domainInfo){
            jsonReturnError("COM008", "信息");
            return false;
        }
        $set = ['status' => 1];
        $where = ['id' => $domainInfo['id'], 'func_code' => $code];
        $result = $this->SubmanageModel->updateDomainConf($set, $where);
        $result = $result ? true : false;
        jsonOkReturn($result); 
    }

    /**
     *  获取有权限的接口列表
     */
    public function getAuthApiList(){
        $domain = $this->input->post_get('host');
        $apiList = $this->SubmanageModel->getAuthApiList($this->uid, $domain);
        jsonOkReturn($apiList); 
    }
}
