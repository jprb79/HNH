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

<table width="100%" cellspacing="0" cellpadding="4" border="0" align="center">
    <tbody><tr class="letra12">
        <td><h3 style="color:darkblue;">Cài đặt tin nhắn thông báo</h3></td>
        <td nowrap="" align="right"><span class="letra12"><span class="required">*</span> Bắt buộc nhập</span></td>
    </tr>
    </tbody>
</table>
<table width="100%" class="tabForm" style="font-size: 16px;">
    <tbody>
    <tr class="letra12">
        <td width="78px" align="left"><b>Nội dung tin nhắn: <span class="required">*</span></b></td>
        <td width="450px" align="left"><textarea id="message_notification" cols="50" rows="4" name="message">{$message}</textarea></td>
        <td width="100px" align="left"><b>Bật thông báo: </b></td>
        <td align="left" width="35px"><input type="checkbox" id="active_notification" name="1" value="1" {if $isActive=='1'}checked{/if}></td>
        <td align="left" width="40px"><button id="btn_save_notification">Cập nhật</button></td>
        <td align="left" width="40px"><button id="btn_refresh_notification">Làm tươi</button></td>
    </tr>
</tbody>
</table>
{* CURRENCY RATE SETUP   *}
<table width="100%" cellspacing="0" cellpadding="4" border="0" align="center">
    <tbody><tr class="letra12">
        <td><h3 style="color:darkblue;">Tỉ giá ngoại tệ</h3></td>
        <td nowrap="" align="right"><span class="letra12"><span class="required">*</span> Bắt buộc nhập</span></td>
    </tr>
    </tbody>
</table>
<table width="100%" class="tabForm" style="font-size: 16px;">
    <tbody>
    <tr class="letra12">
        <td width="78px" align="left"><b>USD: <span class="required">*</span></b></td>
        <td width="78px" align="left"><b>CAN: <span class="required">*</span></b></td>
        <td width="78px" align="left"><b>SGN: <span class="required">*</span></b></td>
        <td width="78px" align="left"><b>EUR: <span class="required">*</span></b></td>
        <td align="left" width="40px"><button id="btn_save_rate">Cập nhật</button></td>
        <td align="left" width="40px"><button id="btn_refresh_rate">Làm tươi</button></td>
    </tr>
    </tbody>
</table>
{*    *}
{literal}
<script type="text/javascript">
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

    function update_notification(data)
    {
        $('#message_notification').val(data['message']);
        if (data['isActive']=='1')
            $('#active_notification').attr('checked');
        else
            $('#active_notification').removeAttr('checked');
    }

    $(document).ready(function(){
        var module_name = 'overall_setting';
        $('#elastix-callcenter-error-message').hide();
        $('#elastix-callcenter-info-message').hide();
        $('#btn_save_notification').button().click(function(){
            var enable;
            if ($('#active_notification').is(':checked'))
                enable = '1';
            else
                enable = '0';
            $.post('index.php?menu=' + module_name + '&rawmode=yes', {
                action:		    'update',
                message:        $('#message_notification').val(),
                isActive:       enable
            },
            function (response) {
                //console.log(response);
                if (response['action'] == 'error')
                    show_error(response['message']);
                else{
                    $.post('index.php?menu=overall_setting&rawmode=yes', {
                                action:		    'refresh'
                            },
                            function (response) {
                                if (response['isActive']=='1'){
                                    $('#general_notification').empty();
                                    $('#general_notification').html(response['message']);
                                }
                            });
                    show_info(response['message']);
                }
            });
        });
        $('#btn_refresh_notification').button().click(function(){
            $.post('index.php?menu=' + module_name + '&rawmode=yes', {
                        action:		    'refresh'
                    },
                    function (response) {
                        //console.log(response);
                        if (response['action'] == 'error')
                            show_error(response['message']);
                        else
                            update_notification(response);
                            show_info('Làm tười thành công');
                    });
        });
    });
</script>
{/literal}