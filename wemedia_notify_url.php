<?php
/*
	template name: 付费阅读异步回调
	description: 支付异步回调通知页面
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
		$id = isset($_POST['id']) ? addslashes($_POST['id']) : '';
		$wxhao = isset($_POST['wxhao']) ? addslashes($_POST['wxhao']) : '';
		$feetype="";
		if($wxhao){
			$feetype="wx";
			$is=spay_pay_verify($wemedia_configs["spay_wxpay_key"],$id,$feetype);
			if($is!==false){
				$result=$wpdb->update($wpdb->prefix."wemedia_fee_item",array('feestatus'=>1),array("feeid"=>$is["orderNumber"]));
				echo 'success';
			}else{
				echo 'fail';
			}
		}else{
			$feetype="alipay";
			if(spay_pay_verify($wemedia_configs["spay_alipay_key"])){
				$ts = $_POST['trade_status'];    
				if ($ts == 'TRADE_FINISHED' || $ts == 'TRADE_SUCCESS'){
					$result=$wpdb->update($wpdb->prefix."wemedia_fee_item",array('feestatus'=>1),array("feeid"=>$_POST["out_trade_no"]));
					echo 'success';    
				}else{
					echo 'fail';    
				}
			}else{
				echo 'fail';    
			}
		}
		break;
	case "payjs":
		$data = $_POST;
		if($data['return_code'] == 1){
			$payjs = new Payjs("","",$wemedia_configs["payjs_wxpay_key"],"");
			$sign_verify = $data['sign'];
			unset($data['sign']);
			if($payjs->sign($data) == $sign_verify&&$data['total_fee']==$data['attach']*100){
				$result=$wpdb->update($wpdb->prefix."wemedia_fee_item",array('feestatus'=>1),array("feeid"=>$data['out_trade_no']));
				echo 'success';
			}
		}
		break;
}
?>