<p>{$LANG.headertext}</p>
<table width="100%" border="0" align="center" cellpadding="10" cellspacing="0">
  <tr>
    <td><div align="center"><a href="clientarea.php"><img src="templates/{$template}/images/clientarea.png" border="0" alt="" /></a></div></td>
    <td width="50%"><strong><a href="clientarea.php">{$LANG.clientareatitle}</a></strong><br />
      {$LANG.clientareadescription}</td>
    <td><div align="center"><a href="announcements.php"><img src="templates/{$template}/images/announcements.png" border="0" alt="" /></a></div></td>
    <td><strong><a href="announcements.php">{$LANG.announcementstitle}</a></strong><br />
      {$LANG.announcementsdescription}</td>
  </tr>
  <tr>
    <td><div align="center"><a href="submitticket.php"><img src="templates/{$template}/images/submitticket.png" border="0" alt="" /></a></div></td>
    <td><strong><a href="submitticket.php">{$LANG.supportticketssubmitticket}</a></strong><br />
      {$LANG.submitticketdescription}</td>
    <td><div align="center"><a href="downloads.php"><img src="templates/{$template}/images/downloads.png" border="0" alt="" /></a></div></td>
    <td><strong><a href="downloads.php">{$LANG.downloadstitle}</a></strong><br />
      {$LANG.downloadsdescription}</td>
  </tr>
  <tr>
    <td><div align="center"><a href="supporttickets.php"><img src="templates/{$template}/images/supporttickets.png" border="0" alt="" /></a></div></td>
    <td><strong><a href="supporttickets.php">{$LANG.supportticketspagetitle}</a><br />
    </strong>{$LANG.supportticketsdescription}</td>
    <td><div align="center"><a href="knowledgebase.php"><img src="templates/{$template}/images/knowledgebase.png" border="0" alt="" /></a></div></td>
    <td width="50%"><strong><a href="knowledgebase.php">{$LANG.knowledgebasetitle}</a></strong><br />
      {$LANG.knowledgebasedescription}</td>
  </tr>
  <tr>
    <td><div align="center"><a href="affiliates.php"><img src="templates/{$template}/images/affiliates.png" border="0" alt="" /></a></div></td>
    <td><strong><a href="affiliates.php">{$LANG.affiliatestitle}</a></strong><br />
      {$LANG.affiliatesdescription}</td>
    <td><div align="center"><a href="cart.php"><img src="templates/{$template}/images/cart.png" border="0" alt="" /></a></div></td>
    <td><strong><a href="cart.php">{$LANG.ordertitle}</a></strong><br />
      {$LANG.orderdescription}</td>
  </tr>
  <tr>
    <td><div align="center"><a href="contact.php"><img src="templates/{$template}/images/contact.png" border="0" alt="" /></a></div></td>
    <td><strong><a href="contact.php">{$LANG.contacttitle}</a></strong><br />
      {$LANG.presalescontactdescription}</td>
    <td><div align="center"><a href="domainchecker.php"><img src="templates/{$template}/images/domainchecker.png" border="0" alt="" /></a></div></td>
    <td><strong><a href="domainchecker.php">{$LANG.domaintitle}</a></strong><br />
      {$LANG.domaincheckerdescription}</td>
  </tr>
  <tr>
    <td><div align="center"><a href="serverstatus.php"><img src="templates/{$template}/images/serverstatus.png" border="0" alt="" /></a></div></td>
    <td><strong><a href="serverstatus.php">{$LANG.serverstatustitle}</a></strong><br />
      {$LANG.serverstatusdescription}</td>
    <td><div align="center"><a href="networkissues.php"><img src="templates/{$template}/images/networkissues.png" border="0" alt="" /></a></div></td>
    <td><strong><a href="networkissues.php">{$LANG.networkissuestitle}</a></strong><br />
      {$LANG.networkissuesdescription}</td>
  </tr>
</table>

{if $twitterusername}
<h2>{$LANG.twitterlatesttweets}</h2>
<div id="twitterfeed">
  <p><img src="images/loading.gif"></p>
</div>
{literal}<script language="javascript">
jQuery(document).ready(function(){
  jQuery.post("announcements.php", { action: "twitterfeed", numtweets: 3 },
    function(data){
      jQuery("#twitterfeed").html(data);
    });
});
</script>{/literal}
{elseif $announcements}
<h2>{$LANG.latestannouncements}</h2>
{foreach from=$announcements item=announcement}
<p>{$announcement.date} - <a href="{if $seofriendlyurls}announcements/{$announcement.id}/{$announcement.urlfriendlytitle}.html{else}announcements.php?id={$announcement.id}{/if}">{$announcement.title}</a><br />{$announcement.text|strip_tags|truncate:100:"..."}</p>
{/foreach}
{/if}