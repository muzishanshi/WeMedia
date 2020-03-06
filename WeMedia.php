<?php
/*
Plugin Name: WeMedia付费阅读
Plugin URI: https://github.com/muzishanshi/WeMedia
Description: 本插件可以隐藏文章中的任意部分内容，当访客付费后，可查看隐藏内容，当前版本支持payjs微信支付。
Version: 1.0.7
Author: 二呆
Author URI: https://www.tongleer.com/
Note: 请勿修改或删除以上信息
*/
define("TLE_WEMEDIA_VERSION",7);
if(isset($_GET['t'])){
	/*设置参数*/
    if($_GET['t'] == 'configwemedia'){
        update_option('tle_wemedia', array('wemedia_ad_return' => $_REQUEST['wemedia_ad_return'],'wemedia_default_price' => $_REQUEST['wemedia_default_price'],'wemedia_default_title' => $_REQUEST['wemedia_default_title'],'wemedia_mailsmtp' => $_REQUEST['wemedia_mailsmtp'],'wemedia_mailport' => $_REQUEST['wemedia_mailport'],'wemedia_mailuser' => $_REQUEST['wemedia_mailuser'],'wemedia_mailpass' => $_REQUEST['wemedia_mailpass'],'wemedia_mailsecure' => $_REQUEST['wemedia_mailsecure'],'wemedia_itemtype' => $_REQUEST['wemedia_itemtype'], 'isEnableJQuery' => $_REQUEST['isEnableJQuery'], 'wemedia_isdrop' => $_REQUEST['wemedia_isdrop'], 'wemedia_paytype' => $_REQUEST['wemedia_paytype'], 'wemedia_cookietime' => $_REQUEST['wemedia_cookietime'], 'spay_wxpay_id' => $_REQUEST['spay_wxpay_id'], 'spay_wxpay_key' => $_REQUEST['spay_wxpay_key'], 'spay_alipay_id' => $_REQUEST['spay_alipay_id'], 'spay_alipay_key' => $_REQUEST['spay_alipay_key'], 'spay_pay_notify_url' => $_REQUEST['spay_pay_notify_url'], 'spay_pay_return_url' => $_REQUEST['spay_pay_return_url'], 'payjs_wxpay_mchid' => $_REQUEST['payjs_wxpay_mchid'], 'payjs_wxpay_key' => $_REQUEST['payjs_wxpay_key'], 'payjs_wxpay_notify_url' => $_REQUEST['payjs_wxpay_notify_url'], 'payjs_wxpay_return_url' => $_REQUEST['payjs_wxpay_return_url']));
    }
	/*设置付费单价*/
	if($_GET['t']=='updateprice'){
		if(!empty($_POST['action'])){
			switch ($_POST['action']) {
			  case 'updateprice':
				$postid = sanitize_text_field($_POST['postid']);
				$price = isset($_POST['price']) ? addslashes($_POST['price']) : '';
				update_post_meta($postid, 'tle_wemedia_submit', $price);
				break;
			}
			exit;
		}
	}
	/*版本检测*/
	if($_GET['t']=='updateversion'){
		$version = isset($_POST['version']) ? addslashes($_POST['version']) : '';
		$version=file_get_contents('https://www.tongleer.com/api/interface/WeMedia.php?action=updateForWordpress&version='.$version);
		echo $version;
		exit;
	}
}
register_activation_hook(__FILE__,'wemedia_install');    
register_deactivation_hook( __FILE__, 'wemedia_remove' );
function wemedia_install() {   
	global $wpdb;
	createTableWemediaFeeItem($wpdb);
	alterColumnWemediaFeeItem($wpdb,DB_NAME,$wpdb->prefix.'wemedia_fee_item','feeitemtype','varchar(11) DEFAULT NULL COMMENT "保存订单类型：默认空为cookie；mail为邮箱保存。"');
	alterColumnWemediaFeeItem($wpdb,DB_NAME,$wpdb->prefix.'wemedia_fee_item','feemail','varchar(64) DEFAULT NULL COMMENT "付款邮箱"');
	funWriteThemePage($wpdb,"wemedia_notify_url.php");
	funWriteThemePage($wpdb,"wemedia_return_url.php");
}
function wemedia_remove() {
	global $wpdb;
	$wemedia_configs = get_settings('tle_wemedia');
	if(isset($wemedia_configs["wemedia_isdrop"])&&$wemedia_configs["wemedia_isdrop"]=="y"){
		dropTableWemediaFeeItem($wpdb);
		funDeleteThemePage($wpdb,"wemedia_notify_url.php");
		funDeleteThemePage($wpdb,"wemedia_return_url.php");
	}
}
function createTableWemediaFeeItem($wpdb) {   
	$wpdb->query('CREATE TABLE IF NOT EXISTS `'.$wpdb->prefix.'wemedia_fee_item` (
	  `feeid` varchar(64) COLLATE utf8_general_ci NOT NULL,
	  `feecid` bigint(20) DEFAULT NULL,
	  `feeuid` bigint(20) DEFAULT NULL,
	  `feeprice` double(10,2) DEFAULT NULL,
	  `feetype` enum("alipay","wxpay","wx","WEIXIN_DAIXIAO","qqpay","bank_pc","tlepay") COLLATE utf8_general_ci DEFAULT "alipay",
	  `feestatus` smallint(2) DEFAULT "0" COMMENT "订单状态：0、未付款；1、付款成功；2、付款失败",
	  `feeinstime` datetime DEFAULT NULL,
	  `feecookie` varchar(255) DEFAULT NULL,
	  PRIMARY KEY (`feeid`)
	) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;');
}
/*修改数据表字段*/
function alterColumnWemediaFeeItem($wpdb,$dbname,$table,$column,$define){
	$row = $wpdb->get_row("select * from information_schema.columns WHERE TABLE_SCHEMA='".$dbname."' and table_name = '".$table."' AND column_name = '".$column."'");
	if(empty($row)){
		$wpdb->query('ALTER TABLE `'.$table.'` ADD COLUMN `'.$column.'` '.$define.';');
	}
}
function dropTableWemediaFeeItem($wpdb) {
	$wpdb->query('drop table if exists `'.$wpdb->prefix.'wemedia_fee_item`;');
}
function funWriteThemePage($wpdb,$filename){
	$rowTheme = $wpdb->get_row( "SELECT option_value FROM `" . $wpdb->prefix . "options` where option_name='template'");
	$path=dirname(__FILE__).'/../../themes/'.$rowTheme->option_value;
	if(!is_writable($path)){
		die("主题目录不可写，请更改目录权限，最好为777。");
	}
	if(!file_exists($path."/".$filename)){
		$regfile = @fopen(dirname(__FILE__)."/".$filename, "r") or die("不能读取".$filename."文件");
		$regtext=fread($regfile,filesize(dirname(__FILE__)."/".$filename));
		fclose($regfile);
		$regpage = fopen($path."/".$filename, "w") or die("不能写入".$filename."文件");
		fwrite($regpage, $regtext);
		fclose($regpage);
	}
}
function funDeleteThemePage($wpdb,$filename) {
	$rowTheme = $wpdb->get_row( "SELECT option_value FROM `" . $wpdb->prefix . "options` where option_name='template'");
	@unlink(dirname(__FILE__).'/../../themes/'.$rowTheme->option_value.'/'.$filename);
}

add_action( 'admin_init', 'tle_wemedia_admin_init' );
function tle_wemedia_admin_init() {
	if( current_user_can( 'manage_options' ) ) {
		add_filter('manage_post_posts_columns', 'tle_wemedia_add_post_columns');
		add_action('manage_posts_custom_column', 'tle_wemedia_render_post_columns', 10, 2);
		
		add_action( 'admin_enqueue_scripts', 'tle_wemedia_scripts' );
		add_filter( 'plugin_action_links', 'tle_wemedia_add_link', 10, 2 );
	}
}
function tle_wemedia_add_post_columns($columns) {
    $columns['wemedia_price_name'] = '设置付费单价';
    return $columns;
}
function tle_wemedia_render_post_columns($column_name, $id) {
    switch ($column_name) {
    case 'wemedia_price_name':
		echo '
		<input class="tle_wemedia_id" id="tle_wemedia_id'.$id.'" data-id="'.$id.'" data-nonce="'.wp_create_nonce( 'tle-wemedia-post' ).'" type="text" value="'.get_post_meta( $id, 'tle_wemedia_submit', TRUE).'" size="8" maxLength="8" />
	  ';
		break;
    }
}
function tle_wemedia_scripts(){
	/*
	wp_register_script( 'tle_wemedia_jquery', 'https://libs.baidu.com/jquery/1.11.1/jquery.min.js');  
	wp_enqueue_script( 'tle_wemedia_jquery' );
	wp_register_script( 'tle_wemedia_js', plugins_url('js/wemedia.js',__FILE__) );  
	wp_enqueue_script( 'tle_wemedia_js' );
	*/
	?>
	<script src="https://libs.baidu.com/jquery/1.11.1/jquery.min.js"></script>
	<script>
	$(function(){
		$(".tle_wemedia_id").each(function(){
			var id=$(this).attr("id");
			$("#"+id).change( function () {
				$.post("admin.php?page=tle-wemedia&t=updateprice",{action:"updateprice",postid:$(this).attr("data-id"),price:$(this).val(),tle_wemedia_post_nonce:$(this).attr("data-nonce"),original:1},function(data){
				});
			});
			$(this).keyup(function(){
				/*先把非数字的都替换掉，除了数字和.*/
				$(this).val($(this).val().replace(/[^\d.]/g,""));
				/*保证只有出现一个.而没有多个.*/
				$(this).val($(this).val().replace(/\.{2,}/g,"."));
				/*必须保证第一个为数字而不是.*/
				$(this).val($(this).val().replace(/^\./g,""));
				/*保证.只出现一次，而不能出现两次以上*/
				$(this).val($(this).val().replace(".","$#$").replace(/\./g,"").replace("$#$","."));
				/*只能输入两个小数*/
				$(this).val($(this).val().replace(/^(\-)*(\d+)\.(\d\d).*$/,"$1$2.$3"));
			});
		});
		$.post("admin.php?page=tle-wemedia&t=updateversion",{version:$("#versionCode").attr("data-code")},function(data){
			$("#versionCode").html(data);
		});
		$("#wemedia_cookietime").keyup(function(){
			/*先把非数字的都替换掉，除了数字和.*/
			$(this).val($(this).val().replace(/[^\d.]/g,""));
			/*保证只有出现一个.而没有多个.*/
			$(this).val($(this).val().replace(/\.{2,}/g,"."));
			/*必须保证第一个为数字而不是.*/
			$(this).val($(this).val().replace(/^\./g,""));
			/*保证.只出现一次，而不能出现两次以上*/
			$(this).val($(this).val().replace(".","$#$").replace(/\./g,"").replace("$#$","."));
			/*只能输入两个小数*/
			$(this).val($(this).val().replace(/^(\d+)$/,"$1"));
		});
	});
	</script>
	<?php
}
function tle_wemedia_add_link( $actions, $plugin_file ) {
  static $plugin;
  if (!isset($plugin))
    $plugin = plugin_basename(__FILE__);
  if ($plugin == $plugin_file) {
      $settings = array('settings' => '<a href="admin.php?page=tle-wemedia-set">' . __('Settings') . '</a>');
      $site_link  = array('version'=>'<span id="versionCode" data-code="'.TLE_WEMEDIA_VERSION.'"></span>','contact' => '<a href="http://mail.qq.com/cgi-bin/qm_share?t=qm_mailme&email=diamond0422@qq.com" target="_blank">反馈</a>','support' => '<a href="https://www.tongleer.com/api/web/pay.png" target="_blank">打赏</a>','club' => '<a href="http://club.tongleer.com" target="_blank">论坛</a>');
      $actions  = array_merge($settings, $actions);
      $actions  = array_merge($site_link, $actions);
  }
  return $actions;
}

function tle_wemedia_setcookie() {
    $wemedia_configs = get_settings('tle_wemedia');
	if(!isset($_COOKIE["TleWemediaPayCookie"])){
		$cookietime=$wemedia_configs["wemedia_cookietime"]==""?1:$wemedia_configs["wemedia_cookietime"];
		$randomCode=randomCode(10,1)[1];
		setcookie("TleWemediaPayCookie",$randomCode, time()+3600*24*$cookietime, COOKIEPATH, COOKIE_DOMAIN, false);
	}
}
add_action( 'init', 'tle_wemedia_setcookie');

/*前台显示付费*/
add_filter('the_content', 'tle_wemedia_content');
function tle_wemedia_content($content){
	global $wpdb;
	$wemedia_configs = get_settings('tle_wemedia');
	$wemedia_price=get_post_meta( get_the_ID(), 'tle_wemedia_submit', TRUE)?get_post_meta( get_the_ID(), 'tle_wemedia_submit', TRUE):($wemedia_configs["wemedia_default_price"]?$wemedia_configs["wemedia_default_price"]:0);
	if (preg_match_all('/<!--WeMedia start-->([\s\S]*?)<!--WeMedia end-->/i', $content, $matches)){
		$hide_content=$matches;
	}else if(preg_match_all('/\[WeMedia\]([\s\S]*?)\[\/WeMedia\]/i', $content, $matches)){
		$hide_content=$matches;
	}else{
		$hide_content="";
	}
	if($hide_content){
		if (is_single()) {
			$html = preg_replace('/<!--WeMedia start-->([\s\S]*?)<!--WeMedia end-->/i', '<div style="border:1px dashed #F60; padding:10px; margin:10px 0; line-height:200%;  background-color:#FFF4FF; overflow:hidden; clear:both;">'.($wemedia_price?'<font color="red">本部分为付费内容，您已获得阅读权限</font><br />':'').'$1</div>', $content);
			$html = preg_replace('/\[WeMedia\]([\s\S]*?)\[\/WeMedia\]/i', '<div style="border:1px dashed #F60; padding:10px; margin:10px 0; line-height:200%;  background-color:#FFF4FF; overflow:hidden; clear:both;">'.($wemedia_price?'<font color="red">本部分为付费内容，您已获得阅读权限</font><br />':'').'$1</div>', $html);
		}else{
			$html = str_replace($hide_content[0], '<div style="border:1px dashed #F60; padding:10px; margin:10px 0; line-height:200%;  background-color:#FFF4FF; overflow:hidden; clear:both;">'.($wemedia_price?'<font color="red">本部分为付费内容，您已获得阅读权限</font><br />':'').'</div>', $content);
		}
	}
	if (!current_user_can('edit_post', $post->ID)&&$hide_content&&$wemedia_price){
		$isPay=false;
		if($wemedia_configs["wemedia_itemtype"]==""){
			if(!isset($_COOKIE["TleWemediaPayCookie"])){
				$randomCode=randomCode(10,1)[1];
				$TleWemediaPayCookie=$randomCode;
			}else{
				$TleWemediaPayCookie=$_COOKIE["TleWemediaPayCookie"];
			}
			$feeItemForCookie = $wpdb->get_row( "SELECT * FROM `" . $wpdb->prefix . "wemedia_fee_item` where feecookie='".$TleWemediaPayCookie."' AND feestatus = 1 AND feecid = ".get_the_ID());
			if($feeItemForCookie){
				$isPay=true;
			}
		}else if($wemedia_configs["wemedia_itemtype"]=="mail"){
			$TleWemediaPayMail = isset($_GET['TleWemediaPayMail']) ? addslashes(trim($_GET['TleWemediaPayMail'])) : '';
			if($TleWemediaPayMail){
				$feeItemForMail = $wpdb->get_row( "SELECT * FROM `" . $wpdb->prefix . "wemedia_fee_item` where feestatus = 1 AND feecid = ".get_the_ID()." AND feemail='".$TleWemediaPayMail."'");
				if($feeItemForMail){
					$isPay=true;
				}
			}
		}
		if(!$isPay){
			if($wemedia_configs["wemedia_paytype"]=="spay"){
				$wemedia_paytype='
					<option value="wx">微信支付</option>
					<option value="alipay">支付宝支付</option>
					<!--
					<option value="qqpay">QQ钱包支付</option>
					<option value="bank_pc">网银支付</option>
					-->
				';
			}else if($wemedia_configs["wemedia_paytype"]=="payjs"){
				$wemedia_paytype='
					<option value="wx">微信支付</option>
				';
			}
			foreach ($hide_content[0] as $k => $m) {
				if (is_single()) {
					if ($k == 0) {
						$hide_notice='
							<div class="wemedia-box wemedia-center">
								<!--<div class="wemedia-mask"></div>-->
								<div class="wemedia-lock"><span class="icon-lock-m"></span></div>
								<div style="border:1px dashed #F60; padding:10px; margin:10px 0; line-height:200%; color:#F00; background-color:#FFF4FF; overflow:hidden; clear:both;">
									<span style="font-size:18px;">'.($wemedia_configs["wemedia_default_title"]?$wemedia_configs["wemedia_default_title"]:"此处内容已经被作者隐藏，请付费后刷新页面查看内容").'</span>
									<form id="wemediaPayPost" method="post" style="margin:10px 0;" action="'.plugins_url().'/WeMedia/pay.php" target="_blank">
										<span class="yzts" style="font-size:18px;float:left;"></span>
										<select id="feetype" name="feetype" style="border:none;width:160px; height:32px; line-height:30px; padding:0 5px; border:1px solid #FF6600;-moz-border-radius: 0px;  -webkit-border-radius: 0px;  border-radius:0px;">
											'.$wemedia_paytype.'
										</select>
										<div style="clear:left;"></div>
										<span class="yzts" style="font-size:18px;float:left;"></span>
										<div style="width:160px; height:32px; line-height:30px;margin:0 auto; padding:0 5px; border:1px solid #FF6600;-moz-border-radius: 0px;  -webkit-border-radius: 0px;  border-radius:0px;">￥'.$wemedia_price.'</div>
										'.($wemedia_configs["wemedia_itemtype"]==""?'':'<input style="border:none;width:160px; height:32px; line-height:30px; padding:0 5px; border:1px solid #FF6600;-moz-border-radius: 0px;  -webkit-border-radius: 0px;  border-radius:0px;" type="email" id="feemail" name="feemail" placeholder="输入个人邮箱" />').'
										<div style="clear:left;"></div>
										<input id="verifybtn" style="border:none;width:160px; height:32px; line-height:32px; padding:0 5px; background-color:#F60; text-align:center; border:none; cursor:pointer; color:#FFF;-moz-border-radius: 0px; font-size:14px;  -webkit-border-radius: 0px;  border-radius:0px;" name="" type="submit" value="付款" />
										<input type="hidden" name="action" value="paysubmit" />
										<input type="hidden" id="feecid" name="feecid" value="'.urlencode(get_the_ID()).'" />
										<input type="hidden" id="feepermalink" name="feepermalink" value="'.WeMediaCurPageURL().'" />
										<input type="hidden" id="feecookie" name="feecookie" value="'.$TleWemediaPayCookie.'" />
									</form>
									<div style="clear:left;"></div>
									'.($wemedia_configs["wemedia_itemtype"]==""?'<span style="color:#00BF30">点击付款支付后'.$wemedia_configs["wemedia_cookietime"].'天内即可阅读隐藏内容。</span>':'<a style="color:#00BF30" id="wemediaPayQuery" href=":;" onClick="return false;">已付款？点击查看(可能会有几秒延迟)</a>').'
									<div class="cl"></div>
									<div style="display:none;" id="wemedia_itemtype">'.$wemedia_configs["wemedia_itemtype"].'</div>
								</div>
							</div>
						';
					}else{
						$hide_notice='
							<div class="wemedia-box wemedia-center">
								<div style="border:1px dashed #F60; padding:10px; margin:10px 0; line-height:200%; color:#F00; background-color:#FFF4FF; overflow:hidden; clear:both;">
									<span style="font-size:18px;">'.($wemedia_configs["wemedia_default_title"]?$wemedia_configs["wemedia_default_title"]:"此处内容已经被作者隐藏，请付费后刷新页面查看内容").'</span>
								</div>
							</div>
						';
					}
				}else{
					$hide_notice="【".($wemedia_configs["wemedia_default_title"]?$wemedia_configs["wemedia_default_title"]:"此处内容已经被作者隐藏，请付费后刷新页面查看内容")."】";
				}
				$content = str_replace_once_for_wemedia($m, $hide_notice, $content);
			}
		}else{
			$content = $html;
		}
	}else{
		if($hide_content){
			$content = $html;
		}else{
			$content=str_replace("[WeMedia]","",$content);
			$content=str_replace("[/WeMedia]","",$content);
		}
	}
	return $content;
}
add_action('wp_head', 'tle_wemedia_wp_header');
function tle_wemedia_wp_header(){
	include "assets/css/wemedia.min.css.php";
}
add_action('wp_footer', 'tle_wemedia_wp_footer');
function tle_wemedia_wp_footer(){
	$wemedia_configs = get_settings('tle_wemedia');
	$wemedia_price=get_post_meta( get_the_ID(), 'tle_wemedia_submit', TRUE)?get_post_meta( get_the_ID(), 'tle_wemedia_submit', TRUE):($wemedia_configs["wemedia_default_price"]?$wemedia_configs["wemedia_default_price"]:0);
	?>
	<?php if(@$wemedia_configs['isEnableJQuery']=="y"){?>
	<script src="https://libs.baidu.com/jquery/1.11.1/jquery.min.js"></script>
	<?php }?>
	<script src="https://www.tongleer.com/cdn/layui/layui.js"></script>
	<script type='text/javascript' src="<?php echo plugins_url(); ?>/WeMedia/assets/js/jquery.cookie.js"></script>
	<script>
		layui.use("layer", function(){
			var $ = layui.jquery, layer = layui.layer;
			$("#wemediaPayQuery").click(function(){
				if($("#feemail").val()==""){
					layer.msg("必须要输入个人邮箱");
					return;
				}
				$.ajax({
					type : "POST",
					url : "<?php echo plugins_url(); ?>/WeMedia/pay.php",
					data : {action:"wemediaPayQuery",feemail:$("#feemail").val(),feecid:$("#feecid").val()},
					dataType : "text",
					success : function(data) {
						var data=JSON.parse(data);
						if(data.code==0){
							location.href="<?=WeMediaCurPageURL().(strpos(WeMediaCurPageURL(),"?")?"&":"?")."TleWemediaPayMail=";?>"+$("#feemail").val();
						}else{
							layer.msg("您还没有付费，请付费后查看。");
						}
					}
				});
			});
			$("#wemediaPayPost").submit(function(){
				var str = "确认要花费￥<?=$wemedia_price;?>购买吗？";
				if($("#wemedia_itemtype").text()=="mail"){
					if($("#feemail").val()==""){
						layer.msg("必须要输入个人邮箱");
						return false;
					}
					str += "<input style=\"border:none;float:left;width:80%; height:32px; line-height:30px; padding:0 5px; border:1px solid #DDD;-moz-border-radius: 0px;  -webkit-border-radius: 0px;  border-radius:0px;\" type=\"text\" id=\"feemailcode\" name=\"feemailcode\" placeholder=\"邮箱验证码\" /><input style=\"border:none;float:left;width:20%;height:32px; line-height:32px; padding:0 5px; background-color:#DDD; text-align:center; border:none; cursor:pointer; color:#222;-moz-border-radius: 0px; font-size:14px;  -webkit-border-radius: 0px;  border-radius:0px;\" type=\"button\" id=\"btnSendCode\" value=\"发送\" /><script>$(\"#feemailcode\").focus();if($.cookie(\"mailCodeCookie\")){var count=$.cookie(\"mailCodeCookie\");$(\"#btnSendCode\").attr(\"disabled\",true);$(\"#btnSendCode\").val(count+\"秒\");var resend = setInterval(function(){count--;if (count > 0){$(\"#btnSendCode\").val(count+\"秒\");$.cookie(\"mailCodeCookie\", count, {path: \"/\", expires: (1/86400)*count});}else {$(\"#btnSendCode\").attr(\"disabled\", false);clearInterval(resend);$(\"#btnSendCode\").val(\"发送\");}}, 1000);}$(\"#btnSendCode\").click(function(){if($(\"#btnSendCode\").val()!=\"发送\"){return;}$(\"#btnSendCode\").val(\"发送中...\");$.post(\"<?php echo plugins_url(); ?>/WeMedia/pay.php\",{action:\"sendMailCode\",feemail:$(\"#feemail\").val()},function(data){var data=JSON.parse(data);if(data.code==0){alert(data.msg);var count = 60; var inl = setInterval(function () {$(\"#btnSendCode\").attr(\"disabled\", true); count -= 1; var text = count + \" 秒\";$.cookie(\"mailCodeCookie\", count, {path: \"/\", expires: (1/86400)*count}); $(\"#btnSendCode\").val(text); if (count <= 0) {clearInterval(inl); $(\"#btnSendCode\").attr(\"disabled\", false); $(\"#btnSendCode\").val(\"发送\"); }}, 1000);}else{alert(data.msg);}});});<\/script>";
				}
				
				layer.confirm(str, {
					btn: ["付款","算了"]
				}, function(){
					var ii = layer.load(2, {shade:[0.1,"#fff"]});
					var wemedia_payjstype="native";
					if(isWemediaWeiXin()){
						wemedia_payjstype="cashier";
					}
					$.ajax({
						type : "POST",
						url : "<?=plugins_url();?>/WeMedia/pay.php",
						data : {"action":"paysubmit","wemedia_payjstype":wemedia_payjstype,"feepermalink":$("#feepermalink").val(),"feetype":$("#feetype").val(),"feecid":$("#feecid").val(),"feecookie":$("#feecookie").val(),feemail:$("#feemail").val(),feemailcode:$("#feemailcode").val()},
						dataType : "json",
						success : function(data) {
							layer.close(ii);
							if(data.status=="ok"){
								if(data.type=="spay"){
									if(data.channel=="wx"){
										str='<center><div>支持微信付款</div><div><img src="https://www.tongleer.com/api/web/?action=qrcode&url='+data.qrcode+'" width="200" /></div><div><a href="'+data.qrcode+'" target="_blank">跳转支付链接</a></div></center>';
									}else if(data.channel=="alipay"){
										str='<center><div>支持支付宝付款</div><div><a href="'+data.qrcode+'" target="_blank">跳转支付链接</a></div></center>';
									}
									
								}else if(data.type=="native"){
									str='<center><div>支持微信付款</div><div><img src="'+data.qrcode+'" width="200" /></div></center>';
								}else if(data.type=="cashier"){
									open("<?=plugins_url();?>/WeMedia/pay.php?wemedia_payjstype="+wemedia_payjstype+"&feepermalink="+$("#feepermalink").val()+"&feetype="+$("#feetype").val()+"&feecid="+$("#feecid").val()+"&feeuid="+$("#feeuid").val()+"&feecookie="+$("#feecookie").val()+"&feemail="+$("#feemail").val()+"&feemailcode="+$("#feemailcode").val());
									return false;
								}
								layer.confirm(str, {
									btn: ["已付款","算了"]
								},function(index){
									window.location.reload();
									layer.close(index);
								});
							}else{
								alert(data.msg);
							}
						},error:function(data){
							layer.close(ii);
							layer.msg("服务器错误");
							return false;
						}
					});
				});
				return false;
			});
		});
		
		function isWemediaWeiXin(){
			var ua = window.navigator.userAgent.toLowerCase();
			if(ua.match(/MicroMessenger/i) == "micromessenger"){
				return true;
			}else{
				return false;
			}
		}
	</script>
	<?php
}

function randomCode($codeLength, $codeCount){
	$str1 = '1234567890';
	$str2 = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
	$str3 = 'abcdefghijklmnopqrstuvwxyz';
	$arr = [$str1 , $str2 , $str3] ;
	$code_list = array();//接收随机数的数组
	for ($j = 1; $j <= $codeCount; $j++) {//生产制定个数
		$code = "";
		for ($i = 1; $i <= $codeLength; $i++) {//生成指定位随机数
			$str = implode('',$arr);
			$code .= $str[mt_rand(0, strlen($str) - 1)];
		}
		if (!in_array($code, $code_list)) {
			$code_list[$j] = $code;
		} else {
			$j--;
		}
	}
	return $code_list;
}
/*插入付费阅读标签*/
add_action('add_meta_boxes', 'tle_wemedia_box');
function tle_wemedia_box(){
	if( current_user_can( 'manage_options' ) ) {
		add_meta_box('tle_wemedia_div', __('付费阅读'), 'tle_wemedia_html', 'post', 'side');
	}
}
function tle_wemedia_html(){
	echo '<script>var tle_wemedia_url="' . admin_url('options-general.php?page=tle-wemedia&t=insert') . '";</script>';
   ?>
   <div onClick="insertWemedia();" style="width:auto;height:20px;border:3px dashed silver;line-height:20px; text-align:center; font-size:20px; color:#d3d3d3;cursor:pointer;">插入付费阅读标签</div>
   <script>
   function insertWemedia(){
	tinyMCE.activeEditor.execCommand('mceInsertContent', 0, '\r\n[WeMedia]\r\n\r\n[/WeMedia]');
   }
   </script>
   <?php
}

//添加插件菜单到后台侧边栏主菜单（WeMedia付费阅读）
function tle_wemedia_menu(){
  add_menu_page( 'WeMedia付费阅读', 'WeMedia付费阅读', 0, 'tle-wemedia','tle_wemedia_options_order','',15);
}
function tle_wemedia_options_order(){
	include ('wemedia_page_order.php');
}
add_action('admin_menu', 'tle_wemedia_menu');
//添加插件菜单到后台侧边栏子菜单（WeMedia付费阅读-设置）
add_action('admin_menu', 'tle_wemedia_menu_set');
function tle_wemedia_menu_set() {
  add_submenu_page( 'tle-wemedia', '设置', '设置', 10, 'tle-wemedia-set', 'tle_wemedia_options' );
}
function tle_wemedia_options() {
	include ('wemedia_page_setting.php');
}

function WeMediaCurPageURL(){
	$pageURL = 'http';
	if ($_SERVER["HTTPS"] == "on"){
		$pageURL .= "s";
	}
	$pageURL .= "://";
	if ($_SERVER["SERVER_PORT"] != "80"){
		$pageURL .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"] . $_SERVER["REQUEST_URI"];
	}else{
		$pageURL .= $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
	}
	return $pageURL;
}
function str_replace_once_for_wemedia($needle, $replace, $haystack) {
    $pos = strpos($haystack, $needle);
    if ($pos === false) {
        return $haystack;
    }
    return substr_replace($haystack, $replace, $pos, strlen($needle));
}
?>