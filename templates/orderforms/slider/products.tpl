<script type="text/javascript" src="includes/jscript/jqueryui.js"></script>
<script type="text/javascript" src="templates/orderforms/{$carttpl}/js/main.js"></script>
<link rel="stylesheet" type="text/css" href="templates/orderforms/{$carttpl}/style.css" />
<link rel="stylesheet" type="text/css" href="templates/orderforms/{$carttpl}/uistyle.css" />

<script type="text/javascript">

var productscount = {$productscount};

{literal}
jQuery(document).ready(function(){
    jQuery( "#productslider" ).slider({
        min: 0,
        max: productscount-1,
        value: 0,
        range: "min",
        slide: function( event, ui ) {
            selproduct(ui.value);
		}
    });
    var width = jQuery("#productslider").width()/productscount;
    jQuery("#productslider").width(width*(productscount-1)+'px');
    jQuery(".sliderlabel").width(width+'px');
    selproduct(0);
{/literal}
{if $pid}
    selproduct(productsNums[{$pid}]);
    selectproduct('{$pid}');
{/if}
{literal}
});

{/literal}
</script>

<div id="order-slider">

{if !$smarty.get.gid && !$smarty.post.gid}

<h1>{$LANG.cartproductselection}</h1>

<br /><br />

{foreach key=num item=productgroup from=$productgroups}
<div class="prodcats"><div><a href="cart.php?gid={$productgroup.gid}">{$productgroup.name}</a></div></div>
{/foreach}
{if $loggedin}
{if $gid neq "addons"}<div class="prodcats"><div><a href="cart.php?gid=addons">{$LANG.cartproductaddons}</a></div></div>{/if}
{if $renewalsenabled && $gid neq "renewals"}<div class="prodcats"><div><a href="cart.php?gid=renewals">{$LANG.domainrenewals}</a></div></div>{/if}
{/if}
{if $registerdomainenabled && $domain neq "register"}<div class="prodcats"><div><a href="cart.php?a=add&domain=register">{$LANG.registerdomain}</a></div></div>{/if}
{if $transferdomainenabled && $domain neq "transfer"}<div class="prodcats"><div><a href="cart.php?a=add&domain=transfer">{$LANG.transferdomain}</a></div></div>{/if}

<div class="clear"></div>

<br />

{else}

<h1>{$groupname}</h1>
<div align="center"><a href="#" onclick="showcats();return false;">({$LANG.cartchooseanothercategory})</a></div>

<div id="categories">
{foreach key=num item=productgroup from=$productgroups}
{if $productgroup.gid neq $gid}<a href="cart.php?gid={$productgroup.gid}">{$productgroup.name}</a>{/if}
{/foreach}
{if $loggedin}
{if $gid neq "addons"}<a href="cart.php?gid=addons">{$LANG.cartproductaddons}</a>{/if}
{if $renewalsenabled && $gid neq "renewals"}<a href="cart.php?gid=renewals">{$LANG.domainrenewals}</a>{/if}
{/if}
{if $registerdomainenabled && $domain neq "register"}<a href="cart.php?a=add&domain=register">{$LANG.registerdomain}</a>{/if}
{if $transferdomainenabled && $domain neq "transfer"}<a href="cart.php?a=add&domain=transfer">{$LANG.transferdomain}</a>{/if}
<a href="cart.php?a=view">{$LANG.viewcart}</a>
</div>
<div class="clear"></div>

{if !$loggedin && $currencies}
<div id="currencychooser">
{foreach from=$currencies item=curr}
<a href="cart.php?gid={$gid}&currency={$curr.id}"><img src="images/flags/{if $curr.code eq "AUD"}au{elseif $curr.code eq "CAD"}ca{elseif $curr.code eq "EUR"}eu{elseif $curr.code eq "GBP"}gb{elseif $curr.code eq "INR"}in{elseif $curr.code eq "JPY"}jp{elseif $curr.code eq "USD"}us{elseif $curr.code eq "ZAR"}za{else}na{/if}.png" border="0" alt="" /> {$curr.code}</a>
{/foreach}
</div>
<div class="clear"></div>
{else}
<br />
{/if}

<div class="cartslider">

<div align="center">
<div id="productslider"></div>
</div>

{foreach from=$products key=num item=product}
<div class="sliderlabel" id="prodlabel{$num}" onclick="selproduct('{$num}')">{$product.name}</div>
{/foreach}
<div class="clear"></div>

</div>

<div class="cartprods">

{foreach from=$products key=num item=product}
<div class="product" id="product{$num}">

<table width="100%"><tr><td>

<div class="name">{$product.name}{if $product.qty!=""} <span class="qty">({$product.qty} {$LANG.orderavailable})</span>{/if}</div>

{foreach from=$product.features key=feature item=value}
<span class="prodfeature"><span class="feature">{$feature}</span><br />{$value}</span>
{/foreach}
<div class="clear"></div>

</td><td valign="top" class="textright" nowrap>

{if $product.bid}
{if $product.displayprice}<span class="pricing">{$product.displayprice}</span><br />{/if}
{$LANG.bundledeal}
{else}
{if $product.pricing.hasconfigoptions}{$LANG.startingfrom}<br />{/if}
<span class="pricing">{$product.pricing.minprice.price}</span><br />
{if $product.pricing.minprice.cycle eq "monthly"}
{$LANG.orderpaymenttermmonthly}
{elseif $product.pricing.minprice.cycle eq "quarterly"}
{$LANG.orderpaymenttermquarterly}
{elseif $product.pricing.minprice.cycle eq "semiannually"}
{$LANG.orderpaymenttermsemiannually}
{elseif $product.pricing.minprice.cycle eq "annually"}
{$LANG.orderpaymenttermannually}
{elseif $product.pricing.minprice.cycle eq "biennially"}
{$LANG.orderpaymenttermbiennially}
{elseif $product.pricing.minprice.cycle eq "triennially"}
{$LANG.orderpaymenttermtriennially}
{/if}
{/if}

</td></tr></table>

<div class="description">{$product.featuresdesc}</div>

<form method="post" action="cart.php?a=add&{if $product.bid}bid={$product.bid}{else}pid={$product.pid}{/if}">
<div class="ordernowbox"><input type="submit" value="{$LANG.ordernowbutton} &raquo;" class="ordernow" /></div>
</form>

</div>
{/foreach}

</div>

{if !$loggedin && $currencies}
<div id="currencychooser">
{foreach from=$currencies item=curr}
<a href="cart.php?gid={$gid}&currency={$curr.id}"><img src="images/flags/{if $curr.code eq "AUD"}au{elseif $curr.code eq "CAD"}ca{elseif $curr.code eq "EUR"}eu{elseif $curr.code eq "GBP"}gb{elseif $curr.code eq "INR"}in{elseif $curr.code eq "JPY"}jp{elseif $curr.code eq "USD"}us{elseif $curr.code eq "ZAR"}za{else}na{/if}.png" border="0" alt="" /> {$curr.code}</a>
{/foreach}
</div>
<div class="clear"></div>
{/if}

{/if}

<br />

<p align="center"><input type="button" value="{$LANG.viewcart}" onclick="window.location='cart.php?a=view'" /></p>

<br />

</div>