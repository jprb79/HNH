<?php

/**
 * PHP version 5
 *
 * @category  Net
 * @package   Net_AsteriskManager
 * @license   http://www.debian.org/misc/bsd.license New BSD License
 * @link      http://pear.php.net/pepr/pepr-proposal-show.php?id=543
 */

/**
 * Including the libraries exception class which extends PEAR_Exception
 */
require 'AsteriskManagerException.php';

/**
 * Class for accessing the Asterisk Manager interface 
 * {@link http://www.voip-info.org/wiki/view/Asterisk+manager+API}
 * 
 * @category Net
 * @package  Net_AsteriskManager
 * @author   Doug Bromley <doug.bromley@gmail.com>
 * @license  http://www.debian.org/misc/bsd.license New BSD License
 * @link     http://pear.php.net/pepr/pepr-proposal-show.php?id=543
 */
class Net_AsteriskManager
{
    /**
     * The Asterisk server which will receives the manager commands 
     * @access public
     * @var string
     */
    public $server;

    /**
     * The port to use when connecting to the Asterisk server
     * @access public
     * @var integer
     */
    public $port = 5038;

    /**
     * The opened socket to the Asterisk server
     * @access private 
     * @var object
     */
    private $_socket;

    /**
     * Class constructor
     * 
     * @param array $params Array of the parameters used to connect to the server
     * <code>
     * array(
     *       'server' => '127.0.0.1'    // The server to connect to
     *       'port' => '5038',          // Port of manager API
     *       'auto_connect' => true     // Autoconnect on construction?
     *      );
     * </code>
     * 
     * @uses AsteriskManager::$server
     * @uses AsteriskManager::$port
     * @uses AsteriskManager::$_socket
     */
    public function __construct($params = array())
    {
        if (!isset($params['server'])) {
            throw new Net_AsteriskManagerException(
                Net_AsteriskManagerException::NOSERVER
            );
        }
        $this->server = $params['server'];			
        if (isset($params['port'])) {
            $this->port = $params['port'];
        }    		

        if (isset($params['auto_connect'])) {
            if ($params['auto_connect']) {
                $this->connect();
            }
        }
    }

    /**
     * Private method for checking there is a socket open to the Asterisk
     * server.
     * 
     * @return null
     */
    private function _checkSocket()
    {
        if (!$this->_socket) {
            throw new Net_AsteriskManagerException(
                Net_AsteriskManagerException::NOSOCKET
            );
        }
    }

    /**
     * Consolidated method for sending the given command to the server and returning
     * its reponse. Any failure in writing or reading will raise an exception.
     * 
     * @param string $command The command to send
     *
     * @return string
     */
    	     
    public function _sendCommand($command)
    {      
        if (!fputs($this->_socket, $command)) {
            throw new Net_AsteriskManagerException(
                Net_AsteriskManagerException::CMDSENDERR
            );
        }			
			//* edited by Tri Do
			if (strpos($command,"Queues"))        
        		$response = stream_get_contents($this->_socket);		  						
			else {
				$response = '';
				do
				{
					$line = fgets($this->_socket, 4096);
					$response .= $line;
					$info = stream_get_meta_data($this->_socket);
				}while ($line != "\r\n" && $info['timed_out'] == false );
			}
			//*/				  			
        if ($response == false) {
            throw new Net_AsteriskManagerException(
                Net_AsteriskManagerException::RESPERR
            );
        }		         
        return $response;
    }

    /**
     * If not already connected then connect to the Asterisk server
     * otherwise close active connection and reconnect
     *
     * @return bool
     */
    public function connect()
    {
        if ($this->_socket) {
            $this->close();
        }
        
        if ($this->_socket = fsockopen($this->server, $this->port)) {
            stream_set_timeout($this->_socket, 1);
            return true;
        }
        
        throw new Net_AsteriskManagerException (
            Net_AsteriskManagerException::CONNECTFAILED
        );
    }

    /**
     * Login into Asterisk Manager interface given the user credentials
     *
     * @param string $username The username to access the interface
     * @param string $password The password defined in manager interface of server
     * @param string $authtype Enabling the ability to handle encrypted connections
     * 
     * @return bool
     */
    public function login($username, $password, $authtype = null)
    {
        $this->_checkSocket();        
        if (strtolower($authtype) == 'md5') {
            $response = $this->_sendCommand("Action: Challenge\r\n"
                ."AuthType: MD5\r\n\r\n");
            if (strpos($response, "Response: Success") !== false) {    
                $challenge = trim(substr($response, 
                    strpos($response, "Challenge: ")));

                $md5_key  = md5($challenge . $password);
                $response = $this->_sendCommand("Action: Login\r\nAuthType: MD5\r\n"
                    ."Username: {$username}\r\n"
                    ."Key: {$md5_key}\r\n\r\n");                    
            } else {
                throw new Net_AsteriskManagerException(
                    Net_AsteriskManagerException::AUTHFAIL
                );
            }
        } else {        		
            $response = $this->_sendCommand("Action: Login\r\n"
                ."Username: {$username}\r\n"
                ."Secret: {$password}\r\n\r\n");
        }			

        if (strpos($response, "Message: Authentication accepted") != false) {			            				           
            return true;
        }
        throw new Net_AsteriskManagerException(
            Net_AsteriskManagerException::AUTHFAIL
        );
    }

    /**
     * Logout of the current manager session attached to $this::socket
     * 
     * @return bool
     */
    public function logout()
    {
        $this->_checkSocket();
        
        $this->_sendCommand("Action: Logoff\r\n\r\n");

        return true;
    }

    /**
     * Close the connection
     *
     * @return bool
     */
    public function close()
    {
        $this->_checkSocket();

        return fclose($this->_socket);
    }

    /**
     * Send a command to the Asterisk CLI interface. Acceptable commands 
     * are dependent on the Asterisk installation.
     *
     * @param string $command Command to execute on server
     *
     * @return string|bool
     */
    public function command($command)
    {
        $this->_checkSocket();
    
        $response = $this->_sendCommand("Action: Command\r\n"
            ."Command: $command\r\n\r\n");

        if (strpos($response, 'No such command') !== false) {
            throw new Net_AsteriskManagerException(
                Net_AsteriskManagerException::NOCOMMAND
            );
        }
        return $response;
    }

    /**
     * A simple 'ping' command which the server responds with 'pong'
     *
     * @return bool
     */
    public function ping()
    {
        $this->_checkSocket();

        $response = $this->_sendCommand("Action: Ping\r\n\r\n");
        if (strpos($response, "Pong") === false) {
            return false;
        }
        return true;
    }

    /**
     * Make a call to an extension with a given channel acting as the originator
     *
     * @param string  $extension The number to dial
     * @param string  $channel   The channel where you wish to originate the call
     * @param string  $context   The context that the call will be dropped into 
     * @param string  $cid       The caller ID to use
     * @param integer $priority  The priority of this command
     * @param integer $timeout   Timeout in milliseconds before attempt dropped
     * @param array   $variables An array of variables to pass to Asterisk
     * @param string  $action_id A unique identifier for this command
     *
     * @return bool
     */
    public function originateCall($extension, 
                           $channel, 
                           $context, 
                           $cid, 
                           $priority = 1, 
                           $timeout = 30000, 
                           $variables = null, 
                           $action_id = null)
    {
        $this->_checkSocket();
        
        $command = "Action: Originate\r\nChannel: $channel\r\n"
            ."Context: $context\r\nExten: $extension\r\nPriority: $priority\r\n"
            ."Callerid: $cid\r\nTimeout: $timeout\r\n";

        if (count($variables) > 0) {
            $chunked_vars = array();
            foreach ($variables as $key => $val)                                                        
            	$command     .= "Variable: $key=$val\r\n";
        }    					
        if ($action_id) {
            $command .= "ActionID: $action_id\r\n";
        }    		
        $this->_sendCommand($command."\r\n");		
        return true;
    }

    /**
     * Returns a list of queues and their status
     *
     * @return string|bool
     */
    public function getQueues()
    {
        $this->_checkSocket();
	
        $response = $this->_sendCommand("Action: Queues\r\n\r\n");
        return $response;
    }

    /**
     * Add a handset to a queue on the server
     * 
     * @param string  $queue   The name of the queue you wish to add the handset too
     * @param string  $handset The handset to add, e.g. SIP/234
     * @param integer $penalty Penalty
     * 
     * @return bool
     */
    public function queueAdd($queue, $handset, $penalty = null)
    {
        $this->_checkSocket();
        
        $command = "Action: QueueAdd\r\nQueue: $queue\r\n"
                    ."Interface: $handset\r\n";

        if ($penalty) {
            $this->_sendCommand($command."Penalty: $penalty\r\n\r\n");
            return true;
        }

        $this->_sendCommand($command."\r\n");
        return true;
    }

    /**
     * Remove a handset from the given queue
     * 
     * @param string $queue   The queue you wish to perform this action on
     * @param string $handset The handset you wish to remove (e.g. SIP/200)
     *
     * @return bool
     */
    public function queueRemove($queue, $handset) 
    {
        $this->_checkSocket();
        
        $this->_sendCommand("Action: QueueRemove\r\nQueue: $queue\r\n"
            ."Interface: $handset\r\n\r\n");

        return true;
    }

    /**
     * Monitor(record) a channel to given file in given format
     *
     * @param string  $channel  Channel to monitor (e.g. SIP/234, ZAP/1)
     * @param string  $filename The filename to save to
     * @param string  $format   The format of the file (e.g. gsm, wav)
     * @param integer $mix      Boolean 1 or 0 on whether to mix
     *
     * @return bool
     */
    public function startMonitor($channel, $filename, $format, $mix = null)
    {
        
        $this->_checkSocket();
        
        $response = $this->_sendCommand("Action: Monitor\r\nChannel: $channel\r\n"
                               ."File: $filename\r\nFormat: $format\r\n"
                               ."Mix: $mix\r\n\r\n");
        
        if (strpos($response, "Success") === false) {
            throw new Net_AsteriskManagerException(
                Net_AsteriskManagerException::MONITORFAIL
            );
        } else {
            return true;
        }
    }

    /**
     * Stop monitoring a channel
     * 
     * @param string $channel The channel you wish to stop monitoring
     *
     * @return bool
     */
    public function stopMonitor($channel)
    {
        $this->_checkSocket();
        
        $this->_sendCommand("Action: StopMonitor\r\n"
                            ."Channel: $channel\r\n\r\n");
        return true;
    }

    /**
     * Get the status information for a channel
     *
     * @param string $channel The channel to query
     * 
     * @return string|string
     */
    public function getChannelStatus($channel = null)
    {
        $this->_checkSocket();
        
        $response = $this->_sendCommand("Action: Status\r\nChannel: "
            ."$channel\r\n\r\n");
        
        return $response;
    }

    /**
     * Get a list of SIP peers and their status
     *
     * @return string|bool
     */
    public function getSipPeers()
    {
        $this->_checkSocket();

        $response = $this->_sendCommand("Action: Sippeers\r\n\r\n");
        return $response;
    }

    /**
     * Return a list of IAX peers and their status
     *
     * @return string|bool
     */
    public function getIaxPeers() 
    {
        $this->_checkSocket();

        $response = $this->_sendCommand("Action: IAXPeers\r\n\r\n");
        return $response;
    }

    /**
     * Returns a list of all parked calls on the server.
     *
     * @return string
     */
    public function parkedCalls()
    {
        $this->_checkSocket();

        $response = $this->_sendCommand("Action: ParkedCalls\r\n"
            ."Parameters: ActionID\r\n\r\n");
        return $response;
    }
}

?>
