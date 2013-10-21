<p>{$LANG.supportticketsheader}</p>
{if $departments}
<ul>
  {foreach key=num item=department from=$departments}
  <li><a href="{$smarty.server.PHP_SELF}?step=2&amp;deptid={$department.id}"><strong>{$department.name}</strong></a>{if $department.description} - {$department.description}{/if}</li>
  {/foreach}
</ul>
{else}
<br />
<div class="errorbox">{$LANG.nosupportdepartments}</div>
{/if}
<br /><br /><br /><br /><br />