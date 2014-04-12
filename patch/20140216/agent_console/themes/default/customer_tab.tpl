<link rel="stylesheet" type="text/css" href="{$TEMP_DIR}/flexigrid/css/flexigrid.css">
<script type="text/javascript" src="{$TEMP_DIR}/flexigrid/js/flexigrid.js"></script>
    
<table oncopy="return false" oncut="return false" oncontextmenu="return false" id="customer_tab" style="display:none"></table>
{literal}
<script type="text/javascript">
    $("#customer_tab").flexigrid({
        url: 'modules/agent_console/customer_tab.php',
        //url: 'index.php?menu=agent_console&action=customer_tab',
        dataType: 'json',
        colModel : [
            {display: '<b>Mã KH</b>', name : 'customer_code', width : 50, sortable : false, align: 'right'},
            {display: '<b>Tên</b>', name : 'firstname', width : 150, sortable : true, align: 'left'},
            {display: '<b>Họ và tên đệm</b>', name : 'lastname', width : 100, sortable : true, align: 'left'},
            {display: '<b>Điện thoại</b>', name : 'phone', width : 160, sortable : false, align: 'left'},
            {display: '<b>Email</b>', name : 'email', width : 200, sortable : false, align: 'left'},
            {display: '<b>Booker</b>', name : 'agent_id', width : 80, sortable : true, align: 'left'},
            {display: '<b>Kinh doanh</b>', name : 'sale', width : 80, sortable : true, align: 'left'},
            {display: '<b>Kế toán</b>', name : 'accountant', width : 80, sortable : true, align: 'left'},
            {display: '<b>Phân loại</b>', name : 'type', width : 80, sortable : false, align: 'left'},
            {display: '<b>Thẻ thành viên</b>', name : 'membership', width : 80, sortable : true, align: 'right'},
            {display: '<b>Hình thức thanh toán</b>', name : 'payment_type', width : 50, sortable : true, align: 'left'},
            {display: '<b>Xem</b>', name : 'view', width : 50, sortable : false, align: 'left'}
        ],
        buttons : [

        ],
        searchitems : [
            {display: 'Tên khách hàng', name : 'firstname', isdefault: true},
            {display: 'Số điện thoại', name : 'phone'},
            {display: 'Mã khách hàng', name : 'customer_code'},
            {display: 'Email', name : 'email'}
                /*
            {display: 'Booker', name : 'agent_id'},
            {display: 'Kinh doanh', name : 'sale'},
            {display: 'Kế toán', name : 'accountant'},
            {display: 'Loại khách hàng', name : 'type'} */
        ],
        sortname: "firstname",
        sortorder: "asc",
        usepager: true,
        title: 'Danh sách khách hàng',
        useRp: true,
        rp: 10,
        showTableToggleBtn: true,
        height: 300
    });

    function addExtraSelect() {
        var elem_text = $("#customer_tab").parents(".flexigrid").find(".sDiv2 .qsbox");
        elem_text.attr("id", "customer_search_text");
        var elem = $("#customer_tab").parents(".flexigrid").find(".sDiv2 select[name='qtype']");
        if (elem.length > 0) {
            elem.after("<select id='customer_booker_select' name='booker' style='display: none;'></select>" +
                    " <select id='customer_type_select' name='type' style='display: none;'></select>");
            elem.attr("id", "customer_field_select");
            // add agent list
            $.post('modules/agent_console/customer_tab.php', {
                        booker_list:	'yes'
                    },
                    function (response) {
                        for (var key in response){
                            $("#customer_booker_select").prepend("<option value='"+key+"'>"+response[key]+"</option>");
                        }
                    });
            // add list for customer type
            $("#customer_type_select").prepend("<option value='0'>Khách lẻ không thường xuyên</option>");
            $("#customer_type_select").prepend("<option value='1'>Khách lẻ thường xuyên</option>");
            $("#customer_type_select").prepend("<option value='2'>Khách hàng công ty</option>");
            $("#customer_type_select").prepend("<option value='3'>Khách hàng đại lý</option>");
        } else {
            setTimeout(function() {addExtraSelect()}, 500);
        }
    }
    //addExtraSelect();

    $("#customer_field_select").live("change", function() {
        if ($(this).val()=='agent_id'){
            $('#customer_search_text').hide();
            $('#customer_type_select').hide();
            $("#customer_booker_select").show();
            $('#customer_search_text').val($('#customer_booker_select').val());
        }
        else if ($(this).val()=='type'){
            $('#customer_search_text').hide();
            $('#customer_type_select').show();
            $("#customer_booker_select").hide();
            $('#customer_search_text').val($('#customer_type_select').val());
        }
        else {
            $('#customer_search_text').show();
            $('#customer_type_select').hide();
            $("#customer_booker_select").hide();
            $('#customer_search_text').val('');
        }
    });
    $('#customer_booker_select').live("change",function (){
        $('#customer_search_text').val($(this).val());
    });
    $('#customer_type_select').live("change",function (){
        $('#customer_search_text').val($(this).val());
    });

</script>
{/literal}