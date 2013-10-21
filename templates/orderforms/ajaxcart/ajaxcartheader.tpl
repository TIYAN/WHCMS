<script type="text/javascript" src="includes/jscript/jqueryui.js"></script>
<script type="text/javascript" src="templates/orderforms/{$carttpl}/js/main.js"></script>
<script type="text/javascript" src="templates/orderforms/{$carttpl}/js/jqueryfloat.js"></script>
<link rel="stylesheet" type="text/css" href="templates/orderforms/{$carttpl}/style.css" />

{literal}<script type="text/javascript">
function removeItem(type,num) {
	var response = confirm("{/literal}{$LANG.cartremoveitemconfirm}{literal}");
	if (response) {
        jQuery.post("cart.php", 'a=remove&r='+type+'&i='+num,function() {
            recalcsummary();
        });
	}
}
function emptyCart(type,num) {
	var response = confirm("{/literal}{$LANG.cartemptyconfirm}{literal}");
	if (response) {
        window.location='cart.php?a=empty';
	}
}
</script>{/literal}

<div id="order-ajaxcart">

<table cellpadding="0" cellspacing="0" class="ajaxcart">
<tr><td valign="top">