<script type="text/javascript" src="includes/jscript/jqueryui.js"></script>
<script type="text/javascript" src="templates/orderforms/{$carttpl}/js/main.js"></script>
<link rel="stylesheet" type="text/css" href="templates/orderforms/{$carttpl}/style.css" />
<link rel="stylesheet" type="text/css" href="templates/orderforms/{$carttpl}/uistyle.css" />

<div id="order-comparison">

{include file="orderforms/comparison/comparisonsteps.tpl" step=3}

<div class="cartcontainer">

<p class="totalduetoday" style="float:left">{$LANG.thereisaproblem}</p><br /><br />

<h2>{$errortitle|strtolower}</h2>

<p>{$errormsg}</p>

<p align="center"><br /><a href="javascript:history.go(-1)">&laquo; {$LANG.problemgoback}</a></p>

</div>

</div>