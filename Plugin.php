<?php
/**
 * Typecho多功能插件
 * @package TleMultiFunction For Typecho
 * @author 二呆<br />(VX:Diamond0422)
 * @version 1.0.4
 * @link http://www.tongleer.com/
 * @date 2018-06-17
 */
class TleMultiFunction_Plugin implements Typecho_Plugin_Interface
{
    // 激活插件
    public static function activate(){
        return _t('插件已经激活，需先配置信息！');
    }

    // 禁用插件
    public static function deactivate(){
		//清空用户登录信息
		file_put_contents(dirname(__FILE__).'/config/setbaidusubmit_config.php','<?php die; ?>'.serialize(array(
			'username'=>'',
			'password'=>'',
			'access_token'=>''
		)));
		//删除页面模板
		$db = Typecho_Db::get();
		$queryTheme= $db->select('value')->from('table.options')->where('name = ?', 'theme'); 
		$rowTheme = $db->fetchRow($queryTheme);
		@unlink(dirname(__FILE__).'/../../themes/'.$rowTheme['value'].'/page_multi_baidusubmit.php');
		@unlink(dirname(__FILE__).'/../../themes/'.$rowTheme['value'].'/page_multi_dwz.php');
		@unlink(dirname(__FILE__).'/../../themes/'.$rowTheme['value'].'/page_multi_bbs.php');
		@unlink(dirname(__FILE__).'/../../themes/'.$rowTheme['value'].'/page_multi_oauthlogin.php');
        return _t('插件已被禁用');
    }

    // 插件配置面板
    public static function config(Typecho_Widget_Helper_Form $form){
		//登录验证
		$user = new Typecho_Widget_Helper_Form_Element_Text('user', null, '', _t('用户名：'));
        $form->addInput($user->addRule('required', _t('用户名不能为空！')));

        $pass = new Typecho_Widget_Helper_Form_Element_Password('pass', null, '', _t('密码：'));
        $form->addInput($pass->addRule('required', _t('密码不能为空！')));
		
		$token = new Typecho_Widget_Helper_Form_Element_Text('token', null, '', _t('Token：'), _t("自行到<a href='http://www.tongleer.com/reg' target='_blank'>同乐儿</a>注册账号后获取"));
        $form->addInput($token->addRule('required', _t('token不能为空！')));
		
		$db = Typecho_Db::get();
		$prefix = $db->getPrefix();
		
		//百度链接提交模块
        $baidu_submit = new Typecho_Widget_Helper_Form_Element_Radio('baidu_submit', array(
            'y'=>_t('启用'),
            'n'=>_t('禁用')
        ), 'y', _t('百度链接提交'), _t("启用后可前往页面进一步配置百度链接提交的相关参数，为您做到心中有数，启用后会创建".$prefix."multi_baidusubmit数据表、page_multi_baidusubmit.php主题文件、百度链接提交页面3项，以提供多功能服务，不会添加任何无用项目，谢谢支持。"));
        $form->addInput($baidu_submit->addRule('enum', _t(''), array('y', 'n')));
		
		//短网址模块
        $dwz = new Typecho_Widget_Helper_Form_Element_Radio('dwz', array(
            'y'=>_t('启用'),
            'n'=>_t('禁用')
        ), 'y', _t('短网址缩短'), _t("启用后可前往页面进一步配置短网址缩短的相关参数，为您做到心中有数，启用后会创建".$prefix."multi_dwz数据表、page_multi_dwz.php主题文件、短网址页面3项，以提供多功能服务，不会添加任何无用项目，谢谢支持。"));
        $form->addInput($dwz->addRule('enum', _t(''), array('y', 'n')));
		
		//论坛模块
        $bbs = new Typecho_Widget_Helper_Form_Element_Radio('bbs', array(
            'y'=>_t('启用'),
            'n'=>_t('禁用')
        ), 'y', _t('论坛'), _t("启用后可前往页面进一步配置短网址缩短的相关参数，为您做到心中有数，启用后会创建".$prefix."page_multi_bbs.php主题文件、论坛页面3项，以提供多功能服务，不会添加任何无用项目，谢谢支持。"));
        $form->addInput($bbs->addRule('enum', _t(''), array('y', 'n')));
		
		//第三方登录模块
        $oauthlogin = new Typecho_Widget_Helper_Form_Element_Radio('oauthlogin', array(
            'y'=>_t('启用'),
            'n'=>_t('禁用')
        ), 'y', _t('第三方登录'), _t("启用后可前往页面进一步配置短网址缩短的相关参数，为您做到心中有数，启用后会创建".$prefix."multi_oauthlogin数据表、page_multi_oauthlogin.php主题文件、第三方登录页面3项，以提供多功能服务，不会添加任何无用项目，谢谢支持。"));
        $form->addInput($oauthlogin->addRule('enum', _t(''), array('y', 'n')));
	
		$user = @isset($_POST['user']) ? addslashes(trim($_POST['user'])) : '';
		$pass = @isset($_POST['pass']) ? addslashes(trim($_POST['pass'])) : '';
		$token = @isset($_POST['token']) ? addslashes(trim($_POST['token'])) : '';
		if($user!=''&&$pass!=''&&$token!=''){
			$code=self::checkUserLogin($user,$pass,$token);
			if($code==100){
				if(get_magic_quotes_gpc()){
					$user=stripslashes($user);
					$pass=stripslashes($pass);
					$token=stripslashes($token);
				}
				file_put_contents(dirname(__FILE__).'/config/setbaidusubmit_config.php','<?php die; ?>'.serialize(array(
					'username'=>$user,
					'password'=>$pass,
					'access_token'=>$token
				)));
				//百度链接提交模块
				$baidu_submit = @isset($_POST['baidu_submit']) ? addslashes(trim($_POST['baidu_submit'])) : '';
				self::moduleBaiduSubmit($db,$baidu_submit);
				//短网址模块
				$dwz = @isset($_POST['dwz']) ? addslashes(trim($_POST['dwz'])) : '';
				self::moduleDwz($db,$dwz);
				//论坛模块
				$bbs = @isset($_POST['bbs']) ? addslashes(trim($_POST['bbs'])) : '';
				self::moduleBBS($db,$bbs);
				//第三方登录模块
				$oauthlogin = @isset($_POST['oauthlogin']) ? addslashes(trim($_POST['oauthlogin'])) : '';
				self::moduleOAuthLogin($db,$oauthlogin);
			}else{
				die('登录失败');
			}
		}
    }
	
	/*第三方登录方法*/
	public static function moduleOAuthLogin($db,$oauthlogin){
		switch($oauthlogin){
			case 'y':
				//创建第三方登录所用数据表
				self::createTableOAuthLogin($db);
				//判断目录权限，并将插件文件写入主题目录
				self::funWriteThemePage($db,'page_multi_oauthlogin.php');
				//如果数据表没有添加页面就插入
				self::funWriteDataPage($db,'第三方登录','multi_oauthlogin','page_multi_oauthlogin.php');
				break;
		}
	}
	
	/*论坛方法*/
	public static function moduleBBS($db,$bbs){
		switch($bbs){
			case 'y':
				//判断目录权限，并将插件文件写入主题目录
				self::funWriteThemePage($db,'page_multi_bbs.php');
				//如果数据表没有添加页面就插入
				self::funWriteDataPage($db,'论坛','multi_bbs','page_multi_bbs.php');
				break;
		}
	}
	
	/*短网址方法*/
	public static function moduleDwz($db,$dwz){
		switch($dwz){
			case 'y':
				//创建短网址所用数据表
				self::createTableDwz($db);
				//判断目录权限，并将插件文件写入主题目录
				self::funWriteThemePage($db,'page_multi_dwz.php');
				//如果数据表没有添加页面就插入
				self::funWriteDataPage($db,'短网址缩短','multi_dwz','page_multi_dwz.php');
				//重写404页面以达到短址重定向目的
				self::funWriteThemePage($db,'404.php');
				break;
		}
	}
	
	/*百度链接提交方法*/
	public static function moduleBaiduSubmit($db,$baidu_submit){
		switch($baidu_submit){
			case 'y':
				//创建百度链接提交所用数据表
				self::createTableBaiduSubmit($db);
				//判断目录权限，并将插件文件写入主题目录
				self::funWriteThemePage($db,'page_multi_baidusubmit.php');
				//如果数据表没有添加页面就插入
				self::funWriteDataPage($db,'百度链接提交','multi_baidusubmit','page_multi_baidusubmit.php');
				break;
		}
	}
	
	/*创建第三方登录缩短所用数据表*/
	public static function createTableOAuthLogin($db){
		$prefix = $db->getPrefix();
		//$db->query('DROP TABLE IF EXISTS '.$prefix.'multi_baidusubmit');
		$db->query('CREATE TABLE IF NOT EXISTS '.$prefix.'multi_oauthlogin(
			`oauthid` varchar(64) COLLATE utf8_bin NOT NULL,
			`oauthuid` bigint(20) COLLATE utf8_bin NOT NULL,
		    `oauthnickname` varchar(64) COLLATE utf8_bin DEFAULT NULL,
		    `oauthfigureurl` varchar(255) COLLATE utf8_bin DEFAULT NULL,
		    `oauthgender` varchar(8) COLLATE utf8_bin DEFAULT NULL,
		    `oauthtype` enum("qq","weibo","weixin") COLLATE utf8_bin DEFAULT NULL,
		    PRIMARY KEY (`oauthid`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin');
	}
	
	/*创建短网址缩短所用数据表*/
	public static function createTableDwz($db){
		$prefix = $db->getPrefix();
		//$db->query('DROP TABLE IF EXISTS '.$prefix.'multi_baidusubmit');
		$db->query('CREATE TABLE IF NOT EXISTS '.$prefix.'multi_dwz(
			`shortid` bigint(20) NOT NULL AUTO_INCREMENT,
			`longurl` varchar(255) DEFAULT NULL,
			`shorturl` varchar(255) DEFAULT NULL,
			`isred` enum("y","n") DEFAULT "n",
			`instime` datetime DEFAULT NULL COMMENT "插入时间",
			PRIMARY KEY (`shortid`)
		) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8');
	}
	
	/*创建百度链接提交所用数据表*/
	public static function createTableBaiduSubmit($db){
		$prefix = $db->getPrefix();
		//$db->query('DROP TABLE IF EXISTS '.$prefix.'multi_baidusubmit');
		$db->query('CREATE TABLE IF NOT EXISTS '.$prefix.'multi_baidusubmit(
			`bsid` int(11) NOT NULL AUTO_INCREMENT,
			`bscid` int(11) NOT NULL,
			`url` varchar(200) COLLATE utf8_bin DEFAULT NULL,
			`linkstatus` varchar(3) DEFAULT NULL,
			`rescstatus` varchar(3) DEFAULT NULL,
			`instime` datetime DEFAULT NULL,
			`error` varchar(255) COLLATE utf8_bin DEFAULT NULL,
			PRIMARY KEY (`bsid`)
		) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8');
	}
	
	/*公共方法：将页面写入数据库*/
	public static function funWriteDataPage($db,$title,$slug,$template){
		$query= $db->select('slug')->from('table.contents')->where('template = ?', $template); 
		$row = $db->fetchRow($query);
		if(count($row)==0){
			$contents = array(
				'title'      =>  $title,
				'slug'      =>  $slug,
				'created'   =>  Typecho_Date::time(),
				'text'=>  '<!--markdown-->',
				'password'  =>  '',
				'authorId'     =>  Typecho_Cookie::get('__typecho_uid'),
				'template'     =>  $template,
				'type'     =>  'page',
				'status'     =>  'hidden',
			);
			$insert = $db->insert('table.contents')->rows($contents);
			$insertId = $db->query($insert);
			$slug=$contents['slug'];
		}else{
			$slug=$row['slug'];
		}
	}
	/*公共方法：将页面写入主题目录*/
	public static function funWriteThemePage($db,$filename){
		$queryTheme= $db->select('value')->from('table.options')->where('name = ?', 'theme'); 
		$rowTheme = $db->fetchRow($queryTheme);
		if(!is_writable(dirname(__FILE__).'/../../themes/'.$rowTheme['value'])){
			Typecho_Widget::widget('Widget_Notice')->set(_t('主题目录不可写，请更改目录权限。'.__TYPECHO_THEME_DIR__.'/'.$rowTheme['value']), 'success');
		}
		if($filename=='404.php'||!file_exists(dirname(__FILE__).'/../../themes/'.$rowTheme['value']."/".$filename)){
			$regfile = fopen(dirname(__FILE__)."/page/".$filename, "r") or die("不能读取".$filename."文件");
			$regtext=fread($regfile,filesize(dirname(__FILE__)."/page/".$filename));
			fclose($regfile);
			$regpage = fopen(dirname(__FILE__).'/../../themes/'.$rowTheme['value']."/".$filename, "w") or die("不能写入".$filename."文件");
			fwrite($regpage, $regtext);
			fclose($regpage);
		}
	}
	/*登录验证*/
	public static function checkUserLogin($user,$pass,$token){
		$data=array(
			"user"=>$user,
			"pass"=>$pass,
			"token"=>$token
		);
		$url = 'http://api.tongleer.com/open/login.php';
		$client = Typecho_Http_Client::get();
		if ($client) {
			//$data=json_encode($data);
			$str = "";
			foreach ( $data as $key => $value ) { 
				$str.= "$key=" . urlencode( $value ). "&" ;
			}
			$data = substr($str,0,-1);
			$client->setData($data)
				//->setHeader('Content-Type','application/json')
				//->setHeader('Authorization','Bearer '.$token)
				->setTimeout(30)
				->send($url);
			$status = $client->getResponseStatus();
			$rs = $client->getResponseBody();
			$arr=json_decode($rs,true);
			return $arr['code'];
		}
		return 0;
	}

    // 个人用户配置面板
    public static function personalConfig(Typecho_Widget_Helper_Form $form){}

    // 获得插件配置信息
    public static function getConfig(){
        return Typecho_Widget::widget('Widget_Options')->plugin('TleMultiFunction');
    }
}