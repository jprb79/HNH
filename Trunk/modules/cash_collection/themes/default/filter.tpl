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
        <td width="12%" align="right"><b>Ngày mua vé:</b></td>
        <td width="12%" align="right"></td>
        <td width="12%" align="right">{$customer_name.LABEL}: </td>
        <td width="12%" align="left" nowrap>{$customer_name.INPUT}</td>
        <td align="center"><input class="button" type="submit" id="btn_find" value="Tìm" style="width: 75px;" /></td>
    </tr>
    <tr class="letra12">
        <td width="12%" align="right">{$date_start.LABEL}: </td>
        <td width="12%" align="left" nowrap>{$date_start.INPUT}</td>
        <td width="12%" align="right">{$customer_number.LABEL}: </td>
        <td width="12%" align="left" nowrap="nowrap">{$customer_number.INPUT}</td>
        <td align="center"><input class="button" id="btn_find_all" value="Bỏ lọc" style="width: 75px;"/></td>
     </tr>
    <tr class="letra12">
        <td width="12%" align="right">{$date_end.LABEL}: </td>
        <td width="12%" align="left" nowrap>{$date_end.INPUT}</td>
        <td width="12%" align="right">{$ticket_code.LABEL}: </td>
        <td width="12%" align="left" nowrap="nowrap">{$ticket_code.INPUT}</td>
    </tr>
    <tr class="letra12">
        <td width="12%" align="right"></td>
        <td width="12%" align="left"></td>
        <td width="12%" align="right">{$status.LABEL}: </td>
        <td width="12%" align="left" nowrap="nowrap">{$status.INPUT}</td>
    </tr>
</table>

<div id="assign_delivery_box" title="Nhận tiền từ nhân viên giao vé">
    <form>
        <div style="display: table; width: 100%">
            <div style="display: table-row;">
                <p>Mã giao vé: <b><span id="assign_ticket_id"></b></b></span></p>
                <p>Đã nhận đủ số tiền?</p>
            </div>
        </div>
    </form>
</div>{* assign_delivery_box *}

{literal}
    <script type="text/javascript">
        var module_name = 'cash_collection';
        function show_info(s)
        {
            $('#elastix-callcenter-info-message-text').text(s);
            $('#elastix-callcenter-info-message').show('slow', 'linear', function() {
                setTimeout(function() {
                    $('#elastix-callcenter-info-message').fadeOut();
                }, 5000);
            });
        }
        function show_error(s)
        {
            $('#elastix-callcenter-error-message-text').text(s);
            $('#elastix-callcenter-error-message').show('slow', 'linear', function() {
                setTimeout(function() {
                    $('#elastix-callcenter-error-message').fadeOut();
                }, 5000);
            });
        }

        function collect_form(ticket_id)
        {
            $('#assign_ticket_id').val(ticket_id);
            $('#assign_ticket_id').html(ticket_id);
            $('#assign_delivery_box').dialog('open');
        }
        function paid()
        {
            $.post('index.php?menu=' + module_name + '&rawmode=yes', {
                        action:		    'assign',
                        ticket_id:      $('#assign_ticket_id').val()
                    },
                    function (response) {
                        console.log(response);
                        if (response['action'] == 'error')
                            show_error(response['message']);
                        else
                            show_info(response['message']);
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
            });
            $('#assign_delivery_box').dialog({
                autoOpen: false,
                width: 300,
                height: 200,
                modal: true,
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
        });
    </script>
{/literal}