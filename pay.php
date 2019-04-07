<?php
define('PATH', dirname(__FILE__).'/');
require_once(PATH . '../../../wp-config.php');  
require_once PATH . 'libs/spay.php';
require_once PATH . 'libs/payjs.php';
global $wpdb;
date_default_timezone_set('Asia/Shanghai');

$action = isset($_POST['action']) ? addslashes($_POST['action']) : '';
if($action=='paysubmit'){
	$feetype = isset($_POST['feetype']) ? addslashes($_POST['feetype']) : '';
	$feecookie = isset($_POST['feecookie']) ? addslashes($_POST['feecookie']) : '';
	$feecid = isset($_POST['feecid']) ? intval(urldecode($_POST['feecid'])) : '';
	$feeuid = isset($_POST['feeuid']) ? intval($_POST['feeuid']) : 0;
	
	$wemedia_configs = get_settings('tle_wemedia');
	
	switch($wemedia_configs["wemedia_paytype"]){
		case "spay":
			$pdata['orderNumber']=date("YmdHis") . rand(100000, 999999);
			$pdata['Money']=get_post_meta( $feecid, 'tle_wemedia_submit', TRUE);
			$pdata['Notify_url']=$wemedia_configs["spay_wxpay_notify_url"];
			$pdata['Return_url']=$wemedia_configs["spay_wxpay_return_url"];
			$pdata['SPayId']=$wemedia_configs["spay_wxpay_id"];
			
			$ret=spay_wpay_pay($pdata,$wemedia_configs["spay_wxpay_key"],$feetype);
			$url=$ret['url'];
			if($url!=''){
				$data = array(
					'feeid'   =>  $pdata['orderNumber'],
					'feecid'   =>  $feecid,
					'feeuid'     =>  $feeuid,
					'feeprice'=>$pdata['Money'],
					'feetype'     =>  $feetype,
					'feestatus'=>0,
					'feeinstime'=>date('Y-m-d H:i:s',time()),
					'feecookie'=>$feecookie
				);
				$result = $wpdb->insert($wpdb->prefix."wemedia_fee_item",$data);
				if($result){
					$json=json_encode(array("status"=>"ok","type"=>"spay","qrcode"=>$url));
					echo $json;
					exit;
				}
			}
			break;
		case "payjs":
			$Money = get_post_meta( $feecid, 'tle_wemedia_submit', TRUE);
			
			$posts = $wpdb->get_row( "SELECT * FROM `" . $wpdb->prefix . "posts` where ID = ".$feecid);
			
			$title = mb_strimwidth(strip_tags( addslashes($posts->post_title)),0, 20,'...');
			
			$time=time();
			$arr = [
				'body' => $title,               // 订单标题
				'out_trade_no' => date("YmdHis",$time) . rand(100000, 999999),       // 订单号
				'total_fee' => $Money*100,             // 金额,单位:分
				'attach'=>$Money// 自定义数据
			];
			$payjs = new Payjs($arr,$wemedia_configs["payjs_wxpay_mchid"],$wemedia_configs["payjs_wxpay_key"],$wemedia_configs["payjs_wxpay_notify_url"]);
			$res = $payjs->pay();
			$rst=json_decode($res,true);
			if($rst["return_code"]==1){
				$data = array(
					'feeid'   =>  $arr['out_trade_no'],
					'feecid'   =>  $feecid,
					'feeuid'     =>  $feeuid,
					'feeprice'=>$Money,
					'feetype'     =>  $feetype,
					'feestatus'=>0,
					'feeinstime'=>date('Y-m-d H:i:s',$time),
					'feecookie'=>$feecookie
				);
				$result = $wpdb->insert($wpdb->prefix."wemedia_fee_item",$data);
				if($result){
					$json=json_encode(array("status"=>"ok","type"=>"payjs","qrcode"=>$rst["qrcode"]));
					echo $json;
					exit;
				}
				
			}
			break;
	}
	$json=json_encode(array("status"=>"fail"));
	echo $json;
	exit;
}
?>