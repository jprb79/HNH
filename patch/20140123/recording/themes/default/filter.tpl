{literal}
<style type="text/css">
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
.delivery-dialog .row label{width:110px; display:inline-block;}
.delivery-dialog .row span{width:25px; display:inline-block;}
.delivery-dialog .buttons .btn{background: url(../images/btn-submit.png); width:122px; height:34px; border:none; color: #fff;}
.delivery-dialog .buttons .btn:hover{background-position: 0px 35px;}
</style>
{/literal}

<table width="99%" border="0" cellspacing="0" cellpadding="0" align="center">
    <tr class="letra12">
	<td width="6%" align="right">{$date_start.LABEL}:</td>
	<td width="4%" align="left" nowrap>{$date_start.INPUT}</td>
	<td width="6%" align="right">{$date_end.LABEL}:</td>
	<td width="4%" align="left" nowrap>{$date_end.INPUT}</td>
	<td align="right">
	{$filter_field.LABEL}:&nbsp;&nbsp;{$filter_field.INPUT}&nbsp;&nbsp;{$filter_value.INPUT}
	  <select id="filter_value_userfield" name="filter_value_userfield" size="1" style="display:none">
                <option value="incoming" {$SELECTED_1} >{$INCOMING}</option>
                <option value="outgoing" {$SELECTED_2} >{$OUTGOING}</option>
                <option value="queue" {$SELECTED_3} >{$QUEUE}</option>
		<option value="group" {$SELECTED_4} >{$GROUP}</option>
           </select>
	<input class="button" type="submit" name="show" value="{$SHOW}" />
	</td>
    </tr>
</table>

<!-- dialog VIEW -->
<div id="dialog-delivery" title="Xem yêu cầu giao vé">
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
    <div class="row">&nbsp;</div>
    <div class="row">
        <label >Giá vé: </label>
        <input type="text" id="view-delivery-price" readonly>
        <span> 	&nbsp;</span>
        <label >Thuế: </label>
        <input type="text" id="view-delivery-tax" readonly>
    </div>
    <div class="row">
        <label >Chiết khấu: </label>
        <input type="text" id="view-delivery-discount" readonly>
        <span> 	&nbsp;</span>
        <label >Tỉ giá: </label>
        <input type="text" id="view-delivery-rate" readonly>
    </div>
    <div class="row">
        <label >Tổng cộng: </label>
        <input type="text" id="view-delivery-pay_amount" readonly>
    </div>
    <div class="row">
        <label >Tình trạng: </label>
        <input type="text" id="view-delivery-status" readonly>
        <span> 	&nbsp;</span>
        <label >Mã vé: </label>
        <textarea id="view-delivery-ticket_code" style="width:133px;height:60px;" readonly></textarea>
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
    <div class="row">&nbsp;</div>
    <div class="row">
        <div class="buttons">
            <input type="button" class="btn" id="btnClose-dialog-delivery" value="Thoát" />
        </div>
    </div>
</div>

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