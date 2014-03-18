var module_name = 'recording';
function view_note(callid)
{
    $.post('index.php?menu=' + module_name + '&rawmode=yes', {
            menu:		module_name,
            rawmode:	'yes',
            action:		'viewNote',
            view_note_id: callid
        },
        function (response) {
            $("#dialog-view-content").val(response);
            $("#dialog-view").dialog( "open");
        });
}

function view_delivery(callid)
{
    $.post('index.php?menu=' + module_name + '&rawmode=yes', {
            menu:		module_name,
            rawmode:	'yes',
            action:		'viewDelivery',
            view_delivery_id: callid
        },
        function (response) {
            //console.log(response['message']);
            $("#view-delivery-customer_name").val(response['message']['customer_name']);
            $("#view-delivery-customer_phone").val(response['message']['customer_phone']);
            $("#view-delivery-purchase_date").val(response['message']['purchase_date']);
            $("#view-delivery-agent_name").val(response['message']['agent']);
            $("#view-delivery-price").val(response['message']['price']);
            $("#view-delivery-deliver_address").val(response['message']['deliver_address']);
            $("#view-delivery-tax").val(response['message']['tax']);
            $("#view-delivery-rate").val(response['message']['currency_rate']);
            $("#view-delivery-discount").val(response['message']['discount']);
            $("#view-delivery-pay_amount").val(response['message']['pay_amount']);
            $("#view-delivery-ticket_code").empty();
            for (var i=0;i<response['message']['ticket_code'].length;i++)
            {
                $("#view-delivery-ticket_code").append(response['message']['ticket_code'][i] + "\n");
            }
            $("#view-delivery-delivery_name").val(response['message']['delivery_name']);
            $("#view-delivery-delivery_date").val(response['message']['delivery_date']);
            $("#view-delivery-office").val(response['message']['office']);
            $("#view-delivery-status").val(response['message']['status']);
            $("#view-delivery-collection_date").val(response['message']['collection_date']);
            $("#dialog-delivery").dialog( "open");
        });
}

$(document).ready(function(){
    if($("#filter_field").val() == "userfield"){
	document.getElementsByName("filter_value")[0].style.display="none";
	document.getElementById("filter_value_userfield").style.display="";
    }
    $("#filter_field").change(function(){
	if($(this).val() == "userfield"){
	    document.getElementsByName("filter_value")[0].style.display="none";
	    document.getElementById("filter_value_userfield").style.display="";
	}
	else{
	    document.getElementsByName("filter_value")[0].style.display="";
	    document.getElementById("filter_value_userfield").style.display="none";
	}
    });

    /* CUSTOMIZE */
    /* ALL DIALOGS INITIALIZE*/
    $( "#dialog-view" ).dialog({
        autoOpen: false,
        modal: true,
        dialogClass: "save-dialog",
        width: 480
    });
    $( "#dialog-delivery" ).dialog({
        autoOpen: false,
        modal: true,
        dialogClass: "delivery-dialog",
        width: 580
    });
    $("#btnClose-dialog-view").click(function(){
        $("#dialog-view").dialog("close");
    });
    $("#btnClose-dialog-delivery").click(function(){
        $("#dialog-delivery").dialog("close");
    });
    /* END OF ALL DIALOGS */
});

