<?php
/*
	template name: 付费阅读同步回调
	description: 同步回调通知页面
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
			$is=spay_pay_verify($wemedia_configs["spay_wxpay_key"],$id,$feetype);
			if($is!==false){
				echo "付款成功";
			}else{
				echo "付款失败";
			}
		}else{
			$feetype="alipay";
			if(spay_pay_verify($wemedia_configs["spay_alipay_key"])){
				$ts = $_GET['trade_status'];    
				if ($ts == 'TRADE_FINISHED' || $ts == 'TRADE_SUCCESS'){
					echo '付款成功';    
				}else{
					echo '付款失败';    
				}
			}else{
				echo '签名验证失败';    
			}
		}
		break;
	case "payjs":
		$out_trade_no = isset($_GET['id']) ? addslashes($_GET['id']) : 0;
		$url = isset($_GET['url']) ? addslashes(base64_decode($_GET['url'])) : "";
		$rowItem = $wpdb->get_row( "SELECT * FROM `" . $wpdb->prefix . "wemedia_fee_item` where feeid = '".$out_trade_no."'");
		if(@$rowItem->feestatus==1){
			?>
			<center><h1>付款成功<br /><a href="<?php echo $url;?>">返回</a></h1></center>
			<?php
		}else{
			?>
			<center><h1>付款失败了<br /><a href="<?php echo $url;?>">返回</a></h1></center>
			<?php
		}
		break;
}
?>