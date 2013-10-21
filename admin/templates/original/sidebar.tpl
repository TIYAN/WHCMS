{if $sidebar eq "home"}

<span class="header"><img src="images/icons/home.png" class="absmiddle" width="16" height="16" /> {$_ADMINLANG.global.shortcuts}</span>
<ul class="menu">
    <li><a href="clientsadd.php">{$_ADMINLANG.clients.addnew}</a></li>
    <li><a href="ordersadd.php">{$_ADMINLANG.orders.addnew}</a></li>
    <li><a href="quotes.php?action=manage">{$_ADMINLANG.quotes.createnew}</a></li>
    <li><a href="todolist.php">{$_ADMINLANG.utilities.todolistcreatenew}</a></li>
    <li><a href="supporttickets.php?action=open">{$_ADMINLANG.support.opennewticket}</a></li>
    <li><a href="whois.php">{$_ADMINLANG.utilities.whois}</a></li>
    <li><a href="#" onClick="showDialog('geninvoices');return false">{$_ADMINLANG.invoices.geninvoices}</a></li>
    <li><a href="#" onClick="showDialog('cccapture');return false">{$_ADMINLANG.invoices.attemptcccaptures}</a></li>
</ul>

<span class="plain_header">{$_ADMINLANG.global.systeminfo}</span>
<div class="smallfont">{$_ADMINLANG.license.regto}: {$licenseinfo.registeredname}<br />{$_ADMINLANG.license.type}: {$licenseinfo.productname}<br />{$_ADMINLANG.license.expires}: {$licenseinfo.expires}<br />{$_ADMINLANG.global.version}: {$licenseinfo.currentversion}{if $licenseinfo.currentversion neq $licenseinfo.latestversion}<br /><span class="textred"><b>{$_ADMINLANG.license.updateavailable}</b></span>{/if}</div>

{elseif $sidebar eq "clients"}

<span class="header"><img src="images/icons/clients.png" class="absmiddle" alt="Clients" width="16" height="16" /> {$_ADMINLANG.clients.title}</span>
<ul class="menu">
    <li><a href="clients.php">{$_ADMINLANG.clients.viewsearch}</a></li>
    <li><a href="clientsadd.php">{$_ADMINLANG.clients.addnew}</a></li>
    <li><a href="massmail.php">{$_ADMINLANG.clients.massmail}</a></li>
</ul>

<span class="header"><img src="images/icons/products.png" alt="Products/Services" width="16" height="16" class="absmiddle" /> {$_ADMINLANG.services.title}</span>
<ul class="menu">
    <li><a href="clientshostinglist.php">{$_ADMINLANG.services.listall}</a></li>
    <li><a href="clientshostinglist.php?type=hostingaccount">- {$_ADMINLANG.services.listhosting}</a></li>
    <li><a href="clientshostinglist.php?type=reselleraccount">- {$_ADMINLANG.services.listreseller}</a></li>
    <li><a href="clientshostinglist.php?type=server">- {$_ADMINLANG.services.listservers}</a></li>
    <li><a href="clientshostinglist.php?type=other">- {$_ADMINLANG.services.listother}</a></li>
    <li><a href="clientsaddonslist.php">{$_ADMINLANG.services.listaddons}</a></li>
    <li><a href="clientsdomainlist.php">{$_ADMINLANG.services.listdomains}</a></li>
    <li><a href="cancelrequests.php">{$_ADMINLANG.clients.cancelrequests}</a></li>
</ul>

<span class="header"><img src="images/icons/affiliates.png" alt="Affiliates" width="16" height="16" class="absmiddle" /> {$_ADMINLANG.affiliates.title}</span>
<ul class="menu">
    <li><a href="affiliates.php">{$_ADMINLANG.affiliates.manage}</a></li>
</ul>

{elseif $sidebar eq "orders"}

<span class="header"><img src="images/icons/orders.png" alt="Affiliates" width="16" height="16" class="absmiddle" /> {$_ADMINLANG.orders.title}</span>
<ul class="menu">
    <li><a href="orders.php">{$_ADMINLANG.orders.listall}</a></li>
    <li><a href="orders.php?status=Pending">- {$_ADMINLANG.orders.listpending}</a></li>
    <li><a href="orders.php?status=Active">- {$_ADMINLANG.orders.listactive}</a></li>
    <li><a href="orders.php?status=Fraud">- {$_ADMINLANG.orders.listfraud}</a></li>
    <li><a href="orders.php?status=Cancelled">- {$_ADMINLANG.orders.listcancelled}</a></li>
    <li><a href="ordersadd.php">{$_ADMINLANG.orders.addnew}</a></li>
</ul>

{elseif $sidebar eq "billing"}

<span class="header"><img src="images/icons/transactions.png" class="absmiddle" width="16" height="16" /> {$_ADMINLANG.billing.title}</span>
<ul class="menu">
    <li><a href="transactions.php">{$_ADMINLANG.billing.transactionslist}</a></li>
    <li><a href="gatewaylog.php">{$_ADMINLANG.billing.gatewaylog}</a></li>
    <li><a href="offlineccprocessing.php">{$_ADMINLANG.billing.offlinecc}</a></li>
</ul>

<span class="header"><img src="images/icons/invoices.png" class="absmiddle" width="16" height="16" /> {$_ADMINLANG.invoices.title}</span>
<ul class="menu">
    <li><a href="invoices.php">{$_ADMINLANG.invoices.listall}</a></li>
    <li><a href="invoices.php?status=Paid">- {$_ADMINLANG.status.paid}</a></li>
    <li><a href="invoices.php?status=Unpaid">- {$_ADMINLANG.status.unpaid}</a></li>
    <li><a href="invoices.php?status=Overdue">- {$_ADMINLANG.status.overdue}</a></li>
    <li><a href="invoices.php?status=Cancelled">- {$_ADMINLANG.status.cancelled}</a></li>
    <li><a href="invoices.php?status=Refunded">- {$_ADMINLANG.status.refunded}</a></li>
    <li><a href="invoices.php?status=Collections">- {$_ADMINLANG.status.collections}</a></li>
</ul>

<span class="header"><img src="images/icons/billableitems.png" class="absmiddle" width="16" height="16" /> {$_ADMINLANG.billableitems.title}</span>
<ul class="menu">
    <li><a href="billableitems.php">{$_ADMINLANG.billableitems.listall}</a></li>
    <li><a href="billableitems.php?status=Uninvoiced">- {$_ADMINLANG.billableitems.uninvoiced}</a></li>
    <li><a href="billableitems.php?status=Recurring">- {$_ADMINLANG.billableitems.recurring}</a></li>
    <li><a href="billableitems.php?action=manage">{$_ADMINLANG.billableitems.addnew}</a></li>
</ul>

<span class="header"><img src="images/icons/quotes.png" class="absmiddle" width="16" height="16" /> {$_ADMINLANG.quotes.title}</span>
<ul class="menu">
    <li><a href="quotes.php">{$_ADMINLANG.quotes.listall}</a></li>
    <li><a href="quotes.php?validity=Valid">- {$_ADMINLANG.status.valid}</a></li>
    <li><a href="quotes.php?validity=Expired">- {$_ADMINLANG.status.expired}</a></li>
    <li><a href="quotes.php?action=manage">{$_ADMINLANG.quotes.createnew}</a></li>
</ul>

{elseif $sidebar eq "support"}

<span class="header"><img src="images/icons/support.png" alt="Support Center" width="16" height="16" class="absmiddle" /> {$_ADMINLANG.support.title}</span>
<ul class="menu">
    <li><a href="supportannouncements.php">{$_ADMINLANG.support.announcements}</a></li>
    <li><a href="supportdownloads.php">{$_ADMINLANG.support.downloads}</a></li>
    <li><a href="supportkb.php">{$_ADMINLANG.support.knowledgebase}</a></li>
    <li><a href="supporttickets.php?action=open">{$_ADMINLANG.support.opennewticket}</a></li>
    <li><a href="supportticketpredefinedreplies.php">{$_ADMINLANG.support.predefreplies}</a></li>
</ul>

<span class="header"><img src="images/icons/tickets.png" alt="Filter Tickets" width="16" height="16" class="absmiddle" /> {$_ADMINLANG.support.filtertickets}</span>
<ul class="menu">
    <li><a href="supporttickets.php">{$_ADMINLANG.support.awaitingreply} ({$ticketsawaitingreply})</a></li>
    <li><a href="supporttickets.php?view=flagged">{$_ADMINLANG.support.flagged} ({$ticketsflagged})</a></li>
    <li><a href="supporttickets.php?view=active">{$_ADMINLANG.support.allactive} ({$ticketsallactive})</a></li>
{foreach from=$ticketcounts item=ticket}
    <li><a href="supporttickets.php?view={$ticket.title}">- {$ticket.title} ({$ticket.count})</a></li>
{/foreach}</ul>

<span class="header"><img src="images/icons/networkissues.png" alt="Network Issues" width="16" height="16" class="absmiddle" /> {$_ADMINLANG.networkissues.title}</span>
<ul class="menu">
    <li><a href="networkissues.php">- {$_ADMINLANG.networkissues.open}</a></li>
    <li><a href="networkissues.php?view=scheduled">- {$_ADMINLANG.networkissues.scheduled}</a></li>
    <li><a href="networkissues.php?view=resolved">- {$_ADMINLANG.networkissues.resolved}</a></li>
    <li><a href="networkissues.php?action=manage">{$_ADMINLANG.networkissues.addnew}</a></li>
</ul>

{elseif $sidebar eq "reports"}

<span class="header"><img src="images/icons/reports.png" class="absmiddle" width="16" height="16" /> {$_ADMINLANG.reports.title}</span>
<ul class="menu">
    {foreach from=$text_reports key=filename item=reporttitle}
    <li><a href="reports.php?report={$filename}">{$reporttitle}</a></li>
    {/foreach}
</ul>

{elseif $sidebar eq "browser"}

<span class="header"><img src="images/icons/browser.png" class="absmiddle" width="16" height="16" /> {$_ADMINLANG.browser.bookmarks}</span>
<ul class="menu">
    <li><a href="http://www.whmcs.com/" target="brwsrwnd">WHMCS Homepage</a></li>
    <li><a href="https://www.whmcs.com/clients/" target="brwsrwnd">WHMCS Client Area</a></li>
    {foreach from=$browserlinks item=link}
    <li><a href="{$link.url}" target="brwsrwnd">{$link.name} <img src="images/delete.gif" width="10" border="0" onclick="doDelete('{$link.id}')"></a></li>
    {/foreach}
</ul>

<form method="post" action="browser.php?action=add">
<span class="header"><img src="images/icons/browseradd.png" class="absmiddle" width="16" height="16" /> {$_ADMINLANG.browser.addnew}</span>
<ul class="menu">
    <li>{$_ADMINLANG.browser.sitename}:<br><input type="text" name="sitename" size="25" style="font-size:9px;"><br>{$_ADMINLANG.browser.url}:<br><input type="text" name="siteurl" size="25" value="http://" style="font-size:9px;"><br><input type="submit" value="{$_ADMINLANG.browser.add}" style="font-size:9px;"></li>
</ul>
</form>

{elseif $sidebar eq "utilities"}

<span class="header"><img src="images/icons/utilities.png" class="absmiddle" width="16" height="16" /> {$_ADMINLANG.utilities.title}</span>
<ul class="menu">
    <li><a href="utilitieslinktracking.php">{$_ADMINLANG.utilities.linktracking}</a></li>
    <li><a href="browser.php">{$_ADMINLANG.utilities.browser}</a></li>
    <li><a href="calendar.php">{$_ADMINLANG.utilities.calendar}</a></li>
    <li><a href="todolist.php">{$_ADMINLANG.utilities.todolist}</a></li>
    <li><a href="whois.php">{$_ADMINLANG.utilities.whois}</a></li>
    <li><a href="utilitiesresolvercheck.php">{$_ADMINLANG.utilities.domainresolver}</a></li>
    <li><a href="systemintegrationcode.php">{$_ADMINLANG.utilities.integrationcode}</a></li>
    <li><a href="whmimport.php">{$_ADMINLANG.utilities.cpanelimport}</a></li>
    <li><a href="systemdatabase.php">{$_ADMINLANG.utilities.dbstatus}</a></li>
    <li><a href="systemcleanup.php">{$_ADMINLANG.utilities.syscleanup}</a></li>
    <li><a href="systemphpinfo.php">{$_ADMINLANG.utilities.phpinfo}</a></li>
</ul>

<span class="header"><img src="images/icons/logs.png" class="absmiddle" width="16" height="16" /> {$_ADMINLANG.utilities.logs}</span>
<ul class="menu">
    <li><a href="systemactivitylog.php">{$_ADMINLANG.utilities.activitylog}</a></li>
    <li><a href="systemadminlog.php">{$_ADMINLANG.utilities.adminlog}</a></li>
    <li><a href="systemmodulelog.php">{$_ADMINLANG.utilities.modulelog}</a></li>
    <li><a href="systememaillog.php">{$_ADMINLANG.utilities.emaillog}</a></li>
    <li><a href="systemmailimportlog.php">{$_ADMINLANG.utilities.ticketmaillog}</a></li>
    <li><a href="systemwhoislog.php">{$_ADMINLANG.utilities.whoislog}</a></li>
</ul>

{elseif $sidebar eq "addonmodules"}

{$addon_module_sidebar}

<span class="header"><img src="images/icons/addonmodules.png" class="absmiddle" width="16" height="16" /> {$_ADMINLANG.utilities.addonmodules}</span>
<ul class="menu">
    {foreach from=$addon_modules key=filename item=addontitle}
    <li><a href="addonmodules.php?module={$filename}">{$addontitle}</a></li>
    {/foreach}
</ul>

{elseif $sidebar eq "config"}

<span class="header"><img src="images/icons/config.png" class="absmiddle" width="16" height="16" /> {$_ADMINLANG.setup.config}</span>
<ul class="menu">
    <li><a href="configgeneral.php">{$_ADMINLANG.setup.general}</a></li>
    <li><a href="configauto.php">{$_ADMINLANG.setup.automation}</a></li>
    <li><a href="configemailtemplates.php">{$_ADMINLANG.setup.emailtpls}</a></li>
    <li><a href="configaddonmods.php">{$_ADMINLANG.setup.addonmodules}</a></li>
    <li><a href="configclientgroups.php">{$_ADMINLANG.setup.clientgroups}</a></li>
    <li><a href="configfraud.php">{$_ADMINLANG.setup.fraud}</a></li>
</ul>

<span class="header"><img src="images/icons/admins.png" class="absmiddle" width="16" height="16" /> {$_ADMINLANG.setup.staff}</span>
<ul class="menu">
    <li><a href="configadmins.php">{$_ADMINLANG.setup.admins}</a></li>
    <li><a href="configadminroles.php">{$_ADMINLANG.setup.adminroles}</a></li>
    <li><a href="configtwofa.php">{$_ADMINLANG.setup.twofa}</a></li>
</ul>

<span class="header"><img src="images/icons/income.png" class="absmiddle" width="16" height="16" /> {$_ADMINLANG.setup.payments}</span>
<ul class="menu">
    <li><a href="configcurrencies.php">{$_ADMINLANG.setup.currencies}</a></li>
    <li><a href="configgateways.php">{$_ADMINLANG.setup.gateways}</a></li>
    <li><a href="configtax.php">{$_ADMINLANG.setup.tax}</a></li>
    <li><a href="configpromotions.php">{$_ADMINLANG.setup.promos}</a></li>
</ul>

<span class="header"><img src="images/icons/products.png" class="absmiddle" width="16" height="16" /> {$_ADMINLANG.setup.products}</span>
<ul class="menu">
    <li><a href="configproducts.php">{$_ADMINLANG.setup.products}</a></li>
    <li><a href="configproductoptions.php">{$_ADMINLANG.setup.configoptions}</a></li>
    <li><a href="configaddons.php">{$_ADMINLANG.setup.addons}</a></li>
    <li><a href="configbundles.php">{$_ADMINLANG.setup.bundles}</a></li>
    <li><a href="configdomains.php">{$_ADMINLANG.setup.domainpricing}</a></li>
    <li><a href="configregistrars.php">{$_ADMINLANG.setup.registrars}</a></li>
    <li><a href="configservers.php">{$_ADMINLANG.setup.servers}</a></li>
</ul>

<span class="header"><img src="images/icons/support.png" class="absmiddle" width="16" height="16" /> {$_ADMINLANG.support.title}</span>
<ul class="menu">
    <li><a href="configticketdepartments.php">{$_ADMINLANG.setup.supportdepartments}</a></li>
    <li><a href="configticketstatuses.php">{$_ADMINLANG.setup.ticketstatuses}</a></li>
    <li><a href="configticketescalations.php">{$_ADMINLANG.setup.escalationrules}</a></li>
    <li><a href="configticketspamcontrol.php">{$_ADMINLANG.setup.spam}</a></li>
</ul>

<span class="header"><img src="images/icons/configother.png" class="absmiddle" width="16" height="16" /> {$_ADMINLANG.setup.other}</span>
<ul class="menu">
    <li><a href="configcustomfields.php">{$_ADMINLANG.setup.customclientfields}</a></li>
    <li><a href="configorderstatuses.php">{$_ADMINLANG.setup.orderstatuses}</a></li>
    <li><a href="configsecurityqs.php">{$_ADMINLANG.setup.securityqs}</a></li>
    <li><a href="configbannedips.php">{$_ADMINLANG.setup.bannedips}</a></li>
    <li><a href="configbannedemails.php">{$_ADMINLANG.setup.bannedemails}</a></li>
    <li><a href="configbackups.php">{$_ADMINLANG.setup.backups}</a></li>
</ul>

{/if}

{if  $sidebar eq "home" OR $sidebar eq "clients" OR $sidebar eq "orders" OR $sidebar eq "billing"}

<span class="plain_header"><img src="images/icons/stats.png" alt="Statistics" width="16" height="16" class="absmiddle" /> {$_ADMINLANG.stats.title}</span>
<div class="smallfont">
<a href="orders.php?filter=true&amp;status=Pending">{$_ADMINLANG.stats.pendingorders}</a>: {$sidebarstats.orders.pending}<br />
<br />
<a href="clients.php?status=Active">{$_ADMINLANG.stats.activeclients}</a>: {$sidebarstats.clients.active}<br />
<a href="clients.php?status=Inactive">{$_ADMINLANG.stats.inactiveclients}</a>: {$sidebarstats.clients.inactive}<br />
<a href="clients.php?status=Closed">{$_ADMINLANG.stats.closedclients}</a>: {$sidebarstats.clients.closed}<br />
<br />
<a href="clientshostinglist.php?status=Pending">{$_ADMINLANG.stats.pendingservices}</a>: {$sidebarstats.services.pending}<br />
<a href="clientshostinglist.php?status=Active">{$_ADMINLANG.stats.activeservices}</a>: {$sidebarstats.services.active}<br />
<a href="clientshostinglist.php?status=Suspended">{$_ADMINLANG.stats.suspendedservices}</a>: {$sidebarstats.services.suspended}<br />
<a href="clientshostinglist.php?status=Terminated">{$_ADMINLANG.stats.terminatedservices}</a>: {$sidebarstats.services.terminated}<br />
<a href="clientshostinglist.php?status=Cancelled">{$_ADMINLANG.stats.cancelledservices}</a>: {$sidebarstats.services.cancelled}<br />
<a href="clientshostinglist.php?status=Fraud">{$_ADMINLANG.stats.fraudservices}</a>: {$sidebarstats.services.fraud}<br />
<br />
<a href="clientsdomainlist.php?status=Pending">{$_ADMINLANG.stats.pendingdomains}</a>: {$sidebarstats.domains.pending}<br />
<a href="clientsdomainlist.php?status=Pending%20Transfer">{$_ADMINLANG.stats.pendingtransferdomains}</a>: {$sidebarstats.domains.pendingtransfer}<br />
<a href="clientsdomainlist.php?status=Active">{$_ADMINLANG.stats.activedomains}</a>: {$sidebarstats.domains.active}<br />
<a href="clientsdomainlist.php?status=Expired">{$_ADMINLANG.stats.expireddomains}</a>: {$sidebarstats.domains.expired}<br />
<a href="clientsdomainlist.php?status=Cancelled">{$_ADMINLANG.stats.cancelleddomains}</a>: {$sidebarstats.domains.cancelled}<br />
<a href="clientsdomainlist.php?status=Fraud">{$_ADMINLANG.stats.frauddomains}</a>: {$sidebarstats.domains.fraud}<br />
<br />
<a href="invoices.php?status=Unpaid">{$_ADMINLANG.stats.unpaidinvoices}</a>: {$sidebarstats.invoices.unpaid}<br />
<a href="invoices.php?status=Overdue">{$_ADMINLANG.stats.overdueinvoices}</a>: {$sidebarstats.invoices.overdue}<br />
<br />
<a href="supporttickets.php?view=active">{$_ADMINLANG.stats.activetickets}</a>: {$sidebarstats.tickets.active}<br />
<a href="supporttickets.php?view=flagged">{$_ADMINLANG.stats.activeflagged}</a>: {$sidebarstats.tickets.flagged}
</div>

{/if}

<br />

<span class="plain_header">{$_ADMINLANG.global.staffonline}</span>
<div class="smallfont">{$adminsonline}</div>
