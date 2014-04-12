{*
<link rel="stylesheet" type="text/css" href="{$TEMP_DIR}/flexigrid/css/flexigrid.css">
<script type="text/javascript" src="{$TEMP_DIR}/flexigrid/js/flexigrid.js"></script>
<table id="customer_tab" style="display:none"></table>
*}
<table id="phonebook_tab" style="display:none"></table>
{literal}
<script type="text/javascript">
    $("#phonebook_tab").flexigrid({
        url: 'modules/agent_console/phonebook_tab.php',
        dataType: 'json',
        colModel : [
            {display: '<b>STT</b>', name : 'stt', width : 40, sortable : false, align: 'left'},
            {display: '<b>Tên</b>', name : 'firstname', width : 50, sortable : true, align: 'left'},
            {display: '<b>Họ</b>', name : 'lastname', width : 100, sortable : true, align: 'left'},
            {display: '<b>Phòng - Công ty</b>', name : 'department', width : 180, sortable : false, align: 'left'},
            {display: '<b>Số công ty</b>', name : 'company_mobile', width : 120, sortable : false, align: 'left'},
            {display: '<b>Số di động</b>', name : 'mobile', width : 120, sortable : false, align: 'left'},
            {display: '<b>Số nội bộ</b>', name : 'extension', width : 120, sortable : true, align: 'left'},
            {display: '<b>Email</b>', name : 'email', width : 180, sortable : true, align: 'right'},
            {display: '<b>Ghi chú</b>', name : 'note', width : 200, sortable : false, align: 'right', hide: true}
        ],
        buttons : [
            {name: 'All', onpress: sortAlpha},
            {name: 'A', onpress: sortAlpha},
            {name: 'B', onpress: sortAlpha},
            {name: 'C', onpress: sortAlpha},
            {name: 'D', onpress: sortAlpha},
            {name: 'E', onpress: sortAlpha},
            {name: 'F', onpress: sortAlpha},
            {name: 'G', onpress: sortAlpha},
            {name: 'H', onpress: sortAlpha},
            {name: 'I', onpress: sortAlpha},
            {name: 'J', onpress: sortAlpha},
            {name: 'K', onpress: sortAlpha},
            {name: 'L', onpress: sortAlpha},
            {name: 'M', onpress: sortAlpha},
            {name: 'N', onpress: sortAlpha},
            {name: 'O', onpress: sortAlpha},
            {name: 'P', onpress: sortAlpha},
            {name: 'Q', onpress: sortAlpha},
            {name: 'R', onpress: sortAlpha},
            {name: 'S', onpress: sortAlpha},
            {name: 'T', onpress: sortAlpha},
            {name: 'U', onpress: sortAlpha},
            {name: 'V', onpress: sortAlpha},
            {name: 'W', onpress: sortAlpha},
            {name: 'X', onpress: sortAlpha},
            {name: 'Y', onpress: sortAlpha},
            {name: 'Z', onpress: sortAlpha}
        ],
        searchitems : [
            {display: 'Tên', name : 'firstname', isdefault: true},
            {display: 'Số công ty', name : 'company_mobile'},
            {display: 'Số di động', name : 'mobile'},
            {display: 'Số nội bộ', name : 'extension'},
            {display: 'Phòng - Công ty', name : 'department'}
        ],
        sortname: "firstname",
        sortorder: "asc",
        usepager: true,
        title: 'Danh bạ điện thoại',
        useRp: true,
        rp: 10,
        showTableToggleBtn: true,
        height: 300
    });
    function sortAlpha(com)
    {
        jQuery('#phonebook_tab').flexOptions({newp:1, params:[{name:'letter_pressed', value: com}]});
        jQuery("#phonebook_tab").flexReload();
    }
</script>
{/literal}