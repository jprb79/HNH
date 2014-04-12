{literal}
<style type="text/css">
    div#log {margin: 20px 0; }
    div#log table { margin: 1em 0; border-collapse: collapse; width: 100%; }
    div#log table td, div#users-contain table th { border: 1px solid #eee; padding: .6em 10px; text-align: left; }
    .ui-dialog .ui-state-error { padding: .3em; }
</style>
{/literal}

<div id="elastix-callcenter-info-message" class="ui-state-highlight ui-corner-all">
    <p>
        <span class="ui-icon ui-icon-info" style="float: left; margin-right: .3em;"></span>
        <span id="elastix-callcenter-info-message-text"></span>
    </p>
</div>
<div id="elastix-callcenter-error-message" class="ui-state-error ui-corner-all">
    <p>
        <span class="ui-icon ui-icon-alert" style="float: left; margin-right: .3em;"></span>
        <span id="elastix-callcenter-error-message-text"></span>
    </p>
</div>

<table width="75%" border="0" cellspacing="0" cellpadding="4" align="center">
    <tr class="letra12">
        <td width="12%" align="right"><b>Ngày xuất vé:</b></td>
        <td width="12%" align="right"></td>
        <td width="12%" align="right">{$customer_name.LABEL}: </td>
        <td width="12%" align="left" nowrap>{$customer_name.INPUT}</td>

    </tr>
    <tr class="letra12">
        <td width="12%" align="right">{$date_start.LABEL}: </td>
        <td width="12%" align="left" nowrap>{$date_start.INPUT}</td>
        <td width="12%" align="right">{$customer_number.LABEL}: </td>
        <td width="12%" align="left" nowrap="nowrap">{$customer_number.INPUT}</td>
     </tr>
    <tr class="letra12">
        <td width="12%" align="right">{$date_end.LABEL}: </td>
        <td width="12%" align="left" nowrap>{$date_end.INPUT}</td>
        <td width="12%" align="right">{$ticket_code.LABEL}: </td>
        <td width="12%" align="left" nowrap="nowrap">{$ticket_code.INPUT}</td>
        <td align="center"><input class="button" type="submit" id="btn_find" value="Tìm" style="width: 75px;" /></td>
    </tr>
    <tr class="letra12">
        <td width="12%" align="right">{$id.LABEL}: </td>
        <td width="12%" align="left" nowrap>{$id.INPUT}</td>
        <td width="12%" align="right">{$status.LABEL}: </td>
        <td width="12%" align="left" nowrap="nowrap">{$status.INPUT}</td>
        <td align="center"><input class="button" id="btn_find_all" value="Bỏ lọc" style="width: 75px;"/></td>
    </tr>
</table>

<div id="assign_delivery_box" title="Phân công nhân viên giao vé">
    <form>
        <div style="display: table; width: 100%">
            <div style="display: table-row;">
                <p>Mã giao vé: <b><span id="assign_ticket_id"></b></b></span></p>
                <p>Chọn nhân viên:
                <select name="delivery_man_select" id="delivery_man_select"
                        class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only"
                        style="width: 100%">{html_options options=$DELIVERY_MAN_LIST}
                </select> </p>
                Ghi chú:<br/> <textarea id="assign_delivery_box_note" style="width: 273px;height: 85px"></textarea>
            </div>
        </div>
    </form>
</div>{* assign_delivery_box *}

<div id="collect_delivery_box" title="Nhận kết quả từ nhân viên giao vé">
    <form>
        <div style="display: table; width: 100%">
            <div style="display: table-row;">
                <p>Mã giao vé: <b><span id="collect_ticket_id"></b></b></span></p>
                <p><input type=radio value="cash" name="delivery_result" checked>Nhận đủ tiền
                    &nbsp;<input type=radio value="ticket" name="delivery_result">Trả lại vé</p>
                Ghi chú:<br/> <textarea id="collect_delivery_box_note" style="width: 273px;height: 85px"></textarea>
            </div>
        </div>
    </form>
</div>{* assign_delivery_box *}

<div id="print_delivery_box" title="In mẫu phiếu giao vé">
    <table style="font-size: 13px;">
    <td><label style="display: table-cell;" for="print_delivery_ticket_id">
        Chọn ID mã giao vé cần in:&nbsp;<br/>(Xuống dòng cho mỗi ID,<br/>tối đa 3 phiếu)</label></td>
    <td><textarea name="print_delivery_ticket_id" id="print_delivery_ticket_id" cols="3"
        class="ui-widget-content ui-corner-all" style="width: 100%"> </textarea></td>
    </table>
</div>

<div id="log_delivery_box" title="Thông tin thêm" style="display:none">
    <p>Mã giao vé: <b><span id="log_ticket_id"></b></span></p>
    <div class="ui-widget">
        <h3>Chi tiết giá vé</h3>
        <table style="font-size: 13px;" width="100%">
        <tr>
            <td width="10%"><label >Giá vé: </label></td>
            <td width="20%"><input type="text" id="view-delivery-price" readonly style="width: 100px"></td>
            <td width="10%"><label >Thuế: </label></td>
            <td width="20%"><input type="text" id="view-delivery-tax" readonly style="width: 100px"></td>
            <td width="10%"><label >Ngày xuất: </label></td>
            <td width="20%"><input type="text" id="view-delivery-purchase_date" readonly style="width: 100px"></td>
        </tr>
        <tr>
            <td><label >Tỉ giá: </label></td>
            <td><input type="text" id="view-delivery-rate" readonly style="width: 100px"></td>
            <td><b><label>Tổng cộng: </label></b></td>
            <td><b><input type="text" id="view-delivery-pay_amount" readonly style="width: 100px"></b></td>
			<td colspan="2">
                <span id="isInvoice"></span>
            </td>            
        </tr>
        </table>
    </div>
    <div id="log" class="ui-widget">
        <h3>Nhật ký</h3>
        <table id="log_table" class="ui-widget ui-widget-content">
            <thead>
            <tr class="ui-widget-header ">
                <th>Ngày</th>
                <th>Mô tả</th>
                <th>Ghi chú</th>
            </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
</div>

<div id="uncollect_delivery_box" title="Nhận tiền từ nhân viên giao vé" style="display:none">
    <form>
        <div style="display: table; width: 100%">
            <div style="display: table-row;">
                <p>Mã giao vé: <b><span id="uncollect_ticket_id"></b></b></span></p>
                <p>Bạn có muốn hủy nhận tiền?</p>
                Ghi chú:<br/> <textarea id="uncollect_delivery_box_note" style="width: 273px;height: 85px"></textarea>
            </div>
        </div>
    </form>
</div>

<div id="disable_delivery_box" title="Hủy yêu cầu giao vé" style="display:none">
    <form>
        <div style="display: table; width: 100%">
            <div style="display: table-row;">
                <p>Mã giao vé: <b><span id="disable_ticket_id"></b></b></span></p>
                <p>Bạn có muốn hủy yêu cầu giao vé này?</p>
                Ghi chú:<br/> <textarea id="disable_delivery_box_note" style="width: 273px;height: 85px"></textarea>
            </div>
        </div>
    </form>
</div>

<div id="enable_delivery_box" title="Tạo lại yêu cầu giao vé" style="display:none">
    <form>
        <div style="display: table; width: 100%">
            <div style="display: table-row;">
                <p>Mã giao vé: <b><span id="enable_ticket_id"></b></b></span></p>
                <p>Bạn có muốn tạo lại yêu cầu giao vé này?</p>
                Ghi chú:<br/> <textarea id="enable_delivery_box_note" style="width: 273px;height: 85px"></textarea>
            </div>
        </div>
    </form>
</div>

<div id="view_address" title="Địa chỉ giao vé" style="display:none">
    <form>
        <div style="display: table; width: 100%">
            <div style="display: table-row;">
                <span id="view_address_content"></span>
            </div>
        </div>
    </form>
</div>{* assign_delivery_box *}

{literal}
    <script type="text/javascript">
        var module_name = 'ticket_delivery';
        function view_address(content)
        {
            //console.log(content);
            $('#view_address_content').html(content);
            $('#view_address').dialog('open');
        }

        function enable(ticket_id)
        {
            $('#enable_ticket_id').val(ticket_id);
            $('#enable_ticket_id').html(ticket_id);
            $('#enable_delivery_box_note').val('');
            $('#enable_delivery_box').dialog('open');
        }

        function disable(ticket_id)
        {
            $('#disable_ticket_id').val(ticket_id);
            $('#disable_ticket_id').html(ticket_id);
            $('#disable_delivery_box_note').val('');
            $('#disable_delivery_box').dialog('open');
        }

        function view_log(ticket_id)
        {
            $('#log_ticket_id').val(ticket_id);
            $('#log_ticket_id').html(ticket_id);
            // retrieve data from server
            $.post('index.php?menu=' + module_name + '&rawmode=yes', {
                action:		    'expand',
                ticket_id:      ticket_id
                },
                function (response) {                    
					if (response['action'] == 'error')
                        show_error(response['message']);
                    else {                        
						var invoice = '';
						if (response['message']['isInvoice']=='1')
							invoice = '<b>Yêu cầu xuất hóa đơn</b>';
                        $('#view-delivery-price').val(response['message']['price_detail']['price']);
                        $('#view-delivery-tax').val(response['message']['price_detail']['tax']);
                        $('#view-delivery-purchase_date').val(response['message']['purchase_date']);
                        $('#isInvoice').html(invoice);
                        $('#view-delivery-rate').val(response['message']['price_detail']['currency_rate']);
                        $('#view-delivery-pay_amount').val(response['message']['price_detail']['pay_amount']);
                        $("#log_table tbody" ).empty();
                        for (var i=0;i<response['message']['log'].length;i++)
                        {
                            $( "#log_table tbody" ).append( "<tr>" +
                                    "<td>" + response['message']['log'][i]['date_log'] + "</td>" +
                                    "<td>" + response['message']['log'][i]['remark'] + "</td>" +
                                    "<td>" + response['message']['log'][i]['note'] + "</td>" +
                                    "</tr>" );
                        }
                    }
            });
            $('#log_delivery_box').dialog('open');
        }

        function collect_form(ticket_id)
        {
            $('#collect_ticket_id').val(ticket_id);
            $('#collect_ticket_id').html(ticket_id);
            $('#collect_delivery_box_note').val('');
            $('#collect_delivery_box').dialog('open');
        }

        function uncollect_form(ticket_id)
        {
            $('#uncollect_ticket_id').val(ticket_id);
            $('#uncollect_ticket_id').html(ticket_id);
            $('#uncollect_delivery_box_note').val('');
            $('#uncollect_delivery_box').dialog('open');
        }
        //tri
        function initPrint(ticket_id)
        {
            $('#print_delivery_ticket_id').val('');
            $('#print_delivery_ticket_id').val(ticket_id);
            $('#print_delivery_box').dialog('open');
        }
        // modified: only print 1 ticket one time
        function print(ticket_id)
        {
            /*
            var ticket_id = $('#print_delivery_ticket_id').val().split("\n");
            var url = 'modules/'+module_name+'/print.php?ticket_id='+ticket_id[0];
            if (typeof ticket_id[1] != 'undefined') {
                url = url + '&ticket_id2='+ticket_id[1];
                if (typeof ticket_id[2] != 'undefined')
                    url = url + '&ticket_id3='+ticket_id[2];
            }
            */
            var url = 'modules/'+module_name+'/print.php?ticket_id='+ticket_id;
            window.open(url);
        }

        function do_enable()
        {
            $.post('index.php?menu=' + module_name + '&rawmode=yes', {
                        action:		    'process',
                        ticket_id:      $('#enable_ticket_id').val(),
                        note:           $('#enable_delivery_box_note').val(),
                        type:           'enable'
                    },
                    function (response) {
                        if (response['action'] == 'error')
                            show_error(response['message']);
                        else {
                            update_row($('#enable_ticket_id').val());
                            show_info(response['message']);}
                    });
        }

        function do_disable()
        {
            $.post('index.php?menu=' + module_name + '&rawmode=yes', {
                        action:		    'process',
                        ticket_id:      $('#disable_ticket_id').val(),
                        note:           $('#disable_delivery_box_note').val(),
                        type:           'disable'
                    },
                    function (response) {
                        if (response['action'] == 'error')
                            show_error(response['message']);
                        else {
                            update_row($('#disable_ticket_id').val());
                            show_info(response['message']);}
                    });
        }

        function do_return()
        {
            $.post('index.php?menu=' + module_name + '&rawmode=yes', {
                        action:		    'process',
                        ticket_id:      $('#collect_ticket_id').val(),
                        note:           $('#collect_delivery_box_note').val(),
                        type:           'return'
                    },
                    function (response) {
                        if (response['action'] == 'error')
                            show_error(response['message']);
                        else {
                            update_row($('#collect_ticket_id').val());
                            show_info(response['message']);}
                    });
        }

        function paid()
        {
            if ($('input[name=delivery_result]:checked', '#collect_delivery_box').val() == 'ticket')
                do_return();
            else{
                $.post('index.php?menu=' + module_name + '&rawmode=yes', {
                        action:		    'collect',
                        ticket_id:      $('#collect_ticket_id').val(),
                        note:           $('#collect_delivery_box_note').val()
                    },
                    function (response) {
                        if (response['action'] == 'error')
                            show_error(response['message']);
                        else {
                            update_row($('#collect_ticket_id').val());
                            show_info(response['message']);}
                    });
            }
        }

        function unpaid()
        {
            $.post('index.php?menu=' + module_name + '&rawmode=yes', {
                        action:		    'collect',
                        ticket_id:      $('#uncollect_ticket_id').val(),
                        unpaid:         'yes',
                        note:           $('#uncollect_delivery_box_note').val()
                    },
                    function (response) {
                        if (response['action'] == 'error')
                            show_error(response['message']);
                        else {
                            update_row($('#uncollect_ticket_id').val());
                            show_info(response['message']);}
                    });
        }

        function show_info(s)
        {
            $('html, body').animate({ scrollTop: 0 }, 'fast');
            $('#elastix-callcenter-info-message-text').text(s);
            $('#elastix-callcenter-info-message').show('slow', 'linear', function() {
                setTimeout(function() {
                    $('#elastix-callcenter-info-message').fadeOut();
                }, 5000);
            });
        }

        function show_error(s)
        {
            $('html, body').animate({ scrollTop: 0 }, 'fast');
            $('#elastix-callcenter-error-message-text').text(s);
            $('#elastix-callcenter-error-message').show('slow', 'linear', function() {
                setTimeout(function() {
                    $('#elastix-callcenter-error-message').fadeOut();
                }, 5000);
            });
        }

        function update_row(ticket_id)
        {
            $.post('index.php?menu=' + module_name + '&rawmode=yes', {
                        action:		    'update_row',
                        ticket_id:      ticket_id
                    },
                    function (response) {
                        //console.log(response);
                        $('#delivery_grid_' + ticket_id).empty();
                        $('#delivery_grid_' + ticket_id).append(response);
                    });
        }

        function assign_form(ticket_id)
        {
            $('#assign_ticket_id').val(ticket_id);
            $('#assign_ticket_id').html(ticket_id);
            $('#assign_delivery_box_note').val('');
            $('#assign_delivery_box').dialog('open');
        }

        function assign_delivery()
        {
            $.post('index.php?menu=' + module_name + '&rawmode=yes', {
                        action:		    'assign',
                        ticket_id:      $('#assign_ticket_id').val(),
                        user_id:        $('#delivery_man_select').val(),
                        note:           $('#assign_delivery_box_note').val()
                    },
                    function (response) {
                        //console.log(response);
                        if (response['action'] == 'error')
                            show_error(response['message']);
                        else
                            update_row($('#assign_ticket_id').val());
                            show_info(response['message']);
                    });
        }

        function wait_delivery()
        {
            $.post('index.php?menu=' + module_name + '&rawmode=yes', {
                        action:		    'process',
                        ticket_id:      $('#assign_ticket_id').val(),
                        type:           'wait'
                    },
                    function (response) {
                        if (response['action'] == 'error')
                            show_error(response['message']);
                        else {
                            update_row($('#assign_ticket_id').val());
                            show_info(response['message']);}
                    });
        }

        $(document).ready(function(){
            $('#elastix-callcenter-error-message').hide();
            $('#elastix-callcenter-info-message').hide();
            $('#date_start').datepicker({
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
            $('#date_end').datepicker({
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
            $('#btn_find').button();
            $('#btn_find_all').button().click(function(){
                $('#date_start').val("");
                $('#date_end').val("");
                $('#customer_name').val("");
                $('#customer_number').val("");
                $('#ticket_code').val("");
                $('#status').val("");
                window.location = window.location.href;
            });
            $('#assign_delivery_box').dialog({
                autoOpen: false,
                width: 330,
                height: 300,
                modal: true,
                position: ['top', 100],
                buttons: [
                    {
                        text: 'Phân công',
                        click: function() { assign_delivery(); $(this).dialog('close'); }
                    },
                    {
                        text: 'Chờ giao',
                        click: function() { wait_delivery(); $(this).dialog('close'); }
                    },
                    {
                        text: 'Hủy bỏ',
                        click: function() { $(this).dialog('close'); }
                    }
                ]
            });
            $('#view_address').dialog({
                autoOpen: false,
                width: 300,
                height: 200,
                modal: true,
                position: ['top', 100],
                buttons: [
                    {
                        text: 'Đóng',
                        click: function() { $(this).dialog('close'); }
                    }
                ]
            });
            $('#collect_delivery_box').dialog({
                autoOpen: false,
                width: 300,
                height: 300,
                modal: true,
                position: ['top', 100],
                buttons: [
                    {
                        text: 'Xác nhận',
                        click: function() { paid(); $(this).dialog('close'); }
                    },
                    {
                        text: 'Hủy bỏ',
                        click: function() { $(this).dialog('close'); }
                    }
                ]
            });
            $('#disable_delivery_box').dialog({
                autoOpen: false,
                width: 300,
                height: 300,
                modal: true,
                position: ['top', 100],
                buttons: [
                    {
                        text: 'Xác nhận',
                        click: function() { do_disable(); $(this).dialog('close'); }
                    },
                    {
                        text: 'Hủy bỏ',
                        click: function() { $(this).dialog('close'); }
                    }
                ]
            });
            $('#enable_delivery_box').dialog({
                autoOpen: false,
                width: 300,
                height: 300,
                modal: true,
                position: ['top', 100],
                buttons: [
                    {
                        text: 'Xác nhận',
                        click: function() { do_enable(); $(this).dialog('close'); }
                    },
                    {
                        text: 'Hủy bỏ',
                        click: function() { $(this).dialog('close'); }
                    }
                ]
            });
            $('#log_delivery_box').dialog({
                autoOpen: false,
                width: 700,
                height: 500,
                modal: true,
                position: ['top', 100],
                buttons: [
                    {
                        text: 'Đóng',
                        click: function() { $(this).dialog('close'); }
                    }
                ]
            });

            $('#print_delivery_box').dialog({
                autoOpen: false,
                width: 300,
                height: 172,
                modal: true,
                position: ['top', 100],
                buttons: [
                    {
                        text: 'In phiếu',
                        click: function() { print(); $(this).dialog('close'); }
                    },
                    {
                        text: 'Hủy bỏ',
                        click: function() { $(this).dialog('close'); }
                    }
                ]
            });
            $('#uncollect_delivery_box').dialog({
                autoOpen: false,
                width: 300,
                height: 300,
                modal: true,
                position: ['top', 100],
                buttons: [
                    {
                        text: 'Xác nhận',
                        click: function() { unpaid(); $(this).dialog('close'); }
                    },
                    {
                        text: 'Hủy bỏ',
                        click: function() { $(this).dialog('close'); }
                    }
                ]
            });
        });
    </script>
{/literal}