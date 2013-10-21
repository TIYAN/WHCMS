<div class="stepscontainer">
<div class="step{if $step eq 1}active{/if}">
<span class="title">{$LANG.step|sprintf2:'1'}</span>
{$LANG.cartproductdomainchoose}
</div>
<div class="arrow{if $step eq 1}activeright{elseif $step eq 2}activeleft{/if}"></div>
<div class="step{if $step eq 2}active{/if}">
<span class="title">{$LANG.step|sprintf2:'2'}</span>
{$LANG.cartproductchooseoptions}
</div>
<div class="arrow{if $step eq 2}activeright{elseif $step eq 3}activeleft{/if}"></div>
<div class="step{if $step eq 3}active{/if}">
<span class="title">{$LANG.step|sprintf2:'3'}</span>
{$LANG.cartreviewcheckout}
</div>
<div class="clear"></div>
</div>