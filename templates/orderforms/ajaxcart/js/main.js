$(document).ready(function(){

    recalcsummary();

    jQuery("#cartsummary").makeFloat({x:"current",y:"current"});

});

function selectproduct(pid) {
    jQuery("#checkoutbtn").hide();
    jQuery("#loading1").slideDown();
    jQuery("#configcontainer1").fadeOut();
    jQuery("#configcontainer2").fadeOut();
    jQuery("#configcontainer3").fadeOut();
    jQuery("#signupcontainer").fadeOut();
    jQuery.post("cart.php", 'ajax=1&a=add&pid='+pid,
    function(data){
        if (data=='') {
            signupstep();
        } else {
            jQuery("#configcontainer1").html(data);
            jQuery("#configcontainer1").slideDown();
        }
        jQuery("#loading1").slideUp();
        recalcsummary();
    });
}

function recalcsummary() {
    jQuery("#cartloader").show();
    jQuery.post("cart.php", 'a=view&cartsummary=1&ajax=1',
    function(data){
        jQuery("#cartsummary").html(data);
        jQuery("#cartloader").hide();
    });
}

function prodconfrecalcsummary() {
    jQuery("#cartloader").show();
    jQuery.post("cart.php", 'ajax=1&a=confproduct&calctotal=true&'+jQuery("#orderfrm").serialize(),
    function(data){
        jQuery.post("cart.php", 'a=view&cartsummary=1&ajax=1',
        function(data){
            jQuery("#cartsummary").html(data);
            jQuery("#cartloader").hide();
        });
    });
}

function prodconfcomplete() {
    jQuery("#prodconfloading").slideDown();
    jQuery.post("cart.php", 'a=confproduct&ajax=1&'+jQuery("#orderfrm").serialize(),
    function(data){
        if (data) {
            jQuery("#configproducterror").html(data);
            jQuery("#configproducterror").slideDown();
            jQuery("#prodconfloading").slideUp();
        } else {
            jQuery.post("cart.php", 'a=confdomains&ajax=1',
            function(data){
                if (data) {
                    jQuery("#configcontainer3").html(data);
                    jQuery("#configcontainer3").slideDown();
                    jQuery("#configproducterror").slideUp();
                    jQuery("#prodconfloading").slideUp();
                    jQuery('html, body').animate({scrollTop: jQuery("#configproducterror").offset().top-5}, 1000);
                } else {
                    jQuery("#configproducterror").slideUp();
                    signupstep();
                }
            });
        }
    });

}

function checkdomain() {
    var domainoption = jQuery(".domainoptions input:checked").val();
    var sld = jQuery("#"+domainoption+"sld").val();
    var tld = '';
    if (domainoption=='incart') var sld = jQuery("#"+domainoption+"sld option:selected").text();
    if (domainoption=='subdomain') var tld = jQuery("#"+domainoption+"tld option:selected").text();
    else var tld = jQuery("#"+domainoption+"tld").val();
    jQuery("#loading3").slideDown();
    jQuery.post("cart.php", { a: "domainoptions", ajax: 1, sld: sld, tld: tld, checktype: domainoption },
    function(data){
        jQuery("#domainresults").html(data);
        jQuery("#domainresults").slideDown();
        jQuery("#loading3").slideUp();
    });
}

function domainconfigupdate() {
    jQuery.post("cart.php", 'a=confdomains&update=1&ajax=1&'+jQuery("#domainconfigfrm").serialize(),
    function(data){
        recalcsummary();
    });
}

function showcustomns() {
    jQuery(".hiddenns").fadeToggle();
}

function completedomainconfig() {
    jQuery("#domainconfloading").slideUp();
    jQuery.post("cart.php", 'a=confdomains&update=1&ajax=1&'+jQuery("#domainconfigfrm").serialize(),
    function(data){
        if (data) {
            jQuery("#configdomainerror").html(data);
            jQuery("#configdomainerror").slideDown();
            jQuery("#domainconfloading").slideUp();
            jQuery('html, body').animate({scrollTop: jQuery("#configdomainerror").offset().top-5}, 1000);
        } else {
            jQuery("#configdomainerror").slideUp();
            signupstep();
        }
    });
}

function signupstep() {
    jQuery.post("cart.php", 'a=checkout&ajax=1',
    function(data){
        jQuery("#signupcontainer").html(data);
        jQuery("#signupcontainer").slideDown();
        jQuery("#prodconfloading").slideUp();
        jQuery("#domainconfloading").slideUp();
    });
}

function checkout() {
    jQuery.post("cart.php", 'a=checkout&ajax=1',
    function(data){
        jQuery("#signupcontainer").html(data);
        jQuery("#signupcontainer").slideDown();
    });
}

function showsignupfields() {
    jQuery(".signupfields").show();
    jQuery(".loginfields").hide();
};

function showloginfields() {
    jQuery(".signupfields").hide();
    jQuery(".loginfields").show();
};

function completeorder() {
    jQuery("#checkoutloader").slideDown();
    jQuery.post("cart.php", 'a=checkout&$checkout=1&ajax=1&'+jQuery("#checkoutfrm").serialize(),
    function(data){
        if (data) {
            jQuery("#checkouterror").html(data);
            jQuery("#checkouterror").slideDown();
            jQuery("#checkoutloader").slideUp();
            jQuery('html, body').animate({scrollTop: jQuery("#checkouterror").offset().top-5}, 1000);
        } else {
            window.location = 'cart.php?a=fraudcheck';
        }
    });
}

function domaincontactchange() {
    if (jQuery("#domaincontact").val()=="addingnew") {
        jQuery("#domaincontactfields").slideDown();
    } else {
        jQuery("#domaincontactfields").slideUp();
    }
}

function currencychange() {
    jQuery("#cartloader").show();
    jQuery.post("cart.php", 'a=view&cartsummary=1&ajax=1&currency='+jQuery("#currency").val(),
    function(data){
        jQuery("#cartsummary").html(data);
        jQuery("#cartloader").hide();
    });
}

function applypromo() {
    jQuery.post("cart.php", { a: "applypromo", promocode: jQuery("#promocode").val() },
    function(data){
        if (data) alert(data);
        else recalcsummary();
    });
}

function removepromo() {
    jQuery.post("cart.php", { a: "removepromo", ajax: 1 },
    function(data){
        recalcsummary();
    });
}

function addonaddtocart(addonid,i) {
    jQuery.post("cart.php", { a: "add", ajax: 1, aid: addonid, productid: jQuery("#addonpid"+i).val() },
    function(data){
        recalcsummary();
    });
}

function renewaladdtocart(domainid,i) {
    jQuery.post("cart.php", { a: "add", renewals: 1, ajax: 1, renewalid: domainid, renewalperiod: jQuery("#renewalperiod"+i).val() },
    function(data){
        recalcsummary();
    });
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