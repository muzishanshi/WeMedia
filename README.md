### WeMediaForWordPress付费阅读插件
---

本插件可以隐藏文章中的任意部分内容，当访客付费后，可查看隐藏内容，当前版本支持SPayQQ微信支付宝支付和payjs微信支付。

#### 安装方法：
第一步：下载本插件，放在 `wp-content/plugins/` 目录中（插件文件夹名必须为WeMedia）；

第二步：激活插件；

第三步：填写配置；

第四步：完成。
#### 特别注意：
选择spay支付时，支付宝最低单价为0.8元。

#### 使用方法：
1、配置参数；

2、在文章中点击右侧付费阅读框插入付费阅读标签 &lt;!--WeMedia start--> &lt;!--WeMedia end--> ，并在标签中间加入付费内容，这里需要注意：WP5.0版本以上需要在编辑器经典模式下进行，插入标签后可以在html模式下查看到，在标签中间添加完付费内容后，可以返回经典编辑器视图模式；

3、在文章列表处修改每篇的付费内容的单价，即可进行付费操作。

#### 版本说明：
此插件使用php5.6+Wordpress5.0.2编写

#### 与我联系：
作者：二呆

网站：http://www.tongleer.com/

Github：https://github.com/muzishanshi/WeMedia

#### 更新记录：
2020-03-07 V1.0.8

	1、新增邮箱下订单，避免cookie带来的困扰；
	2、新增订单列表，方便查看订单信息；
	3、新增可设置多段隐藏内容；
	4、新增手机端回调页面广告位及优化其他代码等；
	5、新增邮箱查询是否支付时需要验证邮箱。

2019-11-02 V1.0.6

	整合payjs的扫码支付和收银台支付为一体，重新适配电脑和微信端支付情况。

2019-10-02 V1.0.5

	修复因加载cloudflare的layer.js失效导致的bug（因已恢复就还在沿用layer.js）。

2019-09-08 V1.0.4
	
	1、新增手机端长按二维码支付
	2、修复了因cookie保存位置不正确导致的http网站无法保存cookie不显示回调的bug。
	
2019-04-23
	
	V1.0.3 除spay微信支付外，新增spay支付宝支付
	
2019-04-07
	
	V1.0.2 新增payjs微信支付
	
2019-04-01
	
	V1.0.1 第一版本实现