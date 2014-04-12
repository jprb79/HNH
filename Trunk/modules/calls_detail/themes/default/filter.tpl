{literal}
<style type="text/css">
#waiting {color: #767676;text-align: center;}
/* DIALOG */
.save-dialog{padding:0; border:0; border-radius:0;}
.save-dialog .ui-dialog-titlebar{border:0; border-radius:0; background:#f89730; color: #fff;}
.save-dialog .ui-dialog-titlebar-close {display: none;}
.save-dialog .row{float:left; width:100%; display:block; padding: 5px 0;}
.save-dialog .row label{float:left; width:160px; display:inline-block;}
.save-dialog .row select.large{width:282px;}
.save-dialog .buttons{float:right;}
.save-dialog .buttons .btn{background: url(../images/btn-submit.png); width:122px; height:34px; border:none; color: #fff;}
.save-dialog .buttons .btn:hover{background-position: 0px 35px;}
/* DELIVERY DIALOG */
.delivery-dialog{padding:0; border:0; border-radius:0;}
.delivery-dialog .ui-dialog-titlebar{border:0; border-radius:0; background:#f89730; color: #fff;}
.delivery-dialog .ui-dialog-titlebar-close {display: none;}
.delivery-dialog .row{float:left; width:100%; display:block; padding: 5px 0;}
.delivery-dialog .row label{width:108px; display:inline-block;}
.delivery-dialog .row span{width:25px; display:inline-block;}
.delivery-dialog .buttons .btn{background: url(../images/btn-submit.png); width:122px; height:34px; border:none; color: #fff;}
.delivery-dialog .buttons .btn:hover{background-position: 0px 35px;}
</style>
{/literal}
{literal}
<script type="text/javascript">
$(document).ready(function(){			
	/* ALL DIALOGS INITIALIZE*/
    $( "#dialog-view" ).dialog({
        autoOpen: false,
        modal: true,
        dialogClass: "save-dialog",
        position: ['top', 100],
        width: 480
    });
    $( "#dialog-delivery" ).dialog({
        autoOpen: false,
        modal: true,
        dialogClass: "delivery-dialog",
        position: ['top', 100],
        width: 594
    });
    /* END OF ALL DIALOGS */	

    /* button close*/    
    $("#btnClose-dialog-view").click(function(){
        $("#dialog-view").dialog("close");
    });
    $("#btnClose-dialog-delivery").click(function(){
        $("#dialog-delivery").dialog("close");
    });			
});

function view_delivery(callid)
{
    $.post('index.php?menu=calls_detail&rawmode=yes', {
                menu:		'calls_detail',
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

function view_note(callid)
{				
	$.post('index.php?menu=calls_detail&rawmode=yes', {
		menu:		'calls_detail',
		rawmode:	'yes',
		action:		'viewNote',
		view_note_id: callid
	},
	function (response) {
		$("#dialog-view-content").val(response['content']);
		$("#dialog-view").dialog( "open");
	});
}
</script>
{/literal}
<table width="99%" border="0" cellspacing="0" cellpadding="4" align="center">
        <tr class="letra12">
            <td width="12%" align="right">{$date_start.LABEL}: <span  class="required">*</span></td>
            <td width="12%" align="left"  nowrap="nowrap">{$date_start.INPUT}</td>
            <td width="12%" align="right">{$date_end.LABEL}: <span  class="required">*</span></td>
            <td width="12%" align="left"  nowrap="nowrap">{$date_end.INPUT}</td>
            <td width="12%" align="center"><input class="button" type="submit" name="filter" value="{$Filter}" /></td>
        </tr>
        <tr>
            <td width="12%" align="right">{$phone.LABEL}:</td>
            <td width="12%" align="left" nowrap>{$phone.INPUT}</td>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td width="12%" align="right">{$agent.LABEL}:</td>
            <td width="12%" align="left" nowrap>{$agent.INPUT}</td>
            <td width="12%" align="right">{$queue.LABEL}:</td>
            <td width="12%" align="left" nowrap>{$queue.INPUT}</td>
            <td>&nbsp;</td>
        </tr>
</table>

<!-- dialog VIEW -->
<div id="dialog-view" title="Xem ghi chú">
    <div class="row">
        <textarea id="dialog-view-content" style="width:440px;height:168px;" readonly></textarea>
    </div>
    <div class="row">
        <div class="buttons">
            <input type="button" class="btn" id="btnClose-dialog-view" value="Thoát" />
        </div>
    </div>
</div>

<!-- dialog VIEW -->
<div id="dialog-delivery" title="Xem yêu cầu giao vé" stype="dislay:none">
    <div class="row">
        <label for="view-delivery-customer_name">Tên khách hàng: </label>
        <input type="text" id="view-delivery-customer_name" readonly>
        <span> 	&nbsp;</span>
        <label for="view-delivery-customer_phone">Số điện thoại: </label>
        <input type="text" id="view-delivery-customer_phone">
    </div>
    <div class="row">
        <label >Ngày mua vé: </label>
        <input type="text" id="view-delivery-purchase_date" readonly>
        <span> 	&nbsp;</span>
        <label >Booker: </label>
        <input type="text" id="view-delivery-agent_name" readonly>
    </div>
    <div class="row">
        <label >Địa chỉ: </label>
        <input type="text" id="view-delivery-deliver_address" style="width:411px;" readonly>
    </div>
    <div class="row">
        <label >Giá vé: </label>
        <input type="text" id="view-delivery-price" readonly>
        <span> 	&nbsp;</span>
        <label >Thuế: </label>
        <input type="text" id="view-delivery-tax" readonly>
    </div>
    <div class="row">
        <label >Tỉ giá: </label>
        <input type="text" id="view-delivery-rate" readonly>
        <span> 	&nbsp;</span>
        <label >Tổng cộng: </label>
        <input type="text" id="view-delivery-pay_amount" readonly>
    </div>
    <div><span id="view-delivery-invoice"></span></div>
    <div class="row">
        <label >Tình trạng: </label>
        <input type="text" id="view-delivery-status" readonly>
        <span> 	&nbsp;</span>
        <label >Mã vé: </label>
        <textarea id="view-delivery-ticket_code" style="width:133px;height:45px;" readonly></textarea>
    </div>
    <div class="row">
        <label >Người giao vé: </label>
        <input type="text" id="view-delivery-delivery_name" readonly>
        <span> 	&nbsp;</span>
        <label >Ngày phân công: </label>
        <input type="text" id="view-delivery-delivery_date" readonly>
    </div>
    <div class="row">
        <label >Chi nhánh: </label>
        <input type="text" id="view-delivery-office" readonly>
        <span> 	&nbsp;</span>
        <label >Ngày nhận tiền: </label>
        <input type="text" id="view-delivery-collection_date" readonly>
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
    <div class="row">&nbsp;</div>
    <div class="row">
        <div class="buttons">
            <input type="button" class="btn" id="btnClose-dialog-delivery" value="Thoát" />
        </div>
    </div>
</div>

<! --  AJAX LOADER  -- !>
<div id="waiting" style="display: none;">
    Vui lòng chờ...<br /><img src="/images/ajax-loader.gif" title="Loader" alt="Loader" />
</div>
<! --  END OF AJAX LOADER  -- !>