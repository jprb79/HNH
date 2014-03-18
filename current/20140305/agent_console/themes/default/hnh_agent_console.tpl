{* Edited by Tri Do *}
<div id="info">
	<div id="request">				
            <div id="transfer" {if $AGENTLINKED eq '0'}style="display:none;"{/if}>
                <table cellspacing="5" style="font-size: 13px">
                    <tbody>
                    <tr>
                        <td>Chuyển máy: </td>
                        <td>
                            <div class="transfer-phone">
                            <form name="" action="" method="post" enctype="multipart/form-data">
                                <input type="text" id="transfer_number" style="height:20px;border:none;width:116px;float:left;margin:1px 0 0 1px;text-align:end;">
                                <a href="javascript:void(0)" class="chuyencuocgoi" onclick="do_transfer_attend()" style="width:22px;height:22px;display:block;float:left;" title="Chuyển máy gián tiếp"></a>
                                <a href="javascript:void(0)" class="chuyenhuong" onclick="do_transfer_blind()" style="width:22px;height:22px;display:block;float:left;" title="Chuyển máy trực tiếp"></a>
                            </form>
                            </div>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
		<div id="clicktocall" {if $AGENTLINKED eq '1'}style="display:none;"{/if}>			
		<table cellspacing="5" style="font-size: 13px">						
            <tbody>
                <tr>
                    <td>Gọi số: </td>
                    <td>
                        <div class="call-phone_click">
                        <form name="" action="" method="post" enctype="multipart/form-data">
                            <input type="text" id="call-number" style="height:20px;border:none;width:200px;float:left;margin:1px 0 0 1px; text-align:end;">
                            <a href="javascript:void(0)" id="btn-call-number" title="Gọi số này"
                                {if isset($OUTGOING)}style="display:none;"{/if}>
                                <img src="{$THEME_PATH}/images/call.png" alt="call" style="margin:0 3px 0 5px;"></a>
                            <a href="javascript:void(0)" id="btn-hangup-call" title="Gọi xong, làm tươi màn hình"
                                {if !isset($OUTGOING)}style="display:none;"{/if}>
                                <img src="{$THEME_PATH}/images/hangup.png" alt="call" style="margin:0 3px 0 5px;"></a>
                            <a href="javascript:void(0)" id="btn-clear" title="Xóa dữ liệu cũ">
                                <img src="{$THEME_PATH}/images/clear.png" alt="clear" style="margin:0 3px 0 5px;"></a>
                        </form>
                        </div>
                    </td>
		        </tr>
            </tbody>
		</table>
		</div>
		<br/>
		<div style="float:center;"><p>Yêu cầu hỗ trợ từ khách hàng</p></div>
		<table cellspacing="5" style="font-size: 13px">
			<tbody>
                <tr>
                    <td>Tên khách hàng</td>
					<td>					
						<input type="text" style="height:20px;width:243px;float:left;" class="ticket-form" id="customer_name" name="customer_name" value="{$CUSTOMER_INFO.customer_name}">
                        <input type="hidden" id="customer_id" name="customer_id" value="{$CUSTOMER_INFO.id}" >
                        <input type="button" class="btn" id="btn-add-customer" value="{if isset($CUSTOMER_INFO.id)}Cập nhật{else}Thêm mới{/if}" customer_type="{$CUSTOMER_INFO.type}"/>
					</td> 
    			</tr>
                <tr>
                    <td>Số điện thoại</td>
                    <td>
                        <input type="text" style="height:20px;width:243px;float:left;" class="ticket-form" id="customer_number" name="customer_number" value="{$CUSTOMER_NUMBER}"/>
                        <input type="text" id="call-id" name="call-id" value="{$CALL_ID}" style="display: none"/>
				    </td>
                </tr>
                <tr>
                    <td>Nội dung: <span style="color:red">*</span></td>
                    <td>
                        <textarea class="ticket-form" id="description" name="description" style="width:312px;height:120px;"></textarea>
                    </td>
                </tr>								
	    	</tbody>
        </table>
		<table cellspacing="5" style="font-size: 13px"><tr>
			<td>
				<a class="submit" id="btn_guardar_formularios" style="color:white;text-decoration:none;display:block;
					background:url({$THEME_PATH}/images/btn-submit.png)
					no-repeat top left; width:122px;height:30px;line-height:34px;text-align:center;"
					>Lưu nội dung</a>
			</td>
			<td>
				<a class="submit" id="btn_external_note" style="color:white;text-decoration:none;display:block;
					background:url({$THEME_PATH}/images/btn-submit.png)
					no-repeat top left; width:122px;height:30px;line-height:34px;text-align:center;"
					>Lưu ngoài</a>
			</td>
			<td>
				<a class="submit" id="btn_delivery_request" style="color:white;text-decoration:none;display:block;
					background:url({$THEME_PATH}/images/btn-submit.png)
					no-repeat top left; width:122px;height:30px;line-height:34px;text-align:center;"
					>Giao vé</a>
			</td>
		</tr></table>
	</div><!-- end request -->
	<div id="table-detail">
		<dl>
			<dt class="active">
                <span>Lịch sử cuộc gọi gần đây</span>
                <a class="ic-minimize" href="javascript:void(0)" title="Expand"><img src="{$THEME_PATH}/images/btn-minimize.png" alt="minimize"></a>
                <a class="ic-refresh" href="javascript:void(0)" title="Refresh"><img src="{$THEME_PATH}/images/btn-refresh.png" alt="refresh" onclick="do_refresh_call_history()"/></a>
			</dt>
			<dd class="mca">
                <table class="tb-detail">
                    <tbody id="call_history_2"><tr>
                        <th>Khách hàng</th>
                        <th>Giờ gọi</th>
                        <th>Tổng đài viên</th>
                        <th>Tình trạng</th>
                        <th>Nội dung</th>
                        <th>Giao vé</th>
                    </tr>
                    {foreach from=$CALL_HISTORY_AGENT key=ID_CALL item=CALL}
                        <tr>
                            <td>{$CALL.phone}</td>
                            <td>{$CALL.calldate}</td>
                            <td>{$CALL.agent}</td>
                            <td>{$CALL.status}</td>
                            <td>
                                {if isset($CALL.note)}
                                    <a href="javascript:void(0)" onclick="view_note('{$CALL.id}')">Xem</a>
                                {elseif $CALL.permit_add eq '1'}
                                    <a href="javascript:void(0)" onclick="add_note_history('{$CALL.id}')">Thêm</a>
                                {/if}
                            </td>
                            <td>{if isset($CALL.delivery)}<a href="javascript:void(0)" onclick="view_delivery('{$CALL.delivery_id}')">Xem</a>{/if}</td>
                        </tr>
                    {/foreach}
                    </tbody>
                </table>
			</dd>
		</dl>
	</div><!-- end table-detail -->
</div><!-- end info -->
<div id="box">
	<dl>	   
		<dt id="paylink">
			<span>Thông tin khách hàng</span>&nbsp;&nbsp;
            <a class="ic-minimize" href="javascript:void(0)" title="Mở rông - Thu gọn"><img src="{$THEME_PATH}/images/btn-minimize.png" alt="minimize"></a>
            <a class="ic-refresh" href="javascript:void(0)" onclick="view_customer('{$CUSTOMER_INFO.id}')"><img src='{$THEME_PATH}/images/extra.png' title='Xem chi tiết'></a>
		</dt>
		<dd class="plink">
            <div id="customer_table" style="overflow:hidden;">
                {if isset($CUSTOMER_INFO.id)}
                    <table class="tb-detail-left" width="50%">
						<tbody><tr>
							<td width="30%" valign="top" ><b>Tên KH:</b></td>
							<td>{$CUSTOMER_INFO.customer_name}</td>
						</tr>
						<tr>
							<td valign="top" ><b>Phân loại:</b></td>
							<td>{$CUSTOMER_INFO.type_name}</td>
						</tr>
                        <tr>
                            <td valign="top" ><b>Email:</b></td>
                            <td>{$CUSTOMER_INFO.email}</td>
                        </tr>
                        <tr>
                            <td valign="top" ><b>Điạ chỉ:</b></td>
                            <td>{$CUSTOMER_INFO.address}</td>
                        </tr>
					</tbody></table><!-- noi dung thong tin paylink -->
					<table class="tb-detail-right" width="50%">
						<tbody>
						<tr>
							<td valign="top" width="35%"><b>Mã KH:</b></td>
							<td>{$CUSTOMER_INFO.customer_code}</td>
						</tr>
                        <tr>
                            <td valign="top" ><b>Booker:</b></td>
                            <td>{$CUSTOMER_INFO.booker}</td>
                        </tr>
                        <tr>
                            <td valign="top" ><b>Kinh doanh:</b></td>
                            <td>{$CUSTOMER_INFO.sale}</td>
                        </tr>
                        <tr>
                            <td valign="top" ><b>Kế toán:</b></td>
                            <td>{$CUSTOMER_INFO.accountant}</td>
                        </tr>
                        <tr>
                            <td valign="top" ><b>Thẻ GLP:</b></td>
                            <td>{$CUSTOMER_INFO.membership}</td>
                        </tr>
					</tbody></table><!-- noi dung thong tin paylink -->
                {/if}
            </div>
            {if isset($CUSTOMER_INFO.contact_name)}
                <h3>Người liên hệ</h3>
                <table class="tb-detail"><tr>
                        <td valign="top" ><b>Tên:</b></td>
                        <td>{$CUSTOMER_INFO.contact_name}</td>
                        <td valign="top" ><b>Email:</b></td>
                        <td>{$CUSTOMER_INFO.contact_email}</td>
                    </tr></table>
            {/if}
			{if isset($CUSTOMER_NUMBER)}
                {if isset($CALL_DELIVERY)}
                <p style="margin:5px 0;"><img src="{$THEME_PATH}/images/bullet2.png" alt="bullet" style="margin:0 3px 0 0;">Yêu cầu giao vé ứng với số điện thoại này:</p>
				<table class="tb-detail">
                    <tbody id=delivery_history">
                        <tr>
                            <th>Tên khách giao vé</th>
                            <th>Ngày đặt vé</th>
                            <th>Booker</th>
                            <th>Số vé</th>
                            <th>Tình trạng</th>
                            <th>Nhân viên giao vé</th>
                            <th>Chi tiết</th>
                        </tr>
                        {foreach from=$CALL_DELIVERY key=ID_DELIVERY item=DELIVERY}
                            <tr>
                                <td>{$DELIVERY.customer_name}</td>
                                <td>{$DELIVERY.purchase_date}</td>
                                <td>{$DELIVERY.agent}</td>
                                <td>
                                    {foreach from=$DELIVERY.ticket_code item=CODE}
                                        {$CODE} <br />
                                    {/foreach}
                                </td>
                                {if $DELIVERY.isActive eq '0'}
                                    <td>Đã hủy</td>
                                {else}
                                    <td>{$DELIVERY.status}</td>
                                {/if}
                                <td>{$DELIVERY.delivery_name}</td>
                                <td><a href="javascript:void(0)" onclick="view_delivery('{$DELIVERY.id}')">Xem</a></td>
                            </tr>{/foreach}
                    </tbody>
			    </table>
                {/if}
                <!-- external note information -->
                {if isset($EXTERNAL_NOTE)}
                <p style="margin:5px 0;"><img src="{$THEME_PATH}/images/bullet2.png" alt="bullet" style="margin:0 3px 0 0;">Các ghi chú bên ngoài của số này:</p>
                <table class="tb-detail">
                    <tbody id=external_note_history">
                    <tr>
                        <th>Ngày ghi chú</th>
                        <th>Nhân viên</th>
                        <th width="50%">Nội dung</th>
                    </tr>
                    {foreach from=$EXTERNAL_NOTE key=ID_EXTERNAL_NOTE item=EXTERNAL_NOTE}
                        <tr>
                            <td>{$EXTERNAL_NOTE.datetime}</td>
                            <td>{$EXTERNAL_NOTE.agent}</td>
                            <td>{$EXTERNAL_NOTE.note}</td>
                        </tr>
                    {/foreach}
                    </tbody>
                </table>
                {/if}
            {/if}
        </dd><!-- end plink -->
    </dl>
    <br />
    <dl>
        <dt id="history">
            <span>Các cuộc gọi gần đây của số này</span>
            <a class="ic-minimize" href="javascript:void(0)" title="Expand"><img src="{$THEME_PATH}/images/btn-minimize.png" alt="minimize"></a>
        </dt>
        <dt>
            {if isset($MOBILE_HISTORY)}
            <table class="tb-detail">
                <tbody>
                <tr>
                    <th>Ngày gọi</th>
                    <th>Thời lượng</th>
                    <th>Nhận cuộc gọi</th>
                    <th>Tình trạng</th>
                    <th>Nội dung</th>
                    <th>Giao vé</th>
                </tr>
                {foreach from=$MOBILE_HISTORY key=ID_MOBILE item=MOBILE}
                    <tr>
                        <td>{$MOBILE.calldate}</td>
                        <td>{$MOBILE.duration}</td>
                        <td>{$MOBILE.agent}</td>
                        <td>{$MOBILE.status}</td>
                        <td>{if isset($MOBILE.note)}<a href="javascript:void(0)" onclick="view_note('{$MOBILE.id}')">Xem</a>{/if}</td>
                        <td>{if isset($MOBILE.delivery)}<a href="javascript:void(0)" onclick="view_delivery('{$MOBILE.delivery_id}')">Xem</a>{/if}</td>
                    </tr>
                {/foreach}
                </tbody>
            </table>
            {/if}
        </dt>
    </dl>
</div><!-- end box -->
<!-- dialog add delivery -->
<div id="ticket-delivery" title="YÊU CẦU GIAO VÉ" style="display: none;">
    <input type="text" style="display: none;" id="deliver-id"/>
    <table cellspacing="5" style="font-size: 13px">
        <tbody>
            <tr>
                <td width="25%">Tên người nhận<span style="color:red">*</span></td>
                <td width="25%">
                    <input type="text"  class="ticket-form" id="deliver-name" name="deliver-name" value="{$CUSTOMER_INFO.customer_name}"/>
                </td>
                <td width="15%">Điện thoại</td>
                <td width="25%">
                    <input type="text"  class="ticket-form" id="deliver-phone" name="deliver-phone" value="{$CUSTOMER_NUMBER}"/>
                    <input type="text" style="display: none;" id="agent-id" name="agent-id" value="{$AGENT_ID}"/>
                </td>
            </tr>
        <tr>
            <td>Giá vé <span style="color:red">*</span></td>
            <td>
                <input type="text"  class="ticket-form" id="deliver-price" name="deliver-price" value=""/>
            </td>
            <td>Thuế</td>
            <td>
                <input type="text"  class="ticket-form" id="deliver-tax" name="deliver-tax" value=""/>
            </td>
        </tr>
        <tr>
            {* depreciated as Customer's required
			<td>Chiết khấu</td>
            <td>
                <input type="text"  class="ticket-form" id="deliver-discount" name="deliver-discount" value=""/>
            </td>
			*}
            <td>Tỉ giá</td>
            <td>
                <input type="text"  class="ticket-form" id="deliver-rate" name="deliver-rate" value=""/>
            </td>
			<td colspan="2">
                <input type="checkbox" id="isInvoice">&nbsp;Yêu cầu xuất hóa đơn
            </td>
        </tr>
        <tr>
            <td>Phải thu <span style="color:red">*</span></td>
            <td>
                <input type="text"  class="ticket-form" id="deliver-pay" name="deliver-pay" value=""/>
            </td>
        </tr>
        <tr>
            <td>Mã vé <span style="color:red">*</span> <br />(xuống dòng cho <br />mỗi mã vé)</td>
            <td colspan="3">
                <textarea class="ticket-form" id="deliver-code" style="width:130px;height:60px;"></textarea>
            </td>
        </tr>
        <tr>
            <td>Địa chỉ <span style="color:red">*</span></td>
            <td colspan="3">
                <textarea class="ticket-form" id="deliver-address" name="deliver-address" style="width:258px;height:40px;">{$CUSTOMER_INFO.address}</textarea>
            </td>
        </tr>
		<tr>
            <td>Ghi chú </td>
            <td colspan="3">
                <textarea class="ticket-form" id="deliver-note" name="deliver-note" style="width:258px;height:40px;"></textarea>
            </td>
        </tr>
        </tbody>
    </table>
    <div id="attachment">
        {$UPLOADER}
        <table id="filelist" style='border-collapse: collapse' class='Grid' border='0' cellspacing='0' cellpadding='2'>
        </table>
    </div> <br/>
    <div class="row">
        <div class="buttons">
            <input type="button" class="btn" id="btn_deliver" value="Gởi yêu cầu"/>
            <input type="button" class="btn" id="btnClose_delivery" value="Thoát" />
        </div>
    </div>
</div>

<!-- dialog customer detail view -->
<div id="customer_detail" title="THÔNG TIN KHÁCH HÀNG" style="display: none;">
    <table id="customer_detail_1" cellspacing="5" style="font-size: 13px">
    <tbody>
        <tr>
            <td width="25%">Tên khách hàng</td>
            <td width="25%">
                <input id="customer_detail_1-firstname" type="text" class="ticket-form"/>
            </td>
            <td width="15%">Mã khách hàng</td>
            <td width="25%">
                <input id="customer_detail_1-customer_code" type="text"  class="ticket-form"/>
                &nbsp;<span id="customer_detail_1-type"></span>
            </td>
        </tr>
        <tr>
            <td>Ngày sinh</td>
            <td>
                <input id="customer_detail_1-birthday" type="text"  class="ticket-form"/>
            </td>
        </tr>
        <tr>
            <td>Nơi sinh</td>
            <td>
                <input id="customer_detail_1-birthplace" type="text"  class="ticket-form"/>
            </td>
            <td>Email</td>
            <td>
                <input id="customer_detail_1-email" type="text"  class="ticket-form"/>
            </td>
        </tr>
        <tr>
            <td>Số điện thoại</td>
                <textarea class="ticket-form" id="customer_detail_1-number" cols="4"></textarea>
            </td>
            <td>Địa chỉ</td>
            <td>
                <textarea class="ticket-form" id="customer_detail_1-address" cols="4"></textarea>
            </td>
        </tr>
        <tr>
            <td>CMND</td>
            <td>
                <input id="customer_detail_1-cmnd" type="text"  class="ticket-form"/>
            </td>
            <td>Passport</td>
            <td>
                <input id="customer_detail_1-passport" type="text"  class="ticket-form"/>
            </td>
        </tr>
        <tr>
            <td>Công ty</td>
            <td>
                <input id="customer_detail_1-company" type="text"  class="ticket-form"/>
            </td>
            <td>Thẻ thành viên</td>
            <td>
                <input id="customer_detail_1-membership" type="text"  class="ticket-form"/>
            </td>
        </tr>
        <tr>
            <td>Booker</td>
            <td>
                <input id="customer_detail_1-agent" type="text"  class="ticket-form"/>
            </td>
            <td>Kinh doanh</td>
            <td>
                <input id="customer_detail_1-sale" type="text"  class="ticket-form"/>
            </td>
        </tr>
        <tr>
            <td>Kế toán</td>
            <td>
                <input id="customer_detail_1-agent" type="text"  class="ticket-form"/>
            </td>
            <td>Thanh toán</td>
            <td>
                <input id="customer_detail_1-payment" type="text"  class="ticket-form"/>
            </td>
        </tr>
    </tbody>
    </table>
    <br/>
    <div class="row">
        <div class="buttons">
            <input type="button" class="btn" id="btnClose_customer_detail" value="Thoát" />
        </div>
    </div>
</div>

<!-- dialog VIEW -->
<div id="dialog-view" title="Xem ghi chú" stype="dislay:none">
    <div class="row">
		<input type="text" id="view_note_id" style="display:none" />
        <textarea id="dialog-view-content" style="width:440px;height:168px;" readonly></textarea>
    </div>
    <div class="row">
        <div class="buttons">
			<input type="button" class="btn" id="btnEdit-dialog-view" value="Chỉnh sửa" style="display:none"/>
            <input type="button" class="btn" id="btnSave-dialog-view" value="Lưu" style="display:none"/>
			<input type="button" class="btn" id="btnClose-dialog-view" value="Thoát" />
        </div>
    </div>
</div>
<!-- dialog "Save" -->
<div id="dialog" title="LƯU THÔNG TIN KHÁCH HÀNG"  stype="dislay:none">
	<table style="font-size: 12px;"
    <tr>
        <td width="20%"><label for="customer_firstname" >Tên khách hàng: </label></td>
        <td><input type="text" id="customer_firstname" style="width:150px;" value="{$CUSTOMER_INFO.customer_name}"></td>
    </tr>
    <tr>
        <td width="20%"><label for="customer_phone">Số điện thoại: </label></td>
        <td><textarea id="customer_phone" style="width:150px;height:45px;">{$CUSTOMER_NUMBER}</textarea></td>
        <td><label for="customer_email">Email: </label></td>
        <td><input type="text" id="customer_email" style="width:150px;" value="{$CUSTOMER_INFO.email}"></td>
    </tr>
    <tr>
        <td><label for="customer_address">Địa chỉ: </label></td>
        <td><textarea id="customer_address" style="width:150px;height:45px;">{$CUSTOMER_INFO.address}</textarea></td>
        <td><label for="customer_membership">Thẻ thành viên: </label></td>
        <td><textarea id="customer_membership" style="width:150px;height:45px;">{$CUSTOMER_INFO.membership}</textarea></td>
    </tr>
    <tr>
        <td><label for="customer_birthday">Ngày sinh:</label></td>
        <td><input type="text" id="customer_birthday" style="width:150px;" value="{$CUSTOMER_INFO.birthday}"></td>
        <td><label for="customer_birthplace"> Nơi sinh: </label></td>
        <td><input type="text" id="customer_birthplace" style="width:150px;" value="{$CUSTOMER_INFO.birthplace}"></td>
    </tr>
    <tr>
		<td><label for="customer_cmnd">Số CMND: </label></td>
        <td><input type="text" id="customer_cmnd" style="width:150px;" value="{$CUSTOMER_INFO.cmnd}"></td>
		<td><label for="customer_passport" >Passport: </label></td>
        <td><input type="text" id="customer_passport" style="width:150px;" value="{$CUSTOMER_INFO.passport}"></td>
    </tr>
    </table>
    <br/>
    <div class="row">
        <div class="buttons">
            <input type="button" class="btn" id="btnSave" value="Lưu" />
            <input type="button" class="btn" id="btnClose" value="Thoát" />
        </div>
    </div>
</div>
<!-- end dialog -->

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

<!-- dialog VIEW -->
<div id="view_customer_dialog" title="Xem chi tiết thông tin khách hàng" stype="dislay:none">
    <h3 id="customer_type">Khách hàng lẻ</h3>
    <div id="KHCTY" stype="dislay:none">
        <div class="row">
            <label>Tên KH: </label>
            <input type="text" class="view_customer_fullname" readonly>
            <span> 	&nbsp;</span>
            <label>Mã khách hàng: </label>
            <input type="text" class="view_customer_customer_code" readonly>
        </div>
        <div class="row">
            <label >NV Booker: </label>
            <input type="text" class="view_customer_booker" readonly>
            <span> 	&nbsp;</span>
            <label >NV Kinh doanh: </label>
            <input type="text" class="view_customer_sale" readonly>
        </div>
        <div class="row">
            <label >NV kế toán: </label>
            <input type="text" class="view_customer_accoutant" readonly>
            <span> 	&nbsp;</span>
        </div>
        <div id="contact" class="ui-widget">
            <h3>LIÊN HỆ</h3>
            <table id="contact_table" class="ui-widget ui-widget-content" width="100%">
                <thead>
                <tr class="ui-widget-header ">
                    <th>Tên</th>
                    <th>Điện thoại</th>
                    <th>Email</th>
                </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div> <! -- end if KHCTY -- !>

    <div id="KHLE">
        <div class="row">
            <label>Tên KH: </label>
            <input type="text" class="view_customer_fullname" readonly>
            <span> 	&nbsp;</span>
            <label>Mã khách hàng: </label>
            <input type="text" class="view_customer_customer_code" readonly>
        </div>
        <div class="row">
            <label >NV Booker: </label>
            <input type="text" class="view_customer_booker" readonly>
            <span> 	&nbsp;</span>
            <label >NV Kinh doanh: </label>
            <input type="text" class="view_customer_sale" readonly>
        </div>
        <div class="row">
            <label >NV kế toán: </label>
            <input type="text" class="view_customer_accountant" readonly>
            <span> 	&nbsp;</span>
            <label >Hình thức TT: </label>
            <input type="text" class="view_customer_payment_type" readonly>
        </div>
        <div class="row">
            <label >Số điện thoại: </label>
            <input type="text" class="view_customer_phone" readonly>
            <span> 	&nbsp;</span>
            <label >Thẻ thành viên: </label>
            <input type="text" class="view_customer_membership" readonly>
        </div>
        <div class="row">
            <label >Địa chỉ: </label>
            <input type="text" class="view_customer_address" readonly>
            <span> 	&nbsp;</span>
        </div>
        <div class="row">
            <label >Ngày sinh: </label>
            <input type="text" id="view_customer_birthday" readonly>
            <span> 	&nbsp;</span>
            <label >Nơi sinh: </label>
            <input type="text" id="view_customer_birthplace" readonly>
        </div>
        <div class="row">
            <label >Số CMND: </label>
            <input type="text" id="view_customer_cmnd" readonly>
            <span> 	&nbsp;</span>
            <label >Số passport: </label>
            <input type="text" id="view_customer_passport" readonly>
        </div>
    </div> <! -- end if KHLE -- !>
    <span id="latest_note" style="font-color: red"></span>

    <div class="row">&nbsp;</div>
    <div class="row">
        <div class="buttons">
            <input type="button" class="btn" id="btnClose_view_customer_dialog" value="Thoát" />
        </div>
    </div>
</div>

<! --  NOTICE USER LOGOUT  -- !>
<div id="notice_logout" title="Thông báo" stype="dislay:none">
    <p>Mất kết nối với máy chủ. Bạn có muốn tải lại không?</p>
</div>
<! --  END OF NOTICE USER LOGOUT -- !>

<! --  AJAX LOADER  -- !>
<div id="waiting" title="Thông báo" stype="dislay:none">
    <span id="waiting_text">Vui lòng chờ... </span><br /><br />
    <img src="{$THEME_PATH}/images/ajax-loader.gif" title="Loader" alt="Loader"/>
</div>
<! --  END OF AJAX LOADER  -- !>

{literal}
<script type="text/javascript">
$(document).ready(function(){
    // uploader table
    ShowAttachmentsTable();
    /* history tab
    $("#tabs").tabs({
        add:	function (event, ui) {
            if (externalurl != null)
                $(ui.panel).append("<iframe scrolling=\"auto\" height=\"450\" frameborder=\"0\" width=\"100%\" src=\"" + externalurl + "\" />");
            externalurl = null;
        }
    }); */
	/* box & info collapse */
	$('#info dd:not(:first)').hide();
	$('#box dd:not(:first)').hide();
	$('dt .ic-minimize').click(function()  {
		if($(this).parent().hasClass('active')) {
			$(this).parent().next().slideUp('slow');
			$(this).attr("title","Expand");
			$(this).parent().removeClass('active');
		}
		else {
			$(this).parent().addClass('active').next().slideDown('slow');
			$(this).attr("title","Collapse");
		}
		return false;
	});
	/* tab ttv */
	$('#box dd.ttv #tabbed_nav .tabs a').click(function(){
		$('#box dd.ttv #tabbed_nav .tabs a.active').removeClass('active');
		$(this).addClass('active');
		$('#box dd.ttv #tabbed_nav .content').hide();
		var content_show = $(this).attr('title');
		$('#'+ content_show).show();

	});
	/* tab mca */
	$('#box dd.mca #tabbed_nav .tabs a').click(function(){
		$('#box dd.mca #tabbed_nav .tabs a.active').removeClass('active');
		$(this).addClass('active');
		$('#box dd.mca #tabbed_nav .content').hide();
		var content_show = $(this).attr('title');
		$('#'+ content_show).show();

	});

	/* ALL DIALOGS INITIALIZE*/
    $( "#customer-detail").dialog({
        autoOpen: false,
        modal: true,
        dialogClass: "delivery-dialog",
        position: ['top', 100]
    });
    $( "#ticket-delivery").dialog({
        autoOpen: false,
        modal: true,
        dialogClass: "delivery-dialog",
        position: ['top', 100],
        width: 500
    });
    $( "#dialog" ).dialog({
		autoOpen: false,
		modal: true,
		dialogClass: "save-dialog",
        position: ['top', 100],
		width: 580
	});
    $( "#waiting" ).dialog({
        autoOpen: false,
        modal: true,
        width: 186,
        position: ['top', 100]
    });
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
    $( "#view_customer_dialog" ).dialog({
        autoOpen: false,
        modal: true,
        dialogClass: "delivery-dialog",
        position: ['top', 100],
        width: 594
    });
    // notice user logout
    $("#notice_logout").dialog({
        autoOpen: false,
        modal: true,
        position: ['top', 100],
        width: 250,
        height: 90,
        buttons: [
            {
                text: 'Đồng ý',
                click: function() { reload(); $(this).dialog('close'); }
            },
            {
                text: 'Hủy bỏ',
                click: function() { $(this).dialog('close'); }
            }
        ]
    });
    /* END OF ALL DIALOGS */
    /* SAVE CUSTOMER DIALOG */
	$('#customer_birthday').datepicker({
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
	$('#btn-add-customer').click(function() {
		// disable/enable input number
        if ($('#btn-add-customer').val() == 'Cập nhật') {
            //$("#customer_phone").attr('readonly','');
            if ($(this).attr('customer_type')=='2'||$(this).attr('customer_type')=='3'){
                alert('Bạn không có quyền cập nhật khách hàng công ty hoặc đại lý!');
                return false;}
        }
        //else {
        //    $("#customer_phone").removeAttr("readonly",'');
        //}
        // set data for editing the existing one
        $("#customer_phone").val($("#customer_number").val());
        if ($("#customer_phone").val()=='')
            $("#customer_phone").focus();
        else
            $("#customer_firstname").focus();
        $('#customer_firstname').val("{/literal}{$CUSTOMER_INFO.customer_name}{literal}");
        $('#customer_birthday').val("{/literal}{$CUSTOMER_INFO.birthday}{literal}");
        $('#customer_birthplace').val("{/literal}{$CUSTOMER_INFO.birthplace}{literal}");
        $('#customer_address').val("{/literal}{$CUSTOMER_INFO.address}{literal}");
        $('#customer_cmnd').val("{/literal}{$CUSTOMER_INFO.cmnd}{literal}");
        $('#customer_passport').val("{/literal}{$CUSTOMER_INFO.passport}{literal}");
        $('#customer_membership').val("{/literal}{$CUSTOMER_INFO.membership}{literal}");
        $('#customer_email').val("{/literal}{$CUSTOMER_INFO.email}{literal}");
        $("#dialog").dialog( "open");
	});

    /* button close*/
    $("#btnClose_view_customer_dialog").click(function(){
        $("#view_customer_dialog").dialog("close");
    });

    $("#btnClose").click(function(){
		$("#dialog").dialog("close");
	});

    $("#btnClose_customer_detail").click(function(){
        $("#customer-detail").dialog("close");
    });
	// view note button
    $("#btnClose-dialog-view").click(function(){
        $('#dialog-view-content').attr('readonly',true);
		$("#btnEdit-dialog-view").hide();
		$("#btnSave-dialog-view").hide();
        $('#dialog-view').dialog('option', 'title', 'Xem ghi chú');
		$("#dialog-view").dialog("close");
    });
	$("#btnEdit-dialog-view").click(function(){
		$("#btnSave-dialog-view").show();
		$(this).hide();	
		$('#dialog-view-content').removeAttr('readonly');
        $('#dialog-view').dialog('option', 'title', 'Sửa ghi chú cuộc gọi');
    });
	$("#btnSave-dialog-view").click(function(){
        editNote();
		$(this).hide();
		$("#btnEdit-dialog-view").hide();
		$("#btnEdit-dialog-view").show();
		$("#dialog-view").dialog("close");
        do_refresh_call_history();
    });	
    $("#btnClose-dialog-delivery").click(function(){
        $("#dialog-delivery").dialog("close");
    });
    $("#btnClose_delivery").click(function(){
        $("#ticket-delivery").dialog("close");
    });

	/* suggestion */
	$( "#transfer_number,#call-number").autocomplete({
		source: "modules/agent_console/suggesstions.php"
	});

    $('#call-number').keydown(function(e){if(e.keyCode==13) btn_call_click()});

    /*  grouping on key up */
    $('#deliver-price,#deliver-tax,#deliver-rate').keyup(function(){
        format(this);
        var price = 0
        if ($('#deliver-price').val().trim()!='')
            price = parseFloat($('#deliver-price').val().replace(/,/g, ''));
        var tax = 0;
        if ($('#deliver-tax').val().trim()!='')
            tax =  parseFloat($('#deliver-tax').val().replace(/,/g, ''));        
        var rate = 0;
        if ($('#deliver-rate').val().trim()!='')
            rate = parseFloat($('#deliver-rate').val().replace(/,/g, ''));

        var total = (price + tax) * rate;
        $('#deliver-pay').val(total);        		
		var pay_input = document.getElementById('deliver-pay');
		format(pay_input);		
    });			

	$("#deliver-pay").on('change keyup paste', function() {
		format(this);
	} );

    $('#btn_external_note').click(function(){
        $.post('index.php?menu=' + module_name + '&rawmode=yes', {
                action:		'addExternalNote',
                callerid:   $('#customer_number').val(),
                note:       $('#description').val()
            },
            function (response) {
                if (response['action'] == 'error') {
                    mostrar_mensaje_error(response['message']);
                }
                else {
                    mostrar_mensaje_info(response['message']);
                }
            });
    });

    $('#btn_guardar_formularios').click(function(){
        if ($('#call-id').val() == '') {
            mostrar_mensaje_error('Không tìm thấy ID cuộc gọi gần nhất. Vui lòng lưu ở cuộc gọi ngoài!');
        }
        else {
            $.post('index.php?menu=' + module_name + '&rawmode=yes', {
                        action:		'addNote',
                        callid:	    $('#call-id').val(),
                        note:       $('#description').val()
                    },
                    function (response) {
                        if (response['action'] == 'error') {
                            mostrar_mensaje_error(response['message']);
                        }
                        else {
                            mostrar_mensaje_info(response['message']);
                            do_refresh_call_history();
                        }
                    });
            if (($('#btn-add-customer').val() == 'Thêm mới') && $('#customer_name').val().trim() != '(không biết)' && $('#customer_name').val().trim() != '') {
                $.post('index.php?menu=' + module_name + '&rawmode=yes', {
                        action:		'addCustomer',
                        phone:		$('#customer_number').val(),
                        firstname:  $('#customer_name').val()
                    },
                    function (response) {
                        // update customer box
                        update_customer($('#customer_number').val());
                });
            }
        }
    });
    // button delivery request
    $('#btn_delivery_request').click(function(){
        done_add_delivery();
        $('#deliver-name').val($('#customer_name').val());
        $('#deliver-phone').val($('#customer_number').val());        
        $('#deliver-address').val({/literal}'{$CUSTOMER_INFO.address}{literal}')
        $( "#ticket-delivery").dialog('open');
    });
    // button submit delivery request
    $('#btn_deliver').click(function(){
		if (!validateDeliveryForm())
			return false;
        if ($('#isInvoice').attr('checked'))
            checkedInvoice = '1';
        else
            checkedInvoice = '0';
        $.post('index.php?menu=' + module_name + '&rawmode=yes', {
            action:		'addDelivery',
            ticket_id:  $('#deliver-id').val(),
            callid:	    $('#call-id').val(),
            agentid:	$('#agent-id').val(),
            name:       $('#deliver-name').val(),
            phone:      $('#deliver-phone').val(),
            price:      $('#deliver-price').val(),
            // depreciated discount value
            //discount:   $('#deliver-discount').val(),
            note:		$('#deliver-note').val(),
            isInvoice:	checkedInvoice,
            rate:       $('#deliver-rate').val(),
            pay:        $('#deliver-pay').val(),
            code:       $('#deliver-code').val(),
            address:    $('#deliver-address').val(),
            call_phone: $('#customer_number').val(),
            tax:		$('#deliver-tax').val(),
            attachment:  fileArray
        },
        function (response) {
            if (response['action'] == 'error') {
                mostrar_mensaje_error(response['message']);
            }
            else {
                mostrar_mensaje_info(response['message']);
                done_add_delivery();
                do_refresh_call_history();
                $("#ticket-delivery").dialog('close');
            }
        });
    });
});
</script>
{/literal}