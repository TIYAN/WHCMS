<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="content-type" content="text/html; charset={$charset}" />
<title>{$companyname} - {$pagetitle}{if $kbarticle.title} - {$kbarticle.title}{/if}</title>
{if $systemurl}<base href="{$systemurl}" />
{/if}<link rel="stylesheet" type="text/css" href="templates/{$template}/style.css" />
<script type="text/javascript" src="includes/jscript/jquery.js"></script>
{$headoutput}
{if $livehelpjs}{$livehelpjs}
{/if}</head>
<body>

{$headeroutput}

<div class="wrapper">

<img src="templates/{$template}/header.jpg" width="730" height="118" alt="" />

<table class="topnavbar"><tr class="topnavbar"><td><a href="index.php">{$LANG.globalsystemname}</a></td><td><a href="clientarea.php">{$LANG.clientareatitle}</a></td><td><a href="announcements.php">{$LANG.announcementstitle}</a></td><td><a href="knowledgebase.php">{$LANG.knowledgebasetitle}</a></td><td><a href="supporttickets.php">{$LANG.supportticketspagetitle}</a></td><td><a href="downloads.php">{$LANG.downloadstitle}</a></td>{if $loggedin}<td><a href="logout.php">{$LANG.logouttitle}</a></td>{/if}</tr></table>

<p>{if "templates/$template/images/$filename.png"|file_exists}<img src="templates/{$template}/images/{$filename}.png" align="right" alt="" />{/if}
<span class="heading">{$pagetitle}</span><br />
{$LANG.globalyouarehere}: {$breadcrumbnav}</p>

{if $loggedin}
<p align="center" class="clientarealinks">
<a href="clientarea.php"><img src="images/clientarea.gif" border="0" hspace="5" align="absmiddle" alt="" />{$LANG.clientareanavhome}</a>
<a href="clientarea.php?action=details"><img src="images/details.gif" border="0" hspace="5" align="absmiddle" alt="" />{$LANG.clientareanavdetails}</a>
<a href="clientarea.php?action=products"><img src="images/products.gif" border="0" hspace="5" align="absmiddle" alt="" />{$LANG.clientareaproducts}</a>
{if $condlinks.domainreg || $condlinks.domaintrans}<a href="clientarea.php?action=domains"><img src="images/domains.gif" border="0" hspace="5" align="absmiddle" alt="" />{$LANG.clientareanavdomains}</a>{/if}
{if $condlinks.pmaddon}<a href="index.php?m=project_management"><img src="images/hosting.gif" border="0" hspace="5" align="absmiddle" alt="" />{$LANG.clientareaprojects}</a>{/if}
<a href="clientarea.php?action=quotes"><img src="images/pdf.gif" border="0" hspace="5" align="absmiddle" alt="" />{$LANG.quotestitle}</a>
<a href="clientarea.php?action=invoices"><img src="images/invoices.gif" border="0" hspace="5" align="absmiddle" alt="" />{$LANG.invoices}</a>
<a href="supporttickets.php"><img src="images/supporttickets.gif" border="0" hspace="5" align="absmiddle" alt="" />{$LANG.clientareanavsupporttickets}</a>
{if $condlinks.affiliates}<a href="affiliates.php"><img src="images/affiliates.gif" border="0" hspace="5" align="absmiddle" alt="" />{$LANG.affiliatestitle}</a>{/if}
<a href="clientarea.php?action=emails"><img src="images/emails.gif" border="0" hspace="5" align="absmiddle" alt="" />{$LANG.clientareaemails}</a>
</p>
{/if}