<div class="wrap">
    <h1></h1>
    <form method="get" action="">
      <?php $wemedia_configs = get_settings('tle_wemedia');?>
      <h2>参数设置</h2>
      <table class="form-table">
		<tr>
          <th scope="row"><label for="paywall-default-status">关于停用</label></th>
          <td>
            <fieldset>
              <legend class="screen-reader-text"><span>关于停用</span></legend>
              <p>
                <label><input name="wemedia_isdrop" type="radio" value="n" <?=isset($wemedia_configs['wemedia_isdrop'])?($wemedia_configs['wemedia_isdrop']=="n"?"checked":""):"checked";?>> 停用插件保留订单数据表及SPay回调模板</label><br>
                <label><input name="wemedia_isdrop" type="radio" value="y" <?=isset($wemedia_configs['wemedia_isdrop'])?($wemedia_configs['wemedia_isdrop']=="y"?"checked":""):"";?>> 停用插件删除订单数据表及SPay回调模板</label>
              </p>
            </fieldset>
            <p class="description"></p>
          </td>
        </tr>
		<tr>
          <th scope="row"><label for="paywall-default-status">前台是否加载jquery</label></th>
          <td>
            <fieldset>
              <legend class="screen-reader-text"><span>前台是否加载jquery</span></legend>
              <p>
                <label><input name="isEnableJQuery" type="radio" value="n" <?=isset($wemedia_configs['isEnableJQuery'])?($wemedia_configs['isEnableJQuery']=="n"?"checked":""):"";?>> 否</label><br>
                <label><input name="isEnableJQuery" type="radio" value="y" <?=isset($wemedia_configs['isEnableJQuery'])?($wemedia_configs['isEnableJQuery']!="n"?"checked":""):"checked";?>> 是</label>
              </p>
            </fieldset>
            <p class="description"></p>
          </td>
        </tr>
		<tr>
          <th scope="row"><label for="paywall-default-amount">默认单价</label></th>
          <td>
            <input value="<?=$wemedia_configs['wemedia_default_price']!=""?$wemedia_configs['wemedia_default_price']:1;?>" size="4" maxlength="4" id="wemedia_default_price" name="wemedia_default_price" type="text" placeholder="默认阅读单价">元
            <p class="description">为文章设置默认付费金额</p>
          </td>
        </tr>
		<tr>
          <th scope="row"><label for="paywall-default-amount">默认付款表单标题</label></th>
          <td>
            <input value="<?=$wemedia_configs['wemedia_default_title']?$wemedia_configs['wemedia_default_title']:"此处内容已经被作者隐藏，请付费后刷新页面查看内容";?>" size="50" maxlength="30" name="wemedia_default_title" type="text" placeholder="付款表单默认标题">
            <p class="description">为付款表单设置默认标题</p>
          </td>
        </tr>
		<tr>
          <th scope="row"><label for="paywall-default-status">以何种方式保存订单</label></th>
          <td>
            <fieldset>
              <legend class="screen-reader-text"><span>以何种方式保存订单</span></legend>
              <p>
                <label><input class="wemedia_itemtype" name="wemedia_itemtype" type="radio" value="mail" <?=isset($wemedia_configs['wemedia_itemtype'])?($wemedia_configs['wemedia_itemtype']=="mail"?"checked":""):"checked";?>> 邮箱</label><br>
                <label><input class="wemedia_itemtype" name="wemedia_itemtype" type="radio" value="" <?=isset($wemedia_configs['wemedia_itemtype'])?($wemedia_configs['wemedia_itemtype']==""?"checked":""):"";?>> cookie(不推荐,受浏览器cookie等影响.)</label>
              </p>
            </fieldset>
            <p class="description"></p>
          </td>
        </tr>
		<tr class="itemtype-cookie" <?=isset($wemedia_configs['wemedia_itemtype'])?($wemedia_configs['wemedia_itemtype']=="mail"?'style="display:none;"':''):'style="display:none;"';?>>
          <th scope="row"><label for="paywall-default-amount">免登录Cookie时间(天)</label></th>
          <td>
            <input value="<?=$wemedia_configs['wemedia_cookietime']!=""?$wemedia_configs['wemedia_cookietime']:1;?>" name="wemedia_cookietime" type="number" size="2" maxlength="2" step="1" max="9999" min="1" placeholder="免登录Cookie时间(天)">天
            <p class="description">
              指定使用免登录付费后几天内可以查看隐藏内容，默认为1天。
            </p>
          </td>
        </tr>
		<tr class="itemtype-mail">
          <th scope="row"><label for="paywall-default-amount">SMTP服务器</label></th>
          <td>
            <input value="<?=$wemedia_configs['wemedia_mailsmtp'];?>" name="wemedia_mailsmtp" type="text" placeholder="SMTP服务器地址">
            <p class="description">邮箱服务器的SMTP地址，如smtp.exmail.qq.com</p>
          </td>
        </tr>
		<tr class="itemtype-mail">
          <th scope="row"><label for="paywall-default-amount">SMTP端口</label></th>
          <td>
			<input type="number" name="wemedia_mailport" placeholder="SMTP服务器端口" value="<?=$wemedia_configs['wemedia_mailport'];?>" />
            <p class="description">邮箱服务器的SMTP端口，如465</p>
          </td>
        </tr>
		<tr class="itemtype-mail">
          <th scope="row"><label for="paywall-default-amount">SMTP用户名</label></th>
          <td>
			<input type="text" name="wemedia_mailuser" placeholder="SMTP服务器用户名" value="<?=$wemedia_configs['wemedia_mailuser'];?>" />
            <p class="description">邮箱服务器的SMTP用户名</p>
          </td>
        </tr>
		<tr class="itemtype-mail">
          <th scope="row"><label for="paywall-default-amount">SMTP密码</label></th>
          <td>
			<input type="password" name="wemedia_mailpass" placeholder="SMTP服务器密码" value="<?=$wemedia_configs['wemedia_mailpass'];?>" />
            <p class="description">邮箱服务器的SMTP密码</p>
          </td>
        </tr>
		<tr class="itemtype-mail">
          <th scope="row"><label for="paywall-default-amount">SMTP安全类型</label></th>
          <td>
			<select name="wemedia_mailsecure">
				<option value="ssl" <?=$wemedia_configs["wemedia_mailsecure"]=="ssl"?"selected":"";?>>SSL</option>
				<option value="tls" <?=$wemedia_configs["wemedia_mailsecure"]=="tls"?"selected":"";?>>TLS</option>
				<option value="none" <?=$wemedia_configs["wemedia_mailsecure"]=="none"?"selected":"";?>>无</option>
			</select>
            <p class="description">邮箱服务器的SMTP安全类型</p>
          </td>
        </tr>
        <tr>
          <th scope="row"><label for="paywall-default-status">支付渠道</label></th>
          <td>
            <fieldset>
              <legend class="screen-reader-text"><span>支付渠道</span></legend>
              <p>
                <label><input class="wemedia_charge" name="wemedia_paytype" type="radio" value="spay" <?=isset($wemedia_configs['wemedia_paytype'])?($wemedia_configs['wemedia_paytype']=="spay"?"checked":""):"checked";?>> spay支付(不推荐,已放弃,可用度未知.)</label><br>
                <label><input class="wemedia_charge" name="wemedia_paytype" type="radio" value="payjs" <?=isset($wemedia_configs['wemedia_paytype'])?($wemedia_configs['wemedia_paytype']=="payjs"?"checked":""):"";?>> payjs支付</label>
              </p>
            </fieldset>
            <p class="description"></p>
          </td>
        </tr>
		<tr class="charge-payjs">
          <th scope="row"><label for="paywall-default-amount">payjs支付商户号</label></th>
          <td>
            <input value="<?=$wemedia_configs['payjs_wxpay_mchid'];?>" name="payjs_wxpay_mchid" type="text" placeholder="payjs商户号">
            <p class="description">
              在<a href="https://payjs.cn/" target="_blank">payjs官网</a>注册的商户号。
            </p>
          </td>
        </tr>
		<tr class="charge-payjs">
          <th scope="row"><label for="paywall-default-amount">payjs支付通信密钥</label></th>
          <td>
			<input type="password" name="payjs_wxpay_key" placeholder="payjs通信密钥" value="<?=$wemedia_configs['payjs_wxpay_key'];?>" />
            <p class="description">
              在<a href="https://payjs.cn/" target="_blank">payjs官网</a>注册的通信密钥。
            </p>
			<input type="hidden" name="payjs_wxpay_notify_url" placeholder="payjs异步回调接口" value="<?php echo plugin_dir_url(__FILE__);?>wemedia_notify_url.php" readOnly />
			<input type="hidden" name="payjs_wxpay_return_url" placeholder="payjs同步回调接口" value="<?php echo plugin_dir_url(__FILE__);?>wemedia_return_url.php" readOnly />
          </td>
        </tr>
		<tr class="charge-spay" style="display:none;">
          <th scope="row"><label for="paywall-default-amount">SPay微信QQ支付合作ID</label></th>
          <td>
            <input value="<?=$wemedia_configs['spay_wxpay_id'];?>" name="spay_wxpay_id" type="text" placeholder="SPay微信QQ支付合作ID">
            <p class="description">
              SPay网站（主：http://spay.swapteam.cn/；副：http://spay.8889838.com）注册授权微信支付的合作身份者id。
            </p>
          </td>
        </tr>
		<tr class="charge-spay" style="display:none;">
          <th scope="row"><label for="paywall-default-amount">SPay微信QQ支付安全码</label></th>
          <td>
            <input value="<?=$wemedia_configs['spay_wxpay_key'];?>" name="spay_wxpay_key" type="password" placeholder="SPay微信QQ支付安全码">
            <p class="description">
              SPay网站（主：http://spay.swapteam.cn/；副：http://spay.8889838.com）注册授权微信支付的安全检验码key。
            </p>
          </td>
        </tr>
		<tr class="charge-spay" style="display:none;">
          <th scope="row"><label for="paywall-default-amount">SPay支付宝支付合作ID</label></th>
          <td>
            <input value="<?=$wemedia_configs['spay_alipay_id'];?>" name="spay_alipay_id" type="text" placeholder="SPay支付宝支付合作ID">
            <p class="description">
              SPay网站（主：http://spay.swapteam.cn/；副：http://spay.8889838.com）注册授权支付宝支付的合作身份者id。
            </p>
          </td>
        </tr>
		<tr class="charge-spay" style="display:none;">
          <th scope="row"><label for="paywall-default-amount">SPay支付宝支付安全码</label></th>
          <td>
            <input value="<?=$wemedia_configs['spay_alipay_key'];?>" name="spay_alipay_key" type="password" placeholder="SPay支付宝支付安全码">
            <p class="description">
              SPay网站（主：http://spay.swapteam.cn/；副：http://spay.8889838.com）注册授权支付宝支付的安全检验码key。
            </p>
          </td>
        </tr>
		<tr class="charge-spay" style="display:none;">
          <th scope="row"><label for="paywall-default-amount">SPay异步回调接口</label></th>
          <td>
            <input value="<?=$wemedia_configs['spay_pay_notify_url'];?>" name="spay_pay_notify_url" type="text" placeholder="SPay异步回调接口">
            <p class="description">
              支付完成后异步回调的接口地址，可自建模板为（付费阅读异步回调）的页面。
            </p>
          </td>
        </tr>
		<tr class="charge-spay" style="display:none;">
          <th scope="row"><label for="paywall-default-amount">SPay同步回调接口</label></th>
          <td>
            <input value="<?=$wemedia_configs['spay_pay_return_url'];?>" name="spay_pay_return_url" type="password" placeholder="SPay同步回调接口">
            <p class="description">
              支付完成后同步回调的接口地址，可自建模板为（付费阅读同步回调）的页面。
            </p>
          </td>
        </tr>
		<tr>
          <th scope="row"><label for="paywall-default-amount">手机端同步回调页广告位</label></th>
          <td>
			<textarea name="wemedia_ad_return" rows="3" cols="50" placeholder="手机端同步回调页广告位广告代码"><?=$wemedia_configs['wemedia_ad_return']?$wemedia_configs['wemedia_ad_return']:"广告位";?></textarea>
            <p class="description">
              手机端同步回调页广告位
            </p>
          </td>
        </tr>
      </table>
	  <input type="hidden" name="t" value="configwemedia" />
	  <input type="hidden" name="page" value="tle-wemedia-set" />
      <?php submit_button(); ?>
    </form>
	<h2>注意事项</h2>
	<p>
		1、在文章中点击右侧付费阅读框插入付费阅读标签 <font color="blue">&lt;!--WeMedia start--> &lt;!--WeMedia end--> </font>，WP5.0版本以上需要在编辑器经典模式下进行，插入标签后可以在html模式下查看到，在标签中间添加完付费内容后，可以返回编辑器视图模式。<font color="red">或者</font>，使用<font color="blue">[WeMedia][/WeMedia]</font>标签可直接在可视化模式插入付费内容；<br />
		2、在<font color="red">文章列表</font>处修改每篇的付费内容的单价，即可进行付费操作；<br />
		3、选择payjs支付时，单笔限额及单日限额参考payjs官方说明。<br />
		4、选择spay支付时，支付宝最低单价为<font color="red">0.8</font>元；
	</p>
  </div>
  <script type="text/javascript">
    (function($) {
		$("#wemedia_default_price").keyup(function(){
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
		$(".wemedia_itemtype").change(function(){
			if($("input[name='wemedia_itemtype']:checked").val()==""){
				$(".itemtype-cookie").css("display","table-row");
				$(".itemtype-mail").css("display","none");
			}else{
				$(".itemtype-cookie").css("display","none");
				$(".itemtype-mail").css("display","table-row");
			}
		});
		$(".wemedia_charge").change(function(){
			if($("input[name='wemedia_paytype']:checked").val()=="payjs"){
				$(".charge-payjs").css("display","table-row");
				$(".charge-spay").css("display","none");
			}else{
				$(".charge-payjs").css("display","none");
				$(".charge-spay").css("display","table-row");
			}
		});
    })(jQuery);
  </script>