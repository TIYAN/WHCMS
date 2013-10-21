{if $invalid}
    <div class="domaininvalid">{$LANG.cartdomaininvalid}</div>
{elseif $alreadyindb}
    <div class="domaininvalid">{$LANG.cartdomainexists}</div>
{else}

{if $checktype=="register" && $regenabled}

<input type="hidden" name="domainoption" value="register" />

{if $status eq "available"}

<div class="domainavailable">{$LANG.cartcongratsdomainavailable|sprintf2:$domain}</div>
<input type="hidden" name="domains[]" value="{$domain}" />
<div class="domainregperiod">{$LANG.cartregisterhowlong} <select name="domainsregperiod[{$domain}]" id="regperiod">{foreach key=period item=regoption from=$regoptions}{if $regoption.register}<option value="{$period}">{$period} {$LANG.orderyears} @ {$regoption.register}</option>{/if}{/foreach}</select></div>

{assign var='continueok' value=true}

{elseif $status eq "unavailable"}

<div class="domainunavailable">{$LANG.cartdomaintaken|sprintf2:$domain}</div>

{/if}

{elseif $checktype=="transfer" && $transferenabled}

<input type="hidden" name="domainoption" value="transfer" />

{if $status eq "available"}

<div class="domainunavailable">{$LANG.carttransfernotregistered|sprintf2:$domain}</div>

{elseif $status eq "unavailable"}

<div class="domainavailable">{$LANG.carttransferpossible|sprintf2:$domain:$transferprice}</div>
<input type="hidden" name="domains[]" value="{$domain}" />
<input type="hidden" name="domainsregperiod[{$domain}]" value="{$transferterm}" />

{assign var='continueok' value=true}

{/if}

{elseif $checktype=="owndomain" || $checktype=="subdomain"}

<input type="hidden" name="domainoption" value="{$checktype}" />
<input type="hidden" name="sld" value="{$sld}" />
<input type="hidden" name="tld" value="{$tld}" />
<script language="javascript">
jQuery("#domainfrm").submit();
</script>

{/if}

{if $othersuggestions}

<div class="center80">
<div class="domainsuggestions">{$LANG.cartotherdomainsuggestions|strtolower}</div>
<table class="centertext">
<tr><th width="50"></th><th>{$LANG.domainname}</th><th>{$LANG.clientarearegistrationperiod}</th></tr>
{foreach from=$othersuggestions item=other}
<tr><td><input type="checkbox" name="domains[]" value="{$other.domain}" /></td><td>{$other.domain}</td><td><select name="domainsregperiod[{$other.domain}]">{foreach from=$other.regoptions key=period item=regoption}{if $regoption.register}<option value="{$period}">{$period} {$LANG.orderyears} @ {$regoption.register}</option>{/if}{/foreach}</select></td></tr>
{/foreach}
</table>
</div>

{assign var='continueok' value=true}

{/if}

<p align="center"><input type="submit" value="{$LANG.ordercontinuebutton}" class="cartbutton green"{if !$continueok} style="display:none;"{/if} /></p>

{literal}
<script language="javascript">
jQuery(document).ready(function(){
    jQuery("input.cartbutton:button,input.cartbutton:submit").button();
});
</script>
{/literal}

{/if}