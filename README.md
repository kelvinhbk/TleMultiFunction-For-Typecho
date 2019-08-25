### TleMultiFunction For Typecho 多功能插件
---

Typecho多功能插件集成多项功能：百度(熊掌号)链接提交、手机登陆、邮箱登陆、QQ登陆、微博登陆、geetest验证码、忘记密码、网址缩短、论坛、多平台用户管理等。

程序有可能会遇到bug不改版本号直接修改代码的时候，所以扫描以下二维码关注公众号“同乐儿”，可直接与作者二呆产生联系，不再为bug烦恼，随时随地解决问题。

<img src="http://me.tongleer.com/content/uploadfile/201706/008b1497454448.png">

#### 使用方法：

	第一步：下载本插件，放在 `usr/plugins/` 目录中（插件文件夹名必须为<b>TleMultiFunction</b>）；
	第二步：激活插件；
	第三步：填写配置；
	第四步：完成。
	突然忘记第五步，如果你的网站根目录下没有配置伪静态的话，可将此插件跟目录下的.htaccess复制到网站根目录（如果子目录的话需要做修改）即可。（如果不会可与作者联系）

#### 使用注意：

	版本推荐php5.6+mysql。

#### 与我联系：

	作者：二呆
	微信：Diamond0422
	网址：http://www.tongleer.com
	Github：https://github.com/muzishanshi/TleMultiFunction-For-Typecho

#### 更新

2019-08-25 1.0.17

	1、修复手机登陆独立页面的发送验证码按钮的禁用状态；
	2、修复发送验证码后更改手机号依然可以注册的bug；
	3、修改QQ登陆时用户名不统一的bug，和登陆成功能够跳转原页面页面等。
	4、新增官方后台的手机注册、邮箱注册、QQ登陆、微博登陆等功能；
	5、新增忘记密码功能；
	6、新增多平台用户管理；
	7、新增geetest验证码；
	8、优化部分逻辑代码及bug修复等。
	
2019-03-22 1.0.16

	修复了因cdn.bootcss.com中JS静态资源不可访问导致的js失效的问题。
	
2019-01-30 V1.0.15

	修复了提交带别名的文章和页面后报错的情况
	
2019-01-18 V1.0.14

	部分细节优化
	
2018-08-30

	对论坛帖子页内容及内容中的图片格式的显示方式做了优化
	对帖子列表的分类和图片代码截取做了优化
	发帖分类设置为父分类可选择
	
2018-08-11 修改了阿里云新短信模板不包含产品名变量时发送短信的情况
2018-07-29 优化帖子内容显示
2018-07-13 修改了百度提交代码的小错误
2018-07-12 新增自动提交百度链接、优化百度提交记录页面。
2018-07-05 新增了手机号注册模块、插件升级检测让你一直使用最新版本。
2018-07-04 修改QQ登录配置界面样式及增加可删除通过QQ登录注册的用户，修改了404页面样式。
2018-07-02 修复了论坛不能显示图片的bug
2018-06-18 新增QQ登录功能
2018-06-17 新增新浪微博短址缩短功能
2018-06-03 新增论坛功能
2018-05-27 新增短网址缩短功能
2018-05-26 新增百度链接提交和百度熊掌号资源提交功能