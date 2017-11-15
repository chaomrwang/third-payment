<?php
defined('BASEPATH') OR exit('No direct script access allowed');
    
/**
 * 日志记录
 */

if ( ! function_exists('recordLog'))
{
    function recordLog($filename,$loginfo){
        //打开文件
        //$fd = fopen(APPPATH.'/logs'.'/'.$filename,"a");
        $fd = fopen('/data0/wwwlogs/trade/'.$filename,"a");
        //增加文件
        $str = "[".date("Y/m/d H:i:s",time())."]".$loginfo;
        //写入字符串 
        fwrite($fd, $str."\n");
        //关闭文件
        fclose($fd);
	}
}