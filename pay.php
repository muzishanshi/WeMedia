<?php
define('PATH', dirname(__FILE__).'/');
require_once(PATH . '../../../wp-config.php');  
require_once PATH . 'libs/spay.php';
global $wpdb;
date_default_timezone_set('Asia/Shanghai');

$action = isset($_POST['action']) ? addslashes($_POST['action']) : '';
if($action=='spaysubmit'){
	$feetype = isset($_POST['feetype']) ? addslashes($_POST['feetype']) : '';
	$feecookie = isset($_POST['feecookie']) ? addslashes($_POST['feecookie']) : '';
	$cid = isset($_POST['cid']) ? intval(urldecode($_POST['cid'])) : '';
	$uid = isset($_POST['uid']) ? intval($_POST['uid']) : 0;
	
	$wemedia_configs = get_settings('tle_wemedia');
	
	$pdata['orderNumber']=date("YmdHis") . rand(100000, 999999);
	$pdata['Money']=get_post_meta( $cid, 'tle_wemedia_submit', TRUE);
	$pdata['Notify_url']=$wemedia_configs["spay_wxpay_notify_url"];
	$pdata['Return_url']=$wemedia_configs["spay_wxpay_return_url"];
	$pdata['SPayId']=$wemedia_configs["spay_wxpay_id"];
	
	$ret=spay_wpay_pay($pdata,$wemedia_configs["spay_wxpay_key"],$feetype);
	$url=$ret['url'];
	if($url!=''){
		$data = array(
			'feeid'   =>  $pdata['orderNumber'],
			'feecid'   =>  $cid,
			'feeuid'     =>  $uid,
			'feeprice'=>$pdata['Money'],
			'feetype'     =>  $feetype,
			'feestatus'=>0,
			'feeinstime'=>date('Y-m-d H:i:s',time()),
			'feecookie'=>$feecookie
		);
		$result = $wpdb->insert($wpdb->prefix."wemedia_fee_item",$data);
		header("Location: {$url}");
	}else{
		?>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		订单号:
		<?php echo $ret['orderNumber'];?><br>
		付款金额:
		<?php echo $ret['Money'];?><br>
		扫描以下二维码进行付款,
		请在
		<?php echo $ret['LatestPayTime'];?>
		之前付款完成 
		否则将有可能支付不到账<br>
		<?php
		  if(!empty($ret['error']))
			die('出现错误:'.$ret['error'])
		?>
		<img src="<?php echo $ret['qrcode'];?>">
		<?php
	}
}
?>