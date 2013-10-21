<div class="stepsbar">

    <div class="step {if $step eq 1}active{/if}"><strong>{$LANG.step|sprintf2:'1'}</strong><br /><a href="cart.php">{$LANG.cartchooseproduct}</a></div>

    <div class="step {if $step eq 2}active{/if}"><strong>{$LANG.step|sprintf2:'2'}</strong><br />{$LANG.orderdomainoptions}</div>

    <div class="step {if $step eq 3}active{/if}"><strong>{$LANG.step|sprintf2:'3'}</strong><br />{$LANG.orderconfigure}</div>

    <div class="step {if $step eq 4}active{/if}"><strong>{$LANG.step|sprintf2:'4'}</strong><br /><a href="cart.php?a=view">{$LANG.orderconfirmorder}</a></div>

    <div class="step {if $step eq 5}active{/if}"><strong>{$LANG.step|sprintf2:'5'}</strong><br /><a href="cart.php?a=checkout">{$LANG.ordercheckout}</a></div>

</div>