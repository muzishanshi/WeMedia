<?php
/*
	template name: 付费阅读spay同步回调
	description: SPay同步回调通知页面
*/
define('PATH', dirname(__FILE__).'/');
require_once(PATH . '../../../wp-config.php');  
require_once PATH . '../../plugins/WeMedia/libs/spay.php';
require_once PATH . '../../plugins/WeMedia/libs/payjs.php';
global $wpdb;
date_default_timezone_set('Asia/Shanghai');
$wemedia_configs = get_settings('tle_wemedia');

switch($wemedia_configs["wemedia_paytype"]){
	case "spay":
		$id = isset($_GET['id']) ? addslashes($_GET['id']) : '';
		$wxhao = isset($_GET['wxhao']) ? addslashes($_GET['wxhao']) : '';
		$feetype="";
		if($wxhao){
			$feetype="wx";
		}
		$is=spay_wpay_verify($id,$wemedia_configs["spay_wxpay_key"],$feetype);

		if($is!==false){
			echo "付款成功";
		}else{
			echo "付款失败";
		}
		break;
	case "payjs":
		
		break;
}
?>