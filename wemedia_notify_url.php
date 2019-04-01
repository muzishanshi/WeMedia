<?php
/*
	template name: 付费阅读异步回调
	description: SPay支付异步回调通知页面
*/
define('PATH', dirname(__FILE__).'/');
require_once(PATH . '../../../wp-config.php');  
require_once PATH . '../../plugins/WeMedia/libs/spay.php';
global $wpdb;
date_default_timezone_set('Asia/Shanghai');
$wemedia_configs = get_settings('tle_wemedia');

$id = isset($_POST['id']) ? addslashes($_POST['id']) : '';
$wxhao = isset($_POST['wxhao']) ? addslashes($_POST['wxhao']) : '';
$feetype="";
if($wxhao){
	$feetype="wx";
}
$is=spay_wpay_verify($id,$wemedia_configs["spay_wxpay_key"],$feetype);

if($is!==false){
	$result=$wpdb->update($wpdb->prefix."wemedia_fee_item",array('feestatus'=>1),array("feeid"=>$is["orderNumber"]));
	echo 'success';
}else{
	echo 'fail';
}
?>