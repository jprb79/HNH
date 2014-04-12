{foreach from=$LISTA_JQUERY_CSS item=CURR_ITEM}
    {if $CURR_ITEM[0] == 'css'}
<link rel="stylesheet" href='{$CURR_ITEM[1]}' />
    {/if}
    {if $CURR_ITEM[0] == 'js'}
<script type="text/javascript" src='{$CURR_ITEM[1]}'></script>
    {/if}
{/foreach}

{if $NO_EXTENSIONS}
<p><h4 align="center">{$LABEL_NOEXTENSIONS}</h4></p>
{elseif $NO_AGENTS}
<p><h4 align="center">{$LABEL_NOAGENTS}</h4></p>
{else}
<center>
<form method="POST" id="agent_login" action="index.php?menu={$MODULE_NAME}" onsubmit="do_login(); return false;">
<p>&nbsp;</p>
<p>&nbsp;</p>
<table width="400" border="0" cellspacing="0" cellpadding="0" align="center">
  <tr>
    <td width="498"  class="menudescription">
      <table width="100%" border="0" cellspacing="0" cellpadding="4" align="center">
        <tr>
          <td class="menudescription2">
              <div align="left"><font color="#ffffff">&nbsp;&raquo;&nbsp;Hồng Ngọc Hà - Callcenter</font></div>
          </td>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td width="498" bgcolor="#ffffff">
      <table width="100%" border="0" cellspacing="0" cellpadding="8" class="tabForm">
        <tr>
          <td colspan="2">
            <div align="center">Chọn tài khoản và nhập mật khẩu:<br/><br/></div>
          </td>
        </tr>
        <tr id="login_fila_estado" {$ESTILO_FILA_ESTADO_LOGIN}>
          <td colspan="2">
            <div align="center" id="login_icono_espera" height='1'><img id="reloj" src="modules/{$MODULE_NAME}/images/loading.gif" border="0" alt=""></div>
            <div align="center" style="font-weight: bold;" id="login_msg_espera">{$MSG_ESPERA}</div>
            <div align="center" id="login_msg_error" style="color: #ff0000;"></div>
          </td>
        </tr>
        <tr>
          <td width="40%">              
              <div align="right" id="label_extension_callback">Người dùng:</div>
          </td>
          <td width="60%">
              <input type="text" name="input_extension_callback" id="input_extension_callback">
          </td>
        </tr>
        <tr>
          <td width="40%">              
              <div align="right" id="label_password_callback">Mật khẩu:</div>
          </td>
          <td width="60%">                
		    <input type="password" name="input_password_callback" id="input_password_callback">
          </td>
        </tr>
        <tr>
          <td colspan="2" align="center">
            <input type="button" id="submit_agent_login" name="submit_agent_login" value="{$LABEL_SUBMIT}" class="button" />
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>
</form>
</center>
{if $REANUDAR_VERIFICACION}
<script type="text/javascript">
    {literal}
    $(document).ready(function() {
        $("#input_password_callback").keydown(function( event ) {
            if ( event.which == 13 ) {
                $("#agent_login').submit();
            }
        });
    });
    {/literal}
    do_checklogin();
</script>
{/if}
{/if}
