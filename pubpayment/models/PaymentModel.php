<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class PaymentModel extends CI_Model {

    private $db;
    public function __construct()
    {
        parent::__construct();
        $this->db = $this->load->database('default', true);
        $this->httpresponse = new Httpresponse();
    }

    /**
     *  添加支付流水
     */
    public function addPayRecord($params)
    {
        if(intval($params['uid']) < 1) return false;
        $this->db->trans_begin();
        try {
            $tableName = 'deal_orders_'.$this->db->getHashId($params['uid']);
            $recordInfo = $this->getRecordByNo($params['uid'], $params['order_sn']);
            $recordMainInfo = $this->getMainRecordByNo($params['uid'], $params['order_sn']);
            if($recordInfo || $recordMainInfo){
                throw new Exception("no pay record info", 1);
            }

            $this->db->insert($tableName, $params);
            $recordId = $this->db->insert_id();
            if(!$recordId){
                throw new Exception("add record faild", 1);
            }
            $paramsMain = array(
                'uid' => $params['uid'],
                'order_sn' => $params['order_sn'],
                'order_id' => $recordId,
                'order_amount' => $params['order_amount'],
                'business' => $params['business'],
                'good_id' => $params['good_id'],
                'good_name' => $params['good_name'],
                'create_time' => SA::$serverTime,
            );
            $this->db->insert('deal_orders_main_0', $paramsMain);
            $recordMainId = $this->db->insert_id();
            if(!$recordMainId){
                throw new Exception("add record main faild", 2);
            }
            $this->db->trans_commit();
            return $recordId;
        } catch (Exception $e) {
            $this->db->trans_rollback();
            SA::$errorNo = $e->getCode();
            SA::$errorMsg = $e->getMessage();
            return false;
        }
    }

    /**
     *  更新支付信息
     */
    public function updatePayInfo($uid, $sn, $third_order, $paid)
    {
        if(intval($uid) < 1) return false;
        $this->db->trans_begin();
        try{
            $tableName = 'deal_orders_'.$this->db->getHashId($uid);
            $recordInfo = $this->getRecordByNo($uid, $sn, true);
            $recordMainInfo = $this->getMainRecordByNo($uid, $sn, true);
            if(!$recordInfo || !$recordMainInfo){
                throw new Exception("no pay record info", 1);
            }

            if($recordInfo['order_status'] == 4){
                throw new Exception("already update record", 4);
            }

            //更新记录状态
            $params = array('pay_status' => '1', 'order_status' => '4', 'pay_time' => $paid, 'update_time' => SA::$serverTime, 'third_order' => $third_order);
            $updateRow = $this->db->update($tableName, $params, ['id' => $recordInfo['id']]);
            if(!$updateRow){
                throw new Exception("update record faild", 2);
            }

            if($recordMainInfo['pay_status'] == 1){
                throw new Exception("already update main record", 5);
            }

            //更新记录主表状态
            $params = array('pay_status' => '1', 'pay_time' => $paid);
            $updateMainRow = $this->db->update('deal_orders_main_0', $params, ['id' => $recordMainInfo['id']]);
            if(!$updateMainRow){
                throw new Exception("update main record faild", 3);
            }

            $this->db->trans_commit();
            return $updateRow;
        }catch (Exception $e){
            $this->db->trans_rollback();
            SA::$errorNo = $e->getCode();
            SA::$errorMsg = $e->getMessage();
            return false;
        }
    }

    /**
     *  查询记录
     */
    public function getRecordList($uid, $sn, $fields = '*', $order = '', $start, $length)
    {
        if(intval($uid) < 1) return false;
        $tableName = 'deal_orders_'.$this->db->getHashId($uid);
        $sql = "select {$fields} from {$tableName} where uid = {$uid}";
        !empty($sn) && $sql .= " and order_sn = '{$sn}'";
        !empty($order) && $sql .= " order by {$order}";
        $sql .= " limit {$start}, {$length}";
        $query = $this->db->query($sql);
        return $query->result_array();
    }

    /**
     *  查询记录
     */
    public function getRecordByNo($uid, $sn, $isLock = false)
    {
        if(intval($uid) < 1) return false;
        $tableName = 'deal_orders_'.$this->db->getHashId($uid);
        $sql = "select * from {$tableName} where uid = {$uid}";
        !empty($sn) && $sql .= " and order_sn = '{$sn}'";
        $sql .= " limit 1";
        if($isLock) $sql .= " for update";
        $query = $this->db->query($sql);
        return $query->row_array();
    }

    /**
     *  查询主表记录
     */
    public function getMainRecordByNo($uid, $sn, $isLock = false)
    {
        if(intval($uid) < 1 && empty($sn)) return false;
        $sql = "select * from deal_orders_main_0 where 1=1";
        if($uid) $sql .= " and uid = {$uid}";
        if($sn) $sql .= " and order_sn = '{$sn}'";
        $sql .= " limit 1";
        if($isLock) $sql .= " for update";
        $query = $this->db->query($sql);
        return $query->row_array();
    }

    /**
     *  获取用户信息
     */
    public function getUserInfo($uid)
    {
        if (intval($uid) < 1 || !$uid){
            return false;
        } 
        $user = $this->httpresponse->getHttpResponse(SA::$fenfenRpc, array('action'=>'getUserDetail', 'uid'=> intval($uid)));
        $userInfo = json_decode($user, true);     
        if(!$userInfo['resultData']){
            return false;
        }
        if(empty($userInfo['resultData']['userInfo']['nickname'])){
            return false;
        }
        return $userInfo['resultData']['userInfo'];
    }

    /**
     *  支付回调
     */
    public function webhook($redirect, $params)
    {
       $result = $this->httpresponse->getHttpResponse($redirect, $params);
    }
}