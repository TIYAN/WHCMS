<?php

if (!defined("WHMCS"))
	die("This file cannot be accessed directly");

function widget_getting_started($vars) {

    $title = "开始使用 WHMCS 时光人破解版";

    $content = '
<span style="font-weight:bold;font-size:14px;color:#29467C;">欢迎来到 WHMCS - 完善的客户管理, 交易 & 帮助系统!</span><br />
这里是供第一次使用 WHMCS 用户的一些建议和技巧...<br />
<blockquote>
<b>步骤 1:</b> 查看 & 设置 <a href="configgeneral.php">系统常规设置</a> 包括公司名称, 链接, 等等...<br />
<b>步骤 2:</b> 激活 & 设置 你想要使用的 <a href="configgateways.php">支付接口</a> <br />
<b>步骤 3:</b> 在你的系统中设置至少 1个 <a href="configproducts.php">产品组</a> & <a href="configproducts.php">产品/服务</a> (<a href="http://www.mtimer.cn/">更多帮助</a>)<br />
</blockquote>
更多信息请查阅官方帮助文档 @ <a href="http://docs.whmcs.com/" target="_blank">http://docs.whmcs.com/</a>
<div align="right" style="padding-top:5px;"><input type="submit" value="隐藏帮助面板" onclick="dismissgs()" /></div>
    ';

    $jscode = 'function dismissgs() {
    $("#getting_started").fadeOut();
    $.post("index.php", { dismissgs: 1 });
}';

    return array('title'=>$title,'content'=>$content,'jscode'=>$jscode);

}

add_hook("AdminHomeWidgets",1,"widget_getting_started");

?>