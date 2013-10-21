$(document).ready(function(){

    $("#replymessage").focus(function () {
    	$.post("supporttickets.php", { action: "makingreply", id: ticketid },
    	function(data){
            $("#replyingadmin").html(data);
        });
        return false;
    });

    $("#replyfrm").submit(function () {
    	var status = $("#ticketstatus").val();
    	var response = $.ajax({
    		type: "POST",
    		url: "supporttickets.php?action=checkstatus",
    		data: "id="+ticketid+"&ticketstatus="+status,
    		async: false
    	}).responseText;
    	if (response == "true") {
        	return true;
    	} else {
    		if (confirm(langstatuschanged+"\n\n"+langstillsubmit)) {
    	        return true;
    	    }
    	    return false;
    	}
    });

    var currentTags = '';
    if ($('#ticketTags').length) {
    $('#ticketTags').textext({
        plugins : 'tags prompt focus autocomplete ajax',
        prompt : 'Add one...',
        tagsItems: ticketTags,
        ajax : {
            url : 'supporttickets.php?action=gettags',
            dataType : 'json',
            cacheResults : true
        }
    }).bind('setFormData', function(e, data, isEmpty) {
			var newTags = $(e.target).textext()[0].hiddenInput().val();
            if (newTags!=currentTags) {
                $.post("supporttickets.php", { action: "savetags", id: ticketid, tags: newTags });
                currentTags = newTags;
            }
		});
    }

    $(window).unload( function () {
        $.post("supporttickets.php", { action: "endreply", id: ticketid });
    });
    $("#insertpredef").click(function () {
        $("#prerepliescontainer").fadeToggle();
        return false;
    });
    $("#addfileupload").click(function () {
        $("#fileuploads").append("<input type=\"file\" name=\"attachments[]\" size=\"85\"><br />");
        return false;
    });
    $("#ticketstatus").change(function () {
        $.post("supporttickets.php", { action: "changestatus", id: ticketid, status: this.options[this.selectedIndex].text });
    });
    $("#predefq").keyup(function () {
        var intellisearchlength = $("#predefq").val().length;
        if (intellisearchlength>2) {
        $.post("supporttickets.php", { action: "loadpredefinedreplies", predefq: $("#predefq").val() },
            function(data){
                $("#prerepliescontent").html(data);
            });
        }
    });

    $("#clientsearchval").keyup(function () {
    	var ticketuseridsearchlength = $("#clientsearchval").val().length;
    	if (ticketuseridsearchlength>2) {
    	$.post("search.php", { ticketclientsearch: 1, value: $("#clientsearchval").val() },
    	    function(data){
                if (data) {
                    $("#ticketclientsearchresults").html(data);
                    $("#ticketclientsearchresults").slideDown("slow");
                    $("#clientsearchcancel").fadeIn();
                }
            });
    	}
    });
    $("#clientsearchcancel").click(function () {
        $("#ticketclientsearchresults").slideUp("slow");
        $("#clientsearchcancel").fadeOut();
    });

});

function doDeleteReply(id) {
    if (confirm(langdelreplysure)) {
        window.location='supporttickets.php?action=viewticket&id='+ticketid+'&sub=del&idsd='+id;
    }
}
function doDeleteTicket() {
    if (confirm(langdelticketsure)) {
        window.location='supporttickets.php?sub=deleteticket&id='+ticketid;
    }
}
function doDeleteNote(id) {
    if (confirm(langdelnotesure)) {
        window.location='supporttickets.php?action=viewticket&id='+ticketid+'&sub=delnote&idsd='+id;
    }
}
function loadTab(target,type,offset) {
    $.post("supporttickets.php", { action: "get"+type, id: ticketid, userid: userid, target: target, offset: offset },
    function(data){
        $("#tab"+target+"box #tab_content").html(data);
    });
}
function expandRelServices() {
    $("#relatedservicesexpand").html('<img src="images/loading.gif" align="top" /> '+langloading);
    $.post("supporttickets.php", { action: "getallservices", id: ticketid, userid: userid },
    function(data){
        $("#relatedservicestbl").append(data);
        $("#relatedservicesexpand").fadeOut();
    });
}
function updateTicket(val) {
    $.post("supporttickets.php", { action: "viewticket", id: ticketid, updateticket: val, value: $("#"+val).val() });
}
function editTicket(id) {
    $(".editbtns"+id).toggle();
    $("#content"+id+" div.message").hide();
    $("#content"+id+" div.message").after('<textarea rows="15" style="width:99%" id="ticketedit'+id+'">'+langloading+'</textarea>');
    $.post("supporttickets.php", { action: "getmsg", ref: id },
        function(data){
            $("#ticketedit"+id).val(data);
        });
}
function editTicketCancel(id) {
    $("#ticketedit"+id).hide();
    $("#content"+id+" div.message").show();
    $(".editbtns"+id).toggle();
}
function editTicketSave(id) {
    $("#ticketedit"+id).hide();
    $("#content"+id+" div.message").show();
    $(".editbtns"+id).toggle();
    $.post("supporttickets.php", { action: "updatereply", ref: id, text: $("#ticketedit"+id).val() },
        function(data){
            $("#content"+id+" div.message").html(data);
        });
}
function quoteTicket(id,ids) {
    $(".tab").removeClass("tabselected");
    $("#tab0").addClass("tabselected");
    $(".tabbox").hide();
    $("#tab0box").show();
    $.post("supporttickets.php", { action: "getquotedtext", id: id, ids: ids },
    function(data){
        $("#replymessage").val(data+"\n\n"+$("#replymessage").val());
    });
    return false;
}
function selectpredefcat(catid) {
    $.post("supporttickets.php", { action: "loadpredefinedreplies", cat: catid },
    function(data){
        $("#prerepliescontent").html(data);
    });
}
function selectpredefreply(artid) {
    $.post("supporttickets.php", { action: "getpredefinedreply", id: artid },
    function(data){
        $("#replymessage").addToReply(data);
    });
    $("#prerepliescontainer").fadeOut();
}
function searchselectclient(userid) {
    $("#clientsearchval").val(userid);
	$("#ticketclientsearchresults").slideUp("slow");
    $("#clientsearchcancel").fadeOut();
}