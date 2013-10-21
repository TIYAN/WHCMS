{literal}<script language="javascript">
function usedefaultns() {
    jQuery(".domnsinputs").attr("disabled", true);
}
function usecustomns() {
    jQuery(".domnsinputs").removeAttr("disabled");
}
jQuery(document).ready(function(){
{/literal}{if $defaultns}usedefaultns();
{/if}
{literal}});
</script>{/literal}

<h2>{$LANG.clientareanavdomains}</h2>
<table width="100%" cellspacing="0" cellpadding="0" class="frame">
  <tr>
    <td><table width="100%" border="0" cellpadding="10" cellspacing="0">
        <tr>
          <td class="fieldarea" width="150">{$LANG.clientareahostingregdate}:</td>
          <td>{$registrationdate}</td>
        </tr>
        <tr>
          <td class="fieldarea">{$LANG.clientareahostingdomain}:</td>
          <td><a href="http://{$domain}" target="_blank">{$domain}</a></td>
        </tr>
        <tr>
          <td class="fieldarea">{$LANG.orderpaymentmethod}:</td>
          <td>{$paymentmethod}</td>
        </tr>
        <tr>
          <td class="fieldarea">{$LANG.firstpaymentamount}:</td>
          <td>{$firstpaymentamount}</td>
        </tr>
        <tr>
          <td class="fieldarea">{$LANG.recurringamount}:</td>
          <td>{$recurringamount}</td>
        </tr>
        {if $recreatesubscriptionbutton}
        <tr>
          <td></td>
          <td>{$recreatesubscriptionbutton}</td>
        </tr>
        {/if}
        <tr>
          <td class="fieldarea">{$LANG.clientareahostingnextduedate}:</td>
          <td>{$nextduedate}</td>
        </tr>
        <tr>
          <td class="fieldarea">{$LANG.clientarearegistrationperiod}:</td>
          <td>{$registrationperiod} {$LANG.orderyears}</td>
        </tr>
        <tr>
          <td class="fieldarea">{$LANG.clientareastatus}:</td>
          <td>{$status}</td>
        </tr>
    </table></td>
  </tr>
</table>
<br />
<div align="center">{$moduleclientarea}</div>
{if $status eq $LANG.clientareaactive}
<form method="post" action="{$smarty.server.PHP_SELF}?action=domaindetails">
  <input type="hidden" name="id" value="{$domainid}">
  <h3>{$LANG.domainsautorenew}</h3>
  {if $donotrenew}
  <div class="errorbox">{$LANG.domainsautorenewdisabledwarning}</div>
  <br>
  {/if}
  <p>{$LANG.domainsautorenewstatus}: {if $donotrenew}{$LANG.domainsautorenewdisabled} &nbsp;&nbsp;&nbsp;
    <input type="hidden" name="autorenew" value="enable">
    <input type="submit" value="{$LANG.domainsautorenewenable}" class="button">
    {else}{$LANG.domainsautorenewenabled} &nbsp;&nbsp;&nbsp;
    <input type="hidden" name="autorenew" value="disable">
    <input type="submit" value="{$LANG.domainsautorenewdisable}" class="button">
    {/if}</p>
</form>
{if $managens}
<h3>{$LANG.domainnameservers}</h3>
{if $error}
<div class="errorbox">{$error}</div>
<br />
{/if}
<form method="post" action="{$smarty.server.PHP_SELF}?action=domaindetails">
  <input type="hidden" name="sub" value="savens">
  <input type="hidden" name="id" value="{$domainid}">
  <p><input type="radio" name="nschoice" value="default" id="nschoicedefault" onclick="usedefaultns()"{if $defaultns} checked{/if} /> <label for="nschoicedefault">{$LANG.nschoicedefault}</label><br />
    <input type="radio" name="nschoice" value="custom" id="nschoicecustom" onclick="usecustomns()"{if !$defaultns} checked{/if} /> <label for="nschoicecustom">{$LANG.nschoicecustom}</label></p>
  <table width="100%" cellspacing="0" cellpadding="0" class="frame">
    <tr>
      <td><table width="100%" border="0" cellpadding="10" cellspacing="0">
          <tr>
            <td class="fieldarea" width="150">{$LANG.domainnameserver1}:</td>
            <td><input type="text" name="ns1" value="{$ns1}" size="40" class="domnsinputs" /></td>
          </tr>
          <tr>
            <td class="fieldarea">{$LANG.domainnameserver2}:</td>
            <td><input type="text" name="ns2" value="{$ns2}" size="40" class="domnsinputs" /></td>
          </tr>
          <tr>
            <td class="fieldarea">{$LANG.domainnameserver3}:</td>
            <td><input type="text" name="ns3" value="{$ns3}" size="40" class="domnsinputs" /></td>
          </tr>
          <tr>
            <td class="fieldarea">{$LANG.domainnameserver4}:</td>
            <td><input type="text" name="ns4" value="{$ns4}" size="40" class="domnsinputs" /></td>
          </tr>
          <tr>
            <td class="fieldarea">{$LANG.domainnameserver5}:</td>
            <td><input type="text" name="ns5" value="{$ns5}" size="40" class="domnsinputs" /></td>
          </tr>
      </table></td>
    </tr>
  </table>
  <p align="center">
    <input type="submit" value="{$LANG.clientareasavechanges}" class="button">
  </p>
</form>
{/if}

{if $lockstatus}
{if $tld neq "co.uk" && $tld neq "org.uk" && $tld neq "ltd.uk" && $tld neq "plc.uk" && $tld neq "me.uk"}
<form method="post" action="{$smarty.server.PHP_SELF}?action=domaindetails">
  <input type="hidden" name="sub" value="savereglock">
  <input type="hidden" name="id" value="{$domainid}">
  <h3>{$LANG.domainregistrarlock}</h3>
  <table width="100%" cellspacing="0" cellpadding="0" class="frame">
    <tr>
      <td><table width="100%" border="0" cellpadding="10" cellspacing="0">
          <tr>
            <td class="fieldarea" width="150">{$LANG.domainregistrarlock}:</td>
            <td><input type="checkbox" name="reglock"{if $lockstatus=="locked"} checked{/if}>
              {$LANG.domainregistrarlockdesc}</td>
          </tr>
      </table></td>
    </tr>
  </table>
  <p align="center">
    <input type="submit" value="{$LANG.clientareasavechanges}" class="button">
  </p>
</form>
{/if}
{/if}

{if $releasedomain}
<h3>{$LANG.domainrelease}</h3>
<form method="post" action="{$smarty.server.PHP_SELF}?action=domaindetails">
  <input type="hidden" name="sub" value="releasedomain">
  <input type="hidden" name="id" value="{$domainid}">
  <table width="100%" cellspacing="0" cellpadding="0" class="frame">
    <tr>
      <td><table width="100%" border="0" cellpadding="10" cellspacing="0">
          <tr>
            <td class="fieldarea" width="150">{$LANG.domainreleasetag}:</td>
            <td><input type="text" name="transtag" size="20" /> {$LANG.domainreleasedescription}</td>
          </tr>
      </table></td>
    </tr>
  </table>
  <p align="center">
    <input type="submit" value="{$LANG.domainrelease}" class="buttonwarn">
  </p>
</form>
{/if}

{/if}
<h3><strong>{$LANG.domainmanagementtools}</strong></h3>
<table border="0" align="center" cellpadding="10" cellspacing="0">
  <tr> {if $renew}
    <td><form method="post" action="{$smarty.server.PHP_SELF}?action=domainrenew">
        <input type="hidden" name="domainid" value="{$domainid}">
        <p align="center">
          <input type="submit" value="{$LANG.domainrenew}" class="button">
        </p>
      </form></td>
    {/if}
    {if $managecontacts}
    <td><form method="post" action="{$smarty.server.PHP_SELF}?action=domaincontacts">
        <input type="hidden" name="domainid" value="{$domainid}">
        <p align="center">
          <input type="submit" value="{$LANG.domaincontactinfo}" class="button">
        </p>
      </form></td>
    {/if}
    {if $emailforwarding}
    <td><form method="post" action="{$smarty.server.PHP_SELF}?action=domainemailforwarding">
        <input type="hidden" name="domainid" value="{$domainid}">
        <p align="center">
          <input type="submit" value="{$LANG.domainemailforwarding}" class="button">
        </p>
      </form></td>
    {/if}
    {if $dnsmanagement}
    <td><form method="post" action="{$smarty.server.PHP_SELF}?action=domaindns">
        <input type="hidden" name="domainid" value="{$domainid}">
        <p align="center">
          <input type="submit" value="{$LANG.domaindnsmanagement}" class="button">
        </p>
      </form></td>
    {/if}
    {if $getepp}
    <td><form method="post" action="{$smarty.server.PHP_SELF}?action=domaingetepp">
        <input type="hidden" name="domainid" value="{$domainid}">
        <p align="center">
          <input type="submit" value="{$LANG.domaingeteppcode}" class="button">
        </p>
      </form></td>
    {/if}
    {if $registerns}
    <td><form method="post" action="{$smarty.server.PHP_SELF}?action=domainregisterns">
        <input type="hidden" name="domainid" value="{$domainid}">
        <p align="center">
          <input type="submit" value="{$LANG.domainregisterns}" class="button">
        </p>
      </form></td>
    {/if} </tr>
</table>

<p align="center"><input type="button" value="{$LANG.clientareabacklink}" onclick="window.location='clientarea.php?action=domains'" class="button" /></p>