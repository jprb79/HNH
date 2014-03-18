<input type='hidden' name='id' value='{$ID}' />
<table width="99%" border="0" cellspacing="0" cellpadding="0" align="center">
    <tr>
	{if ($mode ne 'view')}
	    <td align="right" nowrap><span class="letra12"><span  >*</span> {$REQUIRED_FIELD}</span></td>
	{/if}
    </tr>
    <tr>
        <td  colspan='2'>
            <table width="100%" cellpadding="4" cellspacing="0" border="0" class="tabForm">
                <tr>
                    <td>
                        <table>
                            <tr class="letra12">
                                <td width="180px">
                                    <input type="radio" name="customer_type" id="khach_hang_le" value="0" {$check_0} onclick="Activate_Option_Address_Book()"
                                            {if $Edit}disabled{/if}/>
                                    Khách hàng lẻ
                                </td>
                                <td>
                                    <input type="radio" name="customer_type" id="khach_hang_le_thuong_xuyen" value="1" {$check_1} onclick="Activate_Option_Address_Book()"
                                            {if $Edit}disabled{/if}/>
                                    Khách hàng lẻ thường xuyên
                                </td>
                            </tr>
                            <tr class="letra12">
                                <td>
                                    <input type="radio" name="customer_type" id="khach_hang_cong_ty" value="2" {$check_2} onclick="Activate_Option_Address_Book()"
                                            {if $Edit}disabled{/if}/>
                                    Khách hàng công ty
                                </td>
                                <td>
                                    <input type="radio" name="customer_type" id="khach_hang_dai_ly" value="3" {$check_3} onclick="Activate_Option_Address_Book()"
                                            {if $Edit}disabled{/if}/>
                                    Khách hàng đại lý
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2" align="center">
                                    <img alt="image" src="modules/{$MODULE_NAME}/images/Icon-user.png"/>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2" style="font-size: 12px" align="center">
                                {if $Show}
                                    <input class="button" type="submit" name="save" value="{$SAVE}">
                                {elseif $Edit}
                                    <input class="button" type="submit" name="edit" value="{$EDIT}">
                                {elseif $Commit}
                                    <input class="button" type="submit" name="commit" value="{$SAVE}">
                                {/if}
                                    <input class="button" type="submit" name="cancel" value="{$CANCEL}">
                                </td>
                            </tr>
                        </table>
                    </td>
                    <td  id="td_new_KHLE">
                        <table class="letra12" width="800px" cellpadding="4" cellspacing="0" border="0">
                            <tr>
                                <td align="left" width="20%"><b>{$customer_code.LABEL}: </b></td>
                                <td  align="left">{$customer_code.INPUT}</td>
                                <td align="left"><b>{$payment_type.LABEL}: </b></td>
                                <td  align="left">{$payment_type.INPUT}</td>
                            </tr>
                            <tr>
                                <td class="required" align="left" width="20%"><b>{$firstname.LABEL}: {if ($mode ne 'view')}<span  >*</span>{/if}</b></td>
                                <td align="left">{$firstname.INPUT}</td>
                                <td align="left" width="20%"><b>{$booker.LABEL}: </b></td>
                                <td  align="left">{$booker.INPUT}</td>
                            </tr>
                            <tr>
                                <td align="left" width="20%"><b>{$lastname.LABEL}: </b></td>
                                <td  align="left">{$lastname.INPUT}</td>
                                <td align="left" width="20%"><b>{$sale.LABEL}: </b></td>
                                <td  align="left">{$sale.INPUT}</td>
                            </tr>
                            <tr>
                                <td align="left" width="20%"><b>{$accountant.LABEL}: </b></td>
                                <td  align="left">{$accountant.INPUT}</td>
                                <td align="left" width="20%"><b>{$membership.LABEL}: </b></td>
                                <td  align="left">{$membership.INPUT}</td>
                            </tr>
                            <tr id='tr_phone'>
                                <td class="required" align="left" width="20%"><b>{$phone.LABEL}: {if ($mode ne 'view')}<span  >*</span>{/if}</b></td>
                                <td  align="left">{$phone.INPUT}</td>
                                <td align="left"><b>{$address.LABEL}: </b></td>
                                <td  align="left">{$address.INPUT}</td>
                            </tr>

                            <tr>
                                <td align="left"><b>{$company.LABEL}: </b></td>
                                <td  align="left">{$company.INPUT}</td>
                                <td align="left" width="20%"><b>{$email.LABEL}:</b></td>
                                <td  align="left">{$email.INPUT}</td>
                            </tr>
                            <tr>
                                <td align="left" width="20%"><b>{$birthday.LABEL}: </b></td>
                                <td  align="left">{$birthday.INPUT}</td>
                                <td align="left" width="20%"><b>{$birthplace.LABEL}: </b></td>
                                <td  align="left">{$birthplace.INPUT}</td>
                            </tr>
                            <tr>
                                <td align="left" width="20%"><b>{$cmnd.LABEL}: </b></td>
                                <td  align="left">{$cmnd.INPUT}</td>
                                <td align="left" width="20%"><b>{$passport.LABEL}: </b></td>
                                <td  align="left">{$passport.INPUT}</td>
                            </tr>
                        </table>
                    </td>
                    <td id="td_new_KHCTY">
                        <table class="letra12" width="800px" cellpadding="4" cellspacing="0" border="0">
                            <tr>
                                <td class="required" align="left" width="20%"><b>{$company_name.LABEL}: {if ($mode ne 'view')}<span  >*</span>{/if}</b></td>
                                <td  align="left">{$company_name.INPUT}</td>
                                <td align="left" width="20%"><b>{$company_code.LABEL}: </b></td>
                                <td  align="left">{$company_code.INPUT}</td>

                            </tr>
                            <tr>
                                <td align="left" width="20%"><b>{$company_booker.LABEL}: </b></td>
                                <td  align="left">{$company_booker.INPUT}</td>
                                <td align="left" width="20%"><b>{$company_sale.LABEL}: </b></td>
                                <td  align="left">{$company_sale.INPUT}</td>
                            </tr>
                            <tr>
                                <td align="left" width="20%"><b>{$company_accountant.LABEL}: </b></td>
                                <td  align="left">{$company_accountant.INPUT}</td>
                                <td align="left"><b>{$company_pay_type.LABEL}: </b></td>
                                <td  align="left">{$company_pay_type.INPUT}</td>
                            </tr>
                            <tr>
                                <td align="left"><b>{$company_address.LABEL}: </b></td>
                                <td  align="left">{$company_address.INPUT}</td>
                                <td align="left"><b>{$company_membership.LABEL}: </b></td>
                                <td  align="left">{$company_membership.INPUT}</td>
                            </tr>
                            <tr>
                                <td colspan="4">
                                    <hr>
                                </td>
                            </tr>
                            <tr>
                                <td VALIGN="top"><b>Người liên hệ: </b>
                                    {if !$Edit}
                                        <br/><a href="#" id="add_more_btn"><img alt="Add" src="modules/{$MODULE_NAME}/images/add.png" title="Thêm người liên hệ"/>&nbsp;Thêm</a>
                                    {/if}
                                </td>
                                <td colspan="3">
                                    {if $Edit}
                                        {foreach from=$CONTACT key=ID_CONTACT item=ROW}
                                            <table class="letra12 table_contact">
                                                <tr>
                                                    <td align="left"><b>Tên: </b></td>
                                                    <td  align="left">{$ROW.name}</td>
                                                    <td align="left"><b>Điện thoại: </b></td>
                                                    <td  align="left">{$ROW.phone}</td>
                                                    <td align="left"><b>Email: </b></td>
                                                    <td  align="left">{$ROW.email}</td>
                                                </tr>
                                            </table>
                                        {/foreach}
                                    {/if}
                                    {if $Commit}
                                        {foreach from=$CONTACT key=ID_CONTACT item=ROW}
                                            <table class="letra12 table_contact">
                                                <tr>
                                                    <td align="left"><b>Tên: </b></td>
                                                    <td  align="left"><input type="text" style="width:120px;" value="{$ROW.name}" name="contact_name[]"></td>
                                                    <td align="left"><b>Điện thoại: </b></td>
                                                    <td  align="left"><input type="text" style="width:120px;" value="{$ROW.phone}" name="contact_phone[]"></td>
                                                    <td align="left"><b>Email: </b></td>
                                                    <td  align="left"><input type="text" style="width:120px;" value="{$ROW.email}" name="contact_email[]"></td>
                                                    <td>
                                                        <a href="#" class="remove_btn"><img alt="Remove" src="modules/{$MODULE_NAME}/images/delete.png" title="Xóa người liên hệ"/></a>
                                                    </td>
                                                </tr>
                                            </table>
                                        {/foreach}
                                    {/if}
                                    <div id="list"></div>
                                    <table class="letra12 table_contact" id="contact_seed" style="display: none;">
                                        <tr>
                                            <td align="left"><b>Tên: </b></td>
                                            <td  align="left"><input type="text" style="width:120px;" value="" name="contact_name[]"></td>
                                            <td align="left"><b>Điện thoại: </b></td>
                                            <td  align="left"><input type="text" style="width:120px;" value="" name="contact_phone[]"></td>
                                            <td align="left"><b>Email: </b></td>
                                            <td  align="left"><input type="text" style="width:120px;" value="" name="contact_email[]"></td>
                                            <td>
                                                <a href="#" class="remove_btn"><img alt="Remove" src="modules/{$MODULE_NAME}/images/delete.png" title="Xóa người liên hệ"/></a>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </td>
        </td>
    </tr>

                </tr>
            </table>
        </td>
    </tr>
</table>

{literal}
    <script type="text/javascript">
        Activate_Option_Address_Book();
        function Activate_Option_Address_Book()
        {
            var chk_kh_le = document.getElementById('khach_hang_le');
            var chk_kh_le_2 = document.getElementById('khach_hang_le_thuong_xuyen');
            if(chk_kh_le.checked==true || chk_kh_le_2.checked==true)
            {
                document.getElementById('td_new_KHLE').style.display = '';
                document.getElementById('td_new_KHCTY').style.display = 'none';
            }
            else
            {
                document.getElementById('td_new_KHLE').style.display = 'none';
                document.getElementById('td_new_KHCTY').style.display = '';
            }
        }

        $(document).ready(function(){
            $('#birthday').datepicker({
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
            $('.button').button();
            var index = 0;
            var seed = $("#contact_seed").clone();
            $("#contact_seed").remove();

            $("#add_more_btn").click(function(e) {
                e.preventDefault();
                $("#list").append(seed.clone());
                $("#contact_seed").show();
                $("#contact_seed").attr("id", "contact_row_" + index);
                index++;
            });

            $("a.remove_btn").live("click", function(e) {
                e.preventDefault();
                $(this).parents("table.table_contact").fadeOut("fast", function() {
                    $(this).remove();
                });
            });
        });
    </script>
{/literal}