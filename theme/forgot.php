<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<?php
include 'common.php';

$menu->title = _t('找回密码');

include __ADMIN_DIR__ . '/header.php';

$options = Typecho_Widget::widget('Widget_Options');
$plug_url = $options->pluginUrl;
?>
<style>
    body {
        font-family: "Microsoft YaHei", tahoma, arial, 'Hiragino Sans GB', '\5b8b\4f53', sans-serif;
    }
    .typecho-logo {
        margin: 50px 0 30px;
        text-align: center;
    }
    .typecho-table-wrap {
        padding: 50px 30px;
    }
    .typecho-page-title h2 {
        margin: 0 0 30px;
        font-weight: 500;
        font-size: 20px;
        text-align: center;
    }
    label:after {
        content: " *";
        color: #ed1c24;
    }
    .btn {
        width: 100%;
        height: auto;
        padding: 10px 16px;
        font-size: 18px;
        line-height: 1.33;
    }
</style>
<div class="body container">
    <div class="typecho-logo">
        <h1><a href="<?php $options->siteUrl(); ?>"><?php $options->title(); ?></a></h1>
    </div>

    <div class="row typecho-page-main">
        <div class="col-mb-12 col-tb-6 col-tb-offset-3 typecho-content-panel">
            <div class="typecho-table-wrap">
                <div class="typecho-page-title">
                    <h2>找回密码</h2>
                </div>
                <?php @$this->forgotForm()->render(); ?>
				<ul class="typecho-option" id="typecho-option-item-phone">
					<li>
						<label class="typecho-label" for="phone">手机号</label>
						<input id="phone" name="phone" type="text" class="text" />
						<p class="description">账号对应的(注册时的)手机号</p>
					</li>
					<li>
						<label class="typecho-label" for="smscode">手机验证码</label>
						<input id="smscode" name="smscode" type="text" class="text" />
						<p class="description">
							<button id="sendsmscode" class="btn">发送验证码</button>
						</p>
					</li>
				</ul>
				<ul class="typecho-option typecho-option-submit" id="typecho-option-item-submit">
					<li>
						<button id="findpwdbyphone" type="submit" class="btn primary">通过手机找回</button>
					</li>
				</ul>
            </div>
        </div>
    </div>
</div>
<?php
include __ADMIN_DIR__ . '/common-js.php';
?>
<script>
/*限制键盘只能按数字键、小键盘数字键、退格键*/
$("#smscode").keyup(function(){
	$("#smscode").val($("#smscode").val().replace(/[^\d.]/g,""));
	$("#smscode").val($("#smscode").val().replace(/\.{2,}/g,"."));
	$("#smscode").val($("#smscode").val().replace(/^\./g,""));
	$("#smscode").val($("#smscode").val().replace(".","$#$").replace(/\./g,"").replace("$#$","."));
	$("#smscode").val($("#smscode").val().replace(/^(\-)*(\d+)\.(\d\d).*$/,"$1$2.$3"));
});
/*发送手机验证码*/
$("#sendsmscode").click(function(){
	var phone=$("#phone").val();
	$.post("<?=$plug_url;?>/TleMultiFunction/ajax/sendsms_new.php",{action:"phone",name:phone},function(data){
		var data=JSON.parse(data);
		if(data.error_code==0){
			settime();
		}else{
			alert(data.message);
		}
	});
	return false;
});
var timer;
var countdown=60;
function settime() {
	if (countdown == 0) {
		$("#sendsmscode").html('重新发送验证码');
		$("#sendsmscode").attr('disabled',false);
		countdown = 60;
		clearTimeout(timer);
		return;
	} else {
		$("#sendsmscode").html(countdown+"秒后重新发送");
		$("#sendsmscode").attr('disabled',true);
		countdown--; 
	} 
	timer=setTimeout(function() { 
		settime();
	},1000) 
}
/*通过密码找回*/
$("#findpwdbyphone").click(function(){
	var phone=$("#phone").val();
	var smscode=$("#smscode").val();
	var indexUrl="<?=$options->index;?>";
	$.post("<?=$plug_url;?>/TleMultiFunction/ajax/sendsms_new.php",{submit:"phone",action:"phone",name:phone,smscode:smscode,indexUrl:indexUrl},function(data){
		var data=JSON.parse(data);
		if(data.error_code==0){
			location.href=data.url;
		}else{
			alert(data.message);
		}
	});
	return false;
});
</script>
<?php
include __ADMIN_DIR__ . '/footer.php';
?>
