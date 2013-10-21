<script type="text/javascript" src="includes/jscript/jqueryui.js"></script>
<script type="text/javascript" src="templates/orderforms/{$carttpl}/js/main.js"></script>
<link rel="stylesheet" type="text/css" href="templates/orderforms/{$carttpl}/style.css" />
<link rel="stylesheet" type="text/css" href="templates/orderforms/{$carttpl}/uistyle.css" />

<div id="order-comparison">

{include file="orderforms/comparison/comparisonsteps.tpl" step=1}

<div class="cartcontainer">

<p>{$LANG.cartproductdomaindesc}</p>

<div class="domainoptions">
{if $incartdomains}
<div><label><input type="radio" name="domainoption" value="incart" /> {$LANG.cartproductdomainuseincart}</label></div>
{/if}
{if $registerdomainenabled}
<div><label><input type="radio" name="domainoption" value="register" /> {$LANG.cartregisterdomainchoice|sprintf2:$companyname}</label></div>
{/if}
{if $transferdomainenabled}
<div><label><input type="radio" name="domainoption" value="transfer" /> {$LANG.carttransferdomainchoice|sprintf2:$companyname}</label></div>
{/if}
{if $owndomainenabled}
<div><label><input type="radio" name="domainoption" value="owndomain" /> {$LANG.cartexistingdomainchoice|sprintf2:$companyname}</label></div>
{/if}
{if $subdomains}
<div><label><input type="radio" name="domainoption" value="subdomain" /> {$LANG.cartsubdomainchoice|sprintf2:$companyname}</label></div>
{/if}
</div>

<form onsubmit="checkdomain();return false">

<div class="domainreginput" id="domainincart">
<select id="incartsld">
{foreach key=num item=incartdomain from=$incartdomains}
<option value="{$incartdomain}">{$incartdomain}</option>
{/foreach}
</select>
</div>
<div class="domainreginput" id="domainregister">
www. <input type="text" id="registersld" size="30" value="{$sld}" /> <select id="registertld">
{foreach key=num item=listtld from=$registertlds}
<option value="{$listtld}"{if $listtld eq $tld} selected="selected"{/if}>{$listtld}</option>
{/foreach}
</select>
</div>
<div class="domainreginput" id="domaintransfer">
www. <input type="text" id="transfersld" size="30" value="{$sld}" /> <select id="transfertld">
{foreach key=num item=listtld from=$transfertlds}
<option value="{$listtld}"{if $listtld eq $tld} selected="selected"{/if}>{$listtld}</option>
{/foreach}
</select>
</div>
<div class="domainreginput" id="domainowndomain">
www. <input type="text" id="owndomainsld" size="30" value="{$sld}" /> . <input type="text" id="owndomaintld" size="5" value="{$tld|substr:1}" />
</div>
<div class="domainreginput" id="domainsubdomain">
http:// <input type="text" id="subdomainsld" size="30" value="{$sld}" /> <select id="subdomaintld">{foreach from=$subdomains key=subid item=subdomain}<option value="{$subid}">{$subdomain}</option>{/foreach}</select>
</div>

<p align="center"><input type="submit" value="{$LANG.ordercontinuebutton}" class="cartbutton" /></p>

{if $freedomaintlds}<p>* <em>{$LANG.orderfreedomainregistration} {$LANG.orderfreedomainappliesto}: {$freedomaintlds}</em></p>{/if}

</form>

<div id="loading3" class="loading"><img src="images/loading.gif" border="0" alt="Loading..." /></div>

<form method="post" action="cart.php?a=add&pid={$pid}" id="domainfrm">
<div id="domainresults"></div>
</form>

</div>

{literal}
<script language="javascript">
jQuery(".domainreginput").hide();
jQuery(".domainoptions input:first").attr('checked','checked');
jQuery("#domain"+jQuery(".domainoptions input:first").val()).show();
jQuery(document).ready(function(){
    jQuery(".domainoptions input").click(function(){
        jQuery("#domainresults").slideUp();
        jQuery(".domainreginput").hide();
        jQuery("#domain"+jQuery(this).val()).show();
    });
});
function checkdomain() {
    var domainoption = jQuery(".domainoptions input:checked").val();
    var sld = jQuery("#"+domainoption+"sld").val();
    var tld = '';
    if (domainoption=='incart') var sld = jQuery("#"+domainoption+"sld option:selected").text();
    if (domainoption=='subdomain') var tld = jQuery("#"+domainoption+"tld option:selected").text();
    else var tld = jQuery("#"+domainoption+"tld").val();
    jQuery("#loading3").slideDown();
    jQuery.post("cart.php", { a: "domainoptions", sld: sld, tld: tld, checktype: domainoption, ajax: 1 },
    function(data){
        jQuery("#domainresults").html(data);
        jQuery("#domainresults").slideDown();
        jQuery("#loading3").slideUp();
    });
}
</script>
{/literal}

</div>