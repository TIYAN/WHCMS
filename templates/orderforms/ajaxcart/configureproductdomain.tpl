<h2>{$LANG.domainname}</h2>

<p>{$LANG.cartproductdomaindesc}</p>

<table width="90%" align="center" cellspacing="1" cellpadding="0" class="domainoptions">
{if $incartdomains}
<tr><td width="30"><input type="radio" name="domainoption" value="incart" id="selincart" /></td><td><label for="selincart">{$LANG.cartproductdomainuseincart}</label></td></tr>
{/if}
{if $registerdomainenabled}
<tr><td width="30"><input type="radio" name="domainoption" value="register" id="selregister" /></td><td><label for="selregister">{$LANG.cartregisterdomainchoice|sprintf2:$companyname}</label></td></tr>
{/if}
{if $transferdomainenabled}
<tr><td width="30"><input type="radio" name="domainoption" value="transfer" id="seltransfer" /></td><td><label for="seltransfer">{$LANG.carttransferdomainchoice|sprintf2:$companyname}</label></td></tr>
{/if}
{if $owndomainenabled}
<tr><td width="30"><input type="radio" name="domainoption" value="owndomain" id="selowndomain" /></td><td><label for="selowndomain">{$LANG.cartexistingdomainchoice|sprintf2:$companyname}</label></td></tr>
{/if}
{if $subdomains}
<tr><td width="30"><input type="radio" name="domainoption" value="subdomain" id="selsubdomain" /></td><td><label for="selsubdomain">{$LANG.cartsubdomainchoice|sprintf2:$companyname}</label></td></tr>
{/if}
</table>

<form onsubmit="checkdomain();return false">

<div class="domainreginput" id="domainincart">
<select id="incartsld">
{foreach key=num item=incartdomain from=$incartdomains}
<option value="{$incartdomain}">{$incartdomain}</option>
{/foreach}
</select>
</div>
<div class="domainreginput" id="domainregister">
www. <input type="text" id="registersld" size="25" value="{$sld}" /> <select id="registertld">
{foreach key=num item=listtld from=$registertlds}
<option value="{$listtld}"{if $listtld eq $tld} selected="selected"{/if}>{$listtld}</option>
{/foreach}
</select>
</div>
<div class="domainreginput" id="domaintransfer">
www. <input type="text" id="transfersld" size="25" value="{$sld}" /> <select id="transfertld">
{foreach key=num item=listtld from=$transfertlds}
<option value="{$listtld}"{if $listtld eq $tld} selected="selected"{/if}>{$listtld}</option>
{/foreach}
</select>
</div>
<div class="domainreginput" id="domainowndomain">
www. <input type="text" id="owndomainsld" size="25" value="{$sld}" /> . <input type="text" id="owndomaintld" size="5" value="{$tld|substr:1}" />
</div>
<div class="domainreginput" id="domainsubdomain">
http:// <input type="text" id="subdomainsld" size="15" value="{$sld}" /> <select id="subdomaintld">{foreach from=$subdomains key=subid item=subdomain}<option value="{$subid}">{$subdomain}</option>{/foreach}</select>
</div>

<p align="center"><input type="submit" value="{$LANG.ordercontinuebutton}" /></p>

{if $freedomaintlds}<p>* <em>{$LANG.orderfreedomainregistration} {$LANG.orderfreedomainappliesto}: {$freedomaintlds}</em></p>{/if}

</form>

<div class="errorbox">{$LANG.ajaxcartconfigreqnotice}</div>

<div id="loading3" class="loading"><img src="images/loading.gif" border="0" alt="{$LANG.loading}" /></div>

<form id="domainfrm" onsubmit="completedomain();return false">
<div id="domainresults"></div>
</form>

{literal}
<script type="text/javascript">
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
function completedomain() {
    jQuery("#loading2").slideDown();
    jQuery.post("cart.php", 'ajax=1&a=add&pid={/literal}{$pid}{literal}&domainselect=1&'+jQuery("#domainfrm").serialize(),
    function(data){
        if (data=='') {
            signupstep();
        } else if (data=='nodomains') {
        } else {
            jQuery("#configcontainer2").html(data);
            jQuery("#configcontainer2").slideDown();
            jQuery("#loading2").slideUp();
        }
    });
}
</script>
{/literal}