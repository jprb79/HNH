<form  method='POST' enctype='multipart/form-data' style='margin-bottom:0;' action='?menu=customer'>
<table width="99%" border="0" cellspacing="0" cellpadding="0" align="center">
    <tr>
        <td align="right" nowrap><span class="letra12"><span  >*</span> Bắt buộc</span></td>
        <td><input type='hidden' name='id' value='' /></td>
    </tr>
    <tr class="letra12">
        <table>
            <tr class="letra12">
                <td width="180px">
                    <input type="radio" name="customer_type" id="khach_hang_le" value="0"  onclick="Activate_Option_Address_Book()" />
                    Khách hàng lẻ
                </td>
                <td>
                    <input type="radio" name="customer_type" id="khach_hang_le_thuong_xuyen" value="1"  onclick="Activate_Option_Address_Book()" />
                    Khách hàng lẻ thường xuyên
                </td>
            </tr>
            <tr class="letra12">
                <td>
                    <input type="radio" name="customer_type" id="khach_hang_cong_ty" value="2" checked onclick="Activate_Option_Address_Book()"/>
                    Khách hàng công ty
                </td>
                <td>
                    <input type="radio" name="customer_type" id="khach_hang_dai_ly" value="3"  onclick="Activate_Option_Address_Book()" />
                    Khách hàng đại lý
                </td>
            </tr>
        </table>
    </tr>
    <tr>
        <td  colspan='2'>
            <table width="100%" cellpadding="4" cellspacing="0" border="0" class="tabForm">
                <tr>
                    <td align="center">
                        <img alt="image" src="modules/customer/images/Icon-user.png" style="width:200px;"/>
                        <br/>
                        <table align="center" style="font-size:12px"><tr>
                                <td>
                                    <input class="button" type="submit" name="save" value="Lưu">
                                </td>
                                <td>
                                    <input class="button" type="submit" name="cancel" value="Hủy bỏ">
                                </td></tr>
                        </table>
                    </td>
                    <td  id="td_new_KHLE">
                        <table class="letra12" width="800px" cellpadding="4" cellspacing="0" border="0">
                            <tr>
                                <td align="left" width="20%"><b>M&atilde; kh&aacute;ch h&agrave;ng: </b></td>
                                <td  align="left"><input type="text" name="customer_code" value="" style="width:200px;" /></td>
                                <td align="left"><b>Thanh to&aacute;n: </b></td>
                                <td  align="left"><select name="customer_pay_type" id="customer_pay_type"   ><option value="0" selected="selected">Tiền mặt</option>
                                        <option value="1" >C&ocirc;ng nợ</option>
                                        <option value="2" >(Kh&ocirc;ng)</option></select></td>
                            </tr>
                            <tr>
                                <td class="required" align="left" width="20%"><b>T&ecirc;n kh&aacute;ch h&agrave;ng: <span  >*</span></b></td>
                                <td align="left"><input type="text" name="firstname" value="" style="width:200px;" id="firstname" /></td>
                                <td align="left" width="20%"><b>NV Booker: </b></td>
                                <td  align="left"><select name="booker" id="booker"   ><option value="0" selected="selected">(Kh&ocirc;ng chọn)</option>
                                        <option value="1" >b1</option>
                                        <option value="2" >b2</option></select></td>
                            </tr>
                            <tr>
                                <td align="left" width="20%"><b>Họ v&agrave; t&ecirc;n l&oacute;t: </b></td>
                                <td  align="left"><input type="text" name="lastname" value="" style="width:200px;" /></td>
                                <td align="left" width="20%"><b>NV Sale: </b></td>
                                <td  align="left"><input type="text" name="sale" value="" style="width:200px;" /></td>
                            </tr>
                            <tr id='tr_phone'>
                                <td class="required" align="left" width="20%"><b>Số điện thoại: <span  >*</span></b></td>
                                <td  align="left"><textarea name="phone" rows="3" cols="20" style="width:200px;"></textarea></td>
                                <td align="left"><b>Địa chỉ: </b></td>
                                <td  align="left"><textarea name="address" rows="3" cols="20" style="width:200px;"></textarea></td>
                            </tr>

                            <tr>
                                <td align="left"><b>C&ocirc;ng ty: </b></td>
                                <td  align="left"><input type="text" name="company" value="" style="width:200px;" /></td>
                                <td align="left" width="20%"><b>Email:</b></td>
                                <td  align="left"><input type="text" name="email" value="" style="width:200px;" /></td>
                            </tr>
                            <tr>
                                <td align="left" width="20%"><b>Ng&agrave;y sinh: </b></td>
                                <td  align="left"><input type="text" name="birthday" value="" style="width:200px;" id="birthday" /></td>
                                <td align="left" width="20%"><b>Nơi sinh: </b></td>
                                <td  align="left"><input type="text" name="birthplace" value="" style="width:200px;" /></td>
                            </tr>
                            <tr>
                                <td align="left" width="20%"><b>Số CMND: </b></td>
                                <td  align="left"><input type="text" name="cmnd" value="" style="width:200px;" /></td>
                                <td align="left" width="20%"><b>Số Passport: </b></td>
                                <td  align="left"><input type="text" name="passport" value="" style="width:200px;" /></td>
                            </tr>
                        </table>
                    </td>
                    <td id="td_new_KHCTY">
                        <table class="letra12" width="800px" cellpadding="4" cellspacing="0" border="0">
                            <tr>
                                <td align="left" width="20%"><b>M&atilde; kh&aacute;ch h&agrave;ng: </b></td>
                                <td  align="left"><input type="text" name="company_code" value="" style="width:200px;" /></td>
                                <td align="left" width="20%"><b>NV Booker: </b></td>
                                <td  align="left"><select name="company_booker" id="company_booker"   ><option value="0" selected="selected">(Kh&ocirc;ng chọn)</option>
                                        <option value="1" >b1</option>
                                        <option value="2" >b2</option></select></td>
                            </tr>
                            <tr>
                                <td class="required" align="left" width="20%"><b>T&ecirc;n kh&aacute;ch h&agrave;ng: <span  >*</span></b></td>
                                <td  align="left"><input type="text" name="company_name" value="" style="width:200px;" id="company_name" /></td>
                                <td align="left" width="20%"><b>NV Sale: </b></td>
                                <td  align="left"><input type="text" name="sale" value="" style="width:200px;" /></td>
                            </tr>
                            <tr>
                                <td align="left"><b>Địa chỉ: </b></td>
                                <td  align="left"><textarea name="address" rows="3" cols="20" style="width:200px;"></textarea></td>
                                <td align="left"><b>Thẻ th&agrave;nh vi&ecirc;n: </b></td>
                                <td  align="left"><textarea name="company_membership" rows="3" cols="20" style="width:200px;"></textarea></td>
                            </tr>
                            <tr>
                                <td align="left"><b>Thanh to&aacute;n: </b></td>
                                <td  align="left"><select name="company_pay_type" id="company_pay_type"   ><option value="0" selected="selected">(Chọn h&igrave;nh thức thanh to&aacute;n)</option>
                                        <option value="1" >Tiền mặt</option>
                                        <option value="2" >C&ocirc;ng nợ</option>
                                        <option value="3" >Cọc 20,000,000</option></select></td>
                            </tr>
                            <tr>
                                <td colspan="4">
                                    <hr>
                                </td>
                            </tr>
                            <tr>
                                <td VALIGN="top"><b>Người liên hệ: </b>
                                    <br/><a href="#" id="add_more_btn"><img alt="Add" src="modules/customer/images/add.png" title="Thêm người liên hệ"/>&nbsp;Thêm</a>
                                </td>
                                <td colspan="3">
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
                                                    <a href="#" class="remove_btn"><img alt="Remove" src="modules/customer/images/delete.png" title="Xóa người liên hệ"/></a>
                                                </td>
                                            </tr>
                                        </table>
                                </td>
                            </tr>
                        </table>
                    </td>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</form>

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