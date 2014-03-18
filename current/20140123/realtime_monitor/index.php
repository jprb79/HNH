<?php
function _moduleContent(&$smarty, $module_name)
{	 
    global $arrConf;
    global $arrLang;
		
    require_once "modules/agent_console/libs/elastix2.lib.php";
    require_once "modules/agent_console/libs/paloSantoConsola.class.php";
	require_once "modules/agent_console/getinfo.php";
    require_once "modules/agent_console/libs/JSON.php";
    require_once "modules/$module_name/configs/default.conf.php";
    require_once "modules/$module_name/libs/queue_waiting2.class.php";
    
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

    $sAction = '';
    $sContenido = '';

    $sAction = getParameter('action');

    $oPaloConsola = new PaloSantoConsola();
    switch ($sAction) {
    case 'checkStatus':
        $sContenido = agent_monitoring_checkStatus($module_name, $smarty, $sDirLocalPlantillas, $oPaloConsola);
        break;
    case 'queueWaitingStatus':
    		$sContenido = queue_waiting_checkStatus($module_name, $smarty, $sDirLocalPlantillas, $oPaloConsola);
			break;
    case 'show_call_history':
        $sContenido = refreshCallHistory();
        break;
    default:
        $sContenido = agent_monitoring_HTML($module_name, $smarty, $sDirLocalPlantillas, $oPaloConsola);
        break;
    }
    $oPaloConsola->desconectarTodo();
    
    return $sContenido;
}

// refresh history call - created by Tri Do
function refreshCallHistory()
{
    $respuesta = array(
        'action'    =>  'show_call_history',
        'message'   =>  '(no message)',
    );

    global $arrConf;
    $oCallHistory = new getInfoMainConsole();
    $oCallHistory->callcenter_db_connect($arrConf['cadena_dsn']);
    $bSuccess = $oCallHistory->getCallHistoryArray();
    $oCallHistory->callcenter_db_disconnect();

    if (!$bSuccess) {
        $respuesta['action'] = 'error';
        $respuesta['message'] = 'Error when getting history call: '.$oCallHistory->errMsg;
    }
    else
        $respuesta['message'] = $bSuccess;

    $json = new Services_JSON();
    Header('Content-Type: application/json');
    return $json->encode($respuesta);
}

function agent_monitoring_HTML($module_name, $smarty, $sDirLocalPlantillas, $oPaloConsola)
{
    global $arrLang;        

    $smarty->assign(array(
        'FRAMEWORK_TIENE_TITULO_MODULO' => existeSoporteTituloFramework(),
        'icon'                          => 'modules/'.$module_name.'/images/realtime.png',
        'title'                         =>  'Giám Sát & Quản Lý Tổng Đài Viên',
    ));
	 /* check if user login via agent console
	 if (!isset($_SESSION['callcenter']['agente'])) {
        $smarty->assign(array(
            'mb_title'  =>  'ERROR',
            'mb_message'    =>  'Đăng nhập tại <a href="index.php?menu=agent_console">Agent Console</a> trước khi sử dụng tính năng này',
        ));
		  return '';
    } */
    	
    $estadoMonitor = $oPaloConsola->listarEstadoMonitoreoAgentes();
    //echo '<pre>' . print_r($estadoMonitor,1) . '</pre>';die;
    if (!is_array($estadoMonitor)) {
        $smarty->assign(array(
            'mb_title'  =>  'ERROR',
            'mb_message'    =>  $oPaloConsola->errMsg,
        ));
    		return '';
    }
    ksort($estadoMonitor);	 
	 //echo '<pre>' . print_r($estadoMonitor,1) . '</pre>';die;
    $jsonData = contructDataJSON(mergeQueueAgent($estadoMonitor));
    	 
	 // convert to Smarty data	 
	 $sThemeDir = "modules/$module_name/themes/default";
	 $arrSmartyData = array();    	 	 
	 foreach($jsonData as $jsonKey=>$jsonRow){
		$arrSmartyData[$jsonKey] = $jsonRow;		
		// show time format hh:mm:ss base on last-status		
		$arrSmartyData[$jsonKey]['status_time'] = timestamp_format($jsonRow['sec_laststatus']);
		// show time format hh:mm:ss base on last-status		
		$arrSmartyData[$jsonKey]['sec_calls_time'] = timestamp_format($jsonRow['sec_calls']);
								 					
		switch ($jsonRow['agentstatus']) {
			case 'offline':
				// image status with title information
				$arrSmartyData[$jsonKey]['img_status'] = "$sThemeDir/images/status_offline-ic.png";
				$arrSmartyData[$jsonKey]['img_status_title'] = "$jsonKey: Chưa sẵn sàng";
				// status time (count from sec_laststatus)
				$arrSmartyData[$jsonKey]['status_time_label'] = 'Offline:';				
				break;		
			case 'online':
				// image status with title information
				$arrSmartyData[$jsonKey]['img_status'] = "$sThemeDir/images/status_free-ic.png";
				$arrSmartyData[$jsonKey]['img_status_title'] = "$jsonKey: Đang sẵn sàng";
				// status time (count from sec_laststatus)
				$arrSmartyData[$jsonKey]['status_time_label'] = 'Online:';				
				break;		
			case 'oncall':
				// image status with title information
				$arrSmartyData[$jsonKey]['img_status'] = "$sThemeDir/images/status_on_call-ic.png";
				$arrSmartyData[$jsonKey]['img_status_title'] = "$jsonKey: Đang gọi - Tại kênh: " . $jsonRow['linkqueue'];
				// status time (count from sec_laststatus)
				$arrSmartyData[$jsonKey]['status_time_label'] = 'Đang gọi: ';
				// get customer information				
				$agentState = getAgentState($jsonRow['agent_number']);
				$arrSmartyData[$jsonKey]['callnumber'] = $agentState['callnumber'];
				$arrSmartyData[$jsonKey]['callid'] = $agentState['callid'];
				$customer = getCustomer($arrSmartyData[$jsonKey]['callnumber']);
				$arrSmartyData[$jsonKey]['customer'] = ($customer?$customer:$arrSmartyData[$jsonKey]['callnumber']);
				break;		
			case 'paused':
				// image status with title information
				$arrSmartyData[$jsonKey]['img_status'] = "$sThemeDir/images/status_away-ic.png";
				$arrSmartyData[$jsonKey]['img_status_title'] = "$jsonKey: Đang tạm nghỉ";
				// status time (count from sec_laststatus)
				$arrSmartyData[$jsonKey]['status_time_label'] = 'Tạm nghỉ:';
				break;		
			default:				
		} 										 		
	 }	             
	 // history call
	global $arrConf;
	$oCallHistory = new getInfoMainConsole();
    $oCallHistory->callcenter_db_connect($arrConf['cadena_dsn']);
    $arrHistory = $oCallHistory->getCallHistoryArray();
    $oCallHistory->callcenter_db_disconnect();
	 //$oQueueStatus = new queue_waiting();
	 //$arrQueue = $oQueueStatus->showQueue();	 
	 //echo '<pre>' . print_r($res,1) . '</pre>';die;
    //smarty template assign	  	
    $smarty->assign(array(						
			'AGENT_STATUS'			=>		$arrSmartyData,
			'SUPERVISOR_NUMBER'		=>		$_SESSION['callcenter']['extension'],
			//'SUPERVISOR_QUEUE'	=>		getAgentQueue($_SESSION['callcenter']['agente'],$arrSmartyData),
			'THEME_PATH'			=>		$sThemeDir,			
			'CALL_HISTORY'			=>		$arrHistory,
			//'QUEUE_WAITING'		=>		$arrQueue,
			'ITEM_LIMIT'			=>		6,
    ));      
    //echo '<pre>' . print_r($arrTicket,1) . '</pre>';die;
	 $sContent = $smarty->fetch("$sDirLocalPlantillas/realtime_monitor.tpl");
	
	 //initilize with jsonData    
    foreach (array_keys($jsonData) as $k) unset($jsonData[$k]['agentname']);

    // Extraer la información que el navegador va a usar para actualizar
    $estadoCliente = array();
    foreach (array_keys($jsonData) as $k) {
        $estadoCliente[$k] = array(
            'agentstatus'        =>  $jsonData[$k]['agentstatus'],
            'oncallupdate'  =>  $jsonData[$k]['oncallupdate'],
            'linkqueue'		 =>  $jsonData[$k]['linkqueue'],
        );
    }
    $estadoHash = generarEstadoHash($module_name, $estadoCliente);
    
    $json = new Services_JSON();	  
    $INITIAL_CLIENT_STATE = $json->encode($jsonData);	
    $sJsonInitialize = <<<JSON_INITIALIZE
<script type="text/javascript">
$(function() {
    initialize_client_state($INITIAL_CLIENT_STATE, '$estadoHash');	
});
</script>
JSON_INITIALIZE;
    return $sContent.$sJsonInitialize;
}

function mergeQueueAgent($arr)
{
	//var_dump($arr);die;
	$arrData = array();	
	foreach($arr as $queue_key=>$queue_Row){
		ksort($queue_Row);
		foreach($queue_Row as $agent_key=>$agent_Row){						
			if (!array_key_exists($agent_key, $arrData)) {
				$arrData[$agent_key]=$agent_Row;				
				$arrData[$agent_key]['queues'][] = $queue_key;
				$arrData[$agent_key]['linkqueue'] = (is_null($agent_Row['linkstart'])?NULL:$queue_key);
				if (is_null($arrData[$agent_key]['lastsessionstart'])) 					
					$arrData[$agent_key]['lastsessionstart'] = date("Y-m-d 00:00:00");														
			}
			else{								
				$arrData[$agent_key]['queues'][] = $queue_key;
				$arrData[$agent_key]['sec_calls'] = (int) $arrData[$agent_key]['sec_calls']  + (int) $agent_Row['sec_calls']; 
				$arrData[$agent_key]['num_calls'] = (int) $arrData[$agent_key]['num_calls']  + (int) $agent_Row['num_calls'];
				if (!is_null($agent_Row['linkstart'])) {
					$arrData[$agent_key]['linkstart']	 = $agent_Row['linkstart'];
					$arrData[$agent_key]['linkqueue'] = $queue_key;					
				}					
			}
		}		
	}
	return $arrData;
}

function getAgentQueue($agent_number,$arr)
{
	var_dump($agent_number);
	$agent_key = 'AGENT_'. substr($agent_number,4,strlen($agent_number));
	return $arr[$agent_number]['queues'][0];
}

function getAgentState($agent_number)
{
    $oPaloConsola = new PaloSantoConsola('SIP/'.$agent_number);
	$estado = $oPaloConsola->estadoAgenteLogoneado('');
	$oPaloConsola->desconectarTodo();
	return $estado['callinfo'];
}

function getCustomer($number)
{
	return $number;
}

function generarEstadoHash($module_name, $estadoCliente)
{
    $estadoHash = md5(serialize($estadoCliente));
    $_SESSION[$module_name]['estadoCliente'] = $estadoCliente;
    $_SESSION[$module_name]['estadoClienteHash'] = $estadoHash;

    return $estadoHash;
}

function timestamp_format($i)
{
	return sprintf('%02d:%02d:%02d', 
        ($i - ($i % 3600)) / 3600, 
        (($i - ($i % 60)) / 60) % 60, 
        $i % 60);
}
// function for sorting by value
function aasort (&$array, $key) {
    $sorter=array();
    $ret=array();
    reset($array);
    foreach ($array as $ii => $va) {
        $sorter[$ii]=$va[$key];
    }
    arsort($sorter);
    foreach ($sorter as $ii => $va) {
        $ret[$ii]=$array[$ii];
    }
    $array=$ret;
}

// sort login agent will go first
function sortJSON($arr)
{
    $arrOffline = array();
    $arrLogin = array();
    // split into 2 array
    foreach ($arr as  $key=>$agent  ) {
        if ($agent['agentstatus'] == 'offline')
            $arrOffline[$key] = $arr[$key];
        else
            $arrLogin[$key] = $arr[$key];
    }
    // Sort sec_calls of Offline agent
    aasort($arrOffline,'num_calls');
    // Merg 2 array
    return array_merge($arrLogin,$arrOffline);
}

function contructDataJSON(&$estadoMonitor)
{
	$iTimestampActual = time();
	$jsonData = array();    
	ksort($estadoMonitor);
	$agentList = $estadoMonitor;
	foreach ($agentList as $sAgentChannel => $infoAgente) {
		$iTimestampEstado = NULL;
		$sNumeroAgente = $sAgentChannel;
	    if (substr($sNumeroAgente, 0, 6) == 'Agent/')
	       $sNumeroAgente = substr($sNumeroAgente, 6);
        elseif (substr($sNumeroAgente, 0, 4) == 'SIP/')
            $sNumeroAgente = substr($sNumeroAgente, 4);
	    $jsonKey = 'Agent_'.$sNumeroAgente;
	
	    switch ($infoAgente['agentstatus']) {
	    case 'offline':
	        if (!is_null($infoAgente['lastsessionend']))
	            $iTimestampEstado = strtotime($infoAgente['lastsessionend']);
	        break;
	    case 'online':
	        if (!is_null($infoAgente['lastsessionstart']))
	            $iTimestampEstado = strtotime($infoAgente['lastsessionstart']);
	        break;
	    case 'oncall':
	        if (!is_null($infoAgente['linkstart']))
	            $iTimestampEstado = strtotime($infoAgente['linkstart']);
	        break;
	    case 'paused':
	        if (!is_null($infoAgente['lastpausestart']))
	            $iTimestampEstado = strtotime($infoAgente['lastpausestart']);
	        break;
	    }
	
	   // Preparar estado inicial JSON
	   $jsonData[$jsonKey] = array(
	       'agentname'         =>  $infoAgente['agentname'],
	       'agentstatus'            =>  $infoAgente['agentstatus'],
	       'sec_laststatus'    =>  is_null($iTimestampEstado) ? NULL : ($iTimestampActual - $iTimestampEstado),
	       'sec_calls'         =>  $infoAgente['sec_calls'] + 
	           (is_null($infoAgente['linkstart']) 
	               ? 0 
	               : $iTimestampActual - strtotime($infoAgente['linkstart'])),
	       'logintime'         =>  $infoAgente['logintime'] + (
	           (is_null($infoAgente['lastsessionend']) && !is_null($infoAgente['lastsessionstart'])) 
	               ? $iTimestampActual - strtotime($infoAgente['lastsessionstart'])
	               : 0),
	       'num_calls'         =>  $infoAgente['num_calls'],
	       'oncallupdate'      =>  !is_null($infoAgente['linkstart']),
	       // customize clumn
	       'linkqueue'			=>		$infoAgente['linkqueue'],
	       'queues'					=>		$infoAgente['queues'],
	       'agent_number'		=>		$sNumeroAgente,
	   );
	  }
    return sortJSON($jsonData);
}

// compare queue waiting array, if diff return true
function compareQueueArr($arr1,$arr2)
{
	if (count($arr1) <> count($arr2))
		return true;
	foreach ($arr1 as $phone)
		foreach ($arr2 as $phone2){
			$flag = false;		
			if ($phone['phone_number'] == $phone2['phone_number'])
				$flage = true;
			if (!$flag)
				return true;
			}
	return false;	
}

function contructQueueWaitingJSON($arr)
{
    /* use for query customer info */
    $oCustomer = new getInfoMainConsole;
	$arrResult = $arr;	
	foreach($arr as $key=>$value){
		$vip = '1';
		$phone1 = $arr[$key]['phone_number']; 		
		$phone2 = (is_null($oCustomer->getCustomerName($phone1))?$phone1:$oCustomer->getCustomerName($phone1));
		$arrResult[$key]['phone_number'] = '<span title="' . $phone1 . '">' . $phone2 . '</span>';
		//$arrResult[$key]['wait_time'] = timestamp_format($arrResult[$key]['wait_time']); 
		$arrResult[$key]['vip'] = $vip;
	}
}

function queue_waiting_checkStatus($module_name, $smarty, $sDirLocalPlantillas, $oPaloConsola)
{
	$respuesta = array();	
	ignore_user_abort(true);
   set_time_limit(0);	   
   
   $sModoEventos = getParameter('serverevents');
	$bSSE = (!is_null($sModoEventos) && $sModoEventos); 
	if ($bSSE) {
		Header('Content-Type: text/event-stream');
		printflush("retry: 1\n");
	} else {
		Header('Content-Type: application/json');
	}
	
	$flag = array();
    global $arrConf;
	$oQueueStatus = new queue_waiting();
    $oQueueStatus->callcenter_db_connect($arrConf['cadena_dsn']);
	$iTimeoutPoll = PaloSantoConsola::recomendarIntervaloEsperaAjax();
	do{		
		$iTimestampInicio = time();
		session_commit();
		while (connection_status() == CONNECTION_NORMAL   
            && time() - $iTimestampInicio <  $iTimeoutPoll) {				 
	 			 $respuesta = $oQueueStatus->showQueue();
	 			 //if (compareQueueArr($respuesta,$flag)){
	 			 	if (count($respuesta) <> count($flag)){
	 			 	 $flag = $respuesta;
	 			 	 jsonflush($bSSE, $respuesta);
	 			 	 break;
	 			 }	 		
				usleep(1000);
		}						        
		//$respuesta = array();
    } while ($bSSE && connection_status() == CONNECTION_NORMAL);
    $oQueueStatus->callcenter_db_disconnect();
}

function agent_monitoring_checkStatus($module_name, $smarty, $sDirLocalPlantillas, $oPaloConsola)
{
    $respuesta = array();	
	//return 'test';
    ignore_user_abort(true);
    set_time_limit(0);

    // Estado del lado del cliente
    $estadoHash = getParameter('clientstatehash');
    if (!is_null($estadoHash)) {
        $estadoCliente = isset($_SESSION[$module_name]['estadoCliente']) 
            ? $_SESSION[$module_name]['estadoCliente'] 
            : array();        
    } else {
        $estadoCliente = getParameter('clientstate');
        if (!is_array($estadoCliente)) return;
    }
    foreach (array_keys($estadoCliente) as $k) 
        $estadoCliente[$k]['oncallupdate'] = ($estadoCliente[$k]['oncallupdate'] == 'true'); 

    // Modo a funcionar: Long-Polling, o Server-sent Events
    $sModoEventos = getParameter('serverevents');
    $bSSE = (!is_null($sModoEventos) && $sModoEventos); 
    if ($bSSE) {
        Header('Content-Type: text/event-stream');
        printflush("retry: 1\n");
    } else {
        Header('Content-Type: application/json');
    }
    
    // Verificar hash correcto
    if (!is_null($estadoHash) && $estadoHash != $_SESSION[$module_name]['estadoClienteHash']) {
    	$respuesta['estadoClienteHash'] = 'mismatch';
        jsonflush($bSSE, $respuesta);
        $oPaloConsola->desconectarTodo();
        return;
    }

    // Estado del lado del servidor
    $estadoMonitor = $oPaloConsola->listarEstadoMonitoreoAgentes();
    if (!is_array($estadoMonitor)) {
        $respuesta['error'] = $oPaloConsola->errMsg;
        jsonflush($bSSE, $respuesta);
    	$oPaloConsola->desconectarTodo();
        return;
    }
	//tri
    // Acumular inmediatamente las filas que son distintas en estado    
    ksort($estadoMonitor);	
    $estadoMonitor = mergeQueueAgent($estadoMonitor);
    $jsonData = contructDataJSON($estadoMonitor);    
    foreach ($jsonData as $jsonKey => $jsonRow) {
    	if (isset($estadoCliente[$jsonKey])) {
    		if ($estadoCliente[$jsonKey]['agentstatus'] != $jsonRow['agentstatus'] ||
                $estadoCliente[$jsonKey]['oncallupdate'] != $jsonRow['oncallupdate']) {
                $respuesta[$jsonKey] = $jsonRow;
                $estadoCliente[$jsonKey]['agentstatus'] = $jsonRow['agentstatus'];
                $estadoCliente[$jsonKey]['oncallupdate'] = $jsonRow['oncallupdate'];					 
                unset($respuesta[$jsonKey]['agentname']); 
            }
    	}
    }
	
    $iTimeoutPoll = PaloSantoConsola::recomendarIntervaloEsperaAjax();
    do {
        $oPaloConsola->desconectarEspera();
        
        // Se inicia espera larga con el navegador...
        session_commit();
        $iTimestampInicio = time();
        
        while (connection_status() == CONNECTION_NORMAL && count($respuesta) <= 0 
            && time() - $iTimestampInicio <  $iTimeoutPoll) {

            $listaEventos = $oPaloConsola->esperarEventoSesionActiva();
            if (is_null($listaEventos)) {
                $respuesta['error'] = $oPaloConsola->errMsg;
                jsonflush($bSSE, $respuesta);
                $oPaloConsola->desconectarTodo();
                return;
            }
            
            $iTimestampActual = time();
            foreach ($listaEventos as $evento) {
                $sNumeroAgente = $sCanalAgente = $evento['agent_number'];
                if (substr($sNumeroAgente, 0, 4) == 'SIP/')
                    $sNumeroAgente = substr($sNumeroAgente, 4);
                //$sCanalAgente = str_replace('/','_',$sCanalAgente);

            	switch ($evento['event']) {
            	case 'agentloggedin':
                   // foreach (array_keys($estadoMonitor) as $sAgent) {
                        if (isset($estadoMonitor[$sCanalAgente])) { 
                        	 $jsonKey = 'Agent_'.$sNumeroAgente;
                            if (isset($jsonData[$jsonKey]) && $jsonData[$jsonKey]['agentstatus'] == 'offline') {                            	
                                // Estado en el estado de monitor
                                $estadoMonitor[$sCanalAgente]['agentstatus'] = 'online';
                                $estadoMonitor[$sCanalAgente]['lastsessionstart'] = date('Y-m-d H:i:s', $iTimestampActual);
                                $estadoMonitor[$sCanalAgente]['lastsessionend'] = NULL;
                                if (!is_null($estadoMonitor[$sCanalAgente]['lastpausestart']) && 
                                	 is_null($estadoMonitor[$sCanalAgente]['lastpauseend'])) {
                                		$estadoMonitor[$sCanalAgente]['lastpauseend'] = date('Y-m-d H:i:s', $iTimestampActual);
                                }
                                $estadoMonitor[$sCanalAgente]['linkstart'] = NULL;
                                
                                // Estado en la estructura JSON
                                $jsonData[$jsonKey]['agentstatus'] = $estadoMonitor[$sCanalAgente]['agentstatus'];
                                $jsonData[$jsonKey]['sec_laststatus'] = 0;
                                $jsonData[$jsonKey]['oncallupdate'] = FALSE;
                                // Get customer information //tri
                                $agentState = getAgentState($jsonRow['agent_number']);
										  $arrSmartyData[$jsonKey]['callnumber'] = $agentState['callnumber'];
										  $arrSmartyData[$jsonKey]['callid'] = $agentState['callid'];
										  $customer = getCustomer($arrSmartyData[$jsonKey]['callnumber']);
										  $arrSmartyData[$jsonKey]['customer'] = ($customer?$customer:$arrSmartyData[$jsonKey]['callnumber']);
                                
                                // Estado del cliente
                                $estadoCliente[$jsonKey]['agentstatus'] = $jsonData[$jsonKey]['agentstatus'];
                                $estadoCliente[$jsonKey]['oncallupdate'] = $jsonData[$jsonKey]['oncallupdate'];

                                // Estado a emitir al cliente
                                $respuesta[$jsonKey] = $jsonData[$jsonKey];
                                unset($respuesta[$jsonKey]['agentname']);
                            }
                        }
                   // }
                    break;
                case 'agentloggedout':
                    //foreach (array_keys($estadoMonitor) as $sAgent) {
                        if (isset($estadoMonitor[$sCanalAgente])) {
                            $jsonKey = 'Agent_'.$sNumeroAgente;
                            if (isset($jsonData[$jsonKey]) && $jsonData[$jsonKey]['agentstatus'] != 'offline') {                                
                                // Estado en el estado de monitor
                                $estadoMonitor[$sCanalAgente]['agentstatus'] = 'offline';
                                $estadoMonitor[$sCanalAgente]['lastsessionend'] = date('Y-m-d H:i:s', $iTimestampActual);
                                if (!is_null($estadoMonitor[$sCanalAgente]['lastpausestart']) && 
                                    is_null($estadoMonitor[$sCanalAgente]['lastpauseend'])) {
                                    $estadoMonitor[$sCanalAgente]['lastpauseend'] = date('Y-m-d H:i:s', $iTimestampActual);
                                }
                                $estadoMonitor[$sCanalAgente]['linkstart'] = NULL;
                                if (!is_null($estadoMonitor[$sCanalAgente]['lastsessionstart'])) {
                                    $iTimestampInicio = strtotime($estadoMonitor[$sCanalAgente]['lastsessionstart']);
                                    $iDuracionSesion =  $iTimestampActual - $iTimestampInicio;
                                    if ($iDuracionSesion >= 0) {
                                    	$estadoMonitor[$sCanalAgente]['logintime'] += $iDuracionSesion;
                                    }
                                }
                                
                                // Estado en la estructura JSON
                                $jsonData[$jsonKey]['agentstatus'] = $estadoMonitor[$sCanalAgente]['agentstatus'];
                                $jsonData[$jsonKey]['sec_laststatus'] = 0;
                                $jsonData[$jsonKey]['oncallupdate'] = FALSE;
                                $jsonData[$jsonKey]['logintime'] = $estadoMonitor[$sCanalAgente]['logintime'];
                                
                                // Estado del cliente
                                $estadoCliente[$jsonKey]['agentstatus'] = $jsonData[$jsonKey]['agentstatus'];
                                $estadoCliente[$jsonKey]['oncallupdate'] = $jsonData[$jsonKey]['oncallupdate'];

                                // Estado a emitir al cliente
                                $respuesta[$jsonKey] = $jsonData[$jsonKey];
                                unset($respuesta[$jsonKey]['agentname']);
                            }
                        }
                   // }
                    break;
                case 'pausestart':
                    //foreach (array_keys($estadoMonitor) as $sAgent) {
                        if (isset($estadoMonitor[$sCanalAgente])) {
                            $jsonKey = 'Agent_'.$sNumeroAgente;
                            if (isset($jsonData[$jsonKey]) && $jsonData[$jsonKey]['agentstatus'] != 'offline') {
                                
                                // Estado en el estado de monitor
                                if ($estadoMonitor[$sCanalAgente]['agentstatus'] != 'oncall')
                                    $estadoMonitor[$sCanalAgente]['agentstatus'] = 'paused';
                                $estadoMonitor[$sCanalAgente]['lastpausestart'] = date('Y-m-d H:i:s', $iTimestampActual);
                                $estadoMonitor[$sCanalAgente]['lastpauseend'] = NULL;
                                
                                // Estado en la estructura JSON
                                $jsonData[$jsonKey]['agentstatus'] = $estadoMonitor[$sCanalAgente]['agentstatus'];
                                if ($jsonData[$jsonKey]['agentstatus'] == 'oncall') {
                                    if (!is_null($estadoMonitor[$sCanalAgente]['linkstart'])) {
                                        $iTimestampInicio = strtotime($estadoMonitor[$sCanalAgente]['linkstart']);
                                        $iDuracionLlamada = $iTimestampActual - $iTimestampInicio;
                                        if ($iDuracionLlamada >= 0) {
                                            $jsonData[$jsonKey]['sec_laststatus'] = $iDuracionLlamada;
                                            $jsonData[$jsonKey]['sec_calls'] = 
                                                $estadoMonitor[$sCanalAgente]['sec_calls'] + $iDuracionLlamada; 
                                        }
                                    }
                                } else {
                                    $jsonData[$jsonKey]['sec_laststatus'] = 0;
                                }
                                $jsonData[$jsonKey]['logintime'] = $estadoMonitor[$sCanalAgente]['logintime'];
                                if (!is_null($estadoMonitor[$sCanalAgente]['lastsessionstart'])) {
                                    $iTimestampInicio = strtotime($estadoMonitor[$sCanalAgente]['lastsessionstart']);
                                    $iDuracionSesion =  $iTimestampActual - $iTimestampInicio;
                                    if ($iDuracionSesion >= 0) {
                                        $jsonData[$jsonKey]['logintime'] += $iDuracionSesion;
                                    }
                                }
                                
                                // Estado del cliente
                                $estadoCliente[$jsonKey]['agentstatus'] = $jsonData[$jsonKey]['agentstatus'];
                                $estadoCliente[$jsonKey]['oncallupdate'] = $jsonData[$jsonKey]['oncallupdate'];

                                // Estado a emitir al cliente
                                $respuesta[$jsonKey] = $jsonData[$jsonKey];
                                unset($respuesta[$jsonKey]['agentname']);
                            }
                        }
                   // }
                    break;
                case 'pauseend':
                    //foreach (array_keys($estadoMonitor) as $sAgent) {
                        if (isset($estadoMonitor[$sCanalAgente])) {
                            $jsonKey = 'Agent_'.$sNumeroAgente;
                            if (isset($jsonData[$jsonKey]) && $jsonData[$jsonKey]['agentstatus'] != 'offline') {
                            
                                // Estado en el estado de monitor
                                if ($estadoMonitor[$sCanalAgente]['agentstatus'] != 'oncall')
                                    $estadoMonitor[$sCanalAgente]['agentstatus'] = 'online';
                                $estadoMonitor[$sCanalAgente]['lastpauseend'] = date('Y-m-d H:i:s', $iTimestampActual);
                                
                                // Estado en la estructura JSON
                                $jsonData[$jsonKey]['agentstatus'] = $estadoMonitor[$sCanalAgente]['agentstatus'];
                                if ($jsonData[$jsonKey]['agentstatus'] == 'oncall') {
                                    if (!is_null($estadoMonitor[$sCanalAgente]['linkstart'])) {
                                        $iTimestampInicio = strtotime($estadoMonitor[$sCanalAgente]['linkstart']);
                                        $iDuracionLlamada = $iTimestampActual - $iTimestampInicio;
                                        if ($iDuracionLlamada >= 0) {
                                            $jsonData[$jsonKey]['sec_laststatus'] = $iDuracionLlamada;
                                            $jsonData[$jsonKey]['sec_calls'] = 
                                                $estadoMonitor[$sCanalAgente]['sec_calls'] + $iDuracionLlamada; 
                                        }
                                    }
                                } else {
                                    $jsonData[$jsonKey]['sec_laststatus'] =
                                        $iTimestampActual - strtotime($estadoMonitor[$sCanalAgente]['lastsessionstart']);
                                }
                                $jsonData[$jsonKey]['logintime'] = $estadoMonitor[$sCanalAgente]['logintime'];
                                if (!is_null($estadoMonitor[$sCanalAgente]['lastsessionstart'])) {
                                    $iTimestampInicio = strtotime($estadoMonitor[$sCanalAgente]['lastsessionstart']);
                                    $iDuracionSesion =  $iTimestampActual - $iTimestampInicio;
                                    if ($iDuracionSesion >= 0) {
                                        $jsonData[$jsonKey]['logintime'] += $iDuracionSesion;
                                    }
                                }
                                
                                // Estado del cliente
                                $estadoCliente[$jsonKey]['agentstatus'] = $jsonData[$jsonKey]['agentstatus'];
                                $estadoCliente[$jsonKey]['oncallupdate'] = $jsonData[$jsonKey]['oncallupdate'];

                                // Estado a emitir al cliente
                                $respuesta[$jsonKey] = $jsonData[$jsonKey];
                                unset($respuesta[$jsonKey]['agentname']);
                            }
                        }
                    //}
                    break;
                case 'agentlinked':
                    // Averiguar la cola por la que entró la llamada nueva
                    $sCallQueue = $evento['queue'];
                    if (is_null($sCallQueue)) {
                    	$infoCampania = $oPaloConsola->leerInfoCampania(
                            $evento['call_type'],
                            $evento['campaign_id']);
                        if (!is_null($infoCampania)) $sCallQueue = $infoCampania['queue'];
                    }                    
                    
	                  if (isset($estadoMonitor[$sCanalAgente])) {
	                      $jsonKey = 'Agent_'.$sNumeroAgente;
	                      if (isset($jsonData[$jsonKey]) && $jsonData[$jsonKey]['agentstatus'] != 'offline') {
	                      
	                          // Estado en el estado de monitor
	                          $estadoMonitor[$sCanalAgente]['agentstatus'] = 'oncall';
	                          $estadoMonitor[$sCanalAgente]['linkstart'] = NULL;                                
	                          $estadoMonitor[$sCanalAgente]['num_calls']++;
	                          $estadoMonitor[$sCanalAgente]['linkstart'] = $evento['datetime_linkstart'];                                
	
	                          // Estado en la estructura JSON
	                          $jsonData[$jsonKey]['agentstatus'] = $estadoMonitor[$sCanalAgente]['agentstatus'];
	                          $jsonData[$jsonKey]['sec_laststatus'] = 
	                              is_null($estadoMonitor[$sCanalAgente]['linkstart']) 
	                                  ? NULL
	                                  : $iTimestampActual - strtotime($estadoMonitor[$sCanalAgente]['linkstart']);
	                          $jsonData[$jsonKey]['num_calls'] = $estadoMonitor[$sCanalAgente]['num_calls'];
	                          $jsonData[$jsonKey]['sec_calls'] = $estadoMonitor[$sCanalAgente]['sec_calls'] +
	                              (is_null($jsonData[$jsonKey]['sec_laststatus']) 
	                                  ? 0 
	                                  : $jsonData[$jsonKey]['sec_laststatus']);
	                          $jsonData[$jsonKey]['oncallupdate'] = !is_null($estadoMonitor[$sCanalAgente]['linkstart']);
	                          $jsonData[$jsonKey]['logintime'] = $estadoMonitor[$sCanalAgente]['logintime'];
	                          if (!is_null($estadoMonitor[$sCanalAgente]['lastsessionstart'])) {
	                              $iTimestampInicio = strtotime($estadoMonitor[$sCanalAgente]['lastsessionstart']);
	                              $iDuracionSesion =  $iTimestampActual - $iTimestampInicio;
	                              if ($iDuracionSesion >= 0) {
	                                  $jsonData[$jsonKey]['logintime'] += $iDuracionSesion;
	                              }
	                          }
	                      	  $jsonData[$jsonKey]['linkqueue'] = $evento['queue']; //tri
									  $customer = getCustomer($evento['phone']);
									  $jsonData[$jsonKey]['customer'] = ($customer?$customer:$evento['phone']);                            	                              	   
	
	                          // Estado del cliente
	                          $estadoCliente[$jsonKey]['agentstatus'] = $jsonData[$jsonKey]['agentstatus'];
	                          $estadoCliente[$jsonKey]['oncallupdate'] = $jsonData[$jsonKey]['oncallupdate'];
	
	                          // Estado a emitir al cliente
	                          $respuesta[$jsonKey] = $jsonData[$jsonKey];
	                          unset($respuesta[$jsonKey]['agentname']);
	                          //$respuesta = $evento;
	                      }
	                  }
                    
                    break;
                case 'agentunlinked':
                    //foreach (array_keys($estadoMonitor) as $sAgent) {
                        if (isset($estadoMonitor[$sCanalAgente])) {
                            $jsonKey = 'Agent_'.$sNumeroAgente;
                            if (isset($jsonData[$jsonKey]) && $jsonData[$jsonKey]['agentstatus'] != 'offline') {
                            
                                // Estado en el estado de monitor
                                $estadoMonitor[$sCanalAgente]['agentstatus'] = 
                                    (!is_null($estadoMonitor[$sCanalAgente]['lastpausestart']) && is_null($estadoMonitor[$sCanalAgente]['lastpauseend']))
                                    ? 'paused' : 'online';
                                if (!is_null($estadoMonitor[$sCanalAgente]['linkstart'])) {
                                	$iTimestampInicio = strtotime($estadoMonitor[$sCanalAgente]['linkstart']);
                                    $iDuracionLlamada = $iTimestampActual - $iTimestampInicio;
                                    if ($iDuracionLlamada >= 0) {
                                    	$estadoMonitor[$sCanalAgente]['sec_calls'] += $iDuracionLlamada;
                                    }
                                }
                                $estadoMonitor[$sCanalAgente]['linkstart'] = NULL;
                                
                                // Estado en la estructura JSON
                                $jsonData[$jsonKey]['agentstatus'] = $estadoMonitor[$sCanalAgente]['agentstatus'];
                                if ($jsonData[$jsonKey]['agentstatus'] == 'paused') {
                                    $jsonData[$jsonKey]['sec_laststatus'] =
                                        $iTimestampActual - strtotime($estadoMonitor[$sCanalAgente]['lastpausestart']);
                                } else {
                                    $jsonData[$jsonKey]['sec_laststatus'] =
                                        $iTimestampActual - strtotime($estadoMonitor[$sCanalAgente]['lastsessionstart']);
                                }
                                $jsonData[$jsonKey]['num_calls'] = $estadoMonitor[$sCanalAgente]['num_calls'];
                                $jsonData[$jsonKey]['sec_calls'] = $estadoMonitor[$sCanalAgente]['sec_calls'];
                                $jsonData[$jsonKey]['oncallupdate'] = FALSE;
                                $jsonData[$jsonKey]['logintime'] = $estadoMonitor[$sCanalAgente]['logintime'];
                                if (!is_null($estadoMonitor[$sCanalAgente]['lastsessionstart'])) {
                                    $iTimestampInicio = strtotime($estadoMonitor[$sCanalAgente]['lastsessionstart']);
                                    $iDuracionSesion =  $iTimestampActual - $iTimestampInicio;
                                    if ($iDuracionSesion >= 0) {
                                        $jsonData[$jsonKey]['logintime'] += $iDuracionSesion;
                                    }
                                }

                                // Estado del cliente
                                $estadoCliente[$jsonKey]['agentstatus'] = $jsonData[$jsonKey]['agentstatus'];
                                $estadoCliente[$jsonKey]['oncallupdate'] = $jsonData[$jsonKey]['oncallupdate'];

                                // Estado a emitir al cliente
                                $respuesta[$jsonKey] = $jsonData[$jsonKey];
                                unset($respuesta[$jsonKey]['agentname']);
                            }
                        }
                    //}
                    break;
            	}
            }
            
            
        }
        if (count($respuesta) > 0) {
            @session_start();
            $estadoHash = generarEstadoHash($module_name, $estadoCliente);
            $respuesta['estadoClienteHash'] = $estadoHash;
            session_commit();
        }
		  //$respuesta = $estadoMonitor;
        jsonflush($bSSE, $respuesta);
        jsonflush($bSSE, var_dump($respuesta));
        $respuesta = array();

    } while ($bSSE && connection_status() == CONNECTION_NORMAL);
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

?>