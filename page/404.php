<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit;?>
<?php
$http_type = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';  
$url=$http_type . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
$shorturl=substr($url,strrpos($url,'/')+1);
$shorturl = isset($shorturl) ? addslashes(trim($shorturl)) : '';
if($shorturl!=''){
	$query= $this->db->select('isred','longurl')->from('table.multi_dwz')->where('shorturl = ?', $shorturl); 
	$row = $this->db->fetchRow($query);
	if(count($row)>0){
		if($row['isred']=='y'){
			//检测手机QQ进行跳转
			$siteurl = ($_SERVER['SERVER_PORT'] == '443' ? 'https://' : 'http://') . $_SERVER['SERVER_NAME'].':'.$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
			if (strpos($_SERVER['HTTP_USER_AGENT'], 'QQ/') !== !1 ) {
				//设置广告数据
				$adName=array('赞助商店铺','赞助商图床');
				$adUrl=array('https://weidian.com/?userid=2055073&wfr=c&ifr=shopdetail','http://api.tongleer.com/picturebed/');
				$num=rand(0,0);
				echo '<!DOCTYPE html>
				<html>
				 <head>
				  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
				  <title>请使用浏览器打开</title>
				  <!--
				  <script src="https://open.mobile.qq.com/sdk/qqapi.js?_bid=152"></script>
				  <script type="text/javascript"> mqq.ui.openUrl({ target: 2,url: "' . $siteurl . '"}); </script>
				  <script>$("title").text("第三方跳转中……");</script>
				  -->
				  <style>
					a:link,a:hover,a:active,a:visited{color:#ffffff;text-decoration:none;}
				  </style>
				 </head>
				 <body style="margin: 0;padding: 0;width: 100%;height: 100%;">
					<div style="background-color:#00aaff;width:100%;margin:0 auto; overflow:hidden;position:fixed;top:0;text-align:center;font-size:15px;">
						<h1 style="color:#ffffff;word-wrap:break-word">免责声明：本站永久免费！短网址均由用户生成，所跳转网站内容均与本站无关！如其站点违规，本站对其概不负责，亦不承担任何法律责任。</h1>
					</div>
					<img src="http://tools.tongleer.com/content/templates/mdx/img/bg_mobile.jpg" width="100%" height: 100%; />
					<div style="background-color:#00aaff;width:100%;margin:0 auto; overflow:hidden;position:fixed;bottom:0;text-align:center;font-size:20px;">
						<h1>
							<a href="'.$adUrl[0].'" rel="nofollow">'.$adName[0].'</a>&nbsp;
							<a href="'.$adUrl[1].'" rel="nofollow">'.$adName[1].'</a>
						</h1>
					</div>
				 </body>
				</html>';
				exit;
			}
		}
		header('Location: '.$row['longurl']);
	}
}
?>
<?php $this->need('header.php'); ?>
		<!-- content section -->
        <section class="content-top-margin no-padding-bottom border-top wow fadeIn">
            <div class="container">
                <div class="row">
                    <div class="col-md-10 col-sm-8 col-xs-11 text-center center-col">
                        <p class="not-found-title black-text">404!</p>
                        <p class="text-med text-uppercase letter-spacing-2">找不到你要找的网页</p>
                        <a class="highlight-button-dark btn btn-small no-margin-right" href="<?php $this->options ->siteUrl(); ?>">跳转主页</a>
                    </div>
                </div>
            </div>
        </section>
        <!-- end content section -->
<?php $this->need('footer.php'); ?>