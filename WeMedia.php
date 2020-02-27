<?php
/*
Plugin Name: WeMedia付费阅读
Plugin URI: https://github.com/muzishanshi/WeMedia
Description: 本插件可以隐藏文章中的任意部分内容，当访客付费后，可查看隐藏内容，当前版本支持SPay支付宝、QQ、微信支付和payjs微信支付。
Version: 1.0.6
Author: 二呆
Author URI: https://www.tongleer.com/
Note: 请勿修改或删除以上信息
*/
define("TLE_WEMEDIA_VERSION",6);
if(isset($_GET['t'])){
	/*设置参数*/
    if($_GET['t'] == 'configwemedia'){
        update_option('tle_wemedia', array('isEnableJQuery' => $_REQUEST['isEnableJQuery'], 'wemedia_isdrop' => $_REQUEST['wemedia_isdrop'], 'wemedia_paytype' => $_REQUEST['wemedia_paytype'], 'wemedia_cookietime' => $_REQUEST['wemedia_cookietime'], 'spay_wxpay_id' => $_REQUEST['spay_wxpay_id'], 'spay_wxpay_key' => $_REQUEST['spay_wxpay_key'], 'spay_alipay_id' => $_REQUEST['spay_alipay_id'], 'spay_alipay_key' => $_REQUEST['spay_alipay_key'], 'spay_pay_notify_url' => $_REQUEST['spay_pay_notify_url'], 'spay_pay_return_url' => $_REQUEST['spay_pay_return_url'], 'payjs_wxpay_mchid' => $_REQUEST['payjs_wxpay_mchid'], 'payjs_wxpay_key' => $_REQUEST['payjs_wxpay_key'], 'payjs_wxpay_notify_url' => $_REQUEST['payjs_wxpay_notify_url'], 'payjs_wxpay_return_url' => $_REQUEST['payjs_wxpay_return_url']));
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
		<input class="tle_wemedia_id" id="tle_wemedia_id'.$id.'" data-id="'.$id.'" data-nonce="'.wp_create_nonce( 'tle-wemedia-post' ).'" type="text" value="'.get_post_meta( $id, 'tle_wemedia_submit', TRUE).'" />
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
      $settings = array('settings' => '<a href="admin.php?page=tle-wemedia">' . __('Settings') . '</a>');
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
	$wemedia_price=get_post_meta( get_the_ID(), 'tle_wemedia_submit', TRUE);
	if (preg_match_all('/<!--WeMedia start-->([\s\S]*?)<!--WeMedia end-->/i', $content, $hide_content)&&$wemedia_price){
		if(!isset($_COOKIE["TleWemediaPayCookie"])){
			$cookietime=$wemedia_configs["wemedia_cookietime"]==""?1:$wemedia_configs["wemedia_cookietime"];
			$randomCode=randomCode(10,1)[1];
			$TleWemediaPayCookie=$randomCode;
			//setcookie("TleWemediaPayCookie",$randomCode, time()+3600*24*$cookietime, COOKIEPATH, COOKIE_DOMAIN, false);
		}else{
			$TleWemediaPayCookie=$_COOKIE["TleWemediaPayCookie"];
		}
		$rowFeeItem = $wpdb->get_row( "SELECT * FROM `" . $wpdb->prefix . "wemedia_fee_item` where feecookie='".$TleWemediaPayCookie."' AND feestatus = 1 AND feecid = ".get_the_ID());
		if(!$rowFeeItem){
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
			$hide_notice='
				<div style="border:1px dashed #F60; padding:10px; margin:10px 0; line-height:200%; color:#F00; background-color:#FFF4FF; overflow:hidden; clear:both;">
					<span style="font-size:18px;">此处内容已经被作者隐藏，请付费后刷新页面查看内容</span>
					<form id="wemediaPayPost" method="post" style="margin:10px 0;" action="'.plugins_url().'/WeMedia/pay.php" target="_blank">
						<span class="yzts" style="font-size:18px;float:left;">方式：</span>
						<select id="feetype" name="feetype" style="border:none;float:left;width:160px; height:32px; line-height:30px; padding:0 5px; border:1px solid #FF6600;-moz-border-radius: 0px;  -webkit-border-radius: 0px;  border-radius:0px;">
							'.$wemedia_paytype.'
						</select>
						<div style="clear:left;"></div>
						<span class="yzts" style="font-size:18px;float:left;">价格：</span>
						<div style="border:none;float:left;width:80px; height:32px; line-height:30px; padding:0 5px; border:1px solid #FF6600;-moz-border-radius: 0px;  -webkit-border-radius: 0px;  border-radius:0px;">'.$wemedia_price.'</div>
						<input id="verifybtn" style="border:none;float:left;width:80px; height:32px; line-height:32px; padding:0 5px; background-color:#F60; text-align:center; border:none; cursor:pointer; color:#FFF;-moz-border-radius: 0px; font-size:14px;  -webkit-border-radius: 0px;  border-radius:0px;" name="" type="submit" value="付款" />
						<input type="hidden" name="action" value="paysubmit" />
						<input type="hidden" id="feecid" name="feecid" value="'.urlencode(get_the_ID()).'" />
						<input type="hidden" id="feepermalink" name="feepermalink" value="'.WeMediaCurPageURL().'" />
						<input type="hidden" id="feecookie" name="feecookie" value="'.$TleWemediaPayCookie.'" />
					</form>
					<div style="clear:left;"></div>
					<span style="color:#00BF30">点击付款支付后'.$wemedia_configs["wemedia_cookietime"].'天内即可阅读隐藏内容。</span><div class="cl"></div>
				</div>
			';
			$content = str_replace($hide_content[0], $hide_notice, $content);
		}else{
			$content = str_replace($hide_content[0], '<div style="border:1px dashed #F60; padding:10px; margin:10px 0; line-height:200%;  background-color:#FFF4FF; overflow:hidden; clear:both;">'.$hide_content[1][0].'</div>', $content);
		}
	}
	return $content;
}
add_action('wp_footer', 'tle_wemedia_wp_footer');
function tle_wemedia_wp_footer(){
	$wemedia_configs = get_settings('tle_wemedia');
	$wemedia_price=get_post_meta( get_the_ID(), 'tle_wemedia_submit', TRUE);
	?>
	<?php if(@$wemedia_configs['isEnableJQuery']=="y"){?>
	<script src="https://libs.baidu.com/jquery/1.11.1/jquery.min.js"></script>
	<?php }?>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/layer/2.3/layer.js"></script>
	<script>
		$("#wemediaPayPost").submit(function(){
			var str = "确认要花费￥<?=$wemedia_price;?>购买吗？";
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
					data : {"action":"paysubmit","wemedia_payjstype":wemedia_payjstype,"feepermalink":$("#feepermalink").val(),"feetype":$("#feetype").val(),"feecid":$("#feecid").val(),"feecookie":$("#feecookie").val()},
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
								open("<?=plugins_url();?>/WeMedia/pay.php?wemedia_payjstype="+wemedia_payjstype+"&feepermalink="+$("#feepermalink").val()+"&feetype="+$("#feetype").val()+"&feecid="+$("#feecid").val()+"&feeuid="+$("#feeuid").val()+"&feecookie="+$("#feecookie").val());
								return false;
							}
						}else{
							str="<center><div>请求支付过程出了一点小问题，稍后重试一次吧！</div></center>";
						}
						layer.confirm(str, {
							btn: ["已付款","算了"]
						},function(index){
							window.location.reload();
							layer.close(index);
						});
					},error:function(data){
						layer.close(ii);
						layer.msg("服务器错误");
						return false;
					}
				});
			});
			return false;
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
	tinyMCE.activeEditor.execCommand('mceInsertContent', 0, '\r\n<!--WeMedia start-->\r\n\r\n<!--WeMedia end-->');
   }
   </script>
   <?php
}

/*设置插件参数*/
add_action('admin_menu', 'tle_wemedia_menu');
function tle_wemedia_menu(){
    add_options_page('付费阅读', '付费阅读', 'manage_options', 'tle-wemedia', 'tle_wemedia_options');
}
function tle_wemedia_options(){
    $wemedia_configs = get_settings('tle_wemedia');
	?>
	<div class="wrap">
		<h2>付费阅读设置</h2>
		<form method="get" action="">
			<p>
				关于卸载：
				<input type="radio" name="wemedia_isdrop" value="n" <?=isset($wemedia_configs['wemedia_isdrop'])?($wemedia_configs['wemedia_isdrop']=="n"?"checked":""):"checked";?> />停用插件保留订单数据表及回调模板
				<input type="radio" name="wemedia_isdrop" value="y" <?=isset($wemedia_configs['wemedia_isdrop'])?($wemedia_configs['wemedia_isdrop']=="y"?"checked":""):"";?> />停用插件删除订单数据表及回调模板
			</p>
			<p>
				前台是否加载jquery：
				<input type="radio" name="isEnableJQuery" value="n" <?=isset($wemedia_configs['isEnableJQuery'])?($wemedia_configs['isEnableJQuery']=="n"?"checked":""):"";?> />否
				<input type="radio" name="isEnableJQuery" value="y" <?=isset($wemedia_configs['isEnableJQuery'])?($wemedia_configs['isEnableJQuery']!="n"?"checked":""):"checked";?> />是
			</p>
			<p>
				支付渠道(配置二选一)：
				<input type="radio" name="wemedia_paytype" value="spay" <?=isset($wemedia_configs['wemedia_paytype'])?($wemedia_configs['wemedia_paytype']=="spay"?"checked":""):"checked";?> />spay微信+支付宝支付（已放弃，不确定还能不能用）
				<input type="radio" name="wemedia_paytype" value="payjs" <?=isset($wemedia_configs['wemedia_paytype'])?($wemedia_configs['wemedia_paytype']=="payjs"?"checked":""):"";?> />payjs微信支付（推荐）
			</p>
			<p>
				<input type="number" id="wemedia_cookietime" name="wemedia_cookietime" placeholder="免登录Cookie时间(天)" value="<?=$wemedia_configs['wemedia_cookietime']!=""?$wemedia_configs['wemedia_cookietime']:1;?>" />
				指定使用免登录付费后几天内可以查看隐藏内容，默认为1天，不会记录到买入订单中。
			</p>
			<p><b>spay微信+支付宝支付</b></p>
			<p>
				<input type="text" name="spay_wxpay_id" placeholder="SPay微信支付合作ID" value="<?=$wemedia_configs['spay_wxpay_id'];?>" />
				SPay网站（主：http://spay.swapteam.cn/；副：http://spay.8889838.com）注册授权微信支付的合作身份者id。
			</p>
			<p>
				<input type="text" name="spay_wxpay_key" placeholder="SPay微信支付安全码" value="<?=$wemedia_configs['spay_wxpay_key'];?>" />
				SPay网站（主：http://spay.swapteam.cn/；副：http://spay.8889838.com）注册授权微信支付的安全检验码key。
			</p>
			<p>
				<input type="text" name="spay_alipay_id" placeholder="SPay支付宝支付合作ID" value="<?=$wemedia_configs['spay_alipay_id'];?>" />
				SPay网站（主：http://spay.swapteam.cn/；副：http://spay.8889838.com）注册授权支付宝支付的合作身份者id。
			</p>
			<p>
				<input type="text" name="spay_alipay_key" placeholder="SPay支付宝支付安全码" value="<?=$wemedia_configs['spay_alipay_key'];?>" />
				SPay网站（主：http://spay.swapteam.cn/；副：http://spay.8889838.com）注册授权支付宝支付的安全检验码key。
			</p>
			<p>
				<input type="text" name="spay_pay_notify_url" placeholder="SPay异步回调接口" value="<?=$wemedia_configs['spay_pay_notify_url'];?>" />
				支付完成后异步回调的接口地址，可自建模板为（付费阅读异步回调）的页面。
			</p>
			<p>
				<input type="text" name="spay_pay_return_url" placeholder="SPay同步回调接口" value="<?=$wemedia_configs['spay_pay_return_url'];?>" />
				支付完成后同步回调的接口地址，可自建模板为（付费阅读同步回调）的页面。
			</p>
			<p><b>payjs微信支付</b></p>
			<p>
				<input type="text" name="payjs_wxpay_mchid" placeholder="payjs商户号" value="<?=$wemedia_configs['payjs_wxpay_mchid'];?>" />
				在<a href="https://payjs.cn/" target="_blank">payjs官网</a>注册的商户号。
			</p>
			<p>
				<input type="text" name="payjs_wxpay_key" placeholder="payjs通信密钥" value="<?=$wemedia_configs['payjs_wxpay_key'];?>" />
				在<a href="https://payjs.cn/" target="_blank">payjs官网</a>注册的通信密钥。
			</p>
			<p>
				<input type="text" name="payjs_wxpay_notify_url" placeholder="payjs异步回调接口" value="<?php echo plugin_dir_url(__FILE__);?>wemedia_notify_url.php" readOnly />
				支付完成后异步回调的接口地址。
			</p>
			<p>
				<input type="text" name="payjs_wxpay_return_url" placeholder="payjs同步回调接口" value="<?php echo plugin_dir_url(__FILE__);?>wemedia_return_url.php" readOnly />
				支付完成后同步回调的接口地址。
			</p>
			<p>
				<input type="hidden" name="t" value="configwemedia" />
				<input type="hidden" name="page" value="tle-wemedia" />
				<input type="submit" value="保存配置" />
			</p>
		</form>
		<h2><font color="red">特别注意</font></h2>
		<p>
			选择spay支付时，支付宝最低单价为0.8元。
		</p>
		<h2>使用方法</h2>
		<p>
			1、使用spay支付时新建模板为“付费阅读同步回调”和“付费阅读异步回调”的页面；<br />
			2、配置以上参数；<br />
			3、在文章中点击右侧付费阅读框插入付费阅读标签 &lt;!--WeMedia start--> &lt;!--WeMedia end--> ，并在标签中间加入付费内容，这里需要注意：WP5.0版本以上需要在编辑器经典模式下进行，插入标签后可以在html模式下查看到，在标签中间添加完付费内容后，可以返回经典编辑器视图模式；<br />
			4、在文章列表处修改每篇的付费内容的单价，即可进行付费操作。
		</p>
	</div>
	<?php
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
?>