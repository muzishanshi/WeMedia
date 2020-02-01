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
		?>
		<link href="//cdn.bootcss.com/bootstrap/3.3.5/css/bootstrap.min.css" rel="stylesheet">
		<body background="https://ww2.sinaimg.cn/large/a15b4afegy1fpp139ax3wj200o00g073.jpg">
		<div class="container" style="padding-top:20px;">
			<div class="col-xs-12 col-sm-10 col-lg-8 center-block" style="float: none;">
				<div class="panel panel-primary">
					<div class="panel-body">
						<center>
							<div class="alert alert-success">
								<a href="<?php echo $url;?>">返回</a>
							</div>
						</center>
					</div>
				</div>
			</div>
		</div>
		</body>
		<?php
		exit;
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