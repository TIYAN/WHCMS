{include file='orderforms/ajaxcart/ajaxcartheader.tpl'}

<div id="domaincontainer">

<h2>{$LANG.cartchooseproduct}</h2>

<table width="100%" cellspacing="0" cellpadding="0">
<tr class="rowcolor1"><td>
{foreach from=$productgroups item=group}<label><input type="radio" name="gid" value="{$group.gid}" onclick="window.location='cart.php?gid={$group.gid}'"{if $group.gid eq $gid} checked{/if} /> {$group.name}</label> {/foreach}
{if $loggedin}<label><input type="radio" name="gid" onclick="window.location='cart.php?gid=addons'" /> {$LANG.cartproductaddons}</label>
<label><input type="radio" name="gid" onclick="window.location='cart.php?gid=renewals'" /> {$LANG.domainrenewals}</label> {/if}
{if $registerdomainenabled}<label><input type="radio" name="gid" onclick="window.location='cart.php?a=add&domain=register'"{if $domain eq "register"} checked{/if} /> {$LANG.registerdomain}</label> {/if}
{if $transferdomainenabled}<label><input type="radio" name="gid" onclick="window.location='cart.php?a=add&domain=transfer'"{if $domain eq "transfer"} checked{/if} /> {$LANG.transferdomain}</label>{/if}
</td></tr>
</table>

<h2>{if $domain eq "register"}{$LANG.registerdomain}{else}{$LANG.transferdomain}{/if}</h2>

<p class="domainregtitle">{if $domain eq "register"}{$LANG.registerdomaindesc}{else}{$LANG.transferdomaindesc}{/if}</p>

{if $errormessage}<div class="errorbox">{$errormessage|replace:'<li>':' &nbsp;#&nbsp; '} &nbsp;#&nbsp; </div><br />{/if}
{literal}
<script type="text/javascript">
function checkavailability() {
    jQuery("#loading1").slideDown();
    jQuery.post("cart.php", { a: "domainoptions", sld: jQuery("#sld").val(), tld: jQuery("#tld").val(), checktype: '{/literal}{$domain}{literal}', ajax: 1 },
    function(data){
        jQuery("#domainresults").html(data);
        jQuery("#domainresults").slideDown();
        jQuery("#loading1").slideUp();
    });
}
function adddomain() {
    jQuery("#loading2").slideDown();
    jQuery.post("cart.php", 'ajax=1&a=add&domain={/literal}{$domain}{literal}&'+jQuery("#domainfrm").serialize(),
    function(data){
        recalcsummary();
        jQuery("#configcontainer1").html(data);
        jQuery("#configcontainer1").slideDown();
        jQuery("#loading2").slideUp();
    });
}
</script>
{/literal}


{if !$skipselect}
<form onsubmit="checkavailability();return false">
<div class="domainreginput">www. <input type="text" name="sld" id="sld" size="25" value="{$sld}" /> <select name="tld" id="tld">
{foreach key=num item=listtld from=$tlds}
<option value="{$listtld}"{if $listtld eq $tld} selected="selected"{/if}>{$listtld}</option>
{/foreach}
</select><br /><input type="submit" value=" {$LANG.checkavailability} " /></div>
</form>

<div id="loading1" class="loading"><img src="images/loading.gif" border="0" alt="{$LANG.loading}" /></div>

<form id="domainfrm" onsubmit="adddomain();return false">
<div id="domainresults"></div>
</form>
{else}

<form id="domainfrm">
{assign var='continueok' value=true}

{foreach from=$selecteddomains item=selecteddomain}
<input type="hidden" name="domains[]" value="{$selecteddomain.domain}" />
<input type="hidden" name="domainsregperiod[{$selecteddomain.domain}]" value="{$selecteddomain.regperiod}" />
{/foreach}
<input type="hidden" name="submit" value="{$LANG.ordercontinuebutton}" />

{assign var='domain' value='register'}
</form>
<div id="domainresults">
</div>
<script language="javascript">
adddomain();
</script>
{/if}
<div id="loading2" class="loading"><img src="images/loading.gif" border="0" alt="{$LANG.loading}" /></div>

</div>

<div id="configcontainer1"></div>

<div id="signupcontainer"></div>

{include file='orderforms/ajaxcart/ajaxcartfooter.tpl'}