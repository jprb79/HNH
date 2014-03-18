<?php

require_once $_SERVER['DOCUMENT_ROOT']. '/modules/realtime_monitor/libs/ECCP.class.php';
require_once $_SERVER['DOCUMENT_ROOT']. '/modules/agent_console/libs/JSON.php'; 
require_once $_SERVER['DOCUMENT_ROOT'].'/libs/paloSantoDB.class.php';
//include_once "AsteriskManager.php";
include_once $_SERVER['DOCUMENT_ROOT']. '/modules/realtime_monitor/libs/AsteriskManager.php';
//config for access database

class agentConsoleAction{
	var $errMsg = '';   
   private $_oDB_asterisk = NULL;     
   private $_oDB_call_center = NULL;  
   private $_astman = NULL;
   private $_eccp = NULL;
   private $_agent = NULL;      

   function agentConsoleAction($sAgent = NULL)
   {
		if (!is_null($sAgent)) $this->_agent = $sAgent;
   }  		

	private function _getConnection($sConn)
   {        
        $arrConf['cadena_dsn']="mysql://asterisk:asterisk@localhost/call_center";
		switch ($sConn) {
        case 'asterisk':
            if (!is_null($this->_oDB_asterisk)) return $this->_oDB_asterisk;
            $sDSN = generarDSNSistema('asteriskuser', 'asterisk');
            $oDB = new paloDB($sDSN);
            if ($oDB->connStatus) {
                $this->_errMsg = '(internal) Unable to create asterisk DB conn - '.$oDB->errMsg;
                die($this->_errMsg);
            }
            $this->_oDB_asterisk = $oDB;
            return $this->_oDB_asterisk;
            break;
        case 'call_center':
            if (!is_null($this->_oDB_call_center)) return $this->_oDB_call_center;
            $sDSN = $arrConf['cadena_dsn'];
            $oDB = new paloDB($sDSN);
            if ($oDB->connStatus) {
                $this->_errMsg = '(internal) Unable to create asterisk DB conn - '.$oDB->errMsg;
                die($this->_errMsg);
            }
            $this->_oDB_asterisk = $oDB;
            return $this->_oDB_asterisk;
            break;
        case 'ECCP':
            if (!is_null($this->_eccp)) return $this->_eccp;

            $sUsernameECCP = 'agentconsole';
            $sPasswordECCP = 'agentconsole';
            
            // Verificar si existe la contraseña de ECCP, e insertar si necesario
            $dbConnCC = $this->_getConnection('call_center');
            $md5_passwd = $dbConnCC->getFirstRowQuery(
                'SELECT md5_password FROM eccp_authorized_clients WHERE username = ?',
                TRUE, array($sUsernameECCP));
            if (is_array($md5_passwd)) {
            	if (count($md5_passwd) <= 0) {
            		$dbConnCC->genQuery(
                        'INSERT INTO eccp_authorized_clients (username, md5_password) VALUES(?, md5(?))',
                        array($sUsernameECCP, $sPasswordECCP));
            	}
            }

            $oECCP = new ECCP();
            
            // TODO: configurar credenciales
            $oECCP->connect("localhost", $sUsernameECCP, $sPasswordECCP);
            if (!is_null($this->_agent)) {
            	$oECCP->setAgentNumber($this->_agent);
                
                // El siguiente código asume agente SIP/9000
                if (preg_match('|^SIP/(\d+)$|', $this->_agent, $regs))
                    $sAgentNumber = $regs[1];
                else $sAgentNumber = $this->_agent;
                
                /* Privilegio de localhost - se puede recuperar la clave del
                 * agente sin tener que pedirla explícitamente */                
                $tupla = $dbConnCC->getFirstRowQuery(
                    'SELECT eccp_password FROM agent WHERE number = ? AND estatus="A"', 
                    FALSE, array($sAgentNumber));
                if (!is_array($tupla))
                    throw new ECCPConnFailedException('Failed to retrieve agent password');
                if (count($tupla) <= 0)
                    throw new ECCPUnauthorizedException('Agent not found');
                if (is_null($tupla[0]))
                    throw new ECCPUnauthorizedException('Agent not authorized for ECCP - ECCP password not set');
                $oECCP->setAgentPass($tupla[0]);
                
                // Filtrar los eventos sólo para el agente actual
                $oECCP->filterbyagent();
            }
               
            $this->_eccp = $oECCP;
            return $this->_eccp;
            break;
        }        
        return NULL;
   }
	private function _getConfigManager()
    {
    	$sNombreArchivo = '/etc/asterisk/manager.conf';
        if (!file_exists($sNombreArchivo)) {
        	$this->_errMsg = "(internal) $sNombreArchivo no se encuentra.";
            return NULL;
        }
        if (!is_readable($sNombreArchivo)) {
            $this->_errMsg = "(internal) $sNombreArchivo no puede leerse por usuario de marcador.";
            return NULL;        	
        }
        $infoConfig = parse_ini_file($sNombreArchivo, TRUE);
        if (is_array($infoConfig)) {
            foreach ($infoConfig as $login => $infoLogin) {
            	if ($login != 'general') {
            		if (isset($infoLogin['secret']) && isset($infoLogin['read']) && isset($infoLogin['write'])) {
            			return array($login, $infoLogin['secret']);
            		}
            	}
            }
        } else {
            $this->_errMsg = "(internal) file name can not be parsed correctly.";
        }
        return NULL;
    }
	
	function disconnectAll()
    {
        $this->disconnectWait();
        if (!is_null($this->_eccp)) {
            try {
                $this->_eccp->disconnect();
            } catch (Exception $e) {}
            $this->_eccp = NULL;
        }
    }

   
    function disconnectWait()
    {
        if (!is_null($this->_oDB_asterisk)) {
            $this->_oDB_asterisk->disconnect();
            $this->_oDB_asterisk = NULL;
        }
        if (!is_null($this->_oDB_call_center)) {
            $this->_oDB_call_center->disconnect();
            $this->_oDB_call_center = NULL;
        }
        if (!is_null($this->_astman)) {
            $this->_astman->disconnect();
            $this->_astman = NULL;
        }
    }

    private function _formatoErrorECCP($x)
    {
    	if (isset($x->failure)) {
    		return (int)$x->failure->code.' - '.(string)$x->failure->message;
    	} else {
    		return '';
    	}
    }
	function loginAgent($sExtension)
	{
        $regs = NULL;
        if (preg_match('|^\w+/(\d+)$|', $sExtension, $regs))
            $sNumero = $regs[1];
        else $sNumero = $sExtension;
        try {
            $oECCP = $this->_getConnection('ECCP');
            $loginResponse = $oECCP->loginagent($sNumero);
            if (isset($loginResponse->failure))
                $this->errMsg = '(internal) loginagent: '.$this->_formatoErrorECCP($loginResponse);
            return ($loginResponse->status == 'logged-in' || $loginResponse->status == 'logging');
        } catch (Exception $e) {
            $this->errMsg = '(internal) loginagent: '.$e->getMessage();
            return FALSE;
        }
	}
 	function logoutAgent()
   {
        try {
            $oECCP = $this->_getConnection('ECCP');
            $response = $oECCP->logoutagent();
            if (isset($response->failure)) {
                $this->errMsg = '(internal) logoutagent: '.$this->_formatoErrorECCP($response);
                return FALSE;
            }
            return TRUE;
        } catch (Exception $e) {
            $this->errMsg = '(internal) logoutagent: '.$e->getMessage();
            return FALSE;
        }
   }
	function hangupCall()
	{
		try {			
			$oECCP = $this->_getConnection('ECCP');
			$response = $oECCP->hangup();
			if (isset($response->failure)) {
				$this->errMsg = 'Unable to hangup call: '.$this->_formatoErrorECCP($respuesta);
				return FALSE;
			}
			return TRUE;
		} catch (Exception $e) {
			$this->errMsg = '(internal) hangup: '.$e->getMessage();
			return FALSE;
		}	
	}
	function transferCall($sTransferExt, $bAtxfer = FALSE){
		try {				           
            $oECCP = $this->_getConnection('ECCP');
            $response = $bAtxfer 
                ? $oECCP->atxfercall($sTransferExt) 
                : $oECCP->transfercall($sTransferExt);
            if (isset($response->failure)) {
                $this->errMsg = 'Unable to transfer call: '. $this->_formatoErrorECCP($respuesta);
                return FALSE;
            }
            return TRUE;
        } catch (Exception $e) {
            $this->errMsg = '(internal) transfer call: '.$e->getMessage();
            return FALSE;
        }
 	}
 
 	function spycall($agent_number,$supervisor_queue,$whisper)
 	{
 		try {	 						           						
			$oAsteriskLogin = $this->_getConfigManager();
			$spy_number = '771';//($whisper?'771':'772');            
			//$channel = 'Local/'.$supervisor_queue.'@ext-queues';			
			$channel = 'SIP/'.$supervisor_queue;
			$var = array(
				'AGENT' => $agent_number,
				'WHISPER' => ($whisper?'w':''),
			);					
			$astParams = array(
				'server' => '127.0.0.1',
				'port'	=> '5038',
			);  			
			$oAIM = new Net_AsteriskManager($astParams);				
			$oAIM->connect();														
			$result = $oAIM->login($oAsteriskLogin[0],$oAsteriskLogin[1]);			
			$result = $oAIM->_sendCommand("Action: EVENTS\r\n"
                ."EVENTMASK: OFF\r\n\r\n");         			
			$result = $oAIM->originateCall($spy_number,$channel,'from-internal',$agent_number,1,30000,$var,null);
			$oAIM->logout();
			$oAIM->close();
			return $result;
									
		} catch (PEAR_Exception $e) {
			$this->errMsg = '(internal) spycall: '.$e->getMessage();
			return TRUE; //work arround for right action but show error
		}	
 	}
 
	function addNoteCall($agent_number,$note_ext,$content)
	{
		try {				          
			$oDB = $this->_getConnection('call_center');

    	      //update note for this call
	        $sQuery = "SELECT 1";
			$recordset = $oDB->fetchTable($sQuery, TRUE);	
			
			if (!$recordset){
				$this->errMsg = '(internal) add note failed: ' . $oDB->errMsg;
				return FALSE;
			}
			return TRUE;
			
		} catch (Exception $e) {
			$this->errMsg = '(internal) add note failed: '.$e->getMessage();
			return FALSE;
		}          	
	}		
}
// end of class definition

function login($agent_number) {
	global $response;	
	$oAction = new agentConsoleAction('SIP/'.$agent_number);
	$bSuccess = $oAction->loginAgent($agent_number);
	if (!$bSuccess) {
        $response['action'] = 'error';
        $response['message'] = 'Error when login'.': '.$oAction->errMsg;
		  return FALSE;
    }
	$oAction->disconnectAll();
	return $bSuccess;
}

function logout($agent_number) {
	global $response;	
	$oAction = new agentConsoleAction('SIP/'.$agent_number);
	$bSuccess = $oAction->logoutAgent($agent_number);
	if (!$bSuccess) {
        $response['action'] = 'error';
        $response['message'] = 'Error when logout'.': '.$oAction->errMsg;
			return FALSE;
   }	
	$response['action'] = 'logout';
   $response['message'] = 'Thành công: Kết thúc phiên làm việc của Agent '.$agent_number;
	$oAction->disconnectAll();	
	return $bSuccess;
}

function hangup($agent_number) {
	global $response;	
	$oAction = new agentConsoleAction('SIP/'.$agent_number);
	$bSuccess = $oAction->hangupCall();
	if (!$bSuccess) {
        $response['action'] = 'error';
        $response['message'] = 'Agent không có cuộc gọi'.': '.$oAction->errMsg;
			return FALSE;
   }	
	$response['action'] = 'hangup';
   $response['message'] = 'Thành công: Ngắt máy cuộc gọi của Agent '.$agent_number;
   $oAction->disconnectAll();
	return $bSuccess;
}

function spycall($agent_number,$supervisor_queue,$whisper) {
	global $response;		
	
	if (trim($supervisor_queue) == '') {
		$response['action'] = 'error';
        $response['message'] = 'Chưa đăng nhập màn hình chính';
        return FALSE;
	}	
	$oAction = new agentConsoleAction();	
	$bSuccess = $oAction->spycall($agent_number,$supervisor_queue,$whisper);	
	if (!$bSuccess) {
        $response['action'] = 'error';
        $response['message'] = 'Error when spy call'.': '.$oAction->errMsg;
        return FALSE;
   }	
	$response['action'] = 'spycall';
   $response['message'] = 'Thành công: Nghe xen ' . ($whisper?'có ':'') . 'tư vấn - Agent '.$agent_number;
   $oAction->disconnectAll();
	return $bSuccess;		
}

function transfer($agent_number, $dest) {
	global $response;
	
	if (trim($dest) == '') {
		$response['action'] = 'error';
        $response['message'] = 'Chưa đăng nhập màn hình chính';
        return FALSE;
	}
		
	$oAction = new agentConsoleAction('SIP/'.$agent_number);
	$bSuccess = $oAction->transferCall($dest);
    //var_dump($agent_number);var_dump($dest);
	if (!$bSuccess) {
        $response['action'] = 'error';
        $response['message'] = 'Error when transfer call'.': '.$oAction->errMsg;
        return FALSE;
   }	
	$response['action'] = 'transfer';
   $response['message'] = 'Thành công: Lấy cuộc gọi của Agent '.$agent_number;
   $oAction->disconnectAll();
	return $bSuccess;	
}

function addnote($agent_number,$note_ext, $content) {
	global $response;	
	$oAction = new agentConsoleAction();
	$bSuccess = $oAction->addNoteCall($agent_number,$note_ext,$content);
	if (!$bSuccess) {
        $response['action'] = 'error';
        $response['message'] = 'Error when adding note'.': '.$oAction->errMsg;
        return FALSE;
   }	
	$response['action'] = 'addnote';
   $response['message'] = 'Thành công: Tạo ghi chú cuộc gọi của Agent '.$agent_number;
   
	return $bSuccess;	
}

if (!function_exists('getParameter')) {
    function getParameter($parameter)
    {
        if(isset($_POST[$parameter]))
            return $_POST[$parameter];
        else if(isset($_GET[$parameter]))
            return $_GET[$parameter];
        else
            return null;
    }
}	

// main code here

$response = array(
        'action'    =>  '(no action)',
        'message'   =>  '(no message)',
    );    
 

if (!isset($_REQUEST['type']))
	$response = array(
        'action'    =>  'error',
        'message'   =>  'no action defined',
    );    	
else {
	$sAction = getParameter('type');
	switch ($sAction) {
		case 'logout':
			$agent = getParameter('agent');
			logout($agent);	
			break;
		case 'login':
			$agent = getParameter('agent');
			login($agent);	
			break;			
		case 'hangup':
			$agent = getParameter('agent');
			hangup($agent);
			break;			
		case 'spycall':
			$agent = getParameter('agent');			
			$supervisor = getParameter('supervisor');						
			if (getParameter('whisper')=='true')
				$whisper = true;
			else $whisper = false;
			spycall($agent,$supervisor,$whisper);
			break;		
		case 'transfer':
			$agent = getParameter('agent');
			$dest = '8';
			$dest .= getParameter('extension');
			transfer($agent,$dest);			
			break;
		case 'addnote':
			$agent = getParameter('agent');
			$note_ext = getParameter('extension');
			$note = getParameter('note');
			addnote($agent,$note_ext,$note);
			break;	
		default:
			break;
	}
}

$json = new Services_JSON();
Header('Content-Type: application/json');
echo $json->encode($response);

?>