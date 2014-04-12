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
        <td><h3 style="color:darkblue;">TIN NHẮN CHUNG</h3></td>
        <td nowrap="" align="right"><span class="letra12"><span class="required">*</span> Bắt buộc nhập</span></td>
    </tr>
    </tbody>
</table>
<table width="100%" class="tabForm" style="font-size: 16px;">
    <tbody>
    <tr class="letra12">
        <td width="78px" align="left"><b>Nội dung tin nhắn: <span class="required">*</span></b></td>
        <td width="450px" align="left"><textarea id="message_notification" cols="50" rows="4" name="message">{$message}</textarea></td>
    </tr>
    <tr class="letra12">
        <td width="100px" align="left"><b>Bật thông báo: </b></td>
        <td align="left" width="35px"><input type="checkbox" id="active_notification" name="1" value="1" {if $isActive=='1'}checked{/if}></td>
    </tr>
    <tr class="letra12">
        <td>&nbsp&nbsp</td>
        <td align="left" width="40px"><button id="btn_save_notification">Cập nhật</button>
        &nbsp&nbsp<button id="btn_refresh_notification">Làm tươi</button></td>
    </tr>
</tbody>
</table>
</br>
{* CURRENCY RATE SETUP   *}
<table width="100%" cellspacing="0" cellpadding="4" border="0" align="center">
    <tbody><tr class="letra12">
        <td><h3 style="color:darkblue;">TỈ GIÁ AIRLINES (VND <--> USD)</h3></td>
        <td nowrap="" align="right"><span class="letra12">
    </tr>
    </tbody>
</table>
<table width="100%" class="tabForm" style="font-size: 16px;">
    <tbody><tr class="letra12">
        <td width="20px" align="left"><b>Sabre: </b></td>
        <td align="left"><input id="sabre" type="text" style="width: 500px"></td>
    </tr>
    <tr class="letra12">
        <td width="20px" align="left"><b>BSP: </b></td>
        <td align="left"><input id="bsp" type="text" style="width: 500px"></td>
    </tr>
    <tr class="letra12">
        <td width="20px" align="left"><b>Lion Air: </b></td>
        <td align="left"><input id="lion_air" type="text" style="width: 500px"></td>
    </tr>
    <tr class="letra12">
        <td width="20px" align="left"><b>Air Asia: </b></td>
        <td align="left"><input id="air_asia" type="text" style="width: 500px"></td>
    </tr>
    <tr class="letra12">
        <td width="20px" align="left"><b>Lao Airlines: </b></td>
        <td align="left"><input id="lao_airlines" type="text" style="width: 500px"></td>
    </tr>
    <tr class="letra12">
        <td width="20px" align="left"><b>Transviet: </b></td>
        <td align="left"><input id="transviet" type="text" style="width: 500px"></td>
    </tr>
    <tr class="letra12">
        <td align="left"><b>Ghi chú: </b></td>
        <td align="left"><textarea id="note" cols="50" rows="4" name="note" style="width: 500px"></textarea></td>
    </tr>
    <tr class="letra12">
        <td>&nbsp&nbsp</td>
        <td><span id="info_rate" style="font-style: italic;color: darkred"></span></td>
    </tr>
    <tr class="letra12">
        <td>&nbsp&nbsp</td>
        <td align="left" width="40px"><button id="btn_save_rate">Cập nhật</button>
            &nbsp&nbsp<button id="btn_refresh_rate">Làm tươi</button></td>
    </tr></tbody>
</table>