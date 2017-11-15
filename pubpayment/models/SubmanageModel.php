<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class SubmanageModel extends CI_Model {

    private $db;
    public function __construct()
    {
        parent::__construct();
        $this->db = $this->load->database('xxtuser', true);
        $this->httpresponse = new Httpresponse();
    }

    /**
     *  获取订阅号运营基本信息
     */
    public function getSubscriptionInfo($uid)
    {
        if(intval($uid) < 1) return false;
        $sql = "select * from verification_info_0 where uid = {$uid} limit 1";
        $result = $this->db->query($sql)->row_array();
        return $result;
    }

    /**
     *  修改信息
     */
    public function updateSubscriptionInfo($set, $where)
    {
        $updateRow = $this->db->update('verification_info_0', $set, $where);
        return $updateRow;
    }

    /**
     *  新增开发者信息
     */
    public function addDeveloper($data)
    {
        $this->db->insert('subscribe_developer_0', $data);
        $appId = $this->db->insert_id();
        return $appId;
    }

    /**
     *  获取开发者信息
     */
    public function getDeveloper($uid)
    {
        if(intval($uid) < 1) return false;
        $sql = "select * from subscribe_developer_0 where uid = {$uid} and status = 0 limit 1";
        $result = $this->db->query($sql)->row_array();
        return $result;
    }

    /**
     *  根据appId获取开发者信息
     */
    public function getDeveloperByAppId($appId)
    {
        $sql = "select * from subscribe_developer_0 where id = {$appId} and status = 0 limit 1";
        $result = $this->db->query($sql)->row_array();
        return $result;
    }

    /**
     *  修改开发者信息
     */
    public function updateDeveloper($set, $where)
    {
        $updateRow = $this->db->update('subscribe_developer_0', $set, $where);
        return $updateRow;
    }

    /**
     *  修改oauth_client信息
     */
    public function updateOauthClient($set, $where)
    {
        $updateRow = $this->db->update('oauth_clients_0', $set, $where);
        return $updateRow;
    }

    /**
     *  查询域名列表
     */
    public function getDomainList($uid, $code)
    {
        if(intval($uid) < 1) return false;
        $sql = "select * from subscribe_auth_0 where uid = {$uid} and func_code = '{$code}' and status = 0 order by create_time desc";
        $result = $this->db->query($sql)->result_array();
        return $result;
    }

    /**
     *  查询域名是否存在
     */
    public function getDomainInfo($uid, $domaim, $code)
    {
        if(intval($uid) < 1) return false;
        $sql = "select * from subscribe_auth_0 where uid = {$uid} and domain = '{$domaim}' and func_code = '{$code}' and status = 0 limit 1";
        $result = $this->db->query($sql)->row_array();
        return $result;
    }

    /**
     *  新增域名配置
     */
    public function addDomainConf($data)
    {
        $this->db->insert('subscribe_auth_0', $data);
        $result = $this->db->insert_id();
        return $result;
    }

    /**
     *  修改域名配置
     */
    public function updateDomainConf($set, $where)
    {
        $updateRow = $this->db->update('subscribe_auth_0', $set, $where);
        return $updateRow;
    }

    /**
     *  查询有权限的接口列表
     */
    public function getAuthApiList($uid, $domain)
    {
        if(intval($uid) < 1) return false;
        $sql = "select * from subscribe_api_auth_0 where uid = {$uid} and domain = '{$domain}' and status = 0";
        $result = $this->db->query($sql)->result_array();
        return $result;
    }

    /**
     *  查询所有功能权限
     */
    public function getAuthCodes()
    {
        $sql = "select * from subscribe_func_0 where status = 0";
        $result = $this->db->query($sql)->result_array();
        return $result;
    }
}   