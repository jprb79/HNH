$(document).ready(function(){
    /*
    window.setInterval(function(){
        do_refresh_call_history();
    }, 30000); */
    update_message();
    /*
    window.setInterval(function(){
        update_message();
        }, 900000); */
    $('#btn-call-number').live('click',(function(){
        btn_call_click();
	}));
    $('#btn-hangup-call').live('click',(function(){
        $(this).hide();
        $('#btn-call-number').show();
        show_outgoing_info(null);
    }));
    $('#btn-clear').live('click',(function(){
        location.reload();
    }));

	$('#btnSave').live('click',(function(){
		$.post('index.php?menu=' + module_name + '&rawmode=yes', {
			action:		'addCustomer',
			phone:		$('#customer_phone').val(),
			firstname:	$('#customer_firstname').val(),
			lastname:	$('#customer_lastname').val(),
			birthday:	$('#customer_birthday').val(),
			birthplace:	$('#customer_birthplace').val(),
			address:	$('#customer_address').val(),
			cmnd:		$('#customer_cmnd').val(),
			passport:	$('#customer_passport').val(),
            id:         $('#customer_id').val(),
            email:      $('#customer_email').val(),
            membership: $('#customer_membership').val()
		},
		function (response) {
			//console.log(response);
			if (response['action'] == 'error') {
				mostrar_mensaje_error(response['message']);
			}    
			else {
				mostrar_mensaje_info(response['message']);
                //update customer info
                var customer_name;
                if ($('#customer_lastname').val().trim()=='')
                    customer_name = $('#customer_firstname').val();
                else
                    customer_name = $('#customer_lastname').val() + ' ' + $('#customer_firstname').val();
                $('#customer_name').val(customer_name);
                // update customer box
                update_customer($('#customer_phone').val());
			}
            $("#dialog").dialog( "close" );
		});
	}));
});

function btn_call_click()
{
    var number = $('#call-number').val();
    make_call(number);
}

function update_customer(number)
{
    $.post('index.php?menu=' + module_name + '&rawmode=yes', {
            menu:		module_name,
            rawmode:	'yes',
            action:	    'update_customer',
            number:	    number
        },
        function (content) {
            $("#customer_table").empty();
            $("#customer_table").append(content);
        });
}

function validate(input,alert_str)
{
	if(input.val() == "" )	{
		alert(alert_str);
		input.focus() ;
		return false;
	}
	return true;
}

function validateDeliveryForm()
{ 

	if ($('#deliver-name').val().trim() == "" )	{
		alert( "Thiếu tên khách hàng!" );
		$('#deliver-name').focus() ;
		return false;
	}
	if ($('#deliver-code').val().trim() == "" )	{
		alert( "Thiếu thông tin vé" );
		$('#deliver-code').focus() ;
		return false;
	}
    if ($('#deliver-address').val().trim() == "" )	{
        alert( "Thiếu thông tin địa chỉ" );
        $('#deliver-address').focus() ;
        return false;
    }
	return true;
}

function num(str)
{
    parseFloat(str.replace(/,/g, ''));
}

function format(input)
{
    var nStr = input.value + '';
    nStr = nStr.replace( /\,/g, "");
    var x = nStr.split( '.' );
    var x1 = x[0];
    var x2 = x.length > 1 ? '.' + x[1] : '';
    var rgx = /(\d+)(\d{3})/;
    while ( rgx.test(x1) ) {
        x1 = x1.replace( rgx, '$1' + ',' + '$2' );
    }
    input.value = x1 + x2;
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
    $('#call_history').empty();
    content = '<tr><th>Khách hàng</th><th>Giờ gọi</th><th>NV nghe máy</th><th>Tình trạng</th><th>Nội dung</th><th>Giao vé</th></tr>';
    for (var i = 0; i < data.length; i++) {
        content += '<tr>';
        content += "<td>" + data[i].phone + "</td>";
        content += "<td>" + data[i].calldate + "</td>";
        content += "<td>" + data[i].agent + "</td>";
        content += "<td>" + data[i].status + "</td>";
        //console.log(data[i].note);
        if (data[i].note != null)
            content += "<td><a href=\"javascript:void(0)\" onclick=\"view_note('" + data[i].id + "')\">Xem</a></td>";
        else if (data[i].permit_add == 1)
            content += "<td><a href=\"javascript:void(0)\" onclick=\"add_note_history('" + data[i].id + "')\">Thêm</a></td>";
        else
            content += "<td>  </td>";
        if (data[i].delivery != null)
            content += "<td><a href=\"javascript:void(0)\" onclick=\"view_delivery('" + data[i].delivery_id + "')\">Xem</a></td>";
        else
            content += "<td>  </td>";
        content += '</tr>';
    }
    $('#call_history').append(content);
}

function done_add_delivery()
{
    $('#deliver-id').val(''),
    $('#deliver-name').val(''),
    $('#deliver-phone').val(''),
    $('#deliver-price').val(''),
    // depreciate this field
	//$('#deliver-discount').val(''),
	$('#isInvoice').removeAttr("check")
    $('#deliver-rate').val('1'),
    $('#deliver-pay').val(''),
    $('#deliver-code').val(''),
    $('#deliver-address').val(''),
	$('#deliver-note').val(''),
    $('#deliver-tax').val('0'),
    $('#filelist').empty();
    fileArray = [];
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
            //tri
            //console.log(response['message']);
            $("#view-delivery-customer_name").val(response['message']['customer_name']);
            $("#view-delivery-customer_phone").val(response['message']['customer_phone']);
            $("#view-delivery-purchase_date").val(response['message']['purchase_date']);
            $("#view-delivery-agent_name").val(response['message']['agent']);
            $("#view-delivery-price").val(response['message']['price']);
            $("#view-delivery-deliver_address").val(response['message']['deliver_address']);
            $("#view-delivery-tax").val(response['message']['tax']);
            $("#view-delivery-rate").val(response['message']['currency_rate']);
            // xuat hoa don
            if (response['message']['isInvoice']=='1')
                $("#view-delivery-invoice").html('<b>(Có xuất hóa đơn)</b>');
            else
                $("#view-delivery-invoice").html('');
            //$("#view-delivery-discount").val(response['message']['discount']);
            $("#view-delivery-pay_amount").val(response['message']['pay_amount']);
			$("#view-delivery-ticket_code").empty();
            for (var i=0;i<response['message']['ticket_code'].length;i++)
            {               
				if (i>0 && i<response['message']['ticket_code'].length)
                    $("#view-delivery-ticket_code").append("\n");
                $("#view-delivery-ticket_code").append(response['message']['ticket_code'][i]);
            }
            $("#view-delivery-delivery_name").val(response['message']['delivery_name']);
            $("#view-delivery-delivery_date").val(response['message']['delivery_date']);
            $("#view-delivery-office").val(response['message']['office']);
            if (response['message']['isActive']==0)
                $("#view-delivery-status").val('Đã hủy');
            else
                $("#view-delivery-status").val(response['message']['status']);
			$("#view-delivery-collection_date").val(response['message']['collection_date']);
            // show log history
            $("#log_table tbody" ).empty();
            for (var i=0;i<response['log'].length;i++)
            {
                $( "#log_table tbody" ).append( "<tr>" +
                    "<td>" + response['log'][i]['date_log'] + "</td>" +
                    "<td>" + response['log'][i]['remark'] + "</td>" +
                    "<td>" + response['log'][i]['note'] + "</td>" +
                    "</tr>" );
            }

			$("#dialog-delivery").dialog( "open");					
        });
}

function editNote()
{
    $.post('index.php?menu=' + module_name + '&rawmode=yes', {
            action:		'addNote',
            callid:	    $('#view_note_id').val(),
            note:       $('#dialog-view-content').val()
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

function add_note_history(callid)
{
    $("#view_note_id").val(callid);
    $("#dialog-view-content").val('');
    $('#dialog-view-content').removeAttr('readonly');
    $("#btnSave-dialog-view").show();
    $('#btnEdit-dialog-view').hide();
    $('#dialog-view').dialog('option', 'title', 'Thêm ghi chú cuộc gọi');
    $("#dialog-view").dialog( "open");
}

function view_note(callid)
{    
    $.post('index.php?menu=' + module_name + '&rawmode=yes', {
            menu:		module_name,
            rawmode:	'yes',
            action:		'viewNote',
            view_note_id: callid
        },
        function (response) {
            $("#dialog-view-content").val(response['content']);
			$("#view_note_id").val(response['note_id']);
			//check permission
			if (response['permit']==true)
				$("#btnEdit-dialog-view").show();			
			$("#dialog-view").dialog( "open");
        });
}

function do_transfer_param(dest,type)
{	
	$.post('index.php?menu=' + module_name + '&rawmode=yes', {
		menu:		module_name, 
		rawmode:	'yes',
		action:		'transfer',
		extension:	dest,
		atxfer: 	type
	},
	function (respuesta) {
        if (respuesta['action'] == 'error') {
        	mostrar_mensaje_error(respuesta['message']);
        }               
	});
}
function do_transfer_attend()
{
    $('#waiting_text').val('Vui lòng chờ');
    $('#waiting').dialog('open');
    $.post('index.php?menu=' + module_name + '&rawmode=yes', {
		menu:		module_name, 
		rawmode:	'yes',
		action:		'transfer',
		extension:	$('#transfer_number').val(),
		atxfer: 	true
	},
	function (respuesta) {
        $('#waiting').dialog('close');
        if (respuesta['action'] == 'error') {
        	mostrar_mensaje_error(respuesta['message']);
        } 
		else {
        	mostrar_mensaje_info(respuesta['message']);
        }               
	});
}
function do_transfer_blind()
{
    $('#waiting').dialog('open');
    $.post('index.php?menu=' + module_name + '&rawmode=yes', {
		menu:		module_name, 
		rawmode:	'yes',
		action:		'transfer',
		extension:	$('#transfer_number').val(),
		atxfer: 	false
	},
	function (respuesta) {
        $('#waiting').dialog('close');
        if (respuesta['action'] == 'error') {
        	mostrar_mensaje_error(respuesta['message']);
        }    
		else {
        	mostrar_mensaje_info(respuesta['message']);
        }		
	});
}

function view_customer(customer_id)
{
    if (customer_id=='') {
        alert('Không có id khách hàng');
        return false;
    }
    $.post('index.php?menu=' + module_name + '&rawmode=yes', {
            menu:		module_name,
            rawmode:	'yes',
            action:		'viewCustomer',
            customer_id: customer_id
        },
        function (response) {
            //console.log(response);
            $('.view_customer_fullname').val(response['lastname']+' '+response['firstname']);
            $('.view_customer_customer_code').val(response['customer_code']);
            $('.view_customer_booker').val(response['agent']);
            $('.view_customer_sale').val(response['sale']);
            $('.view_customer_accoutant').val(response['accountant']);
            $('.view_customer_payment_type').val(response['payment']);
            if(typeof response['number'] != 'undefined')
                $('.view_customer_phone').val(response['number']);
            $('.view_customer_membership').val(response['membership']);
            $('#view_customer_company').val(response['company']);
            $('#view_customer_birthday').val(response['birthday']);
            $('#view_customer_birthplace').val(response['birthplace']);
            $('#view_customer_cmnd').val(response['cmnd']);
            $('#view_customer_passport').val(response['passport']);

            $("#contact tbody" ).empty();
            if(typeof response['contact'] != 'undefined') {
                for (var i=0;i<response['contact'].length;i++)
                {
                    $( "#contact tbody" ).append( "<tr>" +
                        "<td>" + response['contact'][i]['name'] + "</td>" +
                        "<td>" + response['contact'][i]['phone'] + "</td>" +
                        "<td>" + response['contact'][i]['email'] + "</td>" +
                        "</tr>" );
                }
            }

            if (response['type']=='0'){
                $('#customer_type').html('KHÁCH HÀNG LẺ');
                $('#KHCTY').hide();
                $('#KHLE').show();
            }
            else if(response['type']=='1'){
                $('#customer_type').html('KHÁCH HÀNG LẺ THƯỜNG XUYÊN');
                $('#KHCTY').hide();
                $('#KHLE').show();
            }
            else if(response['type']=='2'){
                $('#customer_type').html('KHÁCH HÀNG CÔNG TY');
                $('#KHCTY').show();
                $('#KHLE').hide();
            }
            else{
                $('#customer_type').html('KHÁCH HÀNG ĐẠI LÝ');
                $('#KHCTY').show();
                $('#KHLE').hide();
            }
            $('#view_customer_dialog').dialog('open');
        });
}

function view_customer_phone(phone_number)
{
    alert('Đây là chức năng tra cứu thông tin khách hàng theo số điện thoại. Vui lòng thử lại sau!');
}

function update_message()
{
    $.post('index.php?menu=overall_setting&rawmode=yes', {
            action:		    'refresh'
        },
        function (response) {
            if (response['isActive']=='1'){
                $('#general_notification').empty();
                $('#general_notification').html(response['message']);
            }
        });
}

function reload()
{
    location.reload();
}