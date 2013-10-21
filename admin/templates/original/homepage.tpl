<script type="text/javascript" src="../includes/jscript/jqueryag.js"></script>

{if $maintenancemode}
<div align="center" class="errorbox">
{$_ADMINLANG.home.maintenancemode}
</div>
<br />
{/if}

{$infobox}

{if $viewincometotals}
<div id="incometotals" class="infobox" style="font-size:14px;"><a href="transactions.php"><img src="images/icons/transactions.png" align="absmiddle" border="0"> <b>{$_ADMINLANG.billing.income}</b></a> <img src="images/loading.gif" align="absmiddle" /> {$_ADMINLANG.global.loading}</div>
{/if}

<br />

{foreach from=$addons_html item=addon_html}
<div style="margin-bottom:15px;">{$addon_html}</div>
{/foreach}

<div class="homecolumn" id="homecol1">

	<div class="homewidget" id="sysinfo">
		<div class="widget-header">{$_ADMINLANG.global.systeminfo}</div>
		<div class="widget-content">
<table width="100%">
<tr><td width="20%" style="text-align:right;padding-right:5px;">{$_ADMINLANG.license.regto}</td><td width="35%">{$licenseinfo.registeredname}</td><td width="10%" style="text-align:right;padding-right:5px;">{$_ADMINLANG.license.expires}</td><td width="35%">{$licenseinfo.expires}</td></tr>
<tr><td style="text-align:right;padding-right:5px;">{$_ADMINLANG.license.type}</td><td>{$licenseinfo.productname}</td><td style="text-align:right;padding-right:5px;">{$_ADMINLANG.global.version}</td><td>{$licenseinfo.currentversion}{if $licenseinfo.currentversion neq $licenseinfo.latestversion} <span class="textred"><b>{$_ADMINLANG.license.updateavailable}</b></span>{/if}</td></tr>
<tr><td style="text-align:right;padding-right:5px;">{$_ADMINLANG.global.staffonline}</td><td colspan="3">{$adminsonline}</td></tr>
</table>
        </div>
	</div>

{foreach from=$widgets item=widget}
	<div class="homewidget" id="{$widget.name}">
		<div class="widget-header">{$widget.title}</div>
		<div class="widget-content">
            {$widget.content}
        </div>
	</div>
{/foreach}

</div>

<div class="homecolumn" id="homecol2">

</div>

<div style="clear:both;"></div>

<p align="center"><input type="button" value="{$_ADMINLANG.invoices.geninvoices}" class="button" onClick="showDialog('geninvoices')">{if $showattemptccbutton} <input type="button" value="{$_ADMINLANG.invoices.attemptcccaptures}" class="button" onClick="showDialog('cccapture')">{/if}</p>

<div id="geninvoices" title="{$_ADMINLANG.invoices.geninvoices}">
    <p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 40px 0;"></span>{$_ADMINLANG.invoices.geninvoicessendemails}</p>
</div>
<div id="cccapture" title="{$_ADMINLANG.invoices.attemptcccaptures}">
    <p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 40px 0;"></span>{$_ADMINLANG.invoices.attemptcccapturessure}</p>
</div>