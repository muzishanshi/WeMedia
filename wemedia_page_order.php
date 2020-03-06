<?php
global $wpdb,$current_user;

$goto = isset($_GET['goto']) ? addslashes($_GET['goto']) : '';
if($goto=="delWemediaItem"){
	$feeid = isset($_GET['feeid']) ? addslashes($_GET['feeid']) : "";
	$wpdb->delete($wpdb->prefix."wemedia_fee_item",array("feeid"=>$feeid));
	echo "<script>window.location.href='?page=tle-wemedia';</script>";
	exit;
}

$rows = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."wemedia_fee_item AS wfi,".$wpdb->prefix."posts AS p WHERE wfi.feecid=p.ID ORDER BY feeinstime DESC");
$orders=array();
$temi=0;
foreach($rows as $value){
	$orders[$temi]['feeid']=$value->feeid;
	$orders[$temi]['feecid']=$value->feecid;
	$orders[$temi]['feeprice']=$value->feeprice;
	$orders[$temi]['feetype']=$value->feetype;
	$orders[$temi]['feestatus']=$value->feestatus;
	$orders[$temi]['feeinstime']=$value->feeinstime;
	$orders[$temi]['feecookie']=$value->feecookie;
	$orders[$temi]['feemail']=$value->feemail;
	$orders[$temi]['feetitle']=$value->post_title;
	$temi++;
}
$page_now = isset($_GET['page_now']) ? intval($_GET['page_now']) : 1;
if($page_now<1){
	$page_now=1;
}
$page_rec=20;
$totalrec=count($rows);
$page=ceil($totalrec/$page_rec);
if($page_now>$page){
	$page_now=$page;
}
if($page_now<=1){
	$before_page=1;
	if($page>1){
		$after_page=$page_now+1;
	}else{
		$after_page=1;
	}
}else{
	$before_page=$page_now-1;
	if($page_now<$page){
		$after_page=$page_now+1;
	}else{
		$after_page=$page;
	}
}
$ordersArr = array_slice($orders, ($page_now-1)*$page_rec, $page_rec);
?>
<link rel="stylesheet" href="https://www.tongleer.com/cdn/amazeui/css/amazeui.min.css"/>
<script src="<?php bloginfo('template_url'); ?>/assets/js/jquery.min.js"></script>
<div class="wrap nosubsub">
<h1 class="wp-heading-inline">阅读付费订单</h1>
<hr class="wp-header-end">

<form id="posts-filter" method="get">
	<?php if(count($ordersArr)>0){?>
	<table class="am-table am-table-bordered am-table-striped am-text-nowrap">
		<thead>
		<tr>
			<th scope="col" id='feeid' class='manage-column column-feeid'>
				ID
			</th>
			<th scope="col" id='feecid' class='manage-column column-feecid'>
				文章
			</th>
			<th scope="col" id='feeprice' class='manage-column column-feeprice'>
				单价
			</th>
			<th scope="col" id='feetype' class='manage-column column-feetype'>
				渠道
			</th>
			<th scope="col" id='' class='manage-column column-feestatus'>
				状态
			</th>
			<th scope="col" id='' class='manage-column column-feeinstime'>
				时间
			</th>
			<th scope="col" id='feemail' class='manage-column column-feemail'>
				付款邮箱
			</th>
			<th scope="col" id='feeinstime' class='manage-column column-feeinstime'>
				cookie
			</th>
		</tr>
		</thead>
		<tbody id="the-list">
			<?php foreach($ordersArr as $value){?>
			<tr>
				<td><?=$value["feeid"];?></td>
				<td><a href="<?php echo "post.php?post=".$value["feecid"]."&action=edit";?>"><?=mb_strimwidth($value["feetitle"],0,25,"...");?></a></td>
				<td><?=$value["feeprice"];?></td>
				<td>
					<?php if($value["feetype"]=="alipay"){echo "支付宝";}else if($value["feetype"]=="wx"){echo "微信";}else if($value["feetype"]=="qqpay"){echo "QQ";}?>
				</td>
				<td>
					<?php
					if($value["feestatus"]==0){
						?>
						未付款&nbsp;<a href="?page=tle-wemedia&goto=delWemediaItem&feeid=<?=$value["feeid"];?>">删除</a>
						<?php
					}else if($value["feestatus"]==1){
						echo "<font color='green'>付款成功</font>";
					}else if($value["feestatus"]==2){
						echo "<font color='red'>付款失败</font>";
					}
					?>
				</td>
				<td><?=$value["feeinstime"];?></td>
				<td><?=$value["feemail"];?></td>
				<td><?=$value["feecookie"];?></td>
			</tr>
			<?php }?>
		</tbody>
	</table>
	<ul class="am-pagination blog-pagination">
	  <?php if($page_now!=1){?>
		<li class="am-pagination-prev" style="float:left;margin-right:10px;"><a href="?page=tle-wemedia&page_now=1">首页</a></li>
	  <?php }?>
	  <?php if($page_now>1){?>
		<li class="am-pagination-prev" style="float:left;margin-right:10px;"><a href="?page=tle-wemedia&page_now=<?=$before_page;?>">&laquo; 上一页</a></li>
	  <?php }?>
	  <?php if($page_now<$page){?>
		<li class="am-pagination-next" style="float:left;margin-right:10px;"><a id="tlenextpage" href="?page=tle-wemedia&page_now=<?=$after_page;?>">下一页 &raquo;</a></li>
	  <?php }?>
	  <?php if($page_now!=$page){?>
		<li class="am-pagination-next" style="float:left;margin-right:10px;"><a href="?page=tle-wemedia&page_now=<?=$page;?>">尾页</a></li>
	  <?php }?>
	</ul>
	<div class="tablenav bottom">

		<br class="clear" />
	</div>
	<?php }else{?>
	暂无订单条目
	<?php }?>
<div id="ajax-response"></div>
</form>
</div>