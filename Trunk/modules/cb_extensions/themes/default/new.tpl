<form {if $mode eq 'input'}id="form_agent"{/if} method="post">
<table width="99%" border="0" cellspacing="0" cellpadding="0" align="center">
{if !$FRAMEWORK_TIENE_TITULO_MODULO}
<tr class="moduleTitle">
  <td class="moduleTitle" valign="middle">&nbsp;&nbsp;<img src="{$icon}" border="0" align="absmiddle" />&nbsp;&nbsp;{$title}</td>
</tr>
{/if}
<tr>
  <td>
    <table width="100%" cellpadding="4" cellspacing="0" border="0">
      <tr>
        <td align="left">
          {if $mode eq 'input'}
          <input class="button" type="submit" name="submit_save_agent" value="{$SAVE}" />
          <input class="button" type="submit" name="cancel" value="{$CANCEL}"/>
          <input class="button" id="btn_add_queue" name="init_private_queue" type="button" value="Khởi tạo private queue"
              {if $existingQueue eq '1'} style="display:none;"{else} style="display:block;"{/if}/>
          {elseif $mode eq 'edit'}
          <input class="button" type="submit" name="submit_apply_changes" value="{$APPLY_CHANGES}" />
          <input class="button" type="submit" name="cancel" value="{$CANCEL}"/>
          <input class="button" id="btn_add_queue" name="edit_private_queue" type="button" value="Khởi tạo private queue"
                  {if $existingQueue eq '1'} style="display:none;"{else} style="display:block;"{/if}/>
          {else}
          <input class="button" type="submit" name="edit" value="{$EDIT}"/>
          <input class="button" type="submit" name="delete" value="{$DELETE}"  onClick="return confirmSubmit('{$CONFIRM_CONTINUE}')" />
          <input class="button" type="submit" name="cancel" value="{$CANCEL}"/>
          {/if}
        </td>
        <td align="right" nowrap><span  class="required">*</span> <span class="letra12">{$REQUIRED_FIELD}</span></td>
     </tr>
   </table>
  </td>
</tr>
<tr>
  <td>
    <table width="800px" border="0" cellspacing="0" cellpadding="0" class="tabForm">
      <tr>
    <td width="20%">{$extension.LABEL}: <span class="required">*</span></td>
    <td id="edit_ext" width="30%">{$extension.INPUT}</td>
	<td width="15%">{$description.LABEL}: <span  class="required">*</span></td>
	<td width="35%">{$description.INPUT}</td>
      </tr>
{if $mode ne 'view'}
      <tr>
	<td width="20%">{$password1.LABEL}: <span  class="required">*</span></td>
	<td width="30%">{$password1.INPUT}</td>
	<td width="20%">{$password2.LABEL}: <span class="required">*</span></td>
	<td width="30%">{$password2.INPUT}</td>
      </tr>
      <tr hidden style="display:none">
    <td width="20%">{$eccpwd1.LABEL}:</td>
    <td width="30%">{$eccpwd1.INPUT}</td>
    <td width="20%">{$eccpwd2.LABEL}:</td>
    <td width="30%">{$eccpwd2.INPUT}</td>
      </tr>
{/if}
        <tr>
            <td width="20%">{$office.LABEL}: <span  class="required">*</span></td>
            <td width="30%">{$office.INPUT}</td>
        </tr>
    </table>
  </td>
</tr>
</table>
<input type="hidden" name="id_agent" value="{$id_agent}" />
</form>

{literal}
<script type="text/javascript">
$(document).ready(function(){
    var module_name = 'cb_extensions';
    init();
    function add_private_queue(ext)
    {
        var account = '8'+ext[1];
        var name = 'Q-'+ext[1];
        var member = 'S'+ext[1]+',0';
        $.post('config.php?display=queues', {
                    display:		'queues',
                    action:         'add',
                    account:        account,
                    name:           name,
                    password:       '',
                    prefix:         '',
                    queuewait:      '0',
                    alertinfo:      '',
                    members:        '',
                    dynmembers:     member,
                    dynmemberonly:  'no',
                    use_queue_context:'0',
                    agentannounce_id:   '',
                    joinannounce_id:    'None',
                    music:      'HNH',
                    rtone:      '1',
                    maxwait:    '90',
                    maxlen:     '0',
                    joinempty:  'yes',
                    leavewhenempty: 'no',
                    strategy:   'ringall',
                    timeout:    '0',
                    retry:      '5',
                    wrapuptime: '0',
                    'monitor-format': 'gsm',
                    eventwhencalled:    'no',
                    eventmemberstatus:  'no',
                    cwignore:   '0',
                    weight:     '0',
                    qregex:     '',
                    reportholdtime: 'no',
                    servicelevel:   '60',
                    announcefreq:   '0',
                    announceposition:   'no',
                    announceholdtime:   'no',
                    announcemenu:       'none',
                    pannouncefreq:      '0',
                    goto0:      'Terminate_Call',
                    Terminate_Call0:    'app-blackhole,hangup,1'
                },function(){
                    $.get("config.php?display=queues&extdisplay="+account+"&handler=reload").done(function(){
                        alert('Đã tạo private queue '+ext[1]);
                        $("body").css("cursor", "default");
                        $('#btn_add_queue').hide();
                    });
                });
    }

    $('#btn_add_queue').click(function(){
        var command = $(this);
        $("body").css("cursor", "progress");
        $.post('index.php?menu=' + module_name + '&rawmode=yes', {
            action:		'isAdministrator'},
            function(response){
                if (response == 'administrator') {
                    if (command.attr('name')=='edit_private_queue'){
                        var ext = $('#edit_ext').html().split("/");
                        add_private_queue(ext);
                    }
                    else if (command.attr('name')=='init_private_queue'){
                        var ext = $('#extension').val().split("/");
                        add_private_queue(ext);
                    }
                }
                else
                    alert('Account này không tạo được private queue');
            });
    });
    $('#extension').change(function(){
        var agent =  $(this).val().split("/");
        $.post('index.php?menu=' + module_name + '&rawmode=yes', {
            action:	    'existingQueue',
            extension:      agent[1]
            },function(response){
                if (response=='1')
                    $('#btn_add_queue').hide();
                else
                    $('#btn_add_queue').show();
            });
    });

    function init()
    {
        if (typeof $('#extension').val() != 'undefined')
            agent = $('#extension').val().split("/");
        else if ($('#edit_ext').html() != 'undefined')
            agent = $('#edit_ext').html().split("/");
        $.post('index.php?menu=' + module_name + '&rawmode=yes', {
            action:	    'existingQueue',
            extension:      agent[1]
        },function(response){
            if (response=='1')
                $('#btn_add_queue').hide();
            else
                $('#btn_add_queue').show();
        });
    }

});
</script>
{/literal}