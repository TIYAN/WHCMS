{if $langchange}<div align="right">{$setlanguage}</div><br />{/if}
  </div>
  <div id="side_menu">
    <p class="header">{$LANG.quicknav}</p>
    <ul>
      <li><a href="index.php"><img src="templates/{$template}/images/icons/support.gif" alt="{$LANG.globalsystemname}" width="16" height="16" border="0" class="absmiddle" /></a> <a href="index.php" title="{$LANG.globalsystemname}">{$LANG.globalsystemname}</a></li>
      <li><a href="clientarea.php"><img src="templates/{$template}/images/icons/clientarea.gif" alt="{$LANG.clientareatitle}" width="16" height="16" border="0" class="absmiddle" /></a> <a href="clientarea.php" title="{$LANG.clientareatitle}">{$LANG.clientareatitle}</a></li>
      <li><a href="announcements.php" title="{$LANG.announcementstitle}"><img src="templates/{$template}/images/icons/announcement.gif" alt="{$LANG.announcementstitle}" width="16" height="16" border="0" class="absmiddle" /></a> <a href="announcements.php" title="{$LANG.announcementstitle}">{$LANG.announcementstitle}</a></li>
      <li><a href="knowledgebase.php" title="{$LANG.knowledgebasetitle}"><img src="templates/{$template}/images/icons/knowledgebase.gif" alt="{$LANG.knowledgebasetitle}" width="16" height="16" border="0" class="absmiddle" /></a> <a href="knowledgebase.php" title="{$LANG.knowledgebasetitle}">{$LANG.knowledgebasetitle}</a></li>
      <li><a href="submitticket.php" title="{$LANG.supportticketssubmitticket}"><img src="templates/{$template}/images/icons/submit-ticket.gif" alt="{$LANG.supportticketssubmitticket}" width="16" height="16" border="0" class="absmiddle" /></a> <a href="submitticket.php" title="{$LANG.supportticketspagetitle}">{$LANG.supportticketssubmitticket}</a></li>
      <li><a href="downloads.php" title="{$LANG.downloadstitle}"><img src="templates/{$template}/images/icons/downloads.gif" alt="{$LANG.downloadstitle}" width="16" height="16" border="0" class="absmiddle" /></a> <a href="downloads.php" title="{$LANG.downloadstitle}">{$LANG.downloadstitle}</a></li>
      <li><a href="cart.php" title="{$LANG.ordertitle}"><img src="templates/{$template}/images/icons/order.gif" alt="{$LANG.ordertitle}" width="16" height="16" border="0" class="absmiddle" /></a> <a href="cart.php" title="{$LANG.ordertitle}">{$LANG.ordertitle}</a></li>
    </ul>
{if $livehelp}
<p class="header">{$LANG.chatlivehelp}</p>
{$livehelp}
{/if}
{if $loggedin}
    <p class="header">{$LANG.accountinfo}</p>
<p><strong>{$clientsdetails.firstname} {$clientsdetails.lastname} {if $clientsdetails.companyname}({$clientsdetails.companyname}){/if}</strong><br />
{$clientsdetails.address1}, {$clientsdetails.address2}<br />
{$clientsdetails.city}, {$clientsdetails.state}, {$clientsdetails.postcode}<br />
{$clientsdetails.countryname}<br />
{$clientsdetails.email}<br /><br />
{if $condlinks.addfunds}<img src="templates/{$template}/images/icons/money.gif" alt="Add Funds" width="22" height="22" border="0" class="absmiddle" /> <a href="clientarea.php?action=addfunds">{$LANG.addfunds}</a>{/if}</p>
    <p class="header">{$LANG.accountstats}</p>
    <p>{$LANG.statsnumproducts}: <strong>{$clientsstats.productsnumactive}</strong> ({$clientsstats.productsnumtotal})<br />
{$LANG.statsnumdomains}: <strong>{$clientsstats.numactivedomains}</strong> ({$clientsstats.numdomains})<br />
{$LANG.statsnumtickets}: <strong>{$clientsstats.numtickets}</strong><br />
{$LANG.statsnumreferredsignups}: <strong>{$clientsstats.numaffiliatesignups}</strong><br />
{$LANG.statscreditbalance}: <strong>{$clientsstats.creditbalance}</strong><br />
{$LANG.statsdueinvoicesbalance}: <strong>{if $clientsstats.numdueinvoices>0}<span class="red">{/if}{$clientsstats.dueinvoicesbalance}{if $clientsstats.numdueinvoices>0}</span>{/if}</strong></p>
{else}
<form method="post" action="{$systemsslurl}dologin.php">
  <p class="header">{$LANG.clientlogin}</p>
  <p><strong>{$LANG.email}</strong><br />
    <input name="username" type="text" size="25" />
  </p>
  <p><strong>{$LANG.loginpassword}</strong><br />
    <input name="password" type="password" size="25" />
  </p>
  <p>
    <input type="checkbox" name="rememberme" />
    {$LANG.loginrememberme}</p>
  <p>
    <input type="submit" class="submitbutton" value="{$LANG.loginbutton}" />
  </p>
</form>
  <p class="header">{$LANG.knowledgebasesearch}</p>
<form method="post" action="knowledgebase.php?action=search">
  <p>
    <input name="search" type="text" size="25" /><br />
    <select name="searchin">
      <option value="Knowledgebase">{$LANG.knowledgebasetitle}</option>
      <option value="Downloads">{$LANG.downloadstitle}</option>
    </select>
    <input type="submit" value="{$LANG.go}" />
  </p>
</form>
{/if}
{if $twitterusername}<br />
<p align="center"><a href="http://twitter.com/{$twitterusername}" target="_blank"><img src="images/twitterfollow.png" width="150" border="0" alt="{$LANG.twitterfollow}" /></a></p>
{/if}
  </div>
  <div class="clear"></div>
</div>
{$footeroutput}
</body>
</html>