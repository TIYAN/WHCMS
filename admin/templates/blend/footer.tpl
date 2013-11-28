
</div>
<div class="clear"></div>

</div>

<div class="clear"></div>

<div class="footerbar">
<div class="left"><a href="#">Top</a></div>
<div class="right">Copyright &copy; <a href="http://www.whmcs.com/" target="_blank">WHMCompleteSolution</a>.  All Rights Reserved.</div>
</div>

<div class="intellisearch">
<form id="frmintellisearch">
<input type="hidden" name="intellisearch" value="1" />
<input type="hidden" name="token" value="{$csrfToken}" />
<input type="text" name="value" id="intellisearchval" />
<input type="submit" style="display:none;">
</form>
</div>

<div id="searchresults">
<div id="searchresultsscroller"></div>
<div class="close"><a href="#" onclick="searchclose();return false">{$_ADMINLANG.clientsummary.close} <img src="images/delete.gif" width="16" height="16" border="0" align="top" /></a></div>
</div>

<div id="greyout"></div>

<div id="popupcontainer">
<div id="mynotes">
<form id="frmmynotes">
<input type="hidden" name="action" value="savenotes" />
<input type="hidden" name="token" value="{$csrfToken}" />
<div align="right"><a href="#" onclick="notesclose('');return false"><img src="images/delete.gif" width="16" height="16" align="absmiddle" border="0" /> {$_ADMINLANG.clientsummary.close}</a></div>
<textarea id="mynotesbox" name="notes" rows="15">{$admin_notes}</textarea><br /><input type="button" value="{$_ADMINLANG.global.savechanges}" onclick="notesclose('1');return false" />
</form>
</div>
</div>

{$footeroutput}

</body>
</html>