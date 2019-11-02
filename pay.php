<?php
if(!isset($_SESSION)){session_start();}
define('PATH', dirname(__FILE__).'/');
require_once(PATH . '../../../wp-config.php');  
require_once PATH . 'libs/spay.php';
require_once PATH . 'libs/payjs.php';
global $wpdb;
date_default_timezone_set('Asia/Shanghai');

//防止CC攻击，频繁F5刷新
$timestamp = time();
$ll_nowtime = $timestamp ; //判断session是否存在如果存在从session取值，如果不存在进行初始化赋值
if ($_SESSION){
	$ll_lasttime = $_SESSION['ll_lasttime'];
	$ll_times = $_SESSION['ll_times'] + 1;
	$_SESSION['ll_times'] = $ll_times;
}else{
	$ll_lasttime = $ll_nowtime;
	$ll_times = 1;
	$_SESSION['ll_times'] = $ll_times;
	$_SESSION['ll_lasttime'] = $ll_lasttime;
}
if(($ll_nowtime - $ll_lasttime) < 3){//现在时间-开始登录时间 来进行判断 如果登录频繁 跳转 否则对session进行赋值
	if ($ll_times>=5){
		header("location:http://127.0.0.1");exit;//可以换成其他链接，比如站内的404错误显示页面(千万不要用动态页面)
	}
}else{
	$ll_times = 0; $_SESSION['ll_lasttime'] = $ll_nowtime; $_SESSION['ll_times'] = $ll_times;
}

$action = isset($_POST['action']) ? addslashes($_POST['action']) : '';
if($action=='paysubmit'){
	$wemedia_payjstype = isset($_POST['wemedia_payjstype']) ? addslashes($_POST['wemedia_payjstype']) : '';
	$feepermalink = isset($_POST['feepermalink']) ? addslashes($_POST['feepermalink']) : '';
	$feetype = isset($_POST['feetype']) ? addslashes($_POST['feetype']) : '';
	$feecookie = isset($_POST['feecookie']) ? addslashes($_POST['feecookie']) : '';
	$feecid = isset($_POST['feecid']) ? intval(urldecode($_POST['feecid'])) : '';
	$feeuid = isset($_POST['feeuid']) ? intval($_POST['feeuid']) : 0;
	
	$wemedia_configs = get_settings('tle_wemedia');
	
	switch($wemedia_configs["wemedia_paytype"]){
		case "spay":
			$time=time();
			$orderNumber=date("YmdHis",$time) . rand(100000, 999999);
			if($feetype=="wx"){
				$pdata['orderNumber']=$orderNumber;
				$pdata['Money']=get_post_meta( $feecid, 'tle_wemedia_submit', TRUE);
				$pdata['Notify_url']=$wemedia_configs["spay_pay_notify_url"];
				$pdata['Return_url']=$wemedia_configs["spay_pay_return_url"];
				$pdata['SPayId']=$wemedia_configs["spay_wxpay_id"];
				$ret=spay_pay_pay($pdata,$wemedia_configs["spay_wxpay_key"],$feetype);
				$Money=$pdata['Money'];
			}else if($feetype=="alipay"){
				$data['total_fee'] = get_post_meta( $feecid, 'tle_wemedia_submit', TRUE);
				$data['partner']= $wemedia_configs["spay_alipay_id"];
				$data['notify_url']= $wemedia_configs["spay_pay_notify_url"];
				$data['return_url']= $wemedia_configs["spay_pay_return_url"];
				$data['out_trade_no']= $orderNumber;
				$ret=spay_pay_pay($data,$wemedia_configs["spay_alipay_key"]);
				$Money=$data['total_fee'];
			}
			$url=$ret['url'];
			if($url!=''){
				$data = array(
					'feeid'   =>  $orderNumber,
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
					$json=json_encode(array("status"=>"ok","type"=>"spay","channel"=>$feetype,"qrcode"=>$url));
					echo $json;
					exit;
				}
			}
			break;
		case "payjs":
			switch($wemedia_payjstype){
				case "native":
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
					$payjs_wxpay_return_url=$wemedia_configs["payjs_wxpay_return_url"]."?id=".$arr['out_trade_no']."&url=".base64_encode($feepermalink);
					$payjs = new Payjs($arr,$wemedia_configs["payjs_wxpay_mchid"],$wemedia_configs["payjs_wxpay_key"],$payjs_wxpay_return_url,$wemedia_configs["payjs_wxpay_notify_url"]);
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
							$json=json_encode(array("status"=>"ok","type"=>"native","channel"=>"wx","qrcode"=>$rst["qrcode"]));
							echo $json;
							exit;
						}
						
					}
					break;
				case "cashier":
					$json=json_encode(array("status"=>"ok","type"=>"cashier"));
					echo $json;
					exit;
					break;
			}
			break;
	}
	$json=json_encode(array("status"=>"fail"));
	echo $json;
	exit;
}else{
	$wemedia_payjstype = isset($_GET['wemedia_payjstype']) ? addslashes($_GET['wemedia_payjstype']) : '';
	$feepermalink = isset($_GET['feepermalink']) ? addslashes($_GET['feepermalink']) : '';
	$feetype = isset($_GET['feetype']) ? addslashes($_GET['feetype']) : '';
	$feecookie = isset($_GET['feecookie']) ? addslashes($_GET['feecookie']) : '';
	$feecid = isset($_GET['feecid']) ? intval(urldecode($_GET['feecid'])) : '';
	$feeuid = isset($_GET['feeuid']) ? intval($_GET['feeuid']) : 0;
	
	$wemedia_configs = get_settings('tle_wemedia');
	
	switch($wemedia_configs["wemedia_paytype"]){
		case "payjs":
			switch($wemedia_payjstype){
				case "cashier":
					$cashierapi="https://payjs.cn/api/cashier";
					$Money = get_post_meta( $feecid, 'tle_wemedia_submit', TRUE);
					
					$posts = $wpdb->get_row( "SELECT * FROM `" . $wpdb->prefix . "posts` where ID = ".$feecid);
					
					$title = mb_strimwidth(strip_tags( addslashes($posts->post_title)),0, 20,'...');
					$time=time();
					$arr = [
						'body' => $title,               // 订单标题
						'out_trade_no' => date("YmdHis",$time) . rand(100000, 999999),       // 订单号
						'total_fee' => $Money*100,             // 金额,单位:分
						'attach' => $Money// 自定义数据
					];
					$payjs_wxpay_return_url=$wemedia_configs["payjs_wxpay_return_url"]."?id=".$arr['out_trade_no']."&url=".base64_encode($feepermalink);
					$payjs = new Payjs($arr,$wemedia_configs["payjs_wxpay_mchid"],$wemedia_configs["payjs_wxpay_key"],$payjs_wxpay_return_url,$wemedia_configs["payjs_wxpay_notify_url"],$cashierapi);
					$data = $arr;
					$data['mchid'] = $wemedia_configs["payjs_wxpay_mchid"];
					$data['callback_url'] = $payjs_wxpay_return_url;
					$data['notify_url'] = $wemedia_configs["payjs_wxpay_notify_url"];
					$data['auto'] = 1;
					$data['hide'] = 1;
					$sign = $payjs->sign($data);
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
						echo '
							<form id="payform" action="'.$cashierapi.'" method="post">
								<input type="hidden" name="mchid" value="'.$wemedia_configs["payjs_wxpay_mchid"].'" />
								<input type="hidden" name="total_fee" value="'.$arr["total_fee"].'" />
								<input type="hidden" name="out_trade_no" value="'.$arr["out_trade_no"].'" />
								<input type="hidden" name="body" value="'.$arr["body"].'" />
								<input type="hidden" name="attach" value="'.$arr["attach"].'" />
								<input type="hidden" name="callback_url" value="'.$payjs_wxpay_return_url.'" />
								<input type="hidden" name="notify_url" value="'.$wemedia_configs["payjs_wxpay_notify_url"].'" />
								<input type="hidden" name="auto" value="1" />
								<input type="hidden" name="hide" value="1" />
								<input type="hidden" name="sign" value="'.$sign.'" />
							</form>
							<script>document.getElementById("payform").submit();</script>
						';
					}
					break;
			}
			break;
	}
}
?>