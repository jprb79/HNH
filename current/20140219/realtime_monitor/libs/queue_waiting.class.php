<?php
require_once("AsteriskManager.php");
class queue_waiting{ 
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
	private function getNumber($number)
	{
		return substr($number, strpos($number,"/") + 1, strpos($number,"-") - strpos($number,"/") - 1);
	}
	
	private function convert2second($string='12:23')
	{
		$split = explode(":",$string);
		$second = (int)($split[0])*60 + (int)($split[1]); 		
		return $second; 		
	}	
	
	private function convertJsonData($result)
	{
		$arrData = array();
		$index = 0;
		
		$arrQueue = array();
		foreach(explode("\n", $result) as $line){						         						 			
			if (preg_match('/strategy /i', $line)) {
				$pieces = explode(" ", $line);
				$queue_key = $pieces[0];
				$arrQueue[$queue_key] = $line . "\n"; 			
			}
			else {
				if(isset($queue_key))
					$arrQueue[$queue_key] .= $line . "\n";					
			}
		}		
						
		foreach($arrQueue as $key=>$value){						 		 	
			foreach(explode("\n", $value) as $line){						         						 			
				if (preg_match("/wait/i", $line)) {
					$pieces2 = explode(" ", $line);                    
					$arrData[$index]['phone_number'] =  $this->getNumber($pieces2[7]);
					$arrData[$index]['wait_time'] =  $this->convert2second(trim($pieces2[9], ","));
					$arrData[$index]['queue'] = $key;
					$index++;                    
			   }	     		  
			}	
		}				
		return $arrData;
	}

	public function showQueue()
	{
		try {				          
			$oAsteriskLogin = $this->_getConfigManager();
			//get agent_id based on agent number			
			$astParams = array(
				'server' => '127.0.0.1',
				'port'	=> '5038',
			);  			
			$oAIM = new Net_AsteriskManager($astParams);				
			$oAIM->connect();														
			$result = $oAIM->login($oAsteriskLogin[0],$oAsteriskLogin[1]);			
			$result = $oAIM->_sendCommand("Action: EVENTS\r\n"
                ."EVENTMASK: OFF\r\n\r\n");         
			$result = $oAIM->getQueues();			
			$oAIM->logout();
			$oAIM->close();
			return $this->convertJsonData($result);																			
		} catch (Exception $e) {
			$this->errMsg = '(internal) showQueue failed: '.$e->getMessage();
			return FALSE;
		}   	
	}		
}
?>