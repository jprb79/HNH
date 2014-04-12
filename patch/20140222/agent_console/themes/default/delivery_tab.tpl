{literal}
    <style type="text/css">
        div#log {margin: 20px 0; }
        div#log table { margin: 1em 0; border-collapse: collapse; width: 100%; }
        div#log table td, div#users-contain table th { border: 1px solid #eee; padding: .6em 10px; text-align: left; }
        .ui-dialog .ui-state-error { padding: .3em; }
    </style>
{/literal}

<table id="delivery_tab" style="display:none"></table>
{literal}
<script type="text/javascript">
    $("#delivery_tab").flexigrid({
        url: 'modules/agent_console/delivery_tab.php',
        dataType: 'json',
        colModel : [
            {display: '<b>ID</b>', name : 'id', width : 40, sortable : true, align: 'right'},
            {display: '<b>Tên khách hàng</b>', name : 'customer_name', width : 120, sortable : true, align: 'left'},
            {display: '<b>Số điện thoại</b>', name : 'customer_phone', width : 100, sortable : false, align: 'left'},
            {display: '<b>Ngày mua vé</b>', name : 'purchase_date', width : 115, sortable : true, align: 'right'},
			{display: '<b>Mã vé</b>', name : 'ticket_code', width : 90, sortable : false, align: 'right'},
			{display: '<b>Booker</b>', name : 'agent', width : 70, sortable : true, align: 'left'},
            {display: '<b>Tình trạng</b>', name : 'status', width : 70, sortable : true, align: 'right'},
            {display: '<b>Ngày phân công</b>', name : 'delivery_date', width : 115, sortable : true, align: 'right'},
            {display: '<b>Nhân viên giao vé</b>', name : 'delivery_man', width : 100, sortable : true, align: 'left'},
            {display: '<b>Ngày nhận tiền</b>', name : 'collection_date', width : 100, sortable : true, align: 'right'},
			{display: '<b>Chọn</b>', name : 'action', width : 80, sortable : false, align: 'left'}
        ],
		buttons : [
            {name: 'All', onpress: sortAlpha},
            {name: 'A', onpress: sortAlpha},
            {name: 'B', onpress: sortAlpha},
            {name: 'C', onpress: sortAlpha},
            {name: 'D', onpress: sortAlpha},
            {name: 'E', onpress: sortAlpha},
            {name: 'F', onpress: sortAlpha},
            {name: 'G', onpress: sortAlpha},
            {name: 'H', onpress: sortAlpha},
            {name: 'I', onpress: sortAlpha},
            {name: 'J', onpress: sortAlpha},
            {name: 'K', onpress: sortAlpha},
            {name: 'L', onpress: sortAlpha},
            {name: 'M', onpress: sortAlpha},
            {name: 'N', onpress: sortAlpha},
            {name: 'O', onpress: sortAlpha},
            {name: 'P', onpress: sortAlpha},
            {name: 'Q', onpress: sortAlpha},
            {name: 'R', onpress: sortAlpha},
            {name: 'S', onpress: sortAlpha},
            {name: 'T', onpress: sortAlpha},
            {name: 'U', onpress: sortAlpha},
            {name: 'V', onpress: sortAlpha},
            {name: 'W', onpress: sortAlpha},
            {name: 'X', onpress: sortAlpha},
            {name: 'Y', onpress: sortAlpha},
            {name: 'Z', onpress: sortAlpha}
        ],
        searchitems : [
            {display: 'Tên khách hàng', name : 'customer_name'},
            {display: 'Số điện thoại', name : 'customer_phone', isdefault: true},
            {display: 'Tình trạng', name : 'status'},
			{display: 'Booker', name : 'agent_id'},
            {display: 'Nhân viên giao vé', name : 'user_id'},
			{display: 'Mã vé', name : 'ticket_code'},
			{display: 'Ngày mua vé', name : 'purchase_date'},
			{display: 'Ngày phân công', name : 'delivery_date'},
			{display: 'Ngày nhận tiền', name : 'collection_date'}
        ],
        sortname: "purchase_date",
        sortorder: "desc",
        usepager: true,
        title: 'Danh sách yêu cầu giao vé',
        useRp: true,
        rp: 10,
        showTableToggleBtn: true,
        height: 300
    });
	function sortAlpha(com)
    {
        jQuery('#delivery_tab').flexOptions({newp:1, params:[{name:'letter_pressed', value: com}]});
        jQuery("#delivery_tab").flexReload();
    }

    function addExtraSelect_delivery() {
        var elem_text = $("#delivery_tab").parents(".flexigrid").find(".sDiv2 .qsbox");
        elem_text.attr("id", "delivery_search_text");
        var elem = $("#delivery_tab").parents(".flexigrid").find(".sDiv2 select[name='qtype']");
        if (elem.length > 0) {
            elem.after("<select id='delivery_booker_select' name='booker' style='display: none;'></select>" +
                    " <select id='delivery_user_select' name='user' style='display: none;'></select>" +
                    "<select id='delivery_status_select' name='status' style='display: none;'></select>");
            elem.attr("id", "delivery_field_select");
            // add agent list
            $.post('modules/agent_console/delivery_tab.php', {
                        booker_list:	'yes'
                    },
                    function (response) {
                        for (var key in response){
                            $("#delivery_booker_select").prepend("<option value='"+key+"'>"+response[key]+"</option>");
                        }
                    });
            // add delivery man select
            $.post('modules/agent_console/delivery_tab.php', {
                        delivery_man_list:	'yes'
                    },
                    function (response) {
                        for (var key in response){
                            $("#delivery_user_select").prepend("<option value='"+key+"'>"+response[key]+"</option>");
                        }
                    });
            // add list for status
            $("#delivery_status_select").prepend("<option value='Mới'>Mới</option>");
            $("#delivery_status_select").prepend("<option value='Đang giao'>Đang giao</option>");
            $("#delivery_status_select").prepend("<option value='Chờ xử lý'>Chờ xử lý</option>");
            $("#delivery_status_select").prepend("<option value='Đã nhận tiền'>Đã nhận tiền</option>");
            $("#delivery_status_select").prepend("<option value='Đã hủy'>Đã hủy</option>");
        } else {
            setTimeout(function() {addExtraSelect_delivery()}, 500);
        }

    }
    addExtraSelect_delivery();

    $("#delivery_field_select").live("change", function() {
        if ($(this).val()=='agent_id'){
            $('#delivery_search_text').hide();
            $('#delivery_user_select').hide();
            $('#delivery_status_select').hide();
            $("#delivery_booker_select").show();
            $('#delivery_search_text').val($('#delivery_booker_select').val());
        }
        else if ($(this).val()=='user_id'){
            $('#delivery_search_text').hide();
            $('#delivery_status_select').hide();
            $('#delivery_user_select').show();
            $("#delivery_booker_select").hide();
            $('#delivery_search_text').val($('#delivery_user_select').val());
        }
        else if ($(this).val()=='status'){
            $('#delivery_search_text').hide();
            $('#delivery_status_select').show();
            $('#delivery_user_select').hide();
            $("#delivery_booker_select").hide();
            $('#delivery_search_text').val($('#delivery_status_select').val());
        }
        else if ($(this).val()=='purchase_date' || $(this).val()=='delivery_date' || $(this).val()=='collection_date'){
            $('#delivery_search_text').show();
            $('#delivery_status_select').hide();
            $('#delivery_user_select').hide();
            $("#delivery_booker_select").hide();
            $('#delivery_search_text').datepicker({
                showOn:			'both',
                buttonImage:	'libs/js/jscalendar/img.gif',
                buttonImageOnly: true,
                showButtonPanel: true,
                dateFormat:		'dd-mm-yy',
                changeMonth: true,
                changeYear: true,
                showButtonPanel: true,
                yearRange: "-100:+0"
            });
        }
        else {
            $('#delivery_search_text').show();
            $('#delivery_user_select').hide();
            $('#delivery_date_search').hide();
            $('#delivery_status_select').hide();
            $("#delivery_booker_select").hide();
            $("#delivery_search_text").datepicker("destroy");
            $('#delivery_search_text').val('');
        }
    });
    $('#delivery_booker_select').live("change",function (){
        $('#delivery_search_text').val($(this).val());
    });
    $('#delivery_user_select').live("change",function (){
        $('#delivery_search_text').val($(this).val());
    });
    $('#delivery_status_select').live("change",function (){
        $('#delivery_search_text').val($(this).val());
    });
    $('#delivery_date_search').live("change",function (){
        $('#delivery_search_text').val($(this).val());
    });

    function validateEditDeliveryForm()
    {

        if ($('#edit_delivery-edit_delivery').val().trim() == "" )	{
            alert( "Thiếu tên khách hàng!" );
            $('#edit_delivery-edit_delivery').focus() ;
            return false;
        }
        if ($('#edit_delivery-ticket_code').val().trim() == "" )	{
            alert( "Thiếu thông tin vé" );
            $('#edit_delivery-ticket_code').focus() ;
            return false;
        }
        if ($('#edit_delivery-deliver_address').val().trim() == "" )	{
            alert( "Thiếu thông tin địa chỉ" );
            $('#edit_delivery-deliver_address').focus() ;
            return false;
        }
        return true;
    }

    function check_delivery_permission(ticket_id)
    {
        return true;
        $.post('index.php?menu=' + module_name + '&rawmode=yes', {
                menu:		    module_name,
                rawmode:	    'yes',
                action:		    'checkDeliveryPermission',
                delivery_id:    ticket_id
        }).done(
                function (response) {
                    if (response['perrmit']==true){
                        return true;
                    }
                    return false;
        });
    }

    // disable delivery request by agent
    function disable_delivery(ticket_id)
    {
        if (!check_delivery_permission(ticket_id)){
            alert('Không có quyền chỉnh sửa');
            return false;
        }
        $.post('index.php?menu=' + module_name + '&rawmode=yes', {
                menu:		    module_name,
                rawmode:	    'yes',
                action:		    'disableDelivery',
                delivery_id:    ticket_id
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

    // enable delivery request by agent
    function enable_delivery(ticket_id)
    {
        if (!check_delivery_permission(ticket_id)) {
            alert('Không có quyền chỉnh sửa');
            return false;
        }
        $.post('index.php?menu=' + module_name + '&rawmode=yes', {
                    menu:		    module_name,
                    rawmode:	    'yes',
                    action:		    'enableDelivery',
                    delivery_id:    ticket_id
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



    // edit delivery by agent
    function edit_delivery(ticket_id)
    {
        if (!check_delivery_permission(ticket_id)){
            alert('Không có quyền chỉnh sửa');
            return false;
        }
        $("#deliver-id").val(ticket_id);
        $.post('index.php?menu=' + module_name + '&rawmode=yes', {
                menu:		    module_name,
                rawmode:	    'yes',
                action:		    'viewDelivery',
                view_delivery_id: ticket_id
            },
            function (response) {
                $("#deliver-name").val(response['message']['customer_name']);
                $("#deliver-phone").val(response['message']['customer_phone']);
                $("#deliver-price").val(response['message']['price']);
                $("#deliver-address").val(response['message']['deliver_address']);
                $("#deliver-tax").val(response['message']['tax']);
                $("#deliver-rate").val(response['message']['currency_rate']);
                $("#deliver-note").val('');
                //$("#deliver-discount").val(response['message']['discount']);
                if (response['message']['isInvoice'] == '1')
                    $('#isInvoice').attr('checked',true);
                else
                    $('#isInvoice').attr('checked',false);
                $("#deliver-pay").val(response['message']['pay_amount']);
                $("#deliver-code").val();
                edit_code = "";
                for (var i=0;i<response['message']['ticket_code'].length;i++)
                {
                    if (i>0 && i<response['message']['ticket_code'].length)
                        edit_code = edit_code +  "\n";
                    edit_code = edit_code + response['message']['ticket_code'][i];
                }
                $("#deliver-code").val(edit_code);
                // file list attached
                fileArray = [];
                fileArray=fileArray.concat(response['message']['attachment']);
                ShowAttachmentsTable();
            });
        $("#ticket-delivery").dialog('open');
    }
</script>
{/literal}