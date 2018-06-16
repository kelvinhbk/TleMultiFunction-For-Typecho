<?php
/**
 * 多功能-第三方登录
 *
 * @package custom
 */
?>
<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;
$pluginsname='TleMultiFunction';
include dirname(__FILE__).'/../../plugins/'.$pluginsname.'/include/function.php';
include dirname(__FILE__).'/../../plugins/'.$pluginsname.'/libs/qqConnectAPI/qqConnectAPI.php';

$queryPlugins= $this->db->select('value')->from('table.options')->where('name = ?', 'plugins'); 
$rowPlugins = $this->db->fetchRow($queryPlugins);
$plugins=@unserialize($rowPlugins['value']);
if(!isset($plugins['activated']['TleMultiFunction'])){
	die('未启用多功能插件');
}

$queryTleMultiFunction= $this->db->select('value')->from('table.options')->where('name = ?', 'plugin:TleMultiFunction'); 
$rowTleMultiFunction = $this->db->fetchRow($queryTleMultiFunction);
$tleMultiFunction=@unserialize($rowTleMultiFunction['value']);
if($tleMultiFunction['baidu_submit']=='n'){
	die('未启用百度链接提交插件');
}
?>
<?php
$code = isset($_GET['code']) ? addslashes(trim($_GET['code'])) : '';
$state = isset($_GET['state']) ? addslashes(trim($_GET['state'])) : '';
if($code!=''&&$state!=''){
	$db = Typecho_Db::get();
	$qc = new QC();
	$oauthid=$qc->get_openid();
	$userinfo=$qc->get_user_info();
	$name=$userinfo['nickname'];
	$gender=$userinfo['gender'];
	$figureurl=$userinfo['figureurl_qq_2'];
	$query= $this->db->select()->from('table.multi_oauthlogin')->join('table.users', 'table.multi_oauthlogin.oauthuid = table.users.uid',Typecho_Db::INNER_JOIN)->where('oauthid = ?', $oauthid);
	$user = $db->fetchRow($query);
	if($user){
		/*登录*/
		/** 如果已经登录 */
		if ($this->user->hasLogin()) {
			/** 直接返回 */
			$this->response->redirect($this->options->index);
		}
		
		$authCode = function_exists('openssl_random_pseudo_bytes') ?
			bin2hex(openssl_random_pseudo_bytes(16)) : sha1(Typecho_Common::randString(20));
		$user['authCode'] = $authCode;

		Typecho_Cookie::set('__typecho_uid', $user['uid'], 0);
		Typecho_Cookie::set('__typecho_authCode', Typecho_Common::hash($authCode), 0);

		/*更新最后登录时间以及验证码*/
		$db->query($db
		->update('table.users')
		->expression('logged', 'activated')
		->rows(array('authCode' => $authCode))
		->where('uid = ?', $user['uid']));
		
		/*压入数据*/
		$this->push($user);
		$this->_user = $user;
		$this->_hasLogin = true;
		$this->pluginHandle()->loginSucceed($this, $name, '', false);
		
		$this->widget('Widget_Notice')->set(_t('用户已存在，已为您登录 '), 'success');
		/*跳转验证后地址*/
		if (NULL != $this->request->referer) {
			$this->response->redirect($this->request->referer);
		} else if (!$this->user->pass('contributor', true)) {
			/*不允许普通用户直接跳转后台*/
			$this->response->redirect($this->options->profileUrl);
		} else {
			$this->response->redirect($this->options->adminUrl);
		}
	}else{
		/*注册*/
		/** 如果已经登录 */
		if ($this->user->hasLogin() || !$this->options->allowRegister) {
			/** 直接返回 */
			$this->response->redirect($this->options->index);
		}
		$hasher = new PasswordHash(8, true);
		$generatedPassword = Typecho_Common::randString(7);

		$dataStruct = array(
			'name'      =>  $name,
			'mail'      =>  $name.'@tongleer.com',
			'screenName'=>  $name,
			'password'  =>  $hasher->HashPassword($generatedPassword),
			'created'   =>  $this->options->time,
			'group'     =>  'subscriber'
		);
		
		$insert = $db->insert('table.users')->rows($dataStruct);
		$userId = $db->query($insert);
		
		$dataOAuth = array(
			'oauthid'      =>  $oauthid,
			'oauthuid'      =>  $userId,
			'oauthnickname'=>  $name,
			'oauthfigureurl'  =>  $figureurl,
			'oauthgender'   =>  $gender,
			'oauthtype'     =>  'qq'
		);
		
		$insert = $db->insert('table.multi_oauthlogin')->rows($dataOAuth);
		$insertId = $db->query($insert);

		$this->pluginHandle()->finishRegister($this);

		$this->user->login($name, $generatedPassword);

		Typecho_Cookie::delete('__typecho_first_run');
		
		$this->widget('Widget_Notice')->set(_t('用户 <strong>%s</strong> 已经成功注册, 密码为 <strong>%s</strong>', $this->screenName, $generatedPassword), 'success');
		$this->response->redirect($this->options->adminUrl);
	}
}else{
	$page = isset($_GET['page']) ? addslashes(trim($_GET['page'])) : '';
	if($page==''){
		if ($this->user->pass('administrator')){
			?>
			<h3>管理员配置</h3>
			<hr />
			<p>
				第一步：<a href="?page=set"><input type="button" value="配置参数" /></a>
			</p>
			<p>
				第二步：将以下代码放到想要添加QQ登录的地方即可。<br />
				<textarea rows="2" cols="100"><a href="<?=$this->permalink;?>?page=qqlogin"><img src="http://me.tongleer.com/mob/resource/images/qq_login_blue.png" /></a></textarea>
			</p>
			<p>
				测试：<a href="?page=qqlogin"><img src="http://me.tongleer.com/mob/resource/images/qq_login_blue.png" /></a>
			</p>
			<p>
				备注：已登录或禁止注册时不会进行登录和注册。
			</p>
			<?php
		}
	}else if($page=='qqlogin'){
		$qc = new QC();
		$qc->qq_login();
	}else if($page=='set'){
		if ($this->user->pass('administrator')){
			$incFile = fopen(dirname(__FILE__)."/../../plugins/".$pluginsname."/libs/qqConnectAPI/comm/inc.php","r") or die("请设置插件目录下/libs/API/comm/inc.php的权限为777");
			$read=@fread($incFile,filesize(dirname(__FILE__)."/../../plugins/".$pluginsname."/libs/qqConnectAPI/comm/inc.php"));
			fclose($incFile);
			$read=str_replace('<?php die(\'forbidden\'); ?>','',$read);
			$readData=json_decode(trim($read),true);
			$action = isset($_POST['action']) ? addslashes(trim($_POST['action'])) : '';
			if($action=='setoauthlogin'){
				$appid = isset($_POST['appid']) ? addslashes(trim($_POST['appid'])) : '';
				$appkey = isset($_POST['appkey']) ? addslashes(trim($_POST['appkey'])) : '';
				$callback = isset($_POST['callback']) ? addslashes(trim($_POST['callback'])) : '';
				if($appid&&$appkey&&$callback){
					$scope=array(
						'get_user_info','add_share','list_album','add_album','upload_pic','add_topic','add_one_blog','add_weibo','check_page_fans','add_t','add_pic_t','del_t','get_repost_list','get_info','get_other_info','get_fanslist','get_idolist','add_idol','del_idol','get_tenpay_addr'
					);
					$scope = implode(",",$scope);
					$setting=array(
						'appid'=>$appid,
						'appkey'=>$appkey,
						'callback'=>$callback,
						'storageType'=>'file',
						'host'=>'localhost',
						'user'=>'root',
						'password'=>'root',
						'database'=>'test',
						'scope'=>$scope,
						'errorReport'=>true
					);
					$json = "<?php die('forbidden'); ?>\n";
					$json .= json_encode($setting);
					$json = str_replace("\/", "/",$json);
					$incFile = fopen(dirname(__FILE__)."/../../plugins/".$pluginsname."/libs/qqConnectAPI/comm/inc.php","w+") or die("请设置插件目录下/libs/API/comm/inc.php的权限为777");
					if(fwrite($incFile, $json)){
						fclose($incFile);
					}else{
						die("write file Error");
					}
				}
			}
			?>
			<script src="http://apps.bdimg.com/libs/jquery/1.7.1/jquery.min.js" type="text/javascript"></script>
			<link rel="stylesheet" href="//cdn.bootcss.com/mdui/0.4.1/css/mdui.min.css">
			<script src="//cdn.bootcss.com/mdui/0.4.1/js/mdui.min.js"></script>
			<!-- content section -->
			<section>
				<div class="mdui-shadow-10 mdui-center" style="width:300px;">
					<div class="mdui-typo mdui-valign mdui-color-blue mdui-text-color-white">
					  <h6 class="mdui-center">第三方登录设置</h6>
					</div>
					<form action="" method="post" class="mdui-p-x-1 mdui-p-y-1">
						<div class="mdui-textfield mdui-textfield-floating-label">
						  <label class="mdui-textfield-label"><?php _e('QQ互联appid'); ?></label>
						  <input class="mdui-textfield-input" id="appid" name="appid" type="text" required value="<?php if(@$appid!=''){echo $appid;}else{echo @$readData['appid'];} ?>"/>
						  <div class="mdui-textfield-error">QQ互联appid不能为空</div>
						</div>
						<div class="mdui-textfield mdui-textfield-floating-label">
						  <label class="mdui-textfield-label"><?php _e('QQ互联appkey'); ?></label>
						  <input class="mdui-textfield-input" id="appkey" name="appkey" type="text" required value="<?php if(@$appkey!=''){echo $appkey;}else{echo @$readData['appkey'];} ?>"/>
						  <div class="mdui-textfield-error">QQ互联appkey不能为空</div>
						</div>
						<div class="mdui-textfield mdui-textfield-floating-label">
						  <label class="mdui-textfield-label"><?php _e('QQ互联callback回调'); ?></label>
						  <input class="mdui-textfield-input" id="callback" name="callback" type="text" required value="<?php if(@$callback!=''){echo $callback;}else{echo @$readData['callback'];} ?>"/>
						  <div class="mdui-textfield-error">QQ互联callback回调不能为空</div>
						</div>
						<div class="mdui-row-xs-1">
						  <div class="mdui-col">
							<input type="hidden" name="action" value="setoauthlogin" />
							<button id="setoauthlogin" class="mdui-btn mdui-btn-block mdui-btn-raised mdui-color-theme-accent mdui-ripple mdui-color-blue mdui-text-color-white"><?php _e('修改设置'); ?></button>
						  </div>
						</div>
					</form>
				</div>
			</section>
			<!-- end content section -->
			<script>
			$("#setoauthlogin").click(function(){
				$('form').submit();
			});
			</script>
		<?php
		}
	}
}
?>