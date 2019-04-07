### WeMediaForWordPress付费阅读插件
---

<img src="https://ws3.sinaimg.cn/large/ecabade5ly1ftmeqo9d9pj20kk0583yj.jpg" />

本插件可以隐藏文章中的任意部分内容，当访客付费后，可查看隐藏内容，当前版本仅支持SPay和payjs微信支付。

#### 遇到问题
因cookie的关系，目前仅支持https，测试http不能保存cookie，但我的typecho版本的WeMedia插件可以保存，很是纳闷，未找到原因，如果你的http能保存能用的话，那就能用了，不过我这里测试时支持https，不支持http。

#### 安装方法：
第一步：下载本插件，放在 `wp-content/plugins/` 目录中（插件文件夹名必须为WeMedia）；

第二步：激活插件；

第三步：填写配置；

第四步：完成。

#### 使用方法：
1、新建模板为“付费阅读同步回调”和“付费阅读异步回调”的页面；

2、配置以下参数；

3、在文章中点击右侧付费阅读框插入付费阅读标签 &lt;!--WeMedia start--> &lt;!--WeMedia end--> ，并在标签中间加入付费内容，这里需要注意：WP5.0版本以上需要在编辑器经典模式下进行，插入标签后可以在html模式下查看到，在标签中间添加完付费内容后，可以返回经典编辑器视图模式；

4、在文章列表处修改每篇的付费内容的单价，即可进行付费操作。

#### 版本说明：
此插件使用php5.6+Wordpress5.0.2编写

#### 与我联系：
作者：二呆

网站：http://www.tongleer.com/

Github：https://github.com/muzishanshi/WeMedia

#### 更新记录：
2019-04-07
	
	V1.0.2 新增payjs微信支付
	
2019-04-01
	
	V1.0.1 第一版本实现