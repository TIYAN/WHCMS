<div class="errorbox">{$LANG.subaccountpermissiondenied}</div>

<p>{$LANG.subaccountallowedperms}</p>

<ul>
{foreach from=$allowedpermissions item=permission}
<li>{$permission}</li>
{/foreach}
</ul>

<p>{$LANG.subaccountcontactmaster}</p>