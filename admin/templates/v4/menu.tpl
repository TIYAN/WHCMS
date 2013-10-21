<li><a href="index.php" title="Home">{$_ADMINLANG.home.title}</a>
  <ul>
    <li><a href="index.php">{$_ADMINLANG.global.hometitle}</a></li>
    <li><a href="myaccount.php">{$_ADMINLANG.global.myaccount}</a></li>
    <li><a href="logout.php">{$_ADMINLANG.global.logout}</a></li>
  </ul>
</li>
<li><a {if in_array("List Clients",$admin_perms)}href="clients.php"{/if} title="Clients">{$_ADMINLANG.clients.title}</a>
  <ul>
    {if in_array("List Clients",$admin_perms)}<li><a href="clients.php">{$_ADMINLANG.clients.viewsearch}</a></li>{/if}
    {if in_array("Add New Client",$admin_perms)}<li><a href="clientsadd.php">{$_ADMINLANG.clients.addnew}</a></li>{/if}
    {if in_array("List Services",$admin_perms)}
    <li class="expand"><a href="clientshostinglist.php">{$_ADMINLANG.services.title}</a>
        <ul>
        <li><a href="clientshostinglist.php?listtype=hostingaccount">- {$_ADMINLANG.services.listhosting}</a></li>
        <li><a href="clientshostinglist.php?listtype=reselleraccount">- {$_ADMINLANG.services.listreseller}</a></li>
        <li><a href="clientshostinglist.php?listtype=server">- {$_ADMINLANG.services.listservers}</a></li>
        <li><a href="clientshostinglist.php?listtype=other">- {$_ADMINLANG.services.listother}</a></li>
        </ul>
    </li>
    {/if}
    {if in_array("List Addons",$admin_perms)}<li><a href="clientsaddonslist.php">{$_ADMINLANG.services.listaddons}</a></li>{/if}
    {if in_array("List Domains",$admin_perms)}<li><a href="clientsdomainlist.php">{$_ADMINLANG.services.listdomains}</a></li>{/if}
    {if in_array("View Cancellation Requests",$admin_perms)}<li><a href="cancelrequests.php">{$_ADMINLANG.clients.cancelrequests}</a></li>{/if}
    {if in_array("Manage Affiliates",$admin_perms)}<li><a href="affiliates.php">{$_ADMINLANG.affiliates.manage}</a></li>{/if}
    {if in_array("Mass Mail",$admin_perms)}<li><a href="massmail.php">{$_ADMINLANG.clients.massmail}</a></li>{/if}
  </ul>
</li>
<li><a {if in_array("View Orders",$admin_perms)}href="orders.php"{/if} title="Orders">{$_ADMINLANG.orders.title}</a>
  <ul>
    {if in_array("View Orders",$admin_perms)}<li><a href="orders.php">{$_ADMINLANG.orders.listall}</a></li>
    <li><a href="orders.php?status=Pending">- {$_ADMINLANG.orders.listpending}</a></li>
    <li><a href="orders.php?status=Active">- {$_ADMINLANG.orders.listactive}</a></li>
    <li><a href="orders.php?status=Fraud">- {$_ADMINLANG.orders.listfraud}</a></li>
    <li><a href="orders.php?status=Cancelled">- {$_ADMINLANG.orders.listcancelled}</a></li>{/if}
    {if in_array("Add New Order",$admin_perms)}<li><a href="ordersadd.php">{$_ADMINLANG.orders.addnew}</a></li>{/if}
  </ul>
</li>
<li><a {if in_array("List Transactions",$admin_perms)}href="transactions.php"{/if} title="Billing">{$_ADMINLANG.billing.title}</a>
  <ul>
    {if in_array("List Transactions",$admin_perms)}<li><a href="transactions.php">{$_ADMINLANG.billing.transactionslist}</a></li>{/if}
    {if in_array("List Invoices",$admin_perms)}
    <li class="expand"><a href="invoices.php">{$_ADMINLANG.invoices.title}</a>
        <ul>
        <li><a href="invoices.php?status=Paid">- {$_ADMINLANG.status.paid}</a></li>
        <li><a href="invoices.php?status=Unpaid">- {$_ADMINLANG.status.unpaid}</a></li>
        <li><a href="invoices.php?status=Overdue">- {$_ADMINLANG.status.overdue}</a></li>
        <li><a href="invoices.php?status=Cancelled">- {$_ADMINLANG.status.cancelled}</a></li>
        <li><a href="invoices.php?status=Refunded">- {$_ADMINLANG.status.refunded}</a></li>
        <li><a href="invoices.php?status=Collections">- {$_ADMINLANG.status.collections}</a></li>
        </ul>
    </li>{/if}
    {if in_array("View Billable Items",$admin_perms)}<li class="expand"><a href="billableitems.php">{$_ADMINLANG.billableitems.title}</a>
        <ul>
        <li><a href="billableitems.php?status=Uninvoiced">- {$_ADMINLANG.billableitems.uninvoiced}</a></li>
        <li><a href="billableitems.php?status=Recurring">- {$_ADMINLANG.billableitems.recurring}</a></li>
        {if in_array("Manage Billable Items",$admin_perms)}<li><a href="billableitems.php?action=manage">- {$_ADMINLANG.billableitems.addnew}</a></li>{/if}
        </ul>
    </li>{/if}
    {if in_array("Manage Quotes",$admin_perms)}<li class="expand"><a href="quotes.php">{$_ADMINLANG.quotes.title}</a>
        <ul>
        <li><a href="quotes.php?validity=Valid">- {$_ADMINLANG.status.valid}</a></li>
        <li><a href="quotes.php?validity=Expired">- {$_ADMINLANG.status.expired}</a></li>
        <li><a href="quotes.php?action=manage">- {$_ADMINLANG.quotes.createnew}</a></li>
        </ul>
    </li>{/if}
    {if in_array("Offline Credit Card Processing",$admin_perms)}<li><a href="offlineccprocessing.php">{$_ADMINLANG.billing.offlinecc}</a></li>{/if}
    {if in_array("View Gateway Log",$admin_perms)}<li><a href="gatewaylog.php">{$_ADMINLANG.billing.gatewaylog}</a></li>{/if}
  </ul>
</li>
<li><a {if in_array("Support Center Overview",$admin_perms)}href="supportcenter.php"{/if} title="Support">{$_ADMINLANG.support.title}</a>
  <ul>
    {if in_array("Manage Announcements",$admin_perms)}<li><a href="supportannouncements.php">{$_ADMINLANG.support.announcements}</a></li>{/if}
    {if in_array("Manage Downloads",$admin_perms)}<li><a href="supportdownloads.php">{$_ADMINLANG.support.downloads}</a></li>{/if}
    {if in_array("Manage Knowledgebase",$admin_perms)}<li><a href="supportkb.php">{$_ADMINLANG.support.knowledgebase}</a></li>{/if}
    {if in_array("List Support Tickets",$admin_perms)}<li class="expand"><a href="supporttickets.php">{$_ADMINLANG.support.supporttickets}</a>
        <ul>
        <li><a href="supporttickets.php?view=flagged">- {$_ADMINLANG.support.flagged}</a></li>
        <li><a href="supporttickets.php?view=active">- {$_ADMINLANG.support.allactive}</a></li>
        <li><a href="supporttickets.php?view=Open">- Open</a></li>
        <li><a href="supporttickets.php?view=Answered">- Answered</a></li>
        <li><a href="supporttickets.php?view=Customer-Reply">- Customer-Reply</a></li>
        <li><a href="supporttickets.php?view=On Hold">- On Hold</a></li>
        <li><a href="supporttickets.php?view=In Progress">- In Progress</a></li>
        <li><a href="supporttickets.php?view=Closed">- Closed</a></li>
        </ul>
    </li>{/if}
    {if in_array("Open New Ticket",$admin_perms)}<li><a href="supporttickets.php?action=open">{$_ADMINLANG.support.opennewticket}</a></li>{/if}
    {if in_array("Manage Predefined Replies",$admin_perms)}<li><a href="supportticketpredefinedreplies.php">{$_ADMINLANG.support.predefreplies}</a></li>{/if}
    {if in_array("Manage Network Issues",$admin_perms)}<li class="expand"><a href="networkissues.php">{$_ADMINLANG.networkissues.title}</a>
        <ul>
        <li><a href="networkissues.php">- {$_ADMINLANG.networkissues.open}</a></li>
        <li><a href="networkissues.php?view=scheduled">- {$_ADMINLANG.networkissues.scheduled}</a></li>
        <li><a href="networkissues.php?view=resolved">- {$_ADMINLANG.networkissues.resolved}</a></li>
        <li><a href="networkissues.php?action=manage">- {$_ADMINLANG.networkissues.addnew}</a></li>
        </ul>
    </li>{/if}
  </ul>
</li>
{if in_array("View Reports",$admin_perms)}<li><a title="Reports" href="reports.php">{$_ADMINLANG.reports.title}</a>
  <ul>
    <li><a href="reports.php?report=daily_performance">Daily Performance</a></li>
    <li><a href="reports.php?report=income_forecast">Income Forecast</a></li>
    <li><a href="reports.php?report=annual_income_report">Annual Income Report</a></li>
    <li><a href="reports.php?report=new_customers">New Customers</a></li>
    <li><a href="reports.php?report=ticket_feedback_scores">Ticket Feedback Scores</a></li>
    <li><a href="reports.php?report=pdf_batch">Batch Invoice PDF Export</a></li>
    <li><a href="reports.php">More...</a></li>
  </ul>
</li>{/if}
<li><a title="Utilities" href="">{$_ADMINLANG.utilities.title}</a>
  <ul>
    {if in_array("Email Marketer",$admin_perms)}<li><a href="utilitiesemailmarketer.php">{$_ADMINLANG.utilities.emailmarketer}</a></li>{/if}
    {if in_array("Link Tracking",$admin_perms)}<li><a href="utilitieslinktracking.php">{$_ADMINLANG.utilities.linktracking}</a></li>{/if}
    {if in_array("Browser",$admin_perms)}<li><a href="browser.php">{$_ADMINLANG.utilities.browser}</a></li>{/if}
    {if in_array("Calendar",$admin_perms)}<li><a href="calendar.php">{$_ADMINLANG.utilities.calendar}</a></li>{/if}
    {if in_array("To-Do List",$admin_perms)}<li><a href="todolist.php">{$_ADMINLANG.utilities.todolist}</a></li>{/if}
    {if in_array("WHOIS Lookups",$admin_perms)}<li><a href="whois.php">{$_ADMINLANG.utilities.whois}</a></li>{/if}
    {if in_array("Domain Resolver Checker",$admin_perms)}<li><a href="utilitiesresolvercheck.php">{$_ADMINLANG.utilities.domainresolver}</a></li>{/if}
    {if in_array("View Integration Code",$admin_perms)}<li><a href="systemintegrationcode.php">{$_ADMINLANG.utilities.integrationcode}</a></li>{/if}
    {if in_array("WHM Import Script",$admin_perms)}<li><a href="whmimport.php">{$_ADMINLANG.utilities.cpanelimport}</a></li>{/if}
    {if in_array("Database Status",$admin_perms) || in_array("System Cleanup Operations",$admin_perms) || in_array("View PHP Info",$admin_perms)}<li class="expand"><a href="#">{$_ADMINLANG.utilities.system}</a>
        <ul>
        {if in_array("Database Status",$admin_perms)}<li><a href="systemdatabase.php">{$_ADMINLANG.utilities.dbstatus}</a></li>{/if}
        {if in_array("System Cleanup Operations",$admin_perms)}<li><a href="systemcleanup.php">{$_ADMINLANG.utilities.syscleanup}</a></li>{/if}
        {if in_array("View PHP Info",$admin_perms)}<li><a href="systemphpinfo.php">{$_ADMINLANG.utilities.phpinfo}</a></li>{/if}
        </ul>
    </li>{/if}
    {if in_array("View Activity Log",$admin_perms) || in_array("View Admin Log",$admin_perms) || in_array("View Module Debug Log",$admin_perms) || in_array("View Email Message Log",$admin_perms) || in_array("View Ticket Mail Import Log",$admin_perms) || in_array("View WHOIS Lookup Log",$admin_perms)}<li class="expand"><a href="#">{$_ADMINLANG.utilities.logs}</a>
        <ul>
        {if in_array("View Activity Log",$admin_perms)}<li><a href="systemactivitylog.php">{$_ADMINLANG.utilities.activitylog}</a></li>{/if}
        {if in_array("View Admin Log",$admin_perms)}<li><a href="systemadminlog.php">{$_ADMINLANG.utilities.adminlog}</a></li>{/if}
        {if in_array("View Module Debug Log",$admin_perms)}<li><a href="systemmodulelog.php">{$_ADMINLANG.utilities.modulelog}</a></li>{/if}
        {if in_array("View Email Message Log",$admin_perms)}<li><a href="systememaillog.php">{$_ADMINLANG.utilities.emaillog}</a></li>{/if}
        {if in_array("View Ticket Mail Import Log",$admin_perms)}<li><a href="systemmailimportlog.php">{$_ADMINLANG.utilities.ticketmaillog}</a></li>{/if}
        {if in_array("View WHOIS Lookup Log",$admin_perms)}<li><a href="systemwhoislog.php">{$_ADMINLANG.utilities.whoislog}</a></li>{/if}
        </ul>
    </li>{/if}
  </ul>
</li>
<li><a title="Addons" href="addonmodules.php">{$_ADMINLANG.utilities.addonmodules}</a>
  <ul>
    {foreach from=$addon_modules key=module item=displayname}
    <li><a href="addonmodules.php?module={$module}">{$displayname}</a></li>
    {foreachelse}
    <li><a href="addonmodules.php">{$_ADMINLANG.utilities.addonsdirectory}</a></li>
    {/foreach}
  </ul>
</li>
<li><a title="Setup" href="">{$_ADMINLANG.setup.title}</a>
  <ul>
    {if in_array("Configure General Settings",$admin_perms)}<li><a href="configgeneral.php">{$_ADMINLANG.setup.general}</a></li>{/if}
    {if in_array("Configure Automation Settings",$admin_perms)}<li><a href="configauto.php">{$_ADMINLANG.setup.automation}</a></li>{/if}
{if in_array("Configure Administrators",$admin_perms) || in_array("Configure Admin Roles",$admin_perms) || in_array("Configure Two-Factor Authentication",$admin_perms)}
    <li class="expand"><a href="#">{$_ADMINLANG.setup.staff}</a>
        <ul>
        {if in_array("Configure Administrators",$admin_perms)}<li><a href="configadmins.php">{$_ADMINLANG.setup.admins}</a></li>{/if}
        {if in_array("Configure Admin Roles",$admin_perms)}<li><a href="configadminroles.php">{$_ADMINLANG.setup.adminroles}</a></li>{/if}
        {if in_array("Configure Two-Factor Authentication",$admin_perms)}<li><a href="configtwofa.php">{$_ADMINLANG.setup.twofa}</a></li>{/if}
        </ul>
    </li>{else}
    <li><a href="myaccount.php">{$_ADMINLANG.global.myaccount}</a></li>{/if}
{if in_array("Configure Currencies",$admin_perms) || in_array("Configure Payment Gateways",$admin_perms) || in_array("Configure Tax Setup",$admin_perms) || in_array("View Promotions",$admin_perms)}
    <li class="expand"><a href="#">{$_ADMINLANG.setup.payments}</a>
        <ul>
        {if in_array("Configure Currencies",$admin_perms)}<li><a href="configcurrencies.php">{$_ADMINLANG.setup.currencies}</a></li>{/if}
        {if in_array("Configure Payment Gateways",$admin_perms)}<li><a href="configgateways.php">{$_ADMINLANG.setup.gateways}</a></li>{/if}
        {if in_array("Configure Tax Setup",$admin_perms)}<li><a href="configtax.php">{$_ADMINLANG.setup.tax}</a></li>{/if}
        {if in_array("View Promotions",$admin_perms)}<li><a href="configpromotions.php">{$_ADMINLANG.setup.promos}</a></li>{/if}
        </ul>
    </li>{/if}
{if in_array("View Products/Services",$admin_perms) || in_array("Configure Product Addons",$admin_perms) || in_array("Configure Product Bundles",$admin_perms) || in_array("Configure Domain Pricing",$admin_perms) || in_array("Configure Domain Registrars",$admin_perms) || in_array("Configure Servers",$admin_perms)}
    <li class="expand"><a href="#">{$_ADMINLANG.setup.products}</a>
        <ul>
        {if in_array("View Products/Services",$admin_perms)}<li><a href="configproducts.php">{$_ADMINLANG.setup.products}</a></li>{/if}
        {if in_array("View Products/Services",$admin_perms)}<li><a href="configproductoptions.php">{$_ADMINLANG.setup.configoptions}</a></li>{/if}
        {if in_array("Configure Product Addons",$admin_perms)}<li><a href="configaddons.php">{$_ADMINLANG.setup.addons}</a></li>{/if}
        {if in_array("Configure Product Bundles",$admin_perms)}<li><a href="configbundles.php">{$_ADMINLANG.setup.bundles}</a></li>{/if}
        {if in_array("Configure Domain Pricing",$admin_perms)}<li><a href="configdomains.php">{$_ADMINLANG.setup.domainpricing}</a></li>{/if}
        {if in_array("Configure Domain Registrars",$admin_perms)}<li><a href="configregistrars.php">{$_ADMINLANG.setup.registrars}</a></li>{/if}
        {if in_array("Configure Servers",$admin_perms)}<li><a href="configservers.php">{$_ADMINLANG.setup.servers}</a></li>{/if}
        </ul>
    </li>{/if}
{if in_array("Configure Support Departments",$admin_perms) || in_array("Configure Ticket Statuses",$admin_perms) || in_array("Configure Support Departments",$admin_perms) || in_array("Configure Spam Control",$admin_perms)}
    <li class="expand"><a href="#">{$_ADMINLANG.support.title}</a>
        <ul>
        {if in_array("Configure Support Departments",$admin_perms)}<li><a href="configticketdepartments.php">{$_ADMINLANG.setup.supportdepartments}</a></li>{/if}
        {if in_array("Configure Ticket Statuses",$admin_perms)}<li><a href="configticketstatuses.php">{$_ADMINLANG.setup.ticketstatuses}</a></li>{/if}
        {if in_array("Configure Support Departments",$admin_perms)}<li><a href="configticketescalations.php">{$_ADMINLANG.setup.escalationrules}</a></li>{/if}
        {if in_array("Configure Spam Control",$admin_perms)}<li><a href="configticketspamcontrol.php">{$_ADMINLANG.setup.spam}</a></li>{/if}
        </ul>
    </li>{/if}
    {if in_array("View Email Templates",$admin_perms)}<li><a href="configemailtemplates.php">{$_ADMINLANG.setup.emailtpls}</a></li>{/if}
    {if in_array("Configure Addon Modules",$admin_perms)}<li><a href="configaddonmods.php">{$_ADMINLANG.setup.addonmodules}</a></li>{/if}
    {if in_array("Configure Client Groups",$admin_perms)}<li><a href="configclientgroups.php">{$_ADMINLANG.setup.clientgroups}</a></li>{/if}
    {if in_array("Configure Custom Client Fields",$admin_perms)}<li><a href="configcustomfields.php">{$_ADMINLANG.setup.customclientfields}</a></li>{/if}
    {if in_array("Configure Fraud Protection",$admin_perms)}<li><a href="configfraud.php">{$_ADMINLANG.setup.fraud}</a></li>{/if}
{if in_array("Configure Order Statuses",$admin_perms) || in_array("Configure Security Questions",$admin_perms) || in_array("View Banned IPs",$admin_perms) || in_array("Configure Banned Emails",$admin_perms) || in_array("Configure Database Backups",$admin_perms)}
    <li class="expand"><a href="#">{$_ADMINLANG.setup.other}</a>
        <ul>
        {if in_array("Configure Order Statuses",$admin_perms)}<li><a href="configorderstatuses.php">{$_ADMINLANG.setup.orderstatuses}</a></li>{/if}
        {if in_array("Configure Security Questions",$admin_perms)}<li><a href="configsecurityqs.php">{$_ADMINLANG.setup.securityqs}</a></li>{/if}
        {if in_array("View Banned IPs",$admin_perms)}<li><a href="configbannedips.php">{$_ADMINLANG.setup.bannedips}</a></li>{/if}
        {if in_array("Configure Banned Emails",$admin_perms)}<li><a href="configbannedemails.php">{$_ADMINLANG.setup.bannedemails}</a></li>{/if}
        {if in_array("Configure Database Backups",$admin_perms)}<li><a href="configbackups.php">{$_ADMINLANG.setup.backups}</a></li>{/if}
        </ul>
    </li>{/if}
  </ul>
</li>
<li><a title="Help" href="">{$_ADMINLANG.help.title}</a>
  <ul>
    <li><a href="http://docs.whmcs.com/" target="_blank">{$_ADMINLANG.help.docs}</a></li>
    {if in_array("Main Homepage",$admin_perms)}<li><a href="systemlicense.php">{$_ADMINLANG.help.licenseinfo}</a></li>{/if}
    {if in_array("Configure Administrators",$admin_perms)}<li><a href="licenseerror.php?licenseerror=change">{$_ADMINLANG.help.changelicense}</a></li>{/if}
    {if in_array("Configure General Settings",$admin_perms)}<li><a href="systemupdates.php">{$_ADMINLANG.help.updates}</a></li>
    <li><a href="systemsupportrequest.php">{$_ADMINLANG.help.support}</a></li>{/if}
    <li><a href="http://forum.whmcs.com/" target="_blank">{$_ADMINLANG.help.forums}</a></li>
  </ul>
</li>