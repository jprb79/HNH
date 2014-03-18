{foreach from=$LISTA_JQUERY_CSS item=CURR_ITEM}
    {if $CURR_ITEM[0] == 'css'}
        <link rel="stylesheet" href='{$CURR_ITEM[1]}' />
    {/if}
    {if $CURR_ITEM[0] == 'js'}
        <script type="text/javascript" src='{$CURR_ITEM[1]}'></script>
    {/if}
{/foreach}
{* Este DIV se usa para mostrar los mensajes de éxito *}
<div stype="dislay:none"
    id="elastix-callcenter-info-message"
    class="ui-state-highlight ui-corner-all">
    <p>
        <span class="ui-icon ui-icon-info" style="float: left; margin-right: .3em;"></span>
        <span id="elastix-callcenter-info-message-text"></span>
    </p>
</div>
{* Este DIV se usa para mostrar los mensajes de error *}
<div  stype="dislay:none"
    id="elastix-callcenter-error-message"
    class="ui-state-error ui-corner-all">
    <p>
        <span class="ui-icon ui-icon-alert" style="float: left; margin-right: .3em;"></span>
        <span id="elastix-callcenter-error-message-text"></span>
    </p>
</div>
{* Marco principal de la consola de agente *}
<div id="elastix-callcenter-area-principal">
    {* Título con nombre del módulo *}
{if !$FRAMEWORK_TIENE_TITULO_MODULO}
    <div id="elastix-callcenter-titulo-consola" class="moduleTitle">&nbsp;<img src="{$icon}" border="0" align="absmiddle" alt="" />&nbsp;{$title}
    </div>
{/if}    
	{* Estado del agente con número y nombre del agente *}
	<div id="elastix-callcenter-estado-agente" class="{$CLASS_ESTADO_AGENTE_INICIAL}">
	    <span style="margin-left: 8pt;" id="elastix-callcenter-estado-agente-texto">{$TEXTO_ESTADO_AGENTE_INICIAL}</span>
        <div id="elastix-callcenter-cronometro">{$CRONOMETRO}</div>{* elastix-callcenter-cronometro *}
    </div>{* elastix-callcenter-estado-agente *}
    <div id="elastix-callcenter-wrap">
	    {* Los controles que aparecen del lado izquierdo de la interfaz *}
	    <div id="elastix-callcenter-controles">
	        <button id="btn_hangup" class="elastix-callcenter-boton-activo">{$BTN_COLGAR_LLAMADA}</button>
	        <button id="btn_togglebreak" class="{$CLASS_BOTON_BREAK}" >{$BTN_BREAK}</button>
			{* <button id="btn_transfer" class="elastix-callcenter-boton-activo" >{$BTN_TRANSFER}</button> *}
	        <button id="btn_logout" class="elastix-callcenter-boton-activo">{$BTN_FINALIZAR_LOGIN}</button>
            <br/><br/>
            <div id="exch-rates" class="clearfix">
                <h2>Tỷ giá ngoại tệ
                    <a class="ic-refresh" href="javascript:void(0)" title="Refresh"><img src="modules/agent_console/themes/default/images/btn-refresh.png"
                    alt="refresh" onclick="do_refresh_rate()"/></a>
                </h2>
                <table class="tbl-01" cellspacing="1" border="0" id="ctl00_Content_ExrateView">
                    <tbody>
                    <tr class="odd">
                        <td class="code">AUD</td><td>20,116.45</td>
                    </tr>
                    <tr class="even">
                        <td class="code">CAD</td><td>20,361.98</td>
                    </tr>
                    <tr class="even">
                        <td class="code">USD</td><td>21,115.00</td>
                    </tr>
                    <tr class="even">
                        <td class="code">EUR</td><td>28,736.57</td>
                    </tr>
                    </tbody></table>
            </div>
        </div> {* elastix-callcenter-controles *}
	    {* El panel que aparece a la derecha como área principal del módulo *}
	    <div id="elastix-callcenter-contenido">
			{* Definición de las cejillas de información/script/formulario *}
			<div 
			  id="elastix-callcenter-cejillas-contenido"
			  class="{if $CALLINFO_CALLTYPE == ''}elastix-callcenter-cejillas-contenido-barra-oculta{else}elastix-callcenter-cejillas-contenido-barra-visible{/if}">
			   <ul>
			       <li><a href="#elastix-callcenter-llamada-info">{$TAB_LLAMADA_INFO}</a></li>
		           <li><a href="#elastix-callcenter-llamada-script">{$TAB_LLAMADA_SCRIPT}</a></li>
		           <li><a href="#elastix-callcenter-llamada-form">{$TAB_LLAMADA_FORM}</a></li>
                   <li><a href="#delivery-tab">Yêu cầu giao vé</a></li>
                   <li><a href="#knowledge-tab">Kiến thức cần biết</a></li>
			   </ul>
		       <div id="elastix-callcenter-llamada-info">
	{$CONTENIDO_LLAMADA_INFORMACION}           
		       </div>
		       <div id="elastix-callcenter-llamada-script">
	{$CONTENIDO_LLAMADA_SCRIPT}           
		       </div>
		       <div id="elastix-callcenter-llamada-form">
	{$CONTENIDO_LLAMADA_FORMULARIO}           
		       </div>
                <div id="delivery-tab">
    {$DELIVERY_TAB_CONTENT}
                </div>
                <div id="knowledge-tab">
                    <iframe src="modules/agent_console/kb/index.html" width=100% height=100%>Alternate content</iframe>
                </div>
			</div>{* elastix-callcenter-cejillas-contenido *}
	        {* Barra inferior que muestra la información de la llamada entrante *}
			<div 
			  id="elastix-callcenter-barra-llamada-entrante" 
			  class="elastix-callcenter-barra-llamada ui-widget-header ui-rounded-all"
			  {if $CALLINFO_CALLTYPE != 'incoming'}style="display: none;"{/if}>
              <label for="llamada_entrante_contacto_telefono">Cuộc gọi đến từ số điện thoại: </label>
		      <span id="llamada_entrante_contacto_telefono">{$TEXTO_CONTACTO_TELEFONO}</span>
		      {*<label for="llamada_entrante_contacto_id">{$LBL_CONTACTO_SELECT}: </label>
		      <select
		          name="llamada_entrante_contacto_id"
		          id="llamada_entrante_contacto_id"
		          class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only">
		          {html_options options=$LISTA_CONTACTOS}
		      </select>
		      <button id="btn_confirmar_contacto">{$BTN_CONFIRMAR_CONTACTO}</button>*}
			</div>{* elastix-callcenter-barra-llamada-entrante *}
	        {* Barra inferior que muestra la información de la llamada saliente *}
	        <div 
	          id="elastix-callcenter-barra-llamada-saliente" 
	          class="elastix-callcenter-barra-llamada ui-widget-header ui-rounded-all"
	          {if $CALLINFO_CALLTYPE != 'outgoing'}style="display: none;"{/if}>
	          <label for="llamada_saliente_contacto_telefono">Cuộc gọi ra đến số điện thoại: </label>
	          <span id="llamada_saliente_contacto_telefono">{$TEXTO_CONTACTO_TELEFONO}</span>
	        </div>{* elastix-callcenter-barra-llamada-saliente *}
		</div>{* elastix-callcenter-contenido *}
	</div>
</div>{* elastix-callcenter-area-principal *}
<div id="elastix-callcenter-seleccion-break" title="{$TITLE_BREAK_DIALOG}" stype="dislay:none">
    <form>
        <select
            name="break_select"
            id="break_select"
            class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only"
            style="width: 100%">{html_options options=$LISTA_BREAKS}
        </select>
    </form>
</div>{* elastix-callcenter-seleccion-break *}
{literal}
<script type="text/javascript">
// Aplicar temas de jQueryUI a diversos elementos
$(function() {
{/literal}
    apply_ui_styles({$APPLY_UI_STYLES});
    initialize_client_state({$INITIAL_CLIENT_STATE});
{literal}
});
</script>
{/literal}
