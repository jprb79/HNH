<?php
/*
CHANGE LOG:
- Combine 1800 and 1900 info
- Change SecretAnswer by CTLBM  
- Fix </br> tag
- Reset secret answer

Mobivi Callcenter @2012
*/
//define rt database
define('rtHost', '192.168.11.29');
define('rtUser', 'callcenter');
define('rtPass', 'mobiviadmin@5099');

//define callcenter database
define('ccUser', 'root');
define('ccPass', '123456');
//define field form in database 
define('custName', 'field-2-4');
define('custNum', 'field-2-5');
define('custCat','field-2-6');
define('custSubcat','field-2-8');
define('custDesc','field-2-7');
define('custRes','field-2-9');
define('custCallerid','field-2-10');
define('agentEmail','field-2-11');
#configuration
define('PL_host', '192.168.11.26');
define('PL_db', 'AnalyticalDB');
define('PL_user', 'iis');
define('PL_pass', 'IIS-Connect');

define('Mbv_host', '192.168.254.60');
define('Mbv_db', 'mbv');
define('at_host', '192.168.254.61');
define('at_db', 'airtime');
define('Mbv_user', 'root');
define('Mbv_pass', 'Aesx5099');

define('TDN_host', 'localhost');
define('TDN_user', 'root');
define('TDN_pass', '123456');
define('TDN_db', 'asteriskcdrdb');

define('Callcenter_host', 'localhost');
define('Callcenter_user', 'root');
define('Callcenter_pass', '123456');
define('Callcenter_db', 'call_center');
define('Callcenter_formfieldid', 7);
define('Callcenter_1800id', 3);
define('Callcenter_1900id', 4);


#end of configuration

require_once "libs/paloSantoForm.class.php";
require_once "libs/paloSantoTrunk.class.php";
require_once "libs/paloSantoConfig.class.php";

function _moduleContent(&$smarty, $module_name)
{
    global $arrConf;
    global $arrLang;

    require_once "modules/$module_name/libs/elastix2.lib.php";
    require_once "modules/$module_name/libs/paloSantoConsola.class.php";
    require_once "modules/$module_name/configs/default.conf.php";
    require_once "modules/$module_name/libs/JSON.php";
    
    // Directorio de este módulo
    $sDirScript = dirname($_SERVER['SCRIPT_FILENAME']);

    // Se fusiona la configuración del módulo con la configuración global
    $arrConf = array_merge($arrConf, $arrConfModule);

    /* Se pide el archivo de inglés, que se elige a menos que el sistema indique
       otro idioma a usar. Así se dispone al menos de la traducción al inglés
       si el idioma elegido carece de la cadena.
     */
    load_language_module($module_name);

    // Asignación de variables comunes y directorios de plantillas
    $sDirPlantillas = (isset($arrConf['templates_dir'])) 
        ? $arrConf['templates_dir'] : 'themes';
    $sDirLocalPlantillas = "$sDirScript/modules/$module_name/".$sDirPlantillas.'/'.$arrConf['theme'];
    $smarty->assign("MODULE_NAME", $module_name);

    // Incluir todas las bibliotecas y CSS necesarios
    generarRutaJQueryModulo($smarty, $module_name);

    // Estado inicial de la consola del Call Center
    if (!isset($_SESSION['callcenter'])) $_SESSION['callcenter'] = generarEstadoInicial();

    /* Al iniciar la sesión del agente, se asignan las variables elastix_agent_user y elastix_extension  */
    if ($_SESSION['callcenter']['estado_consola'] == 'logged-in') {
        // Manejo de la sesión activa del agente logoneado
        return manejarSesionActiva($module_name, $smarty, $sDirLocalPlantillas);
    } else {
        // Manejo del inicio de la sesión del agente
        return manejarLogin($module_name, $smarty, $sDirLocalPlantillas);
    }
}

/* Procedimiento para generar el estado inicial de la información del agente en
 * la sesión PHP.  */
function generarEstadoInicial()
{
    return array(
        /*  Estado de la consola. Los valores posibles son 
            logged-out  No hay agente logoneado
            logging     Agente intenta autenticarse con la llamada
            logged-in   Agente fue autenticado y está logoneado en consola
         */
        'estado_consola'    =>  'logged-out',

        /* El número del agente que se logonea. P.ej. 8000 para el agente 8000.
         * En estado logout el agente es NULL.
         */
        'agente'            =>  NULL,

        /* El nombre del agente */
        'agente_nombre'     =>  NULL,

        /* El número de la extensión interna que se logonea al agente. En estado
           logout la extensión es NULL 
         */
        'extension'         =>  NULL,
        
        /* El último tipo de llamada y el último ID de llamada atendida. Esto 
         * permite que se pueda guardar un formulario de una llamada que ya 
         * ha terminado */
        'ultimo_calltype'       =>  NULL,
        'ultimo_callid'         =>  NULL,
        'ultimo_callsurvey'     =>  NULL,
        'ultimo_campaignform'   =>  NULL,
        
        /* Se lleva la cuenta de la duración, en segundos, de los breaks que se
         * han iniciado y terminado durante la sesión. El posible break en curso
         * no se cuenta en break_acumulado. Pero el hecho de que hay un break
         * en curso se registra en break_iniciado por si se refresca la interfaz
         * y se encuentra que el break ha terminado. */
        'break_acumulado'       =>  0,
        'break_iniciado'        =>  NULL,
    );
}

// Procedimiento para decidir qué acción tomar en el estado de login de agente
function manejarLogin($module_name, &$smarty, $sDirLocalPlantillas)
{
    $sAction = '';
    $sContenido = '';

    $sAction = getParameter('action');
    if (!in_array($sAction, array('', 'doLogin', 'checkLogin')))
        $sAction = '';

    switch ($sAction) {
    case 'doLogin':
        $sContenido = manejarLogin_doLogin();
        break;
    case 'checkLogin':
        $sContenido = manejarLogin_checkLogin();
        break;
    default:
        $sContenido = manejarLogin_HTML($module_name, $smarty, $sDirLocalPlantillas);
        break;
    }
    
    return $sContenido;
}

// Mostrar el formulario donde el agente ingresa su login
function manejarLogin_HTML($module_name, &$smarty, $sDirLocalPlantillas)
{
    // Acciones para mostrar el formulario, fuera de cualquier acción AJAX
    $smarty->assign(array(
        'FRAMEWORK_TIENE_TITULO_MODULO' => existeSoporteTituloFramework(),
        'icon'                          => 'modules/'.$module_name.'/images/call_center.png',
        'title'                         =>  _tr('Agent Console'),
        'WELCOME_AGENT'         =>  _tr('Welcome to Agent Console'),
        'ENTER_USER_PASSWORD'   =>  _tr('Please select your agent number and your extension'),
        'USERNAME'              =>  _tr('Agent Number'),
        'EXTENSION'             =>  _tr('Extension'),
        'LABEL_SUBMIT'          =>  _tr('Enter'),
        'LABEL_NOEXTENSIONS'    =>  _tr('There are no extensions available. At least one extension is required for agent login.'),
        'LABEL_NOAGENTS'        =>  _tr('There are no agents available. At least one agent is required for agent login.'),
        'ESTILO_FILA_ESTADO_LOGIN'  =>  'style="visibility: hidden; position: absolute;"',
        'REANUDAR_VERIFICACION' =>  0,
    ));
    
    $oPaloConsola = new PaloSantoConsola();
    $listaExtensiones = $oPaloConsola->listarExtensiones();
    $listaAgentes = $oPaloConsola->listarAgentes();
    $oPaloConsola->desconectarTodo();
    $oPaloConsola = NULL;
    
    $smarty->assign(array(
        'LISTA_EXTENSIONES' =>  $listaExtensiones,
        'LISTA_AGENTES'     =>  $listaAgentes,
        'NO_EXTENSIONS'     =>  (count($listaExtensiones) == 0), 
        'NO_AGENTS'         =>  (count($listaAgentes) == 0), 
    ));
    
    // Restaurar el estado de espera en caso de que se refresque la página
    if (!is_null($_SESSION['callcenter']['agente']) &&
        !is_null($_SESSION['callcenter']['extension'])) {
        $smarty->assign(array(
            'ID_AGENT'      =>  $_SESSION['callcenter']['agente'],
            'ID_EXTENSION'  =>  $_SESSION['callcenter']['extension'],
            'ESTILO_FILA_ESTADO_LOGIN'  =>  'style="visibility: visible; position: none;"',
            'MSG_ESPERA'    =>  _tr('Logging agent in. Please wait...'),
            'REANUDAR_VERIFICACION' =>  1,
        ));
        
    }
    $sContenido = $smarty->fetch("$sDirLocalPlantillas/login_agent.tpl");
    return $sContenido;	
}

// Procesar requerimiento AJAX para iniciar el login del agente
function manejarLogin_doLogin()
{
    $oPaloConsola = new PaloSantoConsola();

    // Acción AJAX para iniciar el login de agente
    $sAgente = getParameter('agent');
    $sExtension = getParameter('ext');

    $respuesta = array(
        'status'    =>  FALSE,  // VERDADERO para éxito en iniciar timbrado
        'message'   =>  '(no message)', // Posible mensaje de error
    );
    $bContinuar = TRUE;

    // Verificar que la extensión y el agente son válidos en el sistema
    if ($bContinuar) {
        $listaExtensiones = $oPaloConsola->listarExtensiones();
        $listaAgentes = $oPaloConsola->listarAgentes();
        if (!in_array($sAgente, array_keys($listaAgentes))) {
            $bContinuar = FALSE;
            $respuesta['status'] = FALSE;
            $respuesta['message'] = _tr('Invalid agent number');
        } elseif (!in_array($sExtension, array_keys($listaExtensiones))) {
            $bContinuar = FALSE;
            $respuesta['status'] = FALSE;
            $respuesta['message'] = _tr('Invalid extension number');
        }
    }
    
    // Verificar si el número de agente no está ya ocupado por otra extensión
    if ($bContinuar) {
        $oPaloConsola->desconectarTodo();
        $oPaloConsola = new PaloSantoConsola('Agent/'.$sAgente);

        $estado = $oPaloConsola->estadoAgenteLogoneado($sExtension);
        switch ($estado['estadofinal']) {
        case 'error':
        case 'mismatch':
            $respuesta['status'] = FALSE;
            $respuesta['message'] = _tr('Cannot start agent login').' - '.$oPaloConsola->errMsg;
            break;
        case 'logged-out':
            // No hay canal de login. Se inicia login a través de Originate
            $bExito = $oPaloConsola->loginAgente($listaExtensiones[$sExtension]);
            if (!$bExito) {
                $respuesta['status'] = FALSE;
                $respuesta['message'] = _tr('Cannot start agent login').' - '.$oPaloConsola->errMsg;
                break;
            }
            // En caso de éxito, se cuela al siguiente caso
        case 'logging':
        case 'logged-in':
            $listaAgentes = $oPaloConsola->listarAgentes();

            // Ya está logoneado este agente. Se procede directamente a espera
            $_SESSION['callcenter']['estado_consola'] = 'logging';
            $_SESSION['callcenter']['agente'] = $sAgente;
            $_SESSION['callcenter']['agente_nombre'] = $listaAgentes[$sAgente];
            $_SESSION['callcenter']['extension'] = $sExtension;
            $respuesta['status'] = TRUE;
            $respuesta['message'] = _tr('Logging agent in. Please wait...');
            
            if ($estado['estadofinal'] != 'logged-in') {
                // Esperar hasta 1 segundo para evento de fallo de login.
                $sEstado = $oPaloConsola->esperarResultadoLogin();
                if ($sEstado == 'logged-in') {
                    // El agente ha podido logonearse. Se procede a mostrar el formulario
                    $_SESSION['callcenter']['estado_consola'] = 'logged-in';
                } elseif ($sEstado == 'logged-out') {
                    // El procedimiento de login ha fallado, sin causa conocida
                    $_SESSION['callcenter'] = generarEstadoInicial();
                    $respuesta['status'] = FALSE;
                    $respuesta['message'] = _tr('Agent log-in failed!');
                } elseif ($sEstado == 'error') {
                    // Ocurre un error al consultar el estado del agente
                    $_SESSION['callcenter'] = generarEstadoInicial();
                    $respuesta['status'] = FALSE;
                    $respuesta['message'] = _tr('Agent log-in failed!').' - '.$oPaloConsola->errMsg;
                }
            }
            break;
        }
    }
    
    $json = new Services_JSON();
    $sContenido = $json->encode($respuesta);
    Header('Content-Type: application/json');
    $oPaloConsola->desconectarTodo();

    return $sContenido;
}

// Procesar requerimiento AJAX para revisar el estado del proceso de login
function manejarLogin_checkLogin()
{
    $respuesta = array(
        'action'    =>  'wait', // Opciones: wait login error
        'message'   =>  '(no message)', // Posible mensaje de error
    );
    $bContinuar = TRUE;

    // Verificación rápida para saber si el canal es correcto
    $sAgente = $_SESSION['callcenter']['agente'];
    $sExtension = $_SESSION['callcenter']['extension'];
    $oPaloConsola = new PaloSantoConsola('Agent/'.$sAgente);

    if ($bContinuar) {
        $estado = $oPaloConsola->estadoAgenteLogoneado($sExtension);
        switch ($estado['estadofinal']) {
        case 'error':
        case 'mismatch':
            // Otra extensión ya ocupa el login del agente indicado, o error
            $_SESSION['callcenter'] = generarEstadoInicial();
            $respuesta['action'] = 'error';
            $respuesta['message'] = _tr('Cannot start agent login').' - '.$oPaloConsola->errMsg.
                "ext=$sExtension agente=$sAgente";
            $bContinuar = FALSE;
            break;
        case 'logged-out':
            // No se encuentra evidencia de que se empezara el login
            $_SESSION['callcenter'] = generarEstadoInicial();
            $respuesta['action'] = 'error';
            $respuesta['message'] = _tr('Agent login process not started');
            $bContinuar = FALSE;
            break;
        case 'logging':
            $_SESSION['callcenter']['estado_consola'] = 'logging';
            $respuesta['action'] = 'wait';
            $respuesta['message'] = _tr('Logging agent in. Please wait...');
            break;
        case 'logged-in':
            // El agente ha podido logonearse. Se procede a mostrar el formulario
            $_SESSION['callcenter']['estado_consola'] = 'logged-in';
            $respuesta['action'] = 'login';
            $bContinuar = FALSE;
            break;
        }
    	
    }
    
    if ($bContinuar && $respuesta['action'] == 'wait') {
        $oPaloConsola->desconectarEspera();
        
        // Se inicia espera larga con el navegador...
        $iTimeoutPoll = PaloSantoConsola::recomendarIntervaloEsperaAjax();
        session_commit();
        set_time_limit(0);
        $iTimestampInicio = time();
        
        while ($bContinuar && time() - $iTimestampInicio <  $iTimeoutPoll) {

            // Verificar si el agente ya está en línea
            $sEstado = $oPaloConsola->esperarResultadoLogin();
            if ($sEstado == 'logged-in') {
                // Reiniciar la sesión para poder modificar las variables
                session_start();

                // El agente ha podido logonearse. Se procede a mostrar el formulario
                $_SESSION['callcenter']['estado_consola'] = 'logged-in';
                $respuesta['action'] = 'login';
                $bContinuar = FALSE;

            } elseif ($sEstado == 'logged-out') {
                // Reiniciar la sesión para poder modificar las variables
                session_start();

                // El procedimiento de login ha fallado, sin causa conocida
                $_SESSION['callcenter'] = generarEstadoInicial();
                $respuesta['action'] = 'error';
                $respuesta['message'] = _tr('Agent log-in terminated.');
                $bContinuar = FALSE;
            } elseif ($sEstado == 'error') {
                // Reiniciar la sesión para poder modificar las variables
                session_start();

                // Ocurre un error al consultar el estado del agente
                $_SESSION['callcenter'] = generarEstadoInicial();
                $respuesta['action'] = 'error';
                $respuesta['message'] = _tr('Agent log-in failed!').' - '.$oPaloConsola->errMsg;
                $bContinuar = FALSE;
            }
        }
    }
    
    $json = new Services_JSON();
    $sContenido = $json->encode($respuesta);
    Header('Content-Type: application/json');
    $oPaloConsola->desconectarTodo();

    return $sContenido;
}

// Procedimiento para decidir qué acción tomar en el estado de sesión activa
function manejarSesionActiva($module_name, &$smarty, $sDirLocalPlantillas)
{
    $sAction = '';
    $sContenido = '';

    $sAction = getParameter('action');
    if (!in_array($sAction, array('', 'checkStatus', 'agentLogout', 'hangup', 
        'break', 'unbreak', 'transfer', 'confirm_contact', 'schedule', 
        'saveforms')))
        $sAction = '';

    // Se verifica si el agente sigue logoneado en la cola de Asterisk
    $sAgente = $_SESSION['callcenter']['agente'];
    $sExtension = $_SESSION['callcenter']['extension'];
    $oPaloConsola = new PaloSantoConsola('Agent/'.$sAgente);
    $estado = $oPaloConsola->estadoAgenteLogoneado($sExtension);
    if ($estado['estadofinal'] != 'logged-in') {
        // Se marca el final de la sesión del agente en las tablas de auditoría
        $oPaloConsola->logoutAgente();
        $_SESSION['callcenter'] = generarEstadoInicial();
    }

    switch ($sAction) {
    case 'checkStatus':
        $sContenido = manejarSesionActiva_checkStatus($module_name, $smarty, $sDirLocalPlantillas, $oPaloConsola, $estado);
        break;
    case 'hangup':
        $sContenido = manejarSesionActiva_hangup($oPaloConsola);
        break;
    case 'agentLogout':
        $sContenido = manejarSesionActiva_agentLogout($oPaloConsola);
        break;
    case 'break':
        $sContenido = manejarSesionActiva_agentBreak($oPaloConsola);
        break;
    case 'unbreak':
        $sContenido = manejarSesionActiva_agentUnBreak($oPaloConsola);
        break;
    case 'transfer':
        $sContenido = manejarSesionActiva_agentTransfer($oPaloConsola);
        break;
    case 'confirm_contact':
        $sContenido = manejarSesionActiva_confirmContact($oPaloConsola, $estado);
        break;
    case 'schedule':
        $sContenido = manejarSesionActiva_scheduleCall($oPaloConsola);
        break;
    case 'saveforms':
        $sContenido = manejarSesionActiva_saveForms($oPaloConsola, $estado);
        break;
    default:
        if ($estado['estadofinal'] != 'logged-in') {
            // Para agente no logoneado, se redirecciona a la página de login
            Header('Location: ?menu='.$module_name);
            $sContenido = '';
        } else {
            $sContenido = manejarSesionActiva_HTML($module_name, $smarty, $sDirLocalPlantillas, $oPaloConsola, $estado);
        }
        break;
    }
    $oPaloConsola->desconectarTodo();

    return $sContenido;
}

function manejarSesionActiva_HTML($module_name, &$smarty, $sDirLocalPlantillas, $oPaloConsola, $estado)
{
    $bInactivarBotonColgar = FALSE;
    $bPuedeConfirmarContacto = FALSE;

    // Acciones para mostrar la pantalla principal, fuera de cualquier acción AJAX
    for ($i = 0; $i < 24; $i++) { $ii = sprintf('%02d', $i); $comboHora[$ii] = $ii; }
    for ($i = 0; $i < 60; $i++) { $ii = sprintf('%02d', $i); $comboMinuto[$ii] = $ii; }
    $smarty->assign(array(
        'FRAMEWORK_TIENE_TITULO_MODULO' => existeSoporteTituloFramework(),
        'icon'                          => 'modules/'.$module_name.'/images/call_center.png',
        'title'                         =>  _tr('Agent Console').': '.
            $_SESSION['callcenter']['agente_nombre'],
        'BTN_COLGAR_LLAMADA'            =>  _tr('Hangup'),
        'BTN_TRANSFER'                  =>  _tr('Transfer'),
        'BTN_VTIGERCRM'                 =>  _tr('Mobivi Ticket'),
        'BTN_FINALIZAR_LOGIN'           =>  _tr('End session'),
        'TITLE_BREAK_DIALOG'            =>  _tr('Select break type'),
        'BTN_CONFIRMAR_CONTACTO'        =>  _tr('Confirm contact'),
        'LBL_CONTACTO_TELEFONO'         =>  _tr('Phone number'),
        'LBL_CONTACTO_SELECT'           =>  _tr('Contact'),
        'LBL_CONTACTO_NOMBRES'          =>  _tr('Names'),
        'TEXTO_CONTACTO_NOMBRES'        =>  '',
        'TEXTO_CONTACTO_TELEFONO'       =>  '',
        'BTN_AGENDAR_LLAMADA'           =>  _tr('Schedule call'),
        'TITLE_TRANSFER_DIALOG'         =>  _tr('Select extension to transfer to'),
        'LBL_TRANSFER_BLIND'            =>  _tr('Blind transfer'),
        'LBL_TRANSFER_ATTENDED'         =>  _tr('Attended transfer'),
        'TITLE_SCHEDULE_CALL'           =>  _tr('Schedule call'),
        'LBL_SCHEDULE_CAMPAIGN_END'     =>  _tr('Call at end of campaign'),
        'LBL_SCHEDULE_BYDATE'           =>  _tr('Schedule at date'),
        'LBL_SCHEDULE_DATE_START'       =>  _tr('Start date'),
        'LBL_SCHEDULE_DATE_END'         =>  _tr('End date'),
        'LBL_SCHEDULE_TIME_START'       =>  _tr('Start time'),
        'LBL_SCHEDULE_TIME_END'         =>  _tr('End time'),
        'LBL_SCHEDULE_SAME_AGENT'       =>  _tr('Schedule to same agent'),
        'SCHEDULE_TIME_HH'              =>  $comboHora,
        'SCHEDULE_TIME_MM'              =>  $comboMinuto,
        'TAB_LLAMADA_INFO'              =>  _tr('Thông tin cuộc gọi'),
        'TAB_LLAMADA_SCRIPT'            =>  _tr('Kiến thức cơ bản'),
        'TAB_LLAMADA_FORM'              =>  _tr('Mẫu trình ticket'),
        'CRONOMETRO'                    =>  '00:00:00',
        'LISTA_BREAKS'                  =>  $oPaloConsola->listarBreaks(),
        'CONTENIDO_LLAMADA_INFORMACION' =>  '',
        'CONTENIDO_LLAMADA_SCRIPT'      =>  '<iframe src="http://192.168.11.28/phpmyfaq/index.php" width="100%" height="500" frameborder="0" ></iframe>',
        'CONTENIDO_LLAMADA_FORMULARIO'  =>  '',
        'CALLINFO_CALLTYPE'             =>  '',
        'BTN_HOLD'                      =>  $estado['onhold'] ? _tr('End Hold') : _tr('Hold'),
    ));
    $estadoInicial = array(
        'onhold'        =>  $estado['onhold'],
        'break_id'      =>  is_null($estado['pauseinfo']) ? NULL : $estado['pauseinfo']['pauseid'],
        'calltype'      =>  NULL,
        'campaign_id'   =>  NULL,
        'callid'        =>  NULL,
        'timer_seconds' =>  0,
        'url'           =>  NULL,
        'urlopentype'   =>  NULL,
    );

    // Decidir estado del break a mostrar
    if (!is_null($estado['pauseinfo'])) {
        $_SESSION['callcenter']['break_iniciado'] = $estado['pauseinfo']['pausestart'];
        $iDuracionPausaActual = time() - strtotime($estado['pauseinfo']['pausestart']);
        $iDuracionPausa = $iDuracionPausaActual + $_SESSION['callcenter']['break_acumulado'];
        $smarty->assign(array(
            'CLASS_BOTON_BREAK'             =>  'elastix-callcenter-boton-unbreak',
            'CLASS_ESTADO_AGENTE_INICIAL'   =>  'elastix-callcenter-class-estado-break',
            'BTN_BREAK'                     =>  _tr('End Break'),
            'TEXTO_ESTADO_AGENTE_INICIAL'   =>  _tr('On break').': '.$estado['pauseinfo']['pausename'],

            // TODO: debe contener tiempo acumulado de break desde inicio sesión
            // TODO: idea: sumar inicios y finales de breaks en variable sesión
            'CRONOMETRO'                    =>  sprintf('%02d:%02d:%02d', 
                ($iDuracionPausa - ($iDuracionPausa % 3600)) / 3600, 
                (($iDuracionPausa - ($iDuracionPausa % 60)) / 60) % 60, 
                $iDuracionPausa % 60),
        ));
        $estadoInicial['timer_seconds'] = $iDuracionPausa;
    } else {
        if (!is_null($_SESSION['callcenter']['break_iniciado'])) {
        	/* Si esta condición se cumple, entonces se ha perdido el evento 
             * pauseexit durante la espera en manejarSesionActiva_checkStatus().
             * Se hace la suposición de que el refresco ocurre poco después de
             * que termina el break, y que por lo tanto el error al usar time()
             * como fin del break es pequeño. 
             */
            $_SESSION['callcenter']['break_acumulado'] += time() - strtotime($_SESSION['callcenter']['break_iniciado']);
        }
        
        $smarty->assign(array(
            'CLASS_BOTON_BREAK'             =>  'elastix-callcenter-boton-break',
            'BTN_BREAK'                     =>  _tr('Take Break'),
            'CLASS_ESTADO_AGENTE_INICIAL'   =>  'elastix-callcenter-class-estado-ocioso',
            'TEXTO_ESTADO_AGENTE_INICIAL'   =>  _tr('No active call'),
        ));
        $_SESSION['callcenter']['break_iniciado'] = NULL;
    }
    
    // Cambios según agente conectado a una llamada versus ocioso
    if (!is_null($estado['callinfo'])) {
        // Información sobre la llamada conectada
        $infoLlamada = $oPaloConsola->leerInfoLlamada(
            $estado['callinfo']['calltype'],
            $estado['callinfo']['campaign_id'],
            $estado['callinfo']['callid']);
        if ($estado['callinfo']['calltype'] == 'incoming' && is_null($estado['callinfo']['campaign_id'])) {
            $infoCampania['queue'] = $infoLlamada['queue'];
        	$infoCampania['script'] = $oPaloConsola->leerScriptCola($infoCampania['queue']);
            $infoCampania['forms'] = NULL;
        } else {
            $infoCampania = $oPaloConsola->leerInfoCampania(
                $estado['callinfo']['calltype'],
                $estado['callinfo']['campaign_id']);
        }
        if (is_null($infoCampania['script']) || $infoCampania['script'] == '')
            $infoCampania['script'] = _tr('(No script available)');

        // Almacenar para regenerar formulario
        $_SESSION['callcenter']['ultimo_calltype'] = $estado['callinfo']['calltype'];
        $_SESSION['callcenter']['ultimo_callid'] = $estado['callinfo']['callid'];
        $_SESSION['callcenter']['ultimo_callsurvey']['call_survey'] = $infoLlamada['call_survey'];
        $_SESSION['callcenter']['ultimo_campaignform']['forms'] = $infoCampania['forms'];

        // Fecha completa de la llamada
        $iDuracionLlamada = time() - strtotime($estado['callinfo']['linkstart']);
  
        // Asignaciones independientes del tipo de llamada
        $bInactivarBotonColgar = false; // Se usa para botón hangup y botón transfer
        $smarty->assign(array(
            'CLASS_ESTADO_AGENTE_INICIAL'   =>  'elastix-callcenter-class-estado-activo',
            'TEXTO_ESTADO_AGENTE_INICIAL'   =>  _tr('Đang có cuộc gọi đến'),
            'TEXTO_CONTACTO_TELEFONO'       =>  $estado['callinfo']['callnumber'],
            'CALLINFO_CALLTYPE'             =>  $estado['callinfo']['calltype'],

            // TODO: debe contener tiempo transcurrido en llamada
            'CRONOMETRO'                    =>  sprintf('%02d:%02d:%02d', 
                ($iDuracionLlamada - ($iDuracionLlamada % 3600)) / 3600, 
                (($iDuracionLlamada - ($iDuracionLlamada % 60)) / 60) % 60, 
                $iDuracionLlamada % 60),
            
            'CONTENIDO_LLAMADA_INFORMACION' =>  manejarSesionActiva_HTML_generarInformacion($smarty, $sDirLocalPlantillas, $infoLlamada, $infoCampania),
            'CONTENIDO_LLAMADA_FORMULARIO'  =>  manejarSesionActiva_HTML_generarFormulario($smarty, $sDirLocalPlantillas, $infoLlamada, $infoCampania),
			'CONTENIDO_LLAMADA_SCRIPT'		=>	'<iframe src="http://192.168.11.28/phpmyfaq/index.php" width="100%" height="500" frameborder="0" ></iframe>', 
           	//'CONTENIDO_LLAMADA_SCRIPT'      =>  $infoCampania['script'],
        ));
        $estadoInicial['timer_seconds'] = $iDuracionLlamada;
        $estadoInicial['calltype'] = $estado['callinfo']['calltype'];
        $estadoInicial['campaign_id'] = $estado['callinfo']['campaign_id'];
        $estadoInicial['callid'] = $estado['callinfo']['callid'];
        $estadoInicial['urlopentype'] = isset($infoCampania['urlopentype']) ? $infoCampania['urlopentype'] : NULL;
        $estadoInicial['url'] = is_null($estadoInicial['urlopentype']) 
            ? NULL : construirUrlExterno($infoCampania['urltemplate'], $infoLlamada + array(
            'callnumber'        =>  $estado['callinfo']['callnumber'],
            'callid'            =>  $infoLlamada['call_id'],
            'agent_number'      =>  $estado['callinfo']['agent_number'],
            'remote_channel'    =>  $estado['callinfo']['remote_channel']));
        
        // Asignaciones específicas para llamadas entrantes
        if ($estado['callinfo']['calltype'] == 'incoming') {
        	$comboContactos = array();
            foreach ($infoLlamada['matching_contacts'] as $idContacto => $tuplaContacto) {
                $infoContactoViejo = array();
                $sDescripcionContacto = '';
                foreach ($tuplaContacto as $attrContacto) {
                	$sDescripcionContacto .= $attrContacto['value'].' ';
                    if (in_array($attrContacto['label'], array('first_name', 'last_name', 'cedula_ruc')))
                        $infoContactoViejo[$attrContacto['label']] = $attrContacto['value'];
                }
                if (count($infoContactoViejo) == 3) {
                	$comboContactos[$idContacto] = $infoContactoViejo['cedula_ruc'].
                        ' - '.$infoContactoViejo['first_name'].' '.$infoContactoViejo['last_name'];
                } else {
                    /* TODO: dar formato adecuado para cuando contactos de llamadas 
                     * entrantes puedan tener atributos arbitrarios */
                    $comboContactos[$idContacto] = $sDescripcionContacto;
                }
        	}
            if (count($comboContactos) == 0) {
            	$comboContactos[''] = _tr('(no matching contacts)');
            }
            $smarty->assign(array(
                'LISTA_CONTACTOS'           =>  $comboContactos,
            ));
            $bPuedeConfirmarContacto = (count($comboContactos) > 1);
        }
        
        // Asignaciones específicas para llamadas salientes
        if ($estado['callinfo']['calltype'] == 'outgoing') {

            /* TODO: el siguiente código asume que el atributo 1 es el nombre
             * del cliente. Esta suposición se hereda del callcenter anterior.
             * Se debe de idear un método para dar formato al nombre del cliente
             * a partir de cualquier combinación de columnas */
            $sNombreCliente = isset($infoLlamada['call_attributes'][1]) 
                ? $infoLlamada['call_attributes'][1]['value'] 
                : _tr('(unavailable)');

        	$smarty->assign(array(
                'TEXTO_CONTACTO_NOMBRES'        =>  $sNombreCliente,
            ));
        }
    } else {
    	$bInactivarBotonColgar = true; // Se usa para botón hangup y botón transfer
        $smarty->assign(array(
            'CONTENIDO_LLAMADA_FORMULARIO'  =>  is_null($_SESSION['callcenter']['ultimo_calltype']) 
                ? '' 
                : manejarSesionActiva_HTML_generarFormulario($smarty, $sDirLocalPlantillas, 
                        $_SESSION['callcenter']['ultimo_callsurvey'], 
                        $_SESSION['callcenter']['ultimo_campaignform']),
        ));
    }
    $json = new Services_JSON();
    $smarty->assign(array(
        'APPLY_UI_STYLES'   =>  $json->encode(array(
            'break_commit'              =>  _tr('Take Break'),
            'break_dismiss'             =>  _tr('Dismiss'),
            'transfer_commit'           =>  _tr('Transfer'),
            'transfer_dismiss'          =>  _tr('Dismiss'),
            'schedule_commit'           =>  _tr('Schedule'),
            'schedule_dismiss'          =>  _tr('Dismiss'),
            'external_url_tab'          =>  _tr('External site'),
            'schedule_call_error_msg_missing_date' => _tr('Start and end date are required for date scheduling.'),
            'no_call'                   =>  $bInactivarBotonColgar,
            'can_confirm_contact'       =>  $bPuedeConfirmarContacto,
            )),
        'INITIAL_CLIENT_STATE'  =>  $json->encode($estadoInicial),
    ));
    return $smarty->fetch("$sDirLocalPlantillas/agent_console.tpl");
}

function manejarSesionActiva_HTML_generarInformacion($smarty, $sDirLocalPlantillas, $infoLlamada, $infoCampania)
{
    if (substr($infoLlamada['phone'],0,1) == '0') {
		$number = $infoLlamada['phone']; 
	}else {
		$number = '0' . $infoLlamada['phone']; }
    $smarty->assign(array(
        'LBL_INFORMACION_LLAMADA'   =>  _tr('Gọi đến kênh '. $infoLlamada['queue'] . ' - Số điện thoại: ' . $infoLlamada['phone']),
		'HTML_result'				=>  _tr(getInfo($number)),
    ));        		
	return $smarty->fetch("$sDirLocalPlantillas/agent_console_mobivi.tpl");
}

//edited by Tri Do 
function getAirtimeTrans($mobile)
{
   	$con = mysql_connect(at_host,Mbv_user,Mbv_pass);
    if (!$con)
      {
        die('Could not connect Airtime database: ' . mysql_error());
      }
    mysql_select_db(at_db, $con);    
    $sql = "SELECT channel_id, txn_date, amount, telco_id, conn_type, error_code, txn_status
		FROM at_transaction
		WHERE msisdn = '$mobile'
		AND txn_date >= DATE_SUB(CURRENT_DATE, INTERVAL 90 DAY)
		ORDER BY txn_date DESC
		LIMIT 10";	
	$result = mysql_query($sql);
    if (mysql_num_rows($result)==0){
        $value = "<tr><td><label>Không có giao dịch qua Airtime</label></td></tr><br>";
    }
    else{
			$value = "<br><td><label>Giao dịch qua Airtime: </label></td>
			<TABLE BORDER='1' WIDTH='100%'>
			<tr>
				<td><label>Kênh bán</label></td>
				<td><label>Ngày GD</label></td>
				<td><label>Số tiền</label></td>
				<td><label>Nhà mạng</label></td>
				<td><label>Nhà cung cấp</label></td>
				<td><label>Error code</label></td>
				<td><label>status</label></td>
			</tr>";
			while($row = mysql_fetch_array($result))
			{
			$value = $value . "<tr>
				<td>" . $row['channel_id'] . "</td>
				<td>" . $row['txn_date'] . "</td> 		
				<td>" . $row['amount'] . "</td>				
				<td>" . $row['telco_id'] . "</td>
				<td>" . $row['conn_type'] . "</td>
				<td>" . $row['error_code'] . "</td>
				<td>" . $row['txn_status'] . "</td>				
			</tr>";
			}			
			$value = $value . "</TABLE>";
	}
	return $value;
}

function getTicket($mobile)
{
	$con = mysql_connect(rtHost,rtUser,rtPass);
 	  if (!$con)
      {
        die('Could not connect Ticket database: ' . mysql_error());
      }
    mysql_select_db('rt', $con);    		  
	$sql = "SELECT b.id, b.subject, b.created, b.status, c.Name AS Requestor, c.name AS Owner
	FROM ObjectCustomFieldValues a
	INNER JOIN Tickets b ON a.`ObjectId` = b.id
	INNER JOIN Users c ON b.Creator = c.id
	WHERE a.customField =5
	AND a.content = '$mobile'
	ORDER BY a.created DESC
	LIMIT 10";	
	mysql_query("SET character_set_results = 'utf8', character_set_client = 'utf8', character_set_connection = 'utf8', character_set_database = 'utf8', character_set_server = 'utf8'", $con);
	$result = mysql_query($sql);	
    if (mysql_num_rows($result)==0){
        $value = 'Không có ticket nào của số điện thoại này';
    }
    else{
			$value = "<br><br><td><label>10 ticket gần đây tương ứng số điện thoại này: </label></td>
			<TABLE BORDER='1' WIDTH='100%'>
			<tr>
				<td><label>Ticket ID</label></td>
				<td><label>Tên ticket</label></td>
				<td><label>Ngày tạo</label></td>
				<td><label>Tình trạng</label></td>
				<td><label>Người tạo</label></td>
				<td><label>Người xử lý</label></td>				
			</tr>";
			while($row = mysql_fetch_array($result))
			{
			$value = $value . "<tr>
				<td><a href='http://192.168.11.29/Ticket/Display.html?id=" . $row[0] . "' target='_blank'>" . $row[0] . "</a></td>
				<td>" . $row[1] . "</td>
				<td>" . $row[2] . "</td> 				
				<td>" . $row[3] . "</td>
				<td>" . $row[4] . "</td> 				
				<td>" . $row[5] . "</td>				
			</tr>";
			}			
			$value = $value . "</TABLE>";
	}
  mysql_close($con);
  return $value;
}

function getFormHistory($mobile)
{
   	$value = getTicket($mobile);
	$con = mysql_connect(Callcenter_host,Callcenter_user,Callcenter_pass);
 	  if (!$con)
      {
        die('Could not connect callcenter database: ' . mysql_error());
      }
    mysql_select_db(Callcenter_db, $con);    		  
	$sql = "SELECT a.callerid AS phone, a.datetime_init AS calldate, a.duration, b.name AS agent, a.status, a.trunk, a.ticketid   
	FROM call_entry a
	INNER JOIN agent b ON
	a.id_agent = b.id
	where a.callerid = '$mobile'
	and a.status <> 'activa' 
	ORDER BY a.datetime_init DESC
	LIMIT 10";
	$stt = array(
		'abandonada'	=> 'abandoned',
		'activa'		=> 'active',
		'terminada'		=>	'completed',
		'en-cola'		=>	'in-line',		
	);
	$result = mysql_query($sql);	
    if (mysql_num_rows($result)==0){
       
    }
    else{
			$value = $value . "<br><br><td><label>10 cuộc gọi gần đây của số điện thoại này: </label></td>
			<TABLE BORDER='1' WIDTH='100%'>
			<tr>
				<td><label>Số điện thoại</label></td>
				<td><label>Ngày gọi</label></td>
				<td><label>Duration</label></td>
				<td><label>Agent</label></td>
				<td><label>Status</label></td>
				<td><label>Trunk</label></td>
				<td><label>Ticket ID</label></td>
			</tr>";
			while($row = mysql_fetch_array($result))
			{
			if ($row['ticketid'] == null){
				$ticket = 'No submit';
			}else{
				$ticket = "<a href='http://192.168.11.29/Ticket/Display.html?id=" . $row['ticketid'] . "' target='_blank'>" . $row['ticketid'] . "</a>";
			}
			$value = $value . "<tr>
				<td>" . $row['phone'] . "</td>
				<td>" . $row['calldate'] . "</td> 				
				<td>" . $row['duration'] . "</td>
				<td>" . $row['agent'] . "</td> 				
				<td>" . $stt[$row['status']] . "</td>
				<td>" . $row['trunk'] . "</td> 				 				 				 			
				<td>" . $ticket . "</a></td>
			</tr>";
			}			
			$value = $value . "</TABLE>";
	}
  
	$sql = "SELECT a.callerid AS phone, a.datetime_init AS calldate, a.duration, b.name AS agent, a.status, a.trunk, a.ticketid  
	FROM call_entry a
	INNER JOIN agent b ON
	a.id_agent = b.id
	where a.status <> 'activa' 
	ORDER BY a.datetime_init DESC
	LIMIT 20";
	$result = mysql_query($sql);
	$value = $value . "<br><td><label>Lịch sử 20 cuộc gọi gần đây: </label></td>
			<TABLE BORDER='1' WIDTH='100%'>
			<tr>
				<td><label>Số điện thoại</label></td>
				<td><label>Ngày gọi</label></td>
				<td><label>Duration</label></td>
				<td><label>Agent</label></td>
				<td><label>Status</label></td>
				<td><label>Trunk</label></td>
				<td><label>Ticket ID</label></td>
			</tr>";
			while($row = mysql_fetch_array($result))
			{
			if ($row['ticketid'] == null){
				$ticket = 'No submit';
			}else{
				$ticket = "<a href='http://192.168.11.29/Ticket/Display.html?id=" . $row['ticketid'] . "' target='_blank'>" . $row['ticketid'] . "</a>";
			}
			$value = $value . "<tr>
				<td>" . $row['phone'] . "</td>
				<td>" . $row['calldate'] . "</td> 				
				<td>" . $row['duration'] . "</td>
				<td>" . $row['agent'] . "</td> 				
				<td>" . $stt[$row['status']] . "</td>
				<td>" . $row['trunk'] . "</td> 
				<td>" . $ticket . "</td>
			</tr>";
			}			
			$value = $value . "</TABLE>";
	return $value;
}

function getTheDaNang($mobile)
{
   	$con = mysql_connect(TDN_host,TDN_user,TDN_pass);
    if (!$con)
      {
        die('Could not connect TDN database: ' . mysql_error());
      }
    mysql_select_db(TDN_db, $con);    
    $sql = "SELECT date, pincode, status, result FROM topup 
	WHERE number = '$mobile' ORDER BY date desc LIMIT 10";
	$result = mysql_query($sql);
    if (mysql_num_rows($result)==0){
        $value = "<tr><td><label>Không có thông tin nạp thẻ đa năng</label></td></tr>";
    }
    else{
			$value = "<td><label>Giao dịch nạp thẻ đa năng: </label></td>
			<TABLE BORDER='1' WIDTH='100%'>
			<tr>
				<td><label>Ngày</label></td>
				<td><label>Mã Pin nhập </label></td>
				<td><label>Tình trạng</label></td>
				<td><label>Kết quả</label></td>
			</tr>";
			while($row = mysql_fetch_array($result))
			{
			$value = $value . "<tr>
				<td>" . $row['date'] . "</td>
				<td>" . $row['pincode'] . "</td> 				
				<td>" . $row['status'] . "</td> 
				<td>" . $row['result'] . "</td>			
			</tr>";
			}			
			$value = $value . "</TABLE>";
	}
	mysql_close($con);
	return $value;
}

function getTerInfo($terID)
{
	$dbhandle = mssql_connect(PL_host, PL_user, PL_pass)
          or die("Couldn't connect to SQL Server on $PL_host");      
    $selected = mssql_select_db("OperationalDB", $dbhandle)
          or die("Couldn't open database OperationalDB");
    //declare the SQL statement that will query the database
	$query = "SELECT top 1 Name, Address 
	FROM PaylinkTerminals
	where Id = $terID";		
	//execute the SQL query and return records
    $result = mssql_query($query);		
	$row = mssql_fetch_array($result);
	return "Ter-Name: " . $row['Name']."  - Địa chỉ: " . $row['Address'];
}
function getInfo1800($mobile)
{
    $dbhandle = mssql_connect(PL_host, PL_user, PL_pass)
          or die("Couldn't connect to SQL Server on $PL_host");      
    
	//paylinkplus
	$query="SELECT b.Username, a.IsBlocked, b.Password, c.Balance 
	  FROM PaylinkVirtualAccounts a inner join PaylinkUsers b
	  on a.UserId = b.Id join PaylinkAccounts c
	  on a.AccountId = c.Id
	  where b.username = '$mobile'";
	$selected = mssql_select_db("OperationalDB", $dbhandle)
        or die("Couldn't open database OperationalDB");
	$result = mssql_query($query);
	if (mssql_num_rows($result) == 0) {
		mssql_close($dbhandle);
		$value = "<tr><td><label>Không có tài khoản Paylinkplus</label></td></tr></br>";
	}else{
	$row = mssql_fetch_array($result);
	$blocked[0] = "Không khóa";
	$blocked[1] = "Bị khóa";
	$value = "<td><label>Tài khoản PaylinkPlus: </label></td>";
		$value = $value . "<TABLE BORDER='1' WIDTH=350>
		<tr>
			<td><label>Tên tài khoản</label></td>
			<td><label>Bị khóa</label></td>
			<td><label>Password</label></td>
			<td><label>Balance</label></td>
		</tr>
		<tr>
			<td>" . $row['Username'] . "</td> 
			<td>" . $blocked[$row['IsBlocked']] . "</td>     
			<td>" . $row['Password'] . "</td> 
			<td>" . $row['Balance'] . "</td> 
		</tr>
		</table>";
	}
	// Last 10 transactions
	$selected = mssql_select_db(PL_db, $dbhandle)
          or die("Couldn't open database $PL_db");
    //declare the SQL statement that will query the database	
	$query = "SELECT TOP 10 TerminalId,Type,AmountPayment,PaymentDate,
	Status,CAST(ProcessDescription AS TEXT) AS ProcessDescription FROM TerminalPayments
	where Number = '$mobile'
	and ProcessDate > DATEADD(month,-1,CURRENT_TIMESTAMP)
	order by ProcessDate desc ";    
	//execute the SQL query and return records
    $result = mssql_query($query);        	
	if (mssql_num_rows($result) == 0) {
		mssql_close($dbhandle);
		$value = $value . "<td><label>Không có thông tin giao dịch Paylink</label></td><br>";
	}else{
		$stt[2] = "Đã xử lý";
		$stt[3] = "Lỗi";
		$stt[7] = "Đã xóa"; 
		$value = $value . "<br><td><label>Giao dịch máy Paylink gần đây: </label></td>	
		<TABLE BORDER='1' WIDTH='100%'>
		<tr>
			<td><label>Ter-ID</label></td>
			<td><label>Nhà mạng</label></td>
			<td><label>Số tiền</label></td>
			<td><label>Ngày nạp</label></td>
			<td><label>Tình trạng</label></td>
			<td><label>Nhà cung cấp trả lời</label></td>
        </tr>";		
		while($row = mssql_fetch_array($result))
			{          			
				$info = getTerInfo($row['TerminalId']);				
				$value = $value . "<tr><td><a href='javascript:void(0)' onclick='alert(\"".$info."\")'>" . $row['TerminalId'] . "</a></td>
				<td>" . $row['Type'] . "</td> 
				<td>" . $row['AmountPayment'] . "</td>
				<td>" . $row['PaymentDate'] . "</td>
				<td>" . $stt[$row['Status']] . "</td>
				<td>" . $row['ProcessDescription'] . "</td>
				</tr>";
			}
		$value = $value . "</TABLE>";
		mssql_close($dbhandle);
	}		
	$value = $value . getAirtimeTrans($mobile);
	$value = $value . getTheDaNang($mobile);
	return $value;	
}

function getInfo($mobile)
{	
	$con = mysql_connect(Mbv_host,Mbv_user,Mbv_pass);
    if (!$con)
      {
        die('Could not connect MBV database: ' . mysql_error());
      }
    mysql_select_db(Mbv_db, $con);    
    $search = "%" . substr($mobile,2);    
    $sql = "SELECT account_name FROM xaccounts 
    WHERE mbvMobile LIKE '$search' order by account_name asc LIMIT 10";
    $result = mysql_query($sql);
    $value = '';
	if (mysql_num_rows($result)==0){
        $value = $value . "<tr><label>Không có thông tin ví</label></tr><br>";
    }
    else{
			$i=0;$acc=array();
			while($row=mysql_fetch_array($result))
			{
				$acc[$i]=$row["account_name"];
				$i++;
			}
			$value = $value . "<tr><td><label>Thông tin tài khoản ví: </td></label>";							
			for ($i=0;$i < mysql_num_rows($result);$i++)
			{				
				$value = $value . "<td><a href='index.php?menu=call_center&vi=" . $i ."'>" . $acc[$i] . " | " . "</a></td>";
			}				
			if(isset($_GET['vi'])) {
				$index = $_GET['vi'];
			}else{
				$index=0;
			}			
			$sql = "SELECT a.id, a.account_name, a.account_last_name, a.account_first_name, a.account_email, a.bank_account_number, a.domain, a.last_login,
			a.mbvMobile, b.current FROM xaccounts a INNER JOIN balances b
			ON a.id = b.user_id
			WHERE a.mbvMobile LIKE '$search' order by a.account_name LIMIT $index,1";
			$result = mysql_query($sql);            
			$row = mysql_fetch_array($result);			
			$value = $value . '</tr>
			<TABLE BORDER="1" WIDTH="100%">
			<tr>
				<td><label>Họ tên</label></td>
				<td><label>Tên ví </label></td>
				<td><label>Đăng nhập cuối</label></td>
				<td><label>Email</label></td>
				<td><label>Tài khoản NH</label></td>
				<td><label>Domain</label></td>
				<td><label>Số dư</label></td>
			</tr>
			<tr>
				<td>' . $row['account_first_name']  . ' ' . $row['account_first_name'] . '</td> 
				<td><b>' . $row['account_name'] . '</b></br><input type="button" value="Reset CTLBM" onClick="reset_CTLBM(\'' . $_SESSION['callcenter']['agente'] . '\',\'' . $row['account_name'] . '\')"></td
				<td>' . $row['last_login'] . '</td> 				
				<td>' . $row['account_email'] . '</td> 
				<td>' . $row['bank_account_number'] . '</td>
				<td>' . $row['domain'] . '</td>  
				<td>' . $row['current'] . '</td>    
			</tr></TABLE>';
			// get last 10 transacitons	
            $u = $row['id'];            
            $sql1= "SELECT ref_user_name, xtran_type, description, amount, console_status, confirmation_code, created_at FROM xtrans
                                    WHERE user_id='$u'
									AND created_at >= DATE_SUB(CURRENT_DATE, INTERVAL 30 DAY)
                                    ORDER BY created_at DESC
                                    LIMIT 10";
         	$result1 = mysql_query($sql1);
             if (mysql_num_rows($result1)==0){
                        $value = $value . "<tr><td><label>Không có thông tin giao dịch của ví</label></td></tr><br>";
             }else{            
						$value = $value . "<tr><label>Giao dịch gần đây: </label></tr>";                        
						$value = $value . "<TABLE BORDER='1' WIDTH='100%'>
						<tr>
							<td><label>Ví liên quan</label></td>
							<td><label>Loại GD</label></td>
							<td><label>Mô tả</label></td>
		                    <td><label>Ngày GD</label></td>
							<td><label>Số tiền</label></td>
							<td><label>Tình trạng</label></td>
                        </tr>";
                        while($row1 = mysql_fetch_array($result1))
                        {
                             $value = $value . "<tr><td>" . $row1['ref_user_name'] . "</td> 
                            <td>" . $row1['xtran_type'] . "</td> 
                            <td>" . $row1['description'] . "</td>
                            <td>" . $row1['created_at'] . "</td>
                            <td>" . $row1['amount'] . "</td>
                            <td>" . $row1['console_status'] . "</td>
                            </tr>";
                        }
                        $value = $value . "</TABLE>";
     		}                                                          
    }  
    mysql_close($con);
	$value = $value . getAirtimeTrans($mobile);
	//1800 info
	//$value = $value . "<h3>PAYLINK INFOMATION:</h3>";
	$dbhandle = mssql_connect(PL_host, PL_user, PL_pass)
          or die("Couldn't connect to SQL Server on $PL_host");      
    
	//paylinkplus
	$query="SELECT b.Username, a.IsBlocked, b.Password, c.Balance 
	  FROM PaylinkVirtualAccounts a inner join PaylinkUsers b
	  on a.UserId = b.Id join PaylinkAccounts c
	  on a.AccountId = c.Id
	  where b.username = '$mobile'";
	$selected = mssql_select_db("OperationalDB", $dbhandle)
        or die("Couldn't open database OperationalDB");
	$result = mssql_query($query);
	if (mssql_num_rows($result) == 0) {
		mssql_close($dbhandle);
		$value = $value . "<tr><td><label>Không có tài khoản Paylinkplus</label></td></tr></br>";
	}else{
	$row = mssql_fetch_array($result);
	$blocked[0] = "Không khóa";
	$blocked[1] = "Bị khóa";
	$value = $value . "<td><label>Tài khoản PaylinkPlus: </label></td>";
		$value = $value . "<TABLE BORDER='1' WIDTH=350>
		<tr>
			<td><label>Tên tài khoản</label></td>
			<td><label>Bị khóa</label></td>
			<td><label>Password</label></td>
			<td><label>Balance</label></td>
		</tr>
		<tr>
			<td>" . $row['Username'] . "</td> 
			<td>" . $blocked[$row['IsBlocked']] . "</td>     
			<td>" . $row['Password'] . "</td> 
			<td>" . $row['Balance'] . "</td> 
		</tr>
		</table>";
	}
	// Last 10 transactions
	$selected = mssql_select_db(PL_db, $dbhandle)
          or die("Couldn't open database $PL_db");
    //declare the SQL statement that will query the database	
	$query = "SELECT TOP 10 TerminalId,Type,AmountPayment,PaymentDate,
	Status,CAST(ProcessDescription AS TEXT) AS ProcessDescription FROM TerminalPayments
	where Number = '$mobile'
	and ProcessDate > DATEADD(month,-1,CURRENT_TIMESTAMP)
	order by ProcessDate desc ";    
	//execute the SQL query and return records
    $result = mssql_query($query);        	
	if (mssql_num_rows($result) == 0) {
		mssql_close($dbhandle);
		$value = $value . "<td><label>Không có thông tin giao dịch Paylink</label></td><br>";
	}else{
		$stt[2] = "Đã xử lý";
		$stt[3] = "Lỗi";
		$stt[7] = "Đã xóa"; 
		$value = $value . "<br><td><label>Giao dịch máy Paylink gần đây: </label></td>	
		<TABLE BORDER='1' WIDTH='100%'>
		<tr>
			<td><label>Ter-ID</label></td>
			<td><label>Nhà mạng</label></td>
			<td><label>Số tiền</label></td>
			<td><label>Ngày nạp</label></td>
			<td><label>Tình trạng</label></td>
			<td><label>Nhà cung cấp trả lời</label></td>
        </tr>";		
		while($row = mssql_fetch_array($result))
			{          			
				$info = getTerInfo($row['TerminalId']);				
				$value = $value . "<tr><td><a href='javascript:void(0)' onclick='alert(\"".$info."\")'>" . $row['TerminalId'] . "</a></td>
				<td>" . $row['Type'] . "</td> 
				<td>" . $row['AmountPayment'] . "</td>
				<td>" . $row['PaymentDate'] . "</td>
				<td>" . $stt[$row['Status']] . "</td>
				<td>" . $row['ProcessDescription'] . "</td>
				</tr>";
			}
		$value = $value . "</TABLE>";
		mssql_close($dbhandle);
	}		
	$value = $value . getTheDaNang($mobile);
    return $value;
}
//end edited by Tri Do 

//generate form edited by Tri Do
function manejarSesionActiva_HTML_generarFormulario($smarty, $sDirLocalPlantillas, $infoLlamada, $infoCampania)
{	
	 $con = mysql_connect(rtHost,rtUser,rtPass);
    if (!$con)
      {
	    die('Could not connect: ' . mysql_error());
      }
    mysql_select_db('rt', $con);
	mysql_query("SET character_set_results = 'utf8', character_set_client = 'utf8', character_set_connection = 'utf8', character_set_database = 'utf8', character_set_server = 'utf8'", $con);
    $sql = "select distinct Category from CustomFieldValues where CustomField = 4 order by Category";	
    $query = mysql_query($sql) or die('Could not connect: ' . mysql_error());; 
    $html =  '<div id="elastix-callcenter-formulario-3" class="ui-tabs-panel ui-widget-content ui-corner-bottom">
    <table cellpadding="0" border="0">
            <tbody><tr>
                <th colspan="2"><h3>MẪU THÔNG TIN KHIẾU NẠI KHÁCH HÀNG</h3></th>
            </tr><tr><td></td></tr>
            <tr>
                <td><label>Tên khách hàng: </label></td>                
                <td>
					<input type="text" value="" class="elastix-callcenter-field ui-widget-content ui-corner-all" maxlength="250" size="50" id="' . custName . '" name="' . custName .'">
                </td>
            </tr>
            <tr>
                <td><label>Số điện thoại: </label></td>                
                <td>
					<input type="text" value="' . $infoLlamada['phone'] . '" class="elastix-callcenter-field 
ui-widget-content ui-corner-all" 
maxlength="250" size="50" id="'. custNum . '" name="'. custNum . '">
                </td>
            </tr>
            <tr>
                <td><label>Loại xử lý: </label></td>                
                <td>
					<select style="text-align:left"  class="elastix-callcenter-field ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only" id="'. custCat . '" name="'. custCat . '" onchange="AjaxFunction(this.value);">
					<option value="">Select One</option>';
	while ($row = mysql_fetch_array($query)) 
	{ 	     	
		$html = $html . "<option values = '". $row[0] . "'>".$row[0]."</option>"; 	 
	}  
	
	$html = $html . '</select> </td></tr>           
			<tr>
                <td><label>Chi tiết: </label></td>                
                <td>
					<select style="text-align:left" class="elastix-callcenter-field ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only" id="'. custSubcat . '" name="'. custSubcat . '">
					</select>
                </td>			
			</tr>			
            <tr>
                <td><label>Ghi chú/Mô tả: </label></td>                
                <td>
					<textarea class="elastix-callcenter-field ui-widget-content ui-corner-all" maxlength="1000" cols="50" rows="3" id="'. custDesc . '" name="'. custDesc . '"></textarea>
                </td>
            </tr>           
			<tr>
                <td><label>Resolved? </label></td>                
                <td>
					<input type="checkbox" onclick = "chkResolved(this)" value="No" class="elastix-callcenter-field ui-widget-content ui-corner-all" id="'. custRes . '" name="'. custRes . '">                				
					<input disabled="disabled" type="text" value="' . $infoLlamada['phone']. '" class="elastix-callcenter-field ui-widget-content ui-corner-all" id="' . custCallerid . '" name="' . custCallerid .'">
					<input disabled="disabled" type="text" value="' . getMail(substr($infoLlamada['agent_number'],strpos($infoLlamada['agent_number'],"/")+1)) . '" class="elastix-callcenter-field ui-widget-content ui-corner-all" id="' . agentEmail . '" name="' . agentEmail .'">
                </td>
            </tr> 	
    </tbody></table>
   </div>';
   
	mysql_close($con);
    $smarty->assign(array(    
            'FORMS' => $html,
            'BTN_GUARDAR_FORMULARIOS'   =>  _tr('Submit ticket'),
			'FORM_HISTORY'	=> _tr(getFormHistory($infoLlamada['phone'])),
            ));    
	return $smarty->fetch("$sDirLocalPlantillas/agent_console_formulario_edited.tpl");           
}
//end of generate form edited by Tri Do

// Se usa $infoLlamada['call_survey'] , $infoCampania['forms']
/* comment by Tri Do
function manejarSesionActiva_HTML_generarFormulario($smarty, $sDirLocalPlantillas, $infoLlamada, $infoCampania)
{
    // Se puebla current_value con los valores recogidos previamente, si existen
    if (isset($infoCampania['forms']) && is_array($infoCampania['forms'])) {
        foreach ($infoCampania['forms'] as $idForm => $tuplaForm) {
        	foreach ($tuplaForm['fields'] as $idxCampo => $tuplaCampo) {
        		if (isset($infoLlamada['call_survey'][$idForm]) && 
                    isset($infoLlamada['call_survey'][$idForm][$tuplaCampo['id']]))
                    $infoCampania['forms'][$idForm]['fields'][$idxCampo]['current_value'] = 
                        $infoLlamada['call_survey'][$idForm][$tuplaCampo['id']]['value']; 
        	}
        }
        $smarty->assign(array(
            'FORMS'                     => $infoCampania['forms'],
            'BTN_GUARDAR_FORMULARIOS'   =>  _tr('Save data'),
			'FORM_HISTORY'	=> _tr(getFormHistory($infoLlamada['phone'])),
            ));
        return $smarty->fetch("$sDirLocalPlantillas/agent_console_formulario.tpl");
    } else {
    	return _tr('No forms available for this call');
    }
}
*/

function manejarSesionActiva_agentLogout($oPaloConsola)
{
    $respuesta = array(
        'action'    =>  'logged-out',   // logged-out error
        'message'   =>  '(no message)',
    );
    $bExito = $oPaloConsola->logoutAgente();
    if (!$bExito) {
        $respuesta['action'] = 'error';
        $respuesta['message'] = _tr('Error while logging out agent').' - '.$oPaloConsola->errMsg;
    }
    
    // Se asume que el único error posible en logout es que el agente ya
    // esté deslogoneado.
    $_SESSION['callcenter']['estado_consola'] = 'logged-out';
    $_SESSION['callcenter']['agente'] = NULL;
    $_SESSION['callcenter']['agente_nombre'] = NULL;
    $_SESSION['callcenter']['extension'] = NULL;
    
    $json = new Services_JSON();
    Header('Content-Type: application/json');
    return $json->encode($respuesta);
}

function manejarSesionActiva_hangup($oPaloConsola)
{
    $respuesta = array(
        'action'    =>  'hangup',
        'message'   =>  '(no message)',
    );
    $bExito = $oPaloConsola->colgarLlamada();
    if (!$bExito) {
        $respuesta['action'] = 'error';
        $respuesta['message'] = _tr('Error while hanging up call').' - '.$oPaloConsola->errMsg;
    }

    $json = new Services_JSON();
    Header('Content-Type: application/json');
    return $json->encode($respuesta);
}

function manejarSesionActiva_agentBreak($oPaloConsola)
{
    $respuesta = array(
        'action'    =>  'break',
        'message'   =>  '(no message)',
    );
    $idBreak = getParameter('breakid');
    if (is_null($idBreak) || !ctype_digit($idBreak)) {
    	$respuesta['action'] = 'error';
        $respuesta['message'] = _tr('Invalid or missing break ID');
    } else {
        $bExito = $oPaloConsola->iniciarBreak($idBreak);
        if (!$bExito) {
            $respuesta['action'] = 'error';
            $respuesta['message'] = _tr('Error while starting break').' - '.$oPaloConsola->errMsg;
        }
    }

    $json = new Services_JSON();
    Header('Content-Type: application/json');
    return $json->encode($respuesta);
}

function manejarSesionActiva_agentUnBreak($oPaloConsola)
{
    $respuesta = array(
        'action'    =>  'unbreak',
        'message'   =>  '(no message)',
    );
    $bExito = $oPaloConsola->terminarBreak();
    if (!$bExito) {
        $respuesta['action'] = 'error';
        $respuesta['message'] = _tr('Error while stopping break').' - '.$oPaloConsola->errMsg;
    }

    $json = new Services_JSON();
    Header('Content-Type: application/json');
    return $json->encode($respuesta);
}

function manejarSesionActiva_agentTransfer($oPaloConsola)
{
    $respuesta = array(
        'action'    =>  'transfer',
        'message'   =>  '(no message)',
    );
    $sTransferExt = getParameter('extension');
    if (is_null($sTransferExt) || !ctype_digit($sTransferExt)) {
        $respuesta['action'] = 'error';
        $respuesta['message'] = _tr('Invalid or missing extension to transfer');
    } else {
        $bExito = $oPaloConsola->transferirLlamada($sTransferExt, in_array(getParameter('atxfer'), array('true', 'checked')));
        if (!$bExito) {
            $respuesta['action'] = 'error';
            $respuesta['message'] = _tr('Error while transferring call').' - '.$oPaloConsola->errMsg;
        }
    }

    $json = new Services_JSON();
    Header('Content-Type: application/json');
    return $json->encode($respuesta);
}

function manejarSesionActiva_confirmContact($oPaloConsola, $estado)
{
    $respuesta = array(
        'action'    =>  'confirmed',
        'message'   =>  _tr('Contact successfully confirmed'),
    );
    $idContact = getParameter('id_contact');
    if (is_null($idContact) || !ctype_digit($idContact)) {
        $respuesta['action'] = 'error';
        $respuesta['message'] = _tr('Invalid or missing contact ID');
    } elseif (!isset($estado['callinfo']) || $estado['callinfo']['calltype'] != 'incoming') {
        $respuesta['action'] = 'error';
        $respuesta['message'] = _tr('Agent not handling an incoming call');
    } else {
        $bExito = $oPaloConsola->confirmarContacto($estado['callinfo']['callid'], $idContact);
        if (!$bExito) {
            $respuesta['action'] = 'error';
            $respuesta['message'] = _tr('Error while confirming contact').' - '.$oPaloConsola->errMsg;
        }
    }

    $json = new Services_JSON();
    Header('Content-Type: application/json');
    return $json->encode($respuesta);
}

function manejarSesionActiva_scheduleCall($oPaloConsola)
{
    $respuesta = array(
        'action'    =>  'scheduled',
        'message'   =>  _tr('Call successfully scheduled'),
    );

    $infoAgendar = getParameter('data');
    foreach (array('schedule_new_phone', 'schedule_new_name', 
        'schedule_use_daterange', 'schedule_use_sameagent', 
        'schedule_date_start', 'schedule_date_end', 'schedule_time_start',
        'schedule_time_end') as $k) 
        if (!isset($infoAgendar[$k])) $infoAgendar[$k] = NULL;
    
    $schedule = in_array($infoAgendar['schedule_use_daterange'], array('true', 'checked')) ? array(
        'date_init' =>  $infoAgendar['schedule_date_start'],
        'date_end'  =>  $infoAgendar['schedule_date_end'], 
        'time_init' =>  $infoAgendar['schedule_time_start'],
        'time_end'  =>  $infoAgendar['schedule_time_end'],
    ) : NULL;
    $sameagent = in_array($infoAgendar['schedule_use_sameagent'], array('true', 'checked'));
    $newphone = $infoAgendar['schedule_new_phone'];
    $newname = $infoAgendar['schedule_new_name'];
    
    if (is_array($schedule) && ($schedule['date_init'] == '' || $schedule['date_end'] == '' ||
        $schedule['time_init'] == '' || $schedule['time_end'] == '')) {
        $respuesta = array(
            'action'    =>  'error',
            'message'   =>  _tr('Invalid or incomplete schedule'),
        );
    } else {
        $bExito = $oPaloConsola->agendarLlamada($schedule, $sameagent, $newphone, $newname);
        if (!$bExito) {
            $respuesta['action'] = 'error';
            $respuesta['message'] = _tr('Error while scheduling call').' - '.$oPaloConsola->errMsg;
        }
    }
    $json = new Services_JSON();
    Header('Content-Type: application/json');
    return $json->encode($respuesta);
}

//save form edited by Tri Do
function getMail($number)
{
	$con = mysql_connect('localhost',ccUser,ccPass);
    if (!$con)
      {
	    die('Could not connect: ' . mysql_error());
      }
    mysql_select_db('call_center', $con);
    $sql = "select email from agent where number = '$number' limit 1";	
    $query = mysql_query($sql) or die('Could not connect: ' . mysql_error());; 
	$row = mysql_fetch_array($query);
	if (mysql_num_rows($query)==0){
		return 'ngoc.pham@mobivi.com';
	}
	mysql_close($con);
	return $row[0];
}

function mailTicket($to, $subject, $message, $header='') {
  $header_ = 'MIME-Version: 1.0' . "\r\n" . 'Content-type: text/plain; charset=UTF-8' . "\r\n";
  mail($to, '=?UTF-8?B?'.base64_encode($subject).'?=', $message, $header_ . $header);
}

function manejarSesionActiva_saveForms($oPaloConsola, $estado)
{
    $formdata = getParameter('data');
    $formInfo = array();
    $i = 0;        
    $getValue = array();
    foreach ($formdata as $tupladata) {
    	$regs = NULL;
        if (preg_match('/^field-(\d+)-(\d+)$/', $tupladata[0], $regs)) {
        	$formInfo[$regs[1]][$regs[2]] = $tupladata[1];
            $getValue[$i] = $tupladata[1];                
        }
    	$i ++;
    }    
    $respuesta = array(
        'action'    =>  'saved',
        'message'   =>  _tr('Đã gửi ticket cho số điện thoại ' . $getValue[6] . '!'),
    );
    //0 -> Ten KH    1 -> SDT   2,3 -> Cat    4 -> Desc   5->Resolved 6->callerid 
    $to = 'rt@callcenter.mobivi.com';
    $message =  'Tên khách hàng: '. $getValue[0] . '
Số điện thoại: ' . $getValue[1] . '
Loại xử lý: ' . $getValue[2] . '-' . $getValue[3]  . '
Mô tả: ' . $getValue[4];
    $subject =  $getValue[6] . '_' . $getValue[2] . ': ' . $getValue[3] . '_' . date('dmY-His') . ($getValue[5] == "Resolved" ? ' (Resolved)' : '');
    $from = $getValue[7];   
    mailTicket($to,$subject,$message,"From: " . $from);
        
    $json = new Services_JSON();
    Header('Content-Type: application/json');
    return $json->encode($respuesta);
}
//end of save form edited by Tri Do
/*
function manejarSesionActiva_saveForms($oPaloConsola, $estado)
{
    $respuesta = array(
        'action'    =>  'saved',
        'message'   =>  _tr('Form data successfully saved'),
    );

    $formdata = getParameter('data');
    if (!is_array($formdata)) {
        $respuesta = array(
            'action'    =>  'error',
            'message'   =>  _tr('Invalid or incomplete form data'),
        );
    } else {
        $formInfo = array();
        foreach ($formdata as $tupladata) {
        	$regs = NULL;
            if (preg_match('/^field-(\d+)-(\d+)$/', $tupladata[0], $regs)) {
            	$formInfo[$regs[1]][$regs[2]] = $tupladata[1];
                $_SESSION['callcenter']['ultimo_callsurvey']['call_survey'][$regs[1]][$regs[2]] = array(
                    'label' =>  '', // TODO: asignar desde formulario de campaña
                    'value' =>  $tupladata[1],
                );
            }
        }

        $bExito = $oPaloConsola->guardarDatosFormularios(
            $_SESSION['callcenter']['ultimo_calltype'], //$estado['callinfo']['calltype'],
            $_SESSION['callcenter']['ultimo_callid'], //$estado['callinfo']['callid'],
            $formInfo);
        if (!$bExito) {
            $respuesta['action'] = 'error';
            $respuesta['message'] = _tr('Error while saving form data').' - '.$oPaloConsola->errMsg;
        }
    }
    $json = new Services_JSON();
    Header('Content-Type: application/json');
    return $json->encode($respuesta);
}
*/
function manejarSesionActiva_checkStatus($module_name, $smarty, 
    $sDirLocalPlantillas, $oPaloConsola, $estado)
{
    $respuesta = array();

    ignore_user_abort(true);
    set_time_limit(0);

    $sNombrePausa = NULL;
    $iDuracionLlamada = NULL;
    $iDuracionPausa = $iDuracionPausaActual = NULL;
    
    $estadoCliente = getParameter('clientstate');

    // Validación del estado del cliente:
    // onhold break_id calltype campaign_id callid
    $estadoCliente['onhold'] = isset($estadoCliente['onhold']) 
        ? ($estadoCliente['onhold'] == 'true') 
        : false;
    foreach (array('break_id', 'calltype', 'campaign_id', 'callid') as $k) {
        if (!isset($estadoCliente[$k]) || $estadoCliente[$k] == 'null') 
            $estadoCliente[$k] = NULL;
    }
    if (is_null($estadoCliente['calltype'])) {
        $estadoCliente['campaign_id'] = $estadoCliente['callid'] = NULL;
    } elseif (is_null($estadoCliente['callid'])) {
        $estadoCliente['campaign_id'] = $estadoCliente['calltype'] = NULL;
    } elseif (is_null($estadoCliente['campaign_id']) && $estadoCliente['calltype'] != 'incoming') {
        $estadoCliente['calltype'] = $estadoCliente['callid'] = NULL;
    }

    // Modo a funcionar: Long-Polling, o Server-sent Events
    $sModoEventos = getParameter('serverevents');
    $bSSE = (!is_null($sModoEventos) && $sModoEventos); 
    if ($bSSE) {
        Header('Content-Type: text/event-stream');
        printflush("retry: 1\n");
    } else {
    	Header('Content-Type: application/json');
    }

    // Respuesta inmediata si el agente ya no está logoneado
    if ($estado['estadofinal'] != 'logged-in') {
        // Respuesta inmediata si el agente ya no está logoneado
        jsonflush($bSSE, array(
            'event' =>  'logged-out',
        ));
        return;
    }
	
    // Verificación de la consistencia del estado de break
    if (!is_null($estado['pauseinfo'])) {
        $sNombrePausa = $estado['pauseinfo']['pausename'];
        $iDuracionPausaActual = time() - strtotime($estado['pauseinfo']['pausestart']);
        $iDuracionPausa = $iDuracionPausaActual + $_SESSION['callcenter']['break_acumulado'];
    } else {
        /* Si esta condición se cumple, entonces se ha perdido el evento 
         * pauseexit durante la espera en manejarSesionActiva_checkStatus().
         * Se hace la suposición de que el refresco ocurre poco después de
         * que termina el break, y que por lo tanto el error al usar time()
         * como fin del break es pequeño. 
         */
        if (!is_null($_SESSION['callcenter']['break_iniciado'])) {
           $_SESSION['callcenter']['break_acumulado'] += time() - strtotime($_SESSION['callcenter']['break_iniciado']);
        }
        $_SESSION['callcenter']['break_iniciado'] = NULL;
    }
    if (!is_null($estado['pauseinfo']) && 
        (is_null($estadoCliente['break_id']) || $estadoCliente['break_id'] != $estado['pauseinfo']['pauseid'])) {
        // La consola debe de entrar en break
        $respuesta[] = construirRespuesta_breakenter($estado['pauseinfo']['pauseid']);
    } elseif (!is_null($estadoCliente['break_id']) && is_null($estado['pauseinfo'])) {
        // La consola debe de salir del break
        $respuesta[] = construirRespuesta_breakexit();
    }

    // Verificación de la consistencia del estado de hold
    if (!$estadoCliente['onhold'] && $estado['onhold']) {
        // La consola debe de entrar en hold
        $respuesta[] = construirRespuesta_holdenter();
    } elseif ($estadoCliente['onhold'] && !$estado['onhold']) {
        // La consola debe de salir de hold
        $respuesta[] = construirRespuesta_holdexit();
    }
    
    if (!is_null($estado['callinfo'])) {
        $iDuracionLlamada = time() - strtotime($estado['callinfo']['linkstart']);           
    }
    
    // Verificación de atención a llamada
    if (!is_null($estado['callinfo']) && 
        (is_null($estadoCliente['calltype']) || 
            $estadoCliente['calltype'] != $estado['callinfo']['calltype'] ||
            $estadoCliente['campaign_id'] != $estado['callinfo']['campaign_id'] ||
            $estadoCliente['callid'] != $estado['callinfo']['callid'])) {

        // Información sobre la llamada conectada
        $infoLlamada = $oPaloConsola->leerInfoLlamada(
            $estado['callinfo']['calltype'],
            $estado['callinfo']['campaign_id'],
            $estado['callinfo']['callid']);

        // Leer información del formulario de la campaña
        if ($estado['callinfo']['calltype'] == 'incoming' && is_null($estado['callinfo']['campaign_id'])) {
            $infoCampania['forms'] = NULL;
        } else {
            $infoCampania = $oPaloConsola->leerInfoCampania(
                $estado['callinfo']['calltype'],
                $estado['callinfo']['campaign_id']);
        }

        // Almacenar para regenerar formulario
        $_SESSION['callcenter']['ultimo_calltype'] = $estado['callinfo']['calltype'];
        $_SESSION['callcenter']['ultimo_callid'] = $estado['callinfo']['callid'];
        $_SESSION['callcenter']['ultimo_callsurvey']['call_survey'] = $infoLlamada['call_survey'];
        $_SESSION['callcenter']['ultimo_campaignform']['forms'] = $infoCampania['forms'];

        $respuesta[] = construirRespuesta_agentlinked($smarty, $sDirLocalPlantillas, 
            $oPaloConsola, $estado['callinfo'], $infoLlamada, $infoCampania);
    } elseif (!is_null($estadoCliente['calltype']) && is_null($estado['callinfo'])) {
        // La consola dejó de atender una llamada
        $respuesta[] = construirRespuesta_agentunlinked();
    }

    // Ciclo de verificación para Server-sent Events
    $sAgente = 'Agent/'.$_SESSION['callcenter']['agente'];
    $iTimeoutPoll = PaloSantoConsola::recomendarIntervaloEsperaAjax();
    $bReinicioSesion = FALSE;
    do {
        $oPaloConsola->desconectarEspera();
        
        // Se inicia espera larga con el navegador...
        session_commit();
        $iTimestampInicio = time();
        
        $respuestaEventos = array();
    	
        while (connection_status() == CONNECTION_NORMAL && 
            count($respuestaEventos) <= 0 && count($respuesta) <= 0 
            && time() - $iTimestampInicio <  $iTimeoutPoll) {
            
            $listaEventos = $oPaloConsola->esperarEventoSesionActiva();
            if (is_null($listaEventos)) {
                // Ocurrió una excepción al esperar eventos
                @session_start();

                $respuesta[] = array(
                    'event' =>  'logged-out',
                );

                // Eliminar la información de login
                $_SESSION['callcenter'] = generarEstadoInicial();
                $bReinicioSesion = TRUE;
                break;
            }
            
            foreach ($listaEventos as $evento) 
            if (isset($evento['agent_number']) && $evento['agent_number'] == $sAgente) 
            switch ($evento['event']) {
            case 'agentloggedout':
                // Reiniciar la sesión para poder modificar las variables
                @session_start();

                $respuesta[] = array(
                    'event' =>  'logged-out',
                );

                // Eliminar la información de login
                $_SESSION['callcenter'] = generarEstadoInicial();
                $bReinicioSesion = TRUE;
                break;
            case 'pausestart':
                unset($respuestaEventos[$evento['pause_class']]);
                switch ($evento['pause_class']) {
                case 'break':
                    if (is_null($estadoCliente['break_id']) ||
                        $estadoCliente['break_id'] != $evento['pause_type']) {
                        $sNombrePausa = $evento['pause_name'];
                        $respuestaEventos['break'] = construirRespuesta_breakenter($evento['pause_type']);
                    }
                    @session_start();
                    $iDuracionPausaActual = time() - strtotime($evento['pause_start']);
                    $iDuracionPausa = $iDuracionPausaActual + $_SESSION['callcenter']['break_acumulado'];
                    $_SESSION['callcenter']['break_iniciado'] = $evento['pause_start'];
                    break;
                case 'hold':
                    if (!$estadoCliente['onhold']) {
                        $respuestaEventos['hold'] = construirRespuesta_holdenter();
                    }
                    break;
                }
                break;
            case 'pauseend':
                unset($respuestaEventos[$evento['pause_class']]);
                switch ($evento['pause_class']) {
                case 'break':
                    if (!is_null($estadoCliente['break_id'])) {
                        $respuestaEventos['break'] = construirRespuesta_breakexit();
                    }
                    @session_start();
                    $_SESSION['callcenter']['break_acumulado'] += $evento['pause_duration'];
                    $_SESSION['callcenter']['break_iniciado'] = NULL;
                    break;
                case 'hold':
                    if ($estadoCliente['onhold']) {
                        $respuestaEventos['hold'] = construirRespuesta_holdexit();
                    }
                    break;
                }
                break;
            case 'agentlinked':
                unset($respuestaEventos['llamada']);
                /* Actualizar la interfaz si entra una nueva llamada, o si 
                 * la llamada activa anterior es reemplazada. */
                if (is_null($estadoCliente['calltype']) || 
                    $estadoCliente['calltype'] != $evento['call_type'] ||
                    $estadoCliente['campaign_id'] != $evento['campaign_id'] ||
                    $estadoCliente['callid'] != $evento['call_id']) {
                    $nuevoEstado = array(
                        'calltype'      =>  $evento['call_type'],
                        'campaign_id'   =>  $evento['campaign_id'],
                        'linkstart'     =>  $evento['datetime_linkstart'],
                        'callid'        =>  $evento['call_id'],
                        'callnumber'    =>  $evento['phone'],
                    );
                    $iDuracionLlamada = time() - strtotime($nuevoEstado['linkstart']);

                    // Leer información del formulario de la campaña
                    if ($nuevoEstado['calltype'] == 'incoming' && is_null($nuevoEstado['campaign_id'])) {
                        $infoCampania['forms'] = NULL;
                    } else {
                        $infoCampania = $oPaloConsola->leerInfoCampania(
                            $nuevoEstado['calltype'],
                            $nuevoEstado['campaign_id']);
                    }

                    // Almacenar para regenerar formulario
                    @session_start();
                    $_SESSION['callcenter']['ultimo_calltype'] = $nuevoEstado['calltype'];
                    $_SESSION['callcenter']['ultimo_callid'] = $nuevoEstado['callid'];
                    $_SESSION['callcenter']['ultimo_callsurvey']['call_survey'] = $evento['call_survey'];
                    $_SESSION['callcenter']['ultimo_campaignform']['forms'] = $infoCampania['forms'];

                    $respuestaEventos['llamada'] = construirRespuesta_agentlinked(
                        $smarty, $sDirLocalPlantillas, $oPaloConsola, $nuevoEstado, 
                        $evento, $infoCampania);
                }
                break;
            case 'agentunlinked':
                unset($respuestaEventos['llamada']);
                if (!is_null($estadoCliente['calltype'])) {
                    $respuestaEventos['llamada'] = construirRespuesta_agentunlinked();
                }
                break;
            }
        } // while(...)

        // Sólo debe haber hasta un evento de llamada, de break, de hold 
        if (isset($respuestaEventos['break'])) $respuesta[] = $respuestaEventos['break']; 
        if (isset($respuestaEventos['hold'])) $respuesta[] = $respuestaEventos['hold']; 
        if (isset($respuestaEventos['llamada'])) $respuesta[] = $respuestaEventos['llamada']; 

        // Agregar los textos a cambiar en la interfaz
        $sDescInicial = describirEstadoBarra($estadoCliente);
        $estadoFinal = $estadoCliente;
        foreach ($respuesta as $evento) switch ($evento['event']) {
        case 'holdenter':
            $estadoCliente['onhold'] = TRUE;
            break;
        case 'holdexit':
            $estadoCliente['onhold'] = FALSE;
            break;
        case 'breakenter': 
            $estadoFinal['break_id'] = $evento['break_id'];
            $estadoCliente['break_id'] = $evento['break_id'];
            break;
        case 'breakexit':
            $estadoFinal['break_id'] = NULL;
            $estadoCliente['break_id'] = NULL;
            break;
        case 'agentlinked':
            $estadoFinal['calltype'] = $evento['calltype'];
            $estadoCliente['calltype'] = $evento['calltype'];
            $estadoCliente['campaign_id'] = $evento['campaign_id'];
            $estadoCliente['callid'] = $evento['callid'];
            break;
        case 'agentunlinked':
            $estadoFinal['calltype'] = NULL;
            $estadoCliente['calltype'] = NULL;
            $estadoCliente['campaign_id'] = NULL;
            $estadoCliente['callid'] = NULL;
            break;
        }
        $sDescFinal = describirEstadoBarra($estadoFinal);
        $iPosEvento = count($respuesta) - 1;
        if ($iPosEvento >= 0 && $sDescInicial != $sDescFinal) switch ($sDescFinal) {
        case 'llamada':
            $respuesta[$iPosEvento]['txt_estado_agente_inicial'] = _tr('Đang có cuộc gọi đến');
            $respuesta[$iPosEvento]['class_estado_agente_inicial'] = 'elastix-callcenter-class-estado-activo';            
            $respuesta[$iPosEvento]['timer_seconds'] = $iDuracionLlamada;            
            break;
        case 'break':
            $respuesta[$iPosEvento]['txt_estado_agente_inicial'] = _tr('On break').': '.$sNombrePausa;
            $respuesta[$iPosEvento]['class_estado_agente_inicial'] = 'elastix-callcenter-class-estado-break';            
            $respuesta[$iPosEvento]['timer_seconds'] = $iDuracionPausa;            
            break;
        case 'ocioso':
            $respuesta[$iPosEvento]['txt_estado_agente_inicial'] = _tr('No active call');
            $respuesta[$iPosEvento]['class_estado_agente_inicial'] = 'elastix-callcenter-class-estado-ocioso';
            $respuesta[$iPosEvento]['timer_seconds'] = '';            
            break;
        }
        
        jsonflush($bSSE, $respuesta);
        
        $respuesta = array();

    } while($bSSE && !$bReinicioSesion && connection_status() == CONNECTION_NORMAL);
    $oPaloConsola->desconectarTodo();
}

function jsonflush($bSSE, $respuesta)
{
    $json = new Services_JSON();
    $r = $json->encode($respuesta);
    if ($bSSE)
        printflush("data: $r\n\n");
    else printflush($r);
}

function printflush($s)
{
    print $s;
    ob_flush();
    flush();
}

/*  
 * La barra de color de la interfaz debe terminar en uno de tres estados:
 * llamada, break, ocioso.
 */
function describirEstadoBarra($estado)
{
    if (!is_null($estado['calltype']))
        return 'llamada';
    if (!is_null($estado['break_id']))
        return 'break';
    return 'ocioso';
}

function construirRespuesta_breakenter($pause_id)
{
    return array(
        'event'                     =>  'breakenter',
        'break_id'                  =>  $pause_id,
        
        // Etiquetas a modificar en la interfaz
        'txt_btn_break' =>              _tr('End Break'),
    );
}

function construirRespuesta_breakexit()
{
    return array(
        'event'                     =>  'breakexit',
        
        // Etiquetas a modificar en la interfaz
        'txt_btn_break'             =>  _tr('Take Break'),
    );
}

function construirRespuesta_holdenter()
{
    return array(
        'event'         =>  'holdenter',
        
        // Etiquetas a modificar en la interfaz
        'txt_btn_hold' =>  _tr('End Hold'),
    );
}

function construirRespuesta_holdexit()
{
    return array(
        'event'         =>  'holdexit',
        
        // Etiquetas a modificar en la interfaz
        'txt_btn_hold' =>  _tr('Hold'),
    );
}

function construirRespuesta_agentlinked($smarty, $sDirLocalPlantillas, 
    $oPaloConsola, $callinfo, $infoLlamada, &$infoCampania)
{
    foreach (array('calltype', 'campaign_id', 'callid', 'callnumber',
        'agent_number', 'remote_channel') as $k) {
        if (!isset($infoLlamada[$k]) && isset($callinfo[$k]))
            $infoLlamada[$k] = $callinfo[$k];
    }
    if ($callinfo['calltype'] == 'incoming' && is_null($callinfo['campaign_id'])) {
        $infoCampania['queue'] = $infoLlamada['queue'];
        $infoCampania['script'] = $oPaloConsola->leerScriptCola($infoCampania['queue']);
        $infoCampania['forms'] = NULL;
    }
    if (is_null($infoCampania['script']) || $infoCampania['script'] == '')
        $infoCampania['script'] = _tr('(No script available)');

    // Fecha completa de la llamada
    $iDuracionLlamada = time() - strtotime($callinfo['linkstart']);

    // La consola empezó a atender a una llamada
    $registroCambio = array(
        'event'                 =>  'agentlinked',
        'calltype'              =>  $callinfo['calltype'],
        'campaign_id'           =>  $callinfo['campaign_id'],
        'callid'                =>  $callinfo['callid'],

        'txt_contacto_telefono' =>  $callinfo['callnumber'],
        'cronometro'            =>  sprintf('%02d:%02d:%02d', ($iDuracionLlamada - ($iDuracionLlamada % 3600)) / 3600, (($iDuracionLlamada - ($iDuracionLlamada % 60)) / 60) % 60, $iDuracionLlamada % 60),
        'llamada_informacion'   =>  manejarSesionActiva_HTML_generarInformacion($smarty, $sDirLocalPlantillas, $infoLlamada, $infoCampania),
        'llamada_formulario'    =>  manejarSesionActiva_HTML_generarFormulario($smarty, $sDirLocalPlantillas, $infoLlamada, $infoCampania),
        'llamada_script'        =>  $infoCampania['script'],
        'urlopentype'           =>  isset($infoCampania['urlopentype']) ? $infoCampania['urlopentype'] : NULL,
        'url'                   =>  NULL,
    );
    
    if (isset($infoCampania['urltemplate']) && !is_null($infoCampania['urltemplate'])) {
        $registroCambio['url'] = construirUrlExterno($infoCampania['urltemplate'], $infoLlamada);
    }
    
    // Asignaciones específicas para llamadas entrantes
    if ($callinfo['calltype'] == 'incoming') {
        $comboContactos = array();
        foreach ($infoLlamada['matching_contacts'] as $idContacto => $tuplaContacto) {
            $infoContactoViejo = array();
            $sDescripcionContacto = '';
            foreach ($tuplaContacto as $attrContacto) {
                $sDescripcionContacto .= $attrContacto['value'].' ';
                if (in_array($attrContacto['label'], array('first_name', 'last_name', 'cedula_ruc')))
                    $infoContactoViejo[$attrContacto['label']] = $attrContacto['value'];
            }
            if (count($infoContactoViejo) == 3) {
                $sDescripcionContacto = $infoContactoViejo['cedula_ruc'].
                    ' - '.$infoContactoViejo['first_name'].' '.$infoContactoViejo['last_name'];
            } else {
                /* TODO: dar formato adecuado para cuando contactos de llamadas 
                 * entrantes puedan tener atributos arbitrarios */
                
            }
            
            /* El htmlentities de clave y valor es necesario porque del lado 
             * Javascript, se usa concatenación directa de cadenas, porque el
             * objeto option devuelto por createElement no muestra la etiqueta
             * en IE6. Si se descubre la manera de hacerlo, hay que deshacer
             * el htmlentities aquí. */
            $comboContactos[htmlentities($idContacto, ENT_COMPAT, 'UTF-8')] = 
                htmlentities($sDescripcionContacto, ENT_COMPAT, 'UTF-8');
        }
        if (count($comboContactos) == 0) {
            $comboContactos['x'] = htmlentities(_tr('(no matching contacts)'), ENT_COMPAT, 'UTF-8');
        }

        $registroCambio['lista_contactos'] = $comboContactos;
        $registroCambio['puede_confirmar_contacto'] = (count($comboContactos) > 1);
    }
    
    // Asignaciones específicas para llamadas salientes
    if ($callinfo['calltype'] == 'outgoing') {

        /* TODO: el siguiente código asume que el atributo 1 es el nombre
         * del cliente. Esta suposición se hereda del callcenter anterior.
         * Se debe de idear un método para dar formato al nombre del cliente
         * a partir de cualquier combinación de columnas */
        $sNombreCliente = isset($infoLlamada['call_attributes'][1]) 
            ? $infoLlamada['call_attributes'][1]['value'] 
            : _tr('(unavailable)');

        $registroCambio['txt_contacto_nombres'] = $sNombreCliente;
    }

    return $registroCambio;
}

function construirRespuesta_agentunlinked()
{
    return array(
        'event'     =>  'agentunlinked',
    );	
}

function construirUrlExterno($s, $infoLlamada)
{
    $reemplazos = array(
        '{__AGENT_NUMBER__}'    =>  (isset($infoLlamada['agent_number']) 
                ? $infoLlamada['agent_number'] : ''),
        '{__REMOTE_CHANNEL__}'  =>  (isset($infoLlamada['remote_channel']) 
                ? $infoLlamada['remote_channel'] : ''),
        '{__CALL_TYPE__}'       =>  $infoLlamada['calltype'],
        '{__CAMPAIGN_ID__}'     =>  $infoLlamada['campaign_id'],
        '{__CALL_ID__}'         =>  $infoLlamada['callid'],
        '{__PHONE__}'           =>  $infoLlamada['callnumber'],
    );
    if (isset($infoLlamada['call_attributes'])) foreach ($infoLlamada['call_attributes'] as $tupla) {
        $reemplazos['{'.$tupla['label'].'}'] = $tupla['value'];
    }
    foreach ($reemplazos as $k => $v) {
        $s = str_replace($k, urlencode($v), $s);
    }
    return $s;
}

?>
