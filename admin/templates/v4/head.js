$(document).ready(function(){
     $("#shownotes").click(function () {
        $("#mynotes").toggle("slow");
        return false;
    });
    $("#savenotes").click(function () {
        $("#mynotes").toggle("slow");
        $.post("index.php", $("#frmmynotes").serialize() );
    });
    $("#frmintellisearch").submit(function(e) {
        e.preventDefault();
        $.post("search.php", $("#frmintellisearch").serialize(),
        function(data){
            $("#searchresults").html(data);
            $("#searchresults").slideDown("slow");
        });
    });
    $("#intellisearchval").keyup(function () {
        var intellisearchlength = $("#intellisearchval").val().length;
        if (intellisearchlength>2) {
        $.post("search.php", $("#frmintellisearch").serialize(),
          function(data){
            $("#searchresults").html(data);
            $("#searchresults").slideDown("slow");
          });
        }
    });
    $("#intellisearchcancel").click(function () {
        $("#intellisearchval").val("");
        $("#searchresults").slideUp("slow");
    });
    $(".datepick").datepicker({
        dateFormat: datepickerformat,
        showOn: "button",
        buttonImage: "images/showcalendar.gif",
        buttonImageOnly: true,
        showButtonPanel: true
    });
});