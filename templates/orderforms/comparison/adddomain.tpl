<script type="text/javascript" src="includes/jscript/jqueryui.js"></script>
<script type="text/javascript" src="templates/orderforms/{$carttpl}/js/main.js"></script>
<link rel="stylesheet" type="text/css" href="templates/orderforms/{$carttpl}/style.css" />
<link rel="stylesheet" type="text/css" href="templates/orderforms/{$carttpl}/uistyle.css" />

<div id="order-comparison">

<h1>{if $domain eq "register"}{$LANG.registerdomain}{else}{$LANG.transferdomain}{/if}</h1>

<div class="cartcats">
{foreach key=num item=productgroup from=$productgroups}
{if $gid eq $productgroup.gid}
{$productgroup.name} |
{else}
<a href="{$smarty.server.PHP_SELF}?gid={$productgroup.gid}">{$productgroup.name}</a> |
{/if}
{/foreach}
{if $loggedin}
<a href="{$smarty.server.PHP_SELF}?gid=addons">{$LANG.cartproductaddons}</a> |
{if $renewalsenabled}<a href="{$smarty.server.PHP_SELF}?gid=renewals">{$LANG.domainrenewals}</a> | {/if}
{/if}
{if $registerdomainenabled}{if $domain neq "register"}<a href="{$smarty.server.PHP_SELF}?a=add&domain=register">{/if}{$LANG.registerdomain}</a> | {/if}
{if $transferdomainenabled}{if $domain neq "transfer"}<a href="{$smarty.server.PHP_SELF}?a=add&domain=transfer">{/if}{$LANG.transferdomain}</a> | {/if}
<a href="{$smarty.server.PHP_SELF}?a=view">{$LANG.viewcart}</a>
</div>

{if !$loggedin && $currencies}
<div class="currencychooser">
{foreach from=$currencies item=curr}
<a href="cart.php?a=add&domain={$domain}&currency={$curr.id}"><img src="images/flags/{if $curr.code eq "AUD"}au{elseif $curr.code eq "CAD"}ca{elseif $curr.code eq "EUR"}eu{elseif $curr.code eq "GBP"}gb{elseif $curr.code eq "INR"}in{elseif $curr.code eq "JPY"}jp{elseif $curr.code eq "USD"}us{elseif $curr.code eq "ZAR"}za{else}na{/if}.png" border="0" alt="" /> {$curr.code}</a>
{/foreach}
</div>
<div class="clear"></div>
{/if}

<p class="domainregtitle">{if $domain eq "register"}{$LANG.registerdomaindesc}{else}{$LANG.transferdomaindesc}{/if}</p>

{if $errormessage}<div class="errorbox">{$errormessage|replace:'<li>':' &nbsp;#&nbsp; '} &nbsp;#&nbsp; </div><br />{/if}

<form onsubmit="checkavailability();return false">
<div class="domainreginput">www. <input type="text" name="sld" id="sld" size="25" value="{$sld}" /> <select name="tld" id="tld">
{foreach key=num item=listtld from=$tlds}
<option value="{$listtld}"{if $listtld eq $tld} selected="selected"{/if}>{$listtld}</option>
{/foreach}
</select> &nbsp; <input type="submit" value=" {$LANG.checkavailability} " class="cartbutton green" /></div>
</form>

<div id="loading" class="loading"><img src="images/loading.gif" border="0" alt="Loading..." /></div>

<form method="post" action="cart.php?a=add&domain={$domain}">

<div id="domainresults"></div>

</form>

{literal}
<script language="javascript">
function checkavailability() {
    jQuery("#loading").slideDown();
    jQuery.post("cart.php", { a: "domainoptions", sld: jQuery("#sld").val(), tld: jQuery("#tld").val(), checktype: '{/literal}{$domain}{literal}', ajax: 1 },
    function(data){
        jQuery("#domainresults").html(data);
        jQuery("#domainresults").slideDown();
        jQuery("#loading").slideUp();
    });
}{/literal}
{if $sld}{literal}
jQuery(document).ready(function(){
    checkavailability();
});
{/literal}{/if}
</script>

</div>