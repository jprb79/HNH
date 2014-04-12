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
                                <td>&nbsp;</td>
                                <td width="180px">
                                    <input type="radio" name="customer_type" id="khach_hang_le" value="0" {$check_0} onclick="Activate_Option_Address_Book()"
                                            {if $Edit}disabled{/if}/>
                                    Khách hàng lẻ
                                </td>
                            </tr>
                            <tr class="letra12">
                                <td>&nbsp;</td>
                                <td>
                                    <input type="radio" name="customer_type" id="khach_hang_cong_ty" value="2" {$check_2} onclick="Activate_Option_Address_Book()"
                                            {if $Edit}disabled{/if}/>
                                    Khách hàng công ty
                                </td>
                            </tr>
                            <tr class="letra12">
                                <td>&nbsp;</td>
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
                    <td  id="td_new_KH">
                        <table class="letra12" width="800px" cellpadding="4" cellspacing="0" border="0">
                            <tr>
                                <td align="left" width="20%"><b>{$customer_code.LABEL}: </b></td>
                                <td  align="left">{$customer_code.INPUT}</td>
                                <td align="left" width="20%"><b>{$booker_view.LABEL}: </b></td>
                                <td  align="left">{$booker_view.INPUT}</td>

                            </tr>
                            <tr>
                                <td class="required" align="left" width="20%"><b>{$customer_name.LABEL}: {if ($mode ne 'view')}<span  >*</span>{/if}</b></td>
                                <td align="left">{$customer_name.INPUT}</td>
                                <td align="left" width="20%"><b>{$accountant_view.LABEL}: </b></td>
                                <td  align="left">{$accountant_view.INPUT}</td>
                            </tr>
                            <tr>
                                <td align="left" width="20%"><b>{$address.LABEL}: </b></td>
                                <td  align="left">{$address.INPUT}</td>
                                <td align="left" width="20%"><b>{$membership.LABEL}: </b></td>
                                <td  align="left">{$membership.INPUT}</td>
                            </tr>
                            <tr>
                                <td align="left" width="20%"><b>{$district_view.LABEL}: </b></td>
                                <td  align="left">{$district_view.INPUT}</td>
                                <td align="left" width="20%"><b>{$province_view.LABEL}: </b></td>
                                <td  align="left">{$province_view.INPUT}</td>
                            </tr>

                            <tr>
                                <td align="left" width="20%"><b>{$sale_view.LABEL}: </b></td>
                                <td  align="left">{$sale_view.INPUT}</td>
                                <td  class="new_khle" align="left" width="20%"><b>{$email.LABEL}:</b></td>
                                <td  class="new_khle" align="left">{$email.INPUT}</td>
                            </tr>
                            <tr class="new_khle">
                                <td align="left" width="20%"><b>{$birthday.LABEL}: </b></td>
                                <td  align="left">{$birthday.INPUT}</td>
                                <td align="left" width="20%"><b>{$birthplace.LABEL}: </b></td>
                                <td  align="left">{$birthplace.INPUT}</td>
                            </tr>
                            <tr class="new_khle">
                                <td align="left" width="20%"><b>{$cmnd.LABEL}: </b></td>
                                <td  align="left">{$cmnd.INPUT}</td>
                                <td align="left" width="20%"><b>{$passport.LABEL}: </b></td>
                                <td  align="left">{$passport.INPUT}</td>
                            </tr>
                            <tr>
                                <td colspan="4">
                                    <hr>
                                </td>
                            </tr>
                            <tr>
                                <td VALIGN="top"><b>Liên hệ: </b>
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
            if(chk_kh_le.checked==true)
            {
                var elements = document.getElementsByClassName('new_khle');
                for(var i = 0, length = elements.length; i < length; i++) {
                    elements[i].style.display = '';
                }
            }
            else{
                var elements = document.getElementsByClassName('new_khle');
                for(var i = 0, length = elements.length; i < length; i++) {
                    elements[i].style.display = 'none';
                }
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