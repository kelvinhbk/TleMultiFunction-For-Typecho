<?php
/**
 * 多功能-百度链接提交
 *
 * @package custom
 */
?>
<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;
if (!$this->user->pass('administrator')) exit;
$pluginsname='TleMultiFunction';
include dirname(__FILE__).'/../../plugins/'.$pluginsname.'/include/function.php';

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

/*
$setbaidusubmit_config=@unserialize(ltrim(file_get_contents(dirname(__FILE__).'/../../plugins/'.$pluginsname.'/config/setbaidusubmit_config.php'),'<?php die; ?>'));
$result=checkUser($setbaidusubmit_config['username'],$setbaidusubmit_config['password'],$setbaidusubmit_config['access_token']);
switch($result){
	case 0:
		die('服务器验证错误');
		break;
	case 101:
		die('登录用户名不存在');
		break;
	case 102:
		die('登录密码错误');
		break;
	case 103:
		die('token不存在');
		break;
	case 104:
		die('token已过期');
		break;
}
*/

$setbaidusubmit=@unserialize(ltrim(file_get_contents(dirname(__FILE__).'/../../plugins/'.$pluginsname.'/config/setbaidusubmit.php'),'<?php die; ?>'));
$action = isset($_POST['action']) ? addslashes(trim($_POST['action'])) : '';
if($action=='setbaidusubmit'){
	$url = isset($_POST['url']) ? addslashes(trim($_POST['url'])) : '';
	$linktoken = isset($_POST['linktoken']) ? addslashes(trim($_POST['linktoken'])) : '';
	$appid = isset($_POST['appid']) ? addslashes(trim($_POST['appid'])) : '';
	$resctoken = isset($_POST['resctoken']) ? addslashes(trim($_POST['resctoken'])) : '';
	if($url&&$linktoken){	
		if(get_magic_quotes_gpc()){
			$url=stripslashes($url);
			$linktoken=stripslashes($linktoken);
			$appid=stripslashes($appid);
			$resctoken=stripslashes($resctoken);
		}
		file_put_contents(dirname(__FILE__).'/../../plugins/'.$pluginsname.'/config/setbaidusubmit.php','<?php die; ?>'.serialize(array(
			'url'=>$url,
			'linktoken'=>$linktoken,
			'appid'=>$appid,
			'resctoken'=>$resctoken
		)));
	}
}
?>

<link rel="stylesheet" href="//cdn.bootcss.com/mdui/0.4.1/css/mdui.min.css">
<script src="//cdn.bootcss.com/mdui/0.4.1/js/mdui.min.js"></script>
<script src="http://apps.bdimg.com/libs/jquery/1.7.1/jquery.min.js" type="text/javascript"></script>
<!-- content section -->
<section>
	<div class="mdui-shadow-10 mdui-center" style="width:300px;">
		<div class="mdui-typo mdui-valign mdui-color-blue mdui-text-color-white">
		  <h6 class="mdui-center">百度链接提交设置</h6>
		</div>
		<form action="" method="post" class="mdui-p-x-1 mdui-p-y-1">
			<div class="mdui-textfield mdui-textfield-floating-label">
			  <label class="mdui-textfield-label"><?php _e('站点链接(不带http)'); ?></label>
			  <input class="mdui-textfield-input" id="url" name="url" type="text" required value="<?php if(@$url!=''){echo $url;}else{echo @$setbaidusubmit['url'];} ?>"/>
			  <div class="mdui-textfield-error">站点链接不能为空</div>
			</div>
			<div class="mdui-textfield mdui-textfield-floating-label">
			  <label class="mdui-textfield-label"><?php _e('站点token'); ?></label>
			  <input class="mdui-textfield-input" id="linktoken" name="linktoken" type="text" required value="<?php if(@$linktoken!=''){echo $linktoken;}else{echo @$setbaidusubmit['linktoken'];} ?>"/>
			  <div class="mdui-textfield-error">站点token不能为空</div>
			</div>
			<div class="mdui-textfield mdui-textfield-floating-label">
			  <label class="mdui-textfield-label"><?php _e('熊掌号appid'); ?></label>
			  <input class="mdui-textfield-input" id="appid" name="appid" type="text" required value="<?php if(@$appid!=''){echo $appid;}else{echo @$setbaidusubmit['appid'];} ?>"/>
			  <div class="mdui-textfield-error">熊掌号appid不能为空</div>
			</div>
			<div class="mdui-textfield mdui-textfield-floating-label">
			  <label class="mdui-textfield-label"><?php _e('熊掌号token'); ?></label>
			  <input class="mdui-textfield-input" id="resctoken" name="resctoken" type="text" required value="<?php if(@$resctoken!=''){echo $resctoken;}else{echo @$setbaidusubmit['resctoken'];} ?>"/>
			  <div class="mdui-textfield-error">熊掌号token不能为空</div>
			</div>
			<div class="mdui-row-xs-1">
			  <div class="mdui-col">
				<input type="hidden" name="action" value="setbaidusubmit" />
				<button id="setbaidusubmit" class="mdui-btn mdui-btn-block mdui-btn-raised mdui-color-theme-accent mdui-ripple mdui-color-blue mdui-text-color-white"><?php _e('修改设置'); ?></button>
			  </div>
			</div>
		</form>
	</div>
	<div class="mdui-table-fluid">
	  <table class="mdui-table mdui-table-hoverable">
		<thead>
		  <tr>
			<th class="mdui-table-col-numeric">文章</th>
			<th class="mdui-table-col-numeric">提交网址状态</th>
			<th class="mdui-table-col-numeric">提交熊掌号状态</th>
			<th class="mdui-table-col-numeric">操作网址</th>
			<th class="mdui-table-col-numeric">操作熊掌号</th>
		  </tr>
		</thead>
		<tbody>
		<?php
		$query = $this->db->select()->from('table.contents')->join('table.multi_baidusubmit', 'table.contents.cid = table.multi_baidusubmit.bscid',Typecho_Db::LEFT_JOIN)->where('table.contents.status != ?', 'hidden')->order('modified',Typecho_Db::SORT_DESC)->page(1,20);
		$rows = $this->db->fetchAll($query);
		if(count($rows)>0){
			foreach($rows as $row){
				$val = Typecho_Widget::widget('Widget_Abstract_Contents')->push($row);
				$permalink=str_replace('{cid}',$row['cid'],$val['permalink']);
			?>
			  <tr>
				<td class="mdui-table-col-numeric"><a href="<?=$permalink;?>"><?=$row['title'];?></a></td>
				<td class="mdui-table-col-numeric">
					<?php
					if($row['linkstatus']==''){
						echo '未提交';
					}else if($row['linkstatus']==200){
						echo '<font color="green">成功</font>('.$row['instime'].')';
					}else if($row['linkstatus']!=200&&$row['linkstatus']!=''){
						echo '<font color="red">失败</font>('.$row['instime'].')<font color="red">'.$row['error'].'</font>';
					}
					?>
				</td>
				<td class="mdui-table-col-numeric">
					<?php
					if($row['rescstatus']==''){
						echo '未提交';
					}else if($row['rescstatus']==200){
						echo '<font color="green">成功</font>('.$row['instime'].')';
					}else if($row['rescstatus']!=200&&$row['rescstatus']!=''){
						echo '<font color="red">失败</font>('.$row['instime'].')<font color="red">'.$row['error'].'</font>';
					}
					?>
				</td>
				<td class="mdui-table-col-numeric">
					<?php
					if($row['linkstatus']==''){
						echo '<a href="javascript:;"><span class="baidusubmit" id="baidusubmit'.$row['cid'].'" data-url="'.$permalink.'" data-cid="'.$row['cid'].'" data-pluginsname="'.$pluginsname.'">提交网址</span></a>';
					}else{
						echo '<a href="javascript:;"><span class="baidusubmit" id="baidusubmit'.$row['cid'].'" data-url="'.$permalink.'" data-cid="'.$row['cid'].'" data-pluginsname="'.$pluginsname.'"><font color="red">再次提交网址</font></span></a>';
					}
					?>
				</td>
				<td class="mdui-table-col-numeric">
					<?php
					if($row['rescstatus']==''){
						echo '&nbsp;&nbsp;<a href="javascript:;"><span class="baiduziyuansubmit" id="baiduziyuansubmit'.$row['cid'].'" data-url="'.$permalink.'" data-cid="'.$row['cid'].'" data-pluginsname="'.$pluginsname.'">提交熊掌号</span></a>';
					}else{
						echo '&nbsp;&nbsp;<a href="javascript:;"><span class="baiduziyuansubmit" id="baiduziyuansubmit'.$row['cid'].'" data-url="'.$permalink.'" data-cid="'.$row['cid'].'" data-pluginsname="'.$pluginsname.'"><font color="red">再次提交熊掌号</font></span></a>';
					}
					?>
				</td>
			  </tr>
			<?php
			}
		}else{
		?>
		<tr><td class="tdcenter" colspan="5">暂无文章/页面记录</td></tr>
		<?php
		}
		?>
		</tbody>
	  </table>
	</div>
</section>
<!-- end content section -->

<script>
$("#setbaidusubmit").click(function(){
	if($("#url").val()==''||$("#linktoken").val()==''){
		return; 
	}
	$('form').submit();
});
$(".mdui-table .baidusubmit").each(function(){
	var id=$(this).attr("id")
	$("#"+id).click( function () {
		$.post("<?php $this->options->siteUrl(); ?>usr/plugins/<?=$pluginsname;?>/ajax/baidusubmit.php",{action:'baidusubmit',url:$(this).attr('data-url'),cid:$(this).attr('data-cid'),pluginsname:$(this).attr('data-pluginsname')},function(data){
			if(data==-1){
				alert('提交失败');
			}else if(data!=''){
				alert(data);
			}
			window.location.href='';
		});
	});
});
$(".mdui-table .baiduziyuansubmit").each(function(){
	var id=$(this).attr("id")
	$("#"+id).click( function () {
		$.post("<?php $this->options->siteUrl(); ?>usr/plugins/<?=$pluginsname;?>/ajax/baidusubmit.php",{action:'baiduziyuansubmit',url:$(this).attr('data-url'),cid:$(this).attr('data-cid'),pluginsname:$(this).attr('data-pluginsname')},function(data){
			if(data==-1){
				alert('提交失败');
			}else if(data!=''){
				alert(data);
			}
			window.location.href='';
		});
	});
});
</script>