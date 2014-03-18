// created by Tri Do
// description: all action for real-time monitoring
// 1. login - 2. logout - 3. hangup 4. transfer 5. spycall - 6: spycall-whisper - 7: add_note 
var script_path = 'modules/realtime_monitor/action.php';
var addnote_agent = '';
var addnote_supervisor = '';

$(document).ready(function(){
    window.setInterval(function(){
        setTimeout(do_refresh_call_history,1);
    }, 10000);

    $('#elastix-callcenter-error-message').hide();
    $('#elastix-callcenter-info-message').hide();

    /* ALL DIALOGS INITIALIZE*/
    $( "#dialog-view" ).dialog({
        autoOpen: false,
        modal: true,
        dialogClass: "save-dialog",
        position: ['top', 100],
        width: 480
    });
    $( "#dialog-delivery" ).dialog({
        autoOpen: false,
        modal: true,
        dialogClass: "delivery-dialog",
        position: ['top', 100],
        width: 580
    });
    /* END OF ALL DIALOGS */
    // construct isotope layout
    var container = $(".list-item");
    container.isotope({
        layoutMode: 'cellsByRow',
        cellsByRow: {
            columnWidth: 271,
            rowHeight: 192
        },
        getSortData : {
            totalcall : function(elem) {
                return parseInt(elem.find(".total-call").text());
            },
            totaltime : function(elem) {
                var timer = elem.find("p.timer").text();
                var parts = timer.split(":");
                var time = 0;
                $.each(parts, function(key,val){
                    time = time * 60 + parseInt(val);
                });
                return time;
            }
        },
        filter: '.item'
    });
    // construct dialog
    $( "#note-dialog" ).dialog({
        autoOpen: false,
        modal: true,
        dialogClass: "note-dialog",
        width: 480
    });

    // construct jquery-ui tabs
    //$("#queue").tabs({ active: 0 });
    // show/hide filter/sort list
    $("#selector li").click(function() {
        setClass(this,"#"+$(this).parent().attr("id"));
        var id = "#" + $(this).attr('data-toggle');
        $("ul.sort, ul.filter").not(id).hide();
        $(id).show();
    });
    // sort items by total-call/total-time
    $(".sort li").click(function(){
        var sort = $(this).parent().attr("data-sort");
        var direction = true;	//default sort ascending
        if($(this).hasClass("desc"))
            direction = false;
        container.isotope({
            sortBy : sort,
            sortAscending : direction
        });

        setClass(this,"."+$(this).parent().attr("class"));
        return false;
    });
    // filter user status (online/offline/away/oncall/free/all)
    $(".filter li").click(function(){
        if($(this).hasClass("active"))
            return false
        else {
            if($(this).attr('data-filter') == "") {
                container.isotope({
                    filter: '.item'
                });
                $(this).parent().find(".active").removeClass("active");
            }
            else {
                container.isotope({
                    filter: '[data-status="'+$(this).attr('data-filter')+'"]'
                });
                setClass(this,"."+$(this).parent().attr("class"));
            }
            return false;
        }
    });

    /* Load more items */
    $(document).on("click", '#loadmore', function(){
        var url = $(this).attr("href");
        /*
         $.get(url,function(data){
         options = new Object();
         var newEls = $(data).find('.item');
         container.append(newEls);
         .isotope( 'insert', newEls, function(){
         if($(".filter li").hasClass("active"))
         options.filter = '[data-status="' + $(".filter li.active").attr('data-filter')+'"]';
         if($(".sort li").hasClass("active")) {
         options.sortBy = $(this).parent().attr("data-sort");
         options.sortAscending = $(".sort li.active").hasClass("desc") ? false : true;
         }
         })
         .isotope(options);   //append to isotope (with filter and sort)
         });
         */
        var html = $("#hidden-items").clone();
        var newEls = html.find(".item");
        options = new Object();
        container.append(newEls)
            .isotope( 'insert', newEls, function(){
                if($(".filter li").hasClass("active"))
                    options.filter = '[data-status="' + $(".filter li.active").attr('data-filter')+'"]';
                if($(".sort li").hasClass("active")) {
                    options.sortBy = $(this).parent().attr("data-sort");
                    options.sortAscending = $(".sort li.active").hasClass("desc") ? false : true;
                }
            })
            .isotope(options);   //append to isotope (with filter and sort)
        $("#remove").show();
		$(this).hide();
        return false;
    });
    $(document).on("click", "#remove", function(){
        location.reload();
        return false;
    });
});

$(document).on("click", "a.note", function(){
    $("#note-content").val('');
    $("#note-dialog").dialog("open");
});
$(document).on("click", "#btnClose", function(){
    $("#note-dialog").dialog("close");
});
$(document).on("click", "#btnClose-dialog-view", function(){
    $("#dialog-view").dialog("close");
});
$(document).on("click", "#btnClose-dialog-delivery", function(){
    $("#dialog-delivery").dialog("close");
});
$(document).on("click", "#btnSubmit", function(){
    do_addnote(addnote_agent,addnote_supervisor,$("#note-content").val());
    //close the diaglog
    $("#note-dialog").dialog("close");
});



function mostrar_mensaje_info(s)
{
	$('#elastix-callcenter-info-message-text').text(s);
	$('#elastix-callcenter-info-message').show('slow', 'linear', function() {
		setTimeout(function() {
			$('#elastix-callcenter-info-message').fadeOut();
		}, 5000);
	});
}

function mostrar_mensaje_error(s)
{
	$('#elastix-callcenter-error-message-text').text(s);
	$('#elastix-callcenter-error-message').show('slow', 'linear', function() {
		setTimeout(function() {
			$('#elastix-callcenter-error-message').fadeOut();
		}, 5000);
	});
}

function show_error(message)
{
	alert(message);
}

function do_login(agent_number)
{		
	$.post(script_path, {		
		type:		'login',
		agent:	agent_number
	},
	function (response) {
        if (response['action'] == 'error') {
        	mostrar_mensaje_error(response['message']);
        }    
		else {
        	mostrar_mensaje_info(response['message']);
        }		
	});
}

function do_logout(agent_number)
{	
   $.post(script_path, {		
		type:		'logout',
		agent:	agent_number
	},
	function (response) {
        if (response['action'] == 'error') {
        	mostrar_mensaje_error(response['message']);
        }    
		else {
        	mostrar_mensaje_info(response['message']);
        }		
	});
}

function do_hangup(agent_number)
{
	$.post(script_path, {		
		type:		'hangup',
		agent:	agent_number
	},
	function (response) {
        if (response['action'] == 'error') {
        	mostrar_mensaje_error(response['message']);
        }    
		else {
        	mostrar_mensaje_info(response['message']);
        }		
	});
}

function do_transfer(agent_number,dest)
{
	if ($('#txt_supervisor_ext').val()=='') {
		alert('Nhập số extension lấy cuộc gọi');
		$('#txt_supervisor_ext').focus();
		return;
	}
	$.post(script_path, {		
		type:		'transfer',
		agent:		agent_number,
		extension:	$('#txt_supervisor_ext').val()		
	},
	function (response) {
        if (response['action'] == 'error') {
        	mostrar_mensaje_error(response['message']);
        }    
		else {
        	mostrar_mensaje_info(response['message']);
        }		
	});
}

function do_spycall(agent_number,supervisor,whisper)
{
	if ($('#txt_supervisor_ext').val()=='') {
		alert('Nhập số extension để nghe xen');
		$('#txt_supervisor_ext').focus();
		return;
	}
	$.post(script_path, {		
		type:			'spycall',
		agent:			agent_number,		
		supervisor:		$('#txt_supervisor_ext').val(),
		whisper:		whisper		
	},
	function (response) {
        if (response['action'] == 'error') {
        	mostrar_mensaje_error(response['message']);
        }    
		else {
        	mostrar_mensaje_info(response['message']);
        }		
	});
}

function get_addnote(agent,supervisor)
{
	addnote_agent = agent;
	addnote_supervisor = supervisor;
}

function do_addnote(agent_number,ext,note)
{
	$.post(script_path, {		
		type:		'addnote',
		agent:	agent_number,
		extension:	ext,
		note:		note		
	},
	function (response) {
        if (response['action'] == 'error') {
        	mostrar_mensaje_error(response['message']);
        }    
		else {
        	mostrar_mensaje_info(response['message']);
        }		
	});
}


function setClass(obj,parent) {
	if(!$(obj).hasClass("active")) {
		$(parent).find(".active").removeClass("active");
		$(obj).addClass("active");
	}
}

function do_refresh_call_history()
{
    $.post('index.php?menu=' + module_name + '&rawmode=yes', {
            menu:		module_name,
            rawmode:	'yes',
            action:	'show_call_history'
        },
        function (response) {
            if (response['action'] == 'error') {
                mostrar_mensaje_error(response['message']);
            }
            else {
                refresh_call_history(response['message']);
            }
        });
}

function refresh_call_history(data)
{
    $('#ticket-list').empty();
    content = '<tr><th>Khách hàng</th><th>Ngày gọi</th><th>NV nghe máy</th><th>Tình trạng</th><th>Nội dung</th><th>Giao vé</th></tr>';
    for (var i = 0; i < data.length; i++) {
        content += '<tr>';
        content += "<td>" + data[i].phone + "</td>";
        content += "<td>" + data[i].calldate + "</td>";
        content += "<td>" + data[i].agent + "</td>";
        content += "<td>" + data[i].status + "</td>";
        //console.log(data[i].note);
        if (data[i].note != null)
            content += "<td><a href=\"javascript:void(0)\" onclick=\"view_note('" + data[i].id + "')\">Xem</a></td>";
        else
            content += "<td>  </td>";
        if (data[i].delivery != null)
            content += "<td><a href=\"javascript:void(0)\" onclick=\"view_delivery('" + data[i].delivery_id + "')\">Xem</a></td>";
        else
            content += "<td>  </td>";
        content += '</tr>';
    }
    $('#ticket-list').append(content);
}

function view_delivery(callid)
{
    $.post('index.php?menu=realtime_monitor&rawmode=yes', {
            menu:		'realtime_monitor',
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

function view_note(callid)
{
    $.post('index.php?menu=realtime_monitor&rawmode=yes', {
            menu:		'realtime_monitor',
            action:		'viewNote',
            rawmode:	'yes',
            view_note_id: callid
        },
        function (response) {
            $("#dialog-view-content").val(response);
            $("#dialog-view").dialog( "open");
        });
}
