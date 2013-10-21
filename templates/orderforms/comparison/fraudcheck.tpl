<script type="text/javascript" src="includes/jscript/jqueryui.js"></script>
<script type="text/javascript" src="templates/orderforms/{$carttpl}/js/main.js"></script>
<link rel="stylesheet" type="text/css" href="templates/orderforms/{$carttpl}/style.css" />
<link rel="stylesheet" type="text/css" href="templates/orderforms/{$carttpl}/uistyle.css" />

<div id="order-comparison">

{include file="orderforms/comparison/comparisonsteps.tpl" step=3}

<div class="cartcontainer">

<h2>{$errortitle|strtolower}</h2>

<p>{$error}</p>

<br /><br /><br />

</div>

</div>