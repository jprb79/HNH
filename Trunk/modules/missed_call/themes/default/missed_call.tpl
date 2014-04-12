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
<table width="55%" border="0" cellspacing="0" cellpadding="0">
    <tr class="letra12">
        <td width="7%" align="right">{$date_start.LABEL}: <span  class="required">*</span></td>
        <td width="10%" align="center" nowrap>{$date_start.INPUT}</td>
	<td width="30%" align="right">
            {$filter_field.LABEL}:&nbsp;&nbsp;{$filter_field.INPUT}&nbsp;&nbsp;
            {$filter_value.INPUT}
            {$status.INPUT}
            {$queue.INPUT}&nbsp;&nbsp;
            <input class="button" type="submit" name="show" value="Tìm" />
            <input class="button" type= "button" id="btn_reload" value="Xóa lọc">
        </td>
   </tr>
   <tr class="letra12">     
	<td width="7%" align="right">{$date_end.LABEL}: <span  class="required">*</span></td>
        <td width="10%" align="center" nowrap>{$date_end.INPUT}</td>
        
    </tr>
</table>
<! --  AJAX LOADER  -- !>
<div id="waiting" title="Thông báo" stype="dislay:none">
    <span id="waiting_text">Vui lòng chờ... </span><br /><br />
    <img src="/modules/missed_call/images/ajax-loader.gif" title="Loader" alt="Loader"/>
</div>
<! --  END OF AJAX LOADER  -- !>
{literal}
<script type="text/javascript">
var module_name = 'missed_call';
function make_call(number)
{
    $('#waiting_text').html('Vui lòng nhấc điện thoại của bạn sau đó hệ thống sẽ tự động kết nối đến số '+number);
    $('#waiting').dialog('open');
    $.post('index.php?menu=' + module_name + '&rawmode=yes', {
        action:		'call2phone',
        call_number:	number
    },
    function (response) {
        //console.log(response);
        $('#waiting').dialog('close');
        if (response['action'] == 'error')
            alert('Lỗi: '+response['message']);
    });
}

function filter_select(element){
    if ($(element).val()=='queue') {
        $('#filter_value').attr('style','display:none');
        $('#filter_value').val($('#queue').val());
        $('#queue').attr('style','display:');
        $('#status').attr('style','display:none');
    }
    else if ($(element).val()=='status') {
        $('#filter_value').val($('#status').val());
        $('#filter_value').attr('style','display:none');
        $('#queue').attr('style','display:none');
        $('#status').attr('style','display:');
    }
    else{ // default
        $('#filter_value').val('');
        $('#filter_value').attr('style','display:');
        $('#queue').attr('style','display:none');
        $('#status').attr('style','display:none');
    }
}

$(document).ready(function(){
    /* ALL DIALOGS INITIALIZE*/
    $( "#waiting" ).dialog({
    autoOpen: false,
    modal: true,
    width: 186,
    position: ['top', 100]
    });
    /* END OF ALL DIALOGS */
    $('#elastix-callcenter-error-message').hide();
    $('#elastix-callcenter-info-message').hide();

    $('#btn_reload').click(function(){
        $('#filter_value').val('');
        window.location = window.location.href;
    });

    // init filter status
    filter_select('#filter_field');

    $('#filter_field').change(function (){
        filter_select(this);
    });

    $('#queue').change(function (){
        $('#filter_value').val($(this).val());
    });
    $('#status').change(function (){
        $('#filter_value').val($(this).val());
    });

});
</script>
{/literal}