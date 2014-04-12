function show_info(s)
{
    $('html, body').animate({ scrollTop: 0 }, 'fast');
    $('#elastix-callcenter-info-message-text').text(s);
    $('#elastix-callcenter-info-message').show('slow', 'linear', function() {
        setTimeout(function() {
            $('#elastix-callcenter-info-message').fadeOut();
        }, 30000);
    });
}

function show_error(s)
{
    $('html, body').animate({ scrollTop: 0 }, 'fast');
    $('#elastix-callcenter-error-message-text').text(s);
    $('#elastix-callcenter-error-message').show('slow', 'linear', function() {
        setTimeout(function() {
            $('#elastix-callcenter-error-message').fadeOut();
        }, 30000);
    });
}

function report(data)
{
    var html;
    var message;
    $('#r_filename').html(data['filename']);
    $('#r_filesize').html(data['filesize']);

    message = 'Danh sách mã khách hàng đã cập nhật: \\n';
    html =  data['num_update'];
    for (var i=0;i<data['arr_update'].length;i++)
        message = message + data['arr_update'][i] + ' - ';
    html = html + '&nbsp&nbsp<a href="javascript:void(0)" onclick="alert(\''+ message + '\')">Xem</a>';
    $('#num_update').html(html);

    message = 'Danh sách mã khách hàng đã thêm mới: \\n';
    html =  data['num_new'];
    for (var i=0;i<data['arr_new'].length;i++)
        message = message + data['arr_new'][i] + ' - ';
    html = html + '&nbsp&nbsp<a href="javascript:void(0)" onclick="alert(\''+ message + '\')">Xem</a>';
    $('#num_new').html(html);

    $('#num_row').html(data['num_row']);

    message = 'Danh sách dòng import bị lỗi: \\n';
    html = data['num_error'];
    for (var i=0;i<data['arr_error'].length;i++)
        message = message + 'Dòng: ' + data['arr_error'][i]['row'] + ' - Lỗi: ' + data['arr_error'][i]['error'] + '\\n';
    html = html + '&nbsp&nbsp<a href="javascript:void(0)" onclick="alert(\''+ message + '\')">Xem</a>';
    $('#num_error').html(html);
    $('#num_district').html(data['num_district']);
    $('#num_province').html(data['num_province']);
    $('#num_booker').html(data['num_booker']);
    $('#num_accountant').html(data['num_accountant']);
    $('#num_sale').html(data['num_sale']);

    $('#import_result').show();
}

function filter_select(element){
    if ($(element).val()=='booker_id'){
        $('#booker').attr('style','display:');
        $('#filter_value').attr('style','display:none');
        $('#customer_type').attr('style','display:none');
        $('#accountant').attr('style','display:none');
        $('#sale').attr('style','display:none');
        $('#district').attr('style','display:none');
        $('#province').attr('style','display:none');
    }
    else if ($(element).val()=='type') {
        $('#customer_type').attr('style','display:');
        $('#filter_value').attr('style','display:none');
        $('#booker').attr('style','display:none');
        $('#accountant').attr('style','display:none');
        $('#sale').attr('style','display:none');
        $('#district').attr('style','display:none');
        $('#province').attr('style','display:none');
    }
    else if ($(element).val()=='province_id') {
        $('#customer_type').attr('style','display:none');
        $('#filter_value').attr('style','display:none');
        $('#booker').attr('style','display:none');
        $('#accountant').attr('style','display:none');
        $('#sale').attr('style','display:none');
        $('#district').attr('style','display:none');
        $('#province').attr('style','display:');
    }
    else if ($(element).val()=='district_id') {
        $('#customer_type').attr('style','display:none');
        $('#filter_value').attr('style','display:none');
        $('#booker').attr('style','display:none');
        $('#accountant').attr('style','display:none');
        $('#sale').attr('style','display:none');
        $('#district').attr('style','display:');
        $('#province').attr('style','display:none');
    }
    else if ($(element).val()=='sale_id') {
        $('#customer_type').attr('style','display:none');
        $('#filter_value').attr('style','display:none');
        $('#booker').attr('style','display:none');
        $('#accountant').attr('style','display:none');
        $('#sale').attr('style','display:');
        $('#district').attr('style','display:none');
        $('#province').attr('style','display:none');
    }
    else if ($(element).val()=='accountant_id') {
        $('#customer_type').attr('style','display:none');
        $('#filter_value').attr('style','display:none');
        $('#booker').attr('style','display:none');
        $('#accountant').attr('style','display:');
        $('#sale').attr('style','display:none');
        $('#district').attr('style','display:none');
        $('#province').attr('style','display:none');
    }
    else{ // default
        $('#customer_type').attr('style','display:none');
        $('#booker').attr('style','display:none');
        $('#accountant').attr('style','display:none');
        $('#sale').attr('style','display:none');
        $('#district').attr('style','display:none');
        $('#province').attr('style','display:none');
        $('#filter_value').attr('style','display:');
        $('#booker').attr('style','display:none');
        $('#filter_value').val('');
    }
}