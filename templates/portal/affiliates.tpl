{if $inactive}
<p>{$LANG.affiliatesdisabled}</p>
{else}
<p align="center">{$LANG.affiliatesrealtime}</p>
<p align="center"><strong>{$LANG.affiliatesreferallink}:</strong>
  <input type="text" size="60" value="{$referrallink}">
</p>
<table width="100%" cellpadding="0" cellspacing="0" class="frame" border="0">
  <tr>
    <td><table width="100%" border="0" align="center" cellpadding="10" cellspacing="0">
        <tr>
          <td width="230" class="fieldarea">{$LANG.affiliatesvisitorsreferred}:</td>
          <td>{$visitors}</td>
        </tr>
        <tr>
          <td width="230" class="fieldarea">{$LANG.affiliatessignups}:</td>
          <td>{$signups}</td>
        </tr>
        <tr>
          <td width="230" class="fieldarea">{$LANG.affiliatesconversionrate}:</td>
          <td>{$conversionrate}%</td>
        </tr>
    </table></td>
  </tr>
</table>
<table width="100%" cellpadding="0" cellspacing="0" class="frame" border="0">
  <tr>
    <td><table width="100%" border="0" align="center" cellpadding="10" cellspacing="0">
        <tr>
          <td width="230" class="fieldarea">{$LANG.affiliatescommissionspending}:</td>
          <td>{$pendingcommissions}</td>
        </tr>
        <tr>
          <td width="230" class="fieldarea">{$LANG.affiliatescommissionsavailable}:</td>
          <td>{$balance}</td>
        </tr>
        <tr>
          <td width="230" class="fieldarea">{$LANG.affiliateswithdrawn}:</td>
          <td>{$withdrawn}</td>
        </tr>
    </table></td>
  </tr>
</table>
{if $withdrawrequestsent}
<p align="center">{$LANG.affiliateswithdrawalrequestsuccessful}</p>
{else}
{if $withdrawlevel}
<p align="center">
  <input type="button" value="{$LANG.affiliatesrequestwithdrawal}" onclick="window.location='{$smarty.server.PHP_SELF}?action=withdrawrequest'" class="button">
</p>
{/if}
{/if}
<h2>{$LANG.affiliatesreferals}</h2>
<table width="100%" border="0" align="center" cellpadding="10" cellspacing="0" class="data">
  <tr>
    <th>{$LANG.affiliatessignupdate}</th>
    <th>{$LANG.orderproduct}</th>
    <th>{$LANG.affiliatesamount}</th>
    <th>{$LANG.affiliatescommission}</th>
    <th>{$LANG.affiliatesstatus}</th>
  </tr>
  {foreach key=num item=referral from=$referrals}
  <tr>
    <td>{$referral.date}</td>
    <td>{$referral.service}</td>
    <td>{$referral.amountdesc}</td>
    <td>{$referral.commission}</td>
    <td>{$referral.status}</td>
  </tr>
  {foreachelse}
  <tr>
    <td colspan="5">{$LANG.affiliatesnosignups}</td>
  </tr>
  {/foreach}
</table>
{if $affiliatelinkscode}
<p><strong>{$LANG.affiliateslinktous}</strong></p>
<p align="center">{$affiliatelinkscode}</p>
{/if}

{/if}