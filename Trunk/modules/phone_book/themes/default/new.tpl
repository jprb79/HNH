<input type='hidden' name='id' value='{$ID}' />

<table width="99%" border="0" cellspacing="0" cellpadding="0" align="center">
    <tr>
	{if ($mode ne 'view')}
	    <td align="right" nowrap><span class="letra12"><span  class="required">*</span> {$REQUIRED_FIELD}</span></td>
	{/if}
    </tr>
    <tr>
        <td  colspan='2'>
            <table width="100%" cellpadding="4" cellspacing="0" border="0" class="tabForm">
                <tr id="tr_new_contact">
                    <td width="310px" align="center">
                        <img alt="image" src="modules/{$MODULE_NAME}/images/Icon-user.png"/>            
                    </td>
                    <td>
                        <table class="letra12" width="100%" cellpadding="4" cellspacing="0" border="0">                            
                            <tr>
                                <td align="left" width="25%"><b>{$firstname.LABEL}: {if ($mode ne 'view')}<span  class="required">*</span>{/if}</b></td>
                                <td class="required" align="left">{$firstname.INPUT}</td>
                            </tr>
                            <tr>
                                <td align="left" width="25%"><b>{$lastname.LABEL}: </b></td>
                                <td class="required" align="left">{$lastname.INPUT}</td>
                            </tr>
                            <tr>
                                <td align="left" width="25%"><b>{$department.LABEL}: </b></td>
                                <td class="required" align="left">{$department.INPUT}</td>
                            </tr>
                            <tr>
                                <td align="left" width="25%"><b>{$company_mobile.LABEL}: </b></td>
                                <td class="required" align="left">{$company_mobile.INPUT}</td>
                            </tr>
                            <tr>
                                <td align="left" width="25%"><b>{$mobile.LABEL}: </b></td>
                                <td class="required" align="left">{$mobile.INPUT}</td>
                            </tr>
                            <tr id='tr_phone'>
                                <td align="left" width="25%"><b>{$extension.LABEL}: </b></td>
                                <td class="required" align="left">{$extension.INPUT}</td>
                            </tr>
                            <tr>
                                <td align="left" width="25%"><b>{$email.LABEL}: </b></td>
                                <td class="required" align="left">{$email.INPUT}</td>
                            </tr>
                            <tr>
                                <td align="left"><b>{$note.LABEL}: </b></td>
                                <td class="required" align="left">{$note.INPUT}</td>
                            </tr>
                            <td align="right">
                                {if $Show}
                                    <input class="button" type="submit" name="save" value="{$SAVE}">&nbsp;&nbsp;&nbsp;&nbsp;
                                {elseif $Edit}
                                    <input class="button" type="submit" name="edit" value="{$EDIT}">&nbsp;&nbsp;&nbsp;&nbsp;
                                {elseif $Commit}
                                    <input class="button" type="submit" name="commit" value="{$SAVE}">&nbsp;&nbsp;&nbsp;&nbsp;
                                {/if}
                            </td>
                            <td>
                                <input class="button" type="submit" name="cancel" value="{$CANCEL}">
                            </td>
                        </table>
                    </td>
                </tr>
            </table>
        </td>

    </tr>
</table>

{literal}
    <script type="text/javascript">
        $(document).ready(function(){
            $('#firstname').focus();
            $('.button').button();
        });
    </script>
{/literal}
