var module_name = 'overall_setting';

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
            $('#elastix-callcenter-error-message').fadeOut();}, 5000);
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

function init_rate()
{
    $.post('index.php?menu=' + module_name + '&rawmode=yes', {
            action:		    'refresh_rate'
        },
        function (response) {
            refresh_rate(response);
        });
}

function refresh_rate(data)
{
    $('#sabre').val(data['sabre']);
    $('#bsp').val(data['bsp']);
    $('#lion_air').val(data['lion_air']);
    $('#air_asia').val(data['air_asia']);
    $('#lao_airlines').val(data['lao_airlines']);
    $('#transviet').val(data['transviet']);
    $('#note').val(data['note']);
    $('#info_rate').html('Cập nhật lúc ' + data['created'] + ' bởi ' + data['username']);
}

$(document).ready(function(){

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
            else{
                update_notification(response);
                show_info('Cập nhật thành công');
            }
        });
    });

    //rate button
    $('#btn_save_rate').button().click(function(){
        $.post('index.php?menu=' + module_name + '&rawmode=yes', {
                action:     'update_rate',
                sabre:      $('#sabre').val(),
                bsp:        $('#bsp').val(),
                lion_air:        $('#lion_air').val(),
                air_asia:        $('#air_asia').val(),
                lao_airlines:       $('#lao_airlines').val(),
                transviet:        $('#transviet').val(),
                note:        $('#note').val()
            },
            function (response) {
                //console.log(response);
                if (response['action'] == 'error')
                    show_error(response['message']);
                else{
                    show_info('Cập nhật thành công');
                    // refresh to get saved data
                    $.post('index.php?menu=' + module_name + '&rawmode=yes', {
                        action: 'refresh_rate'
                        },function (response) {
                            refresh_rate(response);
                        });
                    }
            });
    });

    $('#btn_refresh_rate').button().click(function(){
        $.post('index.php?menu=' + module_name + '&rawmode=yes', {
                action:		    'refresh_rate'
            },
            function (response) {
                //console.log(response);
                if (response['action'] == 'error')
                    show_error(response['message']);
                else{
                    refresh_rate(response);
                    show_info('Làm tười thành công');
                }
            });
    });

    //init rate value
    setTimeout(init_rate,100);
});