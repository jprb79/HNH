<table width="99%" border="0" cellspacing="0" cellpadding="4" align="center">
    <tr class="letra12">            
            <td width="43%" align="right">{$field.LABEL}: </td>
            <td width="32%" align="left" nowrap>
                {$field.INPUT} &nbsp;
                {$booker.INPUT}
                {$payment.INPUT} &nbsp;
                {$pattern.INPUT}&nbsp;&nbsp;
                <input class="button" type="submit" name="report" value="{$SHOW}">&nbsp;
                <input class="button" type="button" id="btn_reload" value="Xóa lọc">
            </td>
    </tr>
</table>
{literal}
<script type="text/javascript">
    $(document).ready(function(){
        if ($('#field').val()=='agent_id'){
            $('#booker').attr('style','display:');
            $('#filter_value').attr('style','display:none');
            $('#payment').attr('style','display:none');
        }
        else if ($('#field').val()=='type') {
            $('#payment').attr('style','display:');
            $('#filter_value').attr('style','display:none');
            $('#booker').attr('style','display:none');
        }
        else{
            $('#payment').attr('style','display:none');
            $('#filter_value').attr('style','display:');
            $('#booker').attr('style','display:none');
        }

        $('#field').change(function (){
            if ($(this).val()=='agent_id'){
                $('#booker').attr('style','display:');
                $('#filter_value').attr('style','display:none');
                $('#payment').attr('style','display:none');
            }
            else if ($(this).val()=='type') {
                $('#payment').attr('style','display:');
                $('#filter_value').attr('style','display:none');
                $('#booker').attr('style','display:none');
            }
            else{
                $('#payment').attr('style','display:none');
                $('#filter_value').attr('style','display:');
                $('#booker').attr('style','display:none');
                $('#filter_value').val('');
            }
        });

        $('#booker').change(function (){
            $('#filter_value').val($(this).val());
        });
        $('#payment').change(function (){
            $('#filter_value').val($(this).val());
        });

        $('#btn_reload').click(function(){
            $('#filter_value').val('');
            location.reload('index.php?menu=customer');
        });
    });
</script>
{/literal}


