{literal}
<style type="text/css">
    #import_result {
        width:600px;
    }
    #close {
        float:right;
        display:inline-block;
        padding:0px 5px;
        background:#ccc;
    }
    #close:hover {
        float:right;
        display:inline-block;
        padding:0px 5px;
        background:#ccc;
        color:#fff;
    }
    .bar {
        height: 18px;
        background: green;
    }

    #fileupload {
        cursor: pointer;
        direction: ltr;
        margin: 0;
        opacity: 0;
        position: absolute;
        right: 0;
        top: 0;
    }

    .CSSTableGenerator {
        margin:0px;padding:0px;
        width:100%;
        box-shadow: 10px 10px 5px #888888;
        border:1px solid #000000;

        -moz-border-radius-bottomleft:0px;
        -webkit-border-bottom-left-radius:0px;
        border-bottom-left-radius:0px;

        -moz-border-radius-bottomright:0px;
        -webkit-border-bottom-right-radius:0px;
        border-bottom-right-radius:0px;

        -moz-border-radius-topright:0px;
        -webkit-border-top-right-radius:0px;
        border-top-right-radius:0px;

        -moz-border-radius-topleft:0px;
        -webkit-border-top-left-radius:0px;
        border-top-left-radius:0px;
    }.CSSTableGenerator table{
         border-collapse: collapse;
         border-spacing: 0;
         width:100%;
         height:100%;
         margin:0px;padding:0px;
     }.CSSTableGenerator tr:last-child td:last-child {
          -moz-border-radius-bottomright:0px;
          -webkit-border-bottom-right-radius:0px;
          border-bottom-right-radius:0px;
      }
    .CSSTableGenerator table tr:first-child td:first-child {
        -moz-border-radius-topleft:0px;
        -webkit-border-top-left-radius:0px;
        border-top-left-radius:0px;
    }
    .CSSTableGenerator table tr:first-child td:last-child {
        -moz-border-radius-topright:0px;
        -webkit-border-top-right-radius:0px;
        border-top-right-radius:0px;
    }.CSSTableGenerator tr:last-child td:first-child{
         -moz-border-radius-bottomleft:0px;
         -webkit-border-bottom-left-radius:0px;
         border-bottom-left-radius:0px;
     }.CSSTableGenerator tr:hover td{

      }
    .CSSTableGenerator tr:nth-child(odd){ background-color:#ffc9c9; }
    .CSSTableGenerator tr:nth-child(even)    { background-color:#ffffff; }.CSSTableGenerator td{
                                                                              vertical-align:middle;


                                                                              border:1px solid #000000;
                                                                              border-width:0px 1px 1px 0px;
                                                                              text-align:left;
                                                                              padding:7px;
                                                                              font-size:10px;
                                                                              font-family:Arial;
                                                                              font-weight:bold;
                                                                              color:#000000;
                                                                          }.CSSTableGenerator tr:last-child td{
                                                                               border-width:0px 1px 0px 0px;
                                                                           }.CSSTableGenerator tr td:last-child{
                                                                                border-width:0px 0px 1px 0px;
                                                                            }.CSSTableGenerator tr:last-child td:last-child{
                                                                                 border-width:0px 0px 0px 0px;
                                                                             }
    .CSSTableGenerator tr:first-child td:last-child{
        border-width:0px 0px 1px 1px;
    }
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

<div id="import_result" title="Kết quả import" style="display:none">
    <span id='close'>x</span>
    <b>Kết quả import</b>
    <div class="CSSTableGenerator" >
        <table >
            <tr>
                <td width="130px"><b>Tên file</b></td>
                <td  id="r_filename"></td>
                <td width="130px"><b>Dung lương</b></td>
                <td  id="r_filesize"></td>
            </tr>
            <tr>
                <td><b>Số KH thay đổi</b></td>
                <td  id="num_update"></td>
                <td><b>Số KH thêm mới</b></td>
                <td  id="num_new"></td>
            </tr>
            <tr>
                <td><b>Số dòng lỗi</b></td>
                <td  id="num_error"></td>
                <td><b>DM quận/huyện thêm mới</b></td>
                <td  id="num_district"></td>
            </tr>
            <tr>
                <td><b>DM tỉnh/thành thêm mới</b></td>
                <td  id="num_province"></td>
                <td><b>DM Booker thêm mới</b></td>
                <td  id="num_booker"></td>
            </tr>
            <tr>
                <td><b>DM kế toán thêm mới</b></td>
                <td  id="num_accountant"></td>
                <td><b>DM Sale thêm mới</b></td>
                <td  id="num_sale"></td>
            </tr>

        </table>
    </div>

</div>

<table width="99%" border="0" cellspacing="0" cellpadding="4" align="center">
    <tr class="letra12">
            <td style="padding:0px;"><div class="table-header-row-filter-first">
                <img border="0" align="absmiddle" src="modules/customer/images/table-import-icon.png">
                <span>Nhập từ excel</span>
                <input class="table-action" value="Nhập từ excel" id="fileupload" name="files[]" data-url="modules/customer/libs/Upload/server/php/" type="file"
                       filename="" multiple>
            </div>
            <div class="table-header-row-filter">
                <img border="0" align="absmiddle" src="modules/customer/images/table-export-icon.png">
                <input class="table-action" value="Xuất ra excel" id="export" name="export">
            </div></td>
            <td width="20%" align="right">{$field.LABEL}: </td>
            <td width="32%" align="left" nowrap>
                {$field.INPUT} &nbsp;&nbsp;
                {$booker.INPUT}
                {$sale.INPUT}
                {$province.INPUT}
                {$accountant.INPUT}
                {$district.INPUT}
                {$customer_type.INPUT}
                {$pattern.INPUT}&nbsp;&nbsp;
                <input class="button" type="submit" name="report" value="{$SHOW}">&nbsp;
                <input class="button" type= "button" id="btn_reload" value="Xóa lọc">
            </td>
    </tr>
</table>
<div id="progress">
    <div class="bar" style="width: 0%;"></div>
</div>

{literal}
<script src="modules/customer/libs/Upload/js/vendor/jquery.ui.widget.js"></script>
<script src="modules/customer/libs/Upload/js/jquery.iframe-transport.js"></script>
<script src="modules/customer/libs/Upload/js/jquery.fileupload.js"></script>
<script type="text/javascript">
    $(document).ready(function(){

        window.onload = function(){
            document.getElementById('close').onclick = function(){
                $('#import_result').hide();
                $('#progress').hide();
                $('#elastix-callcenter-info-message').hide();
                $('#elastix-callcenter-error-message').hide();
            };
        };

        $('.table_data tr').mouseover(function() {
            if(!($(this).attr("class")))
                $(this).children(':last-child').children(':first-child').children(':last-child').children(':first-child').attr("style", "visibility: visible;");

        });

        $('.table_data tr').mouseout(function(){
            if(!($(this).attr("class")))
                var dd = $(this).children(':last-child').children(':first-child').children(':last-child').children(':first-child').attr("style", "visibility: hidden;");
        });

        $('#elastix-callcenter-error-message').hide();
        $('#elastix-callcenter-info-message').hide();

        $('#btn_reload').click(function(){
            $('#filter_value').val('');
            window.location = window.location.href;
        });

        $('#export').click(function(){
            alert('Chức năng này đang cập nhật');
        });

        $('#fileupload').fileupload({
            /* ... */
            progressall: function (e, data) {
                var progress = parseInt(data.loaded / data.total * 100, 10);
                $('#progress .bar').css(
                        'width',
                        progress + '%'
                );
            }
        });

        // init filter status
        filter_select('#field');

        $('#field').change(function (){
            filter_select(this);
        });
        $('#filter_value').val('{/literal}{$PATTERN}{literal}');

        $('#booker #sale #accountant #province #district #customer_type').change(function (){
            $('#filter_value').val($(this).val());
        });

        $(function () {
            $('#fileupload').fileupload({
                dataType: 'json',
                add: function (e, data) {
                    //data.context = $('<p/>').text('Uploading...').appendTo(document.body);
                    data.submit();
                },
                done: function (e, data) {
                    $('body').css('cursor', 'wait');
                    //data.context.text('Upload finished.');
                    //$('#fileupload').attr("filename",data['_response']['result']['files']['name']);
                    //console.log(data['_response']['result']['files']);
                    $.post('index.php?menu=customer&rawmode=yes', {
                                action:		    'import',
                                file:           data['_response']['result']['files'][0]['name']
                            },
                            function (response) {
                                if (response['action'] == 'error'){
                                    show_error(response['message']);
                                    $('body').css('cursor', 'default');}
                                else {
                                    report(response['message']);
                                    $('body').css('cursor', 'default');
                                }
                            });
                }
            });
        });
    });
</script>
{/literal}