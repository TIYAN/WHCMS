jQuery(document).ready(function(){
    jQuery("input.cartbutton:button,input.cartbutton:submit").button();
    jQuery("input:radio,input:checkbox").css('border','0');
});

function recalctotals() {
    jQuery.post("cart.php", 'ajax=1&a=confproduct&calctotal=true&'+jQuery("#orderfrm").serialize(),
    function(data){
        jQuery("#producttotal").html(data);
    });
}

function showcustomns() {
    jQuery(".hiddenns").fadeToggle();
}
function domaincontactchange() {
    if (jQuery("#domaincontact").val()=="addingnew") {
        jQuery("#domaincontactfields").slideDown();
    } else {
        jQuery("#domaincontactfields").slideUp();
    }
}
function showCCForm() {
    jQuery("#ccinputform").slideDown();
}
function hideCCForm() {
    jQuery("#ccinputform").slideUp();
}
function useExistingCC() {
    jQuery(".newccinfo").hide();
}
function enterNewCC() {
    jQuery(".newccinfo").show();
}

function applypromo() {
    jQuery.post("cart.php", { a: "applypromo", promocode: jQuery("#promocode").val() },
    function(data){
        if (data) alert(data);
        else window.location='cart.php?a=checkout';
    });
}

function domaincontactchange() {
    if (jQuery("#domaincontact").val()=="addingnew") {
        jQuery("#domaincontactfields").slideDown();
    } else {
        jQuery("#domaincontactfields").slideUp();
    }
}

function showloginform() {
    jQuery("#loginfrm").slideToggle();
    jQuery("#signupfrm").slideToggle();
    if (jQuery("#custtype").val()=="new") {
        jQuery("#custtype").val("existing");
    } else {
        jQuery("#custtype").val("new");
    }
}