<link rel="stylesheet" type="text/css" href="templates/orderforms/{$carttpl}/style.css" />

<div id="order-web20cart">

<h1>{$LANG.cartdomainsconfig}</h1>

<p>{$LANG.cartdomainsconfigdesc}</p>

{if $errormessage}
<div class="errorbox"><ul>{$errormessage}</ul></div>
<br />
{/if}

<form method="post" action="{$smarty.server.PHP_SELF}?a=confdomains">
<input type="hidden" name="update" value="true" />

{foreach key=num item=domain from=$domains}

<h3>{$domain.domain} - {$domain.regperiod} {$LANG.orderyears} {if $domain.hosting}<span class="textgreen">[{$LANG.cartdomainshashosting}]</span>{else}<a href="cart.php" class="textred">[{$LANG.cartdomainsnohosting}]</a>{/if}</h3>

{if $domain.configtoshow}
<div class="cartbox">

<table class="noborders">
{if $domain.eppenabled}
<tr><td class="fieldlabel">{$LANG.domaineppcode}</td><td><input type="text" name="epp[{$num}]" size="20" value="{$domain.eppvalue}" /> {$LANG.domaineppcodedesc}</td></tr>
{/if}
<tr><td class="fieldlabel">{$LANG.cartaddons}</td><td>
{if $domain.dnsmanagement}
    <label><input type="checkbox" name="dnsmanagement[{$num}]"{if $domain.dnsmanagementselected} checked{/if} />
    {$LANG.domaindnsmanagement} ({$domain.dnsmanagementprice})</label>
{/if}
{if $domain.emailforwarding}
    <label><input type="checkbox" name="emailforwarding[{$num}]"{if $domain.emailforwardingselected} checked{/if} />
    {$LANG.domainemailforwarding} ({$domain.emailforwardingprice})</label>
{/if}
{if $domain.idprotection}
    <label><input type="checkbox" name="idprotection[{$num}]"{if $domain.idprotectionselected} checked{/if} />
    {$LANG.domainidprotection} ({$domain.idprotectionprice})</label>
{/if}
</td></tr>
{foreach from=$domain.fields key=domainfieldname item=domainfield}
<tr><td class="fieldlabel">{$domainfieldname}</td><td>{$domainfield}</td></tr>
{/foreach}
</table>

</div>
{/if}

{/foreach}

{if $atleastonenohosting}

<h2>{$LANG.domainnameservers}</h2>

<p>{$LANG.cartnameserversdesc}</p>

<div class="cartbox">
    <table class="noborders">
        <tr>
            <td width="150" class="fieldarea">{$LANG.domainnameserver1}:</td>
            <td><input type="text" name="domainns1" size="40" value="{$domainns1}" /></td>
        </tr>
        <tr>
            <td width="150" class="fieldarea">{$LANG.domainnameserver2}:</td>
            <td><input type="text" name="domainns2" size="40" value="{$domainns2}" /></td>
        </tr>
        <tr>
            <td width="150" class="fieldarea">{$LANG.domainnameserver3}:</td>
            <td><input type="text" name="domainns3" size="40" value="{$domainns3}" /></td>
        </tr>
        <tr>
            <td width="150" class="fieldarea">{$LANG.domainnameserver4}:</td>
            <td><input type="text" name="domainns4" size="40" value="{$domainns4}" /></td>
        </tr>
        <tr>
            <td width="150" class="fieldarea">{$LANG.domainnameserver5}:</td>
            <td><input type="text" name="domainns5" size="40" value="{$domainns5}" /></td>
        </tr>
    </table>
</div>

{/if}

<p align="center"><input type="submit" value="{$LANG.updatecart}" /></p>

</form>

</div>