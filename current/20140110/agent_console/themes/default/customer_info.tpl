{if isset($CUSTOMER_INFO.id)}
    <table class="tb-detail-left" width="50%">
        <tbody><tr>
            <td width="30%" valign="top" ><b>Tên KH:</b></td>
            <td>{$CUSTOMER_INFO.lastname} {$CUSTOMER_INFO.firstname}</td>
        </tr>
        <tr>
            <td valign="top" ><b>Phân loại:</b></td>
            <td>{$CUSTOMER_INFO.type_name}</td>
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
            <td valign="top" ><b>Điện thoại:</b></td>
            <td>{$CUSTOMER_INFO.phone}</td>
        </tr>
        <tr>
            <td valign="top" ><b>Thành viên:</b></td>
            <td>{$CUSTOMER_INFO.membership}</td>
        </tr>

        </tbody></table><!-- noi dung thong tin paylink -->
    <table class="tb-detail-right" width="50%">
        <tbody>
        <tr>
            <td valign="top" width="30%"><b>Mã KH:</b></td>
            <td>{$CUSTOMER_INFO.customer_code}</td>
        </tr>
        <tr>
            <td valign="top" ><b>Booker:</b></td>
            <td>{$CUSTOMER_INFO.agent}</td>
        </tr>
        <tr>
            <td valign="top" ><b>Thanh toán:</b></td>
            <td>{$CUSTOMER_INFO.payment}</td>
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
{/if}