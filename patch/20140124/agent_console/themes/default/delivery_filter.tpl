<div id="delivery-callcenter-info-message" class="ui-state-highlight ui-corner-all">
    <p>
        <span class="ui-icon ui-icon-info" style="float: left; margin-right: .3em;"></span>
        <span id="delivery-callcenter-info-message-text"></span>
    </p>
</div>
<div id="delivery-callcenter-error-message" class="ui-state-error ui-corner-all">
    <p>
        <span class="ui-icon ui-icon-alert" style="float: left; margin-right: .3em;"></span>
        <span id="delivery-callcenter-error-message-text"></span>
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
{literal}
    <script type="text/javascript">
        function show_info(s)
        {
            $('#delivery-callcenter-info-message-text').text(s);
            $('#delivery-callcenter-info-message').show('slow', 'linear', function() {
                setTimeout(function() {
                    $('#delivery-callcenter-info-message').fadeOut();
                }, 5000);
            });
        }
        function show_error(s)
        {
            $('#delivery-callcenter-error-message-text').text(s);
            $('#delivery-callcenter-error-message').show('slow', 'linear', function() {
                setTimeout(function() {
                    $('#delivery-callcenter-error-message').fadeOut();
                }, 5000);
            });
        }

        $(document).ready(function(){
            $('#delivery-callcenter-error-message').hide();
            $('#delivery-callcenter-info-message').hide();
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
        });
    </script>
{/literal}