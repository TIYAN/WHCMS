function intellisearch() {
    $("#intellisearchval").css("background-image","url('images/loading.gif')");
    $.post("search.php", { intellisearch: "true", value: $("#intellisearchval").val() },
    function(data){
        $("#searchresultsscroller").html(data);
        $("#searchresults").slideDown("slow",function(){
                $("#intellisearchval").css("background-image","url('images/icons/search.png')");
            });
    });
}
function searchclose() {
    $("#searchresults").slideUp();
}
function sidebarOpen() {
    $("#sidebaropen").fadeOut();
    $("#contentarea").animate({"margin-left":"209px"},1000,function() {
        $("#sidebar").fadeIn("slow");
    });
    $.post("search.php","a=maxsidebar");
}
function sidebarClose() {
    $("#sidebar").fadeOut("slow",function(){
        $("#contentarea").animate({"margin-left":"10px"});
        $("#sidebaropen").fadeIn();
    });
    $.post("search.php","a=minsidebar");
}
function notesclose(save) {
    $("#popupcontainer").toggle("slow",function () {
        $("#mynotes").hide();
    });
    if (save) $.post("index.php", { action: "savenotes", notes: $("#mynotesbox").val() });
    $("#greyout").fadeOut();
}
$(document).ready(function(){
    $("#shownotes").click(function () {
        $("#mynotes").show();
        $("#greyout").fadeIn();
        $("#popupcontainer").slideDown();
        return false;
    });
    $(".datepick").datepicker({
        dateFormat: datepickerformat,
        showOn: "button",
        buttonImage: "images/showcalendar.gif",
        buttonImageOnly: true,
        showButtonPanel: true,
        showOtherMonths: true,
		selectOtherMonths: true
    });
});