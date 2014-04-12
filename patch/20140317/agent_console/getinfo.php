<?php
define('LOG_ENABLE',false);

// class define for applying new template
class getInfoMainConsole{
    public $errMsg = null;
    private $db;
    private $con;

    private $CallcenterDb_user;
    private $CallcenterDb_password;
    private $CallcenterDb_host;
    private $CallcenterDb_name;

    private function getDBConfig($str)
    {
        $slit = substr($str,strpos($str,'://')+3,strlen($str));

        $data = explode('@',$slit);
        $credential = explode(':',$data[0]);
        $user = $credential[0];
        $password = $credential[1];

        $database = explode('/',$data[1]);
        $host = $database[0];
        $db = $database[1];

        return array($user,$password,$host,$db);
    }
	
    public function callcenter_db_connect($str)
    {
        $this->db = $this->getDBConfig($str);
        $this->CallcenterDb_user = $this->db[0];
        $this->CallcenterDb_password = $this->db[1];
        $this->CallcenterDb_host = $this->db[2];
        $this->CallcenterDb_name = $this->db[3];
        $this->con = mysql_connect($this->CallcenterDb_host,$this->CallcenterDb_user,$this->CallcenterDb_password);
        if (!$this->con){
            $this->writeLog('Could not connect Callcenter database: ' . mysql_error());
            $this->errMsg = mysql_error();
            return false;
        }
        mysql_select_db($this->CallcenterDb_name, $this->con);
        return true;
    }

    public function callcenter_db_disconnect()
    {
        $result = mysql_close($this->con);
        if (!$result)
            return false;
        return true;
    }

    private function timestamp_format($i)
    {
        return sprintf('%02d:%02d:%02d',
            ($i - ($i % 3600)) / 3600,
            (($i - ($i % 60)) / 60) % 60,
            $i % 60);
    }

    private function writeLog($content){
        if (LOG_ENABLE){
            $LOG_FILE = '/var/www/html/modules/agent_console/getInfoMainConsole.log';
            $fplog = fopen($LOG_FILE,"a");
            fwrite($fplog,date('d M Y H:i:s'). ": " . $content . "\n");
            fclose($fplog);
        }
    }

    //RUN SQL COMMAND
    public function runSQL($sql)
    {
        try{
            $result = mysql_query($sql);
            $data = array();
            if (!$result){
                $this->errMsg = mysql_error($this->con);
                return false;
            }
            $index = 0;
            while ($row = mysql_fetch_array($result)) {
                $data[$index] = $row;
                $index ++;
            }
            return $data;
        }catch (Exception $e) {
            // show error message
            $this->errMsg = 'Exception:' . $e;
            return false;
        }
    }

    function getAgentQueue($number)
    {
        $search = $this->runSQL("SELECT id FROM agent WHERE number='$number' AND estatus='A'");
        if (count($search)>0)
            return '8'.$number;
        else
            return $number;
    }

    private function getAgentQueueId($number)
    {
        $search = $this->runSQL("SELECT id FROM queue_call_entry WHERE queue='$number'");
        if (count($search)>0)
            return $search[0][0];
        else
            return false;
    }

    public function changePassword($agent,$old,$new)
    {
        try{
            // check old password
            $result = $this->runSQL("SELECT password from agent WHERE `number` = '$agent'");

            // compare password
            if (strcmp($result[0][0],$old)!=0)
                return 0;
            // change password
            $result = mysql_query("UPDATE agent SET password='$new' WHERE number='$agent'");
            if (!$result)
                return -1;
            return 1;
        }catch (Exception $e) {
            $this->writeLog('changePassword: Exception -> ' . $e);
            $this->errMsg = 'Exception:' . $e;
            return -1;
        }
    }

    // Get CUSTOMER MODULES
    public function getCustomerInfo($mobile)
    {
        try{
            $module_name = 'customer';
            // cut two first CLID if start with 08
            if (substr($mobile,0,2) == '08')
                $mobile_search = '%' . substr($mobile,2,strlen($mobile));
            else
                $mobile_search = $mobile;
            // if not found try to search in customer contact table
            $result = mysql_query("SELECT customer_id FROM customer_contact WHERE
                 phone like '$mobile_search' LIMIT 1");
            if (mysql_num_rows($result)==0)
                return null;

            $row = mysql_fetch_array($result);
            $id = $row['customer_id'];
            // get info about this customer
            $customer = $this->runSQL("SELECT a.*,b.name AS district,c.name AS province, d.name AS booker, e.name AS accountant, f.name AS sale FROM customer a
                                LEFT JOIN customer_district b ON a.district_id = b.id LEFT JOIN customer_province c ON a.province_id = c.id LEFT JOIN customer_booker d
                                ON a.booker_id = d.id LEFT JOIN customer_accountant e ON a.accountant_id = e.id LEFT JOIN customer_sale f ON a.sale_id = f.id
                                WHERE a.id=$id");
            $arrResult = $customer[0];
            $type = $arrResult['type'];
            switch ($type){
                case '0':
                    $arrResult['type_name'] = '<img border=0 src="/modules/'.$module_name.'/images/nor-customer.png" title="Khách hàng lẽ"/>KLE';
                    break;
                case '1':
                    $arrResult['type_name'] = '<img border=0 src="/modules/'.$module_name.'/images/fre-customer.png" title="Khách hàng lẽ thường xuyên"/>KLE-TX';
                    break;
                case '2':
                    $arrResult['type_name'] = '<img border=0 src="/modules/'.$module_name.'/images/company.png" title="Khách hàng công ty"/>CTY';
                    break;
                case '3':
                    $arrResult['type_name'] = '<img border=0 src="/modules/'.$module_name.'/images/agency.png" title="Khách hàng đại lý"/>DLY';
                    break;
                default:
                    break;
            }
            $arr_contact = $this->runSQL("SELECT * FROM customer_contact WHERE phone = '$mobile_search' and customer_id = $id");

            if (trim($arr_contact[0]['name']) != '') {
                $arrResult['contact_name'] = $arr_contact[0]['name'];
                $arrResult['contact_email'] = $arr_contact[0]['email']==''?'Chưa cập nhật':$arr_contact[0]['email'];
            }
            //var_dump($arrResult['contact_name']);
            return $arrResult;

        }catch (Exception $e) {
            $this->writeLog('getCustomerInfo: Exception -> ' . $e);
            // show error message
            $this->errMsg = 'Exception:' . $e;
            return false;
        }
    }
    function getCustomerName($mobile)
    {
        return 'KH_'.$mobile;
    }
	
    function getAgentId($sAgent)
    {
        try{
            //check if existing call_id
            $result = mysql_query("SELECT id from agent WHERE number = '$sAgent' LIMIT 1");
            if (!$result){
                $this->errMsg = mysql_error($this->con);
                return false;
            }
            $row =  mysql_fetch_array($result);
            return $row['id'];
        }catch (Exception $e) {
            $this->writeLog('getAgentId: Exception -> ' . $e);
            // show error message
            $this->errMsg = 'Exception:' . $e;
            return false;
        }
    }
		
    private function getDeliveryName($user_id)
    {
        $query = "SELECT name from ticket_delivery_user WHERE id=$user_id";
        $result = mysql_query($query);
        if (!$result){
            $this->errMsg = mysql_error($this->con);
            return false;
        }
        $row = mysql_fetch_assoc($result);
        return $row['name'];
    }

    function getDelivery($search_id,$search_callid=true)
    {
        try{
            if ($search_callid)
                $sql = "SELECT a.*,b.name as agent from ticket_delivery a inner join agent b
                        on a.agent_id = b.id WHERE call_entry_id='$search_id' order by a.purchase_date desc LIMIT 5";
            else
                $sql = "SELECT a.*,b.name as agent from ticket_delivery a inner join agent b
                        on a.agent_id = b.id WHERE phone='$search_id' order by a.purchase_date desc LIMIT 5";
            $result = mysql_query($sql);
            if (!$result){
                $this->errMsg = mysql_error($this->con);
                return false;
            }
            $data = array();
            $index = 0;
            while ($row = mysql_fetch_assoc($result)) {
                $data[$index] = $row;
                $delivery_id = $row['id'];
                $res = mysql_query("SELECT ticket_code from ticket_delivery_code WHERE ticket_id = $delivery_id");
                while ($code = mysql_fetch_assoc($res))
                    $data[$index]['ticket_code'][] = $code['ticket_code'];
                // get delivery_man name
                if (!is_null($row['user_id']))
                    $data[$index]['delivery_name'] = $this->getDeliveryName($row['user_id']);
                else
                    $data[$index]['delivery_name'] = '(Chưa phân công)';
                $index ++;
            }
            return $data;
        }catch (Exception $e) {
            $this->writeLog('getDelivery: Exception -> ' . $e);
            // show error message
            $this->errMsg = 'Exception:' . $e;
            return false;
        }
    }

	function getAgentOffice($agent_id)
	{
		try{
            $sql = "SELECT a.office_name FROM office a INNER JOIN agent_office b ON
                a.id = b.office_id
                WHERE agent_id = $agent_id";
            $result = mysql_query($sql);
            if (!$result){
                $this->errMsg = mysql_error($this->con);
                return false;
            }
			$row = mysql_fetch_assoc($result);
			return $row['office_name'];
		}catch (Exception $e) {          
            // show error message
            $this->errMsg = 'Exception:' . $e;
            return false;
        }
	}

    function getDeliveryCdr($uniqueid)
    {
        try{
            //check if existing call_id
            $sql = "SELECT a.id from ticket_delivery a
            LEFT JOIN call_entry b
            ON a.call_entry_id = b.id
            WHERE b.uniqueid = '$uniqueid'";
            $result = mysql_query($sql);
            if (!$result){
                $this->errMsg = mysql_error($this->con);
                return false;
            }
            $data =  mysql_fetch_array($result);
            return $data;
        }catch (Exception $e) {
            $this->writeLog('getDelivery: Exception -> ' . $e);
            // show error message
            $this->errMsg = 'Exception:' . $e;
            return false;
        }
    }

    function getDeliveryById($ticket_id)
    {
        try{
            $sql = "SELECT a.*,b.name as agent from ticket_delivery a LEFT JOIN agent b
                        on a.agent_id = b.id WHERE a.id=$ticket_id order by a.purchase_date desc";
            $result = mysql_query($sql);
            if (!$result){
                $this->errMsg = mysql_error($this->con);
                return false;
            }
            $data = array();
            $index = 0;
            $row = mysql_fetch_assoc($result);
            $data = $row;
            $delivery_id = $row['id'];
            $res = mysql_query("SELECT ticket_code from ticket_delivery_code WHERE ticket_id = $delivery_id");
            $data['ticket_code'] =array();
			while ($code = mysql_fetch_assoc($res))
                $data['ticket_code'][] = $code['ticket_code'];
            // get delivery_man name
            if (!is_null($row['user_id']))
                $data['delivery_name'] = $this->getDeliveryName($row['user_id']);
            else
                $data['delivery_name'] = '(Chưa phân công)';
			// get office name
			//$data['office'] = $this->getAgentOffice($row['agent_id']);
			// adjust date format
			$data['purchase_date'] = (is_null($data['purchase_date'])?'':date("d-m-Y",strtotime($data['purchase_date'])));
			$data['delivery_date'] = (is_null($data['delivery_date'])?'(Chưa phân công)':date("d-m-Y H:i:s",strtotime($data['delivery_date'])));
			$data['collection_date'] = (is_null($data['collection_date'])?'(Chưa nhận tiền)':date("d-m-Y H:i:s",strtotime($data['collection_date'])));
            // get file attachment
            $res = mysql_query("SELECT * from ticket_delivery_attachment WHERE ticket_id = $delivery_id");
            $data['attachment'] =array();
            $index = 0;
            while ($file = mysql_fetch_assoc($res)) {
                $data['attachment'][$index]['FileName'] = $file['filename'];
                $data['attachment'][$index]['OriginalFileName'] = $file['filepath'];
                $data['attachment'][$index]['FileGuid'] = 'guid';
                $index++;
            }

            return $data;
        }catch (Exception $e) {
            $this->writeLog('getDelivery: Exception -> ' . $e);
            // show error message
            $this->errMsg = 'Exception:' . $e;
            return false;
        }
    }

    function disableDelivery($id)
    {
        $result = mysql_query("UPDATE ticket_delivery SET isActive=0 WHERE id=$id");
        if (!$result){
            $this->errMsg = mysql_error($this->con);
            return false;
        }
        $agent_name = $_SESSION['callcenter']['agente_nombre'];
        mysql_query("INSERT INTO ticket_delivery_log(ticket_id, remark, date_log,note)
                    VALUES($id,'$agent_name: Hủy yêu cầu giao vé',now(),'Booker chỉnh sửa')");
        return true;
    }

    function enableDelivery($id)
    {
        $result = mysql_query("UPDATE ticket_delivery SET isActive=1 WHERE id=$id");
        if (!$result){
            $this->errMsg = mysql_error($this->con);
            return false;
        }
        $agent_name = $_SESSION['callcenter']['agente_nombre'];
        mysql_query("INSERT INTO ticket_delivery_log(ticket_id, remark, date_log,note)
                    VALUES($id,'$agent_name: Mở lại yêu cầu giao vé',now(),'Booker chỉnh sửa')");
        return true;
    }

    function addDelivery($params,$edit=false)
    {
        try{
            // get parameters
            $call_entry_id = ($params['callid']==''?'null':$params['callid']);
            $agentid = $params['agentid'];
            $price = $params['price'];
            $currency_rate = $params['rate'];
            $purchase_date  = $params['purchase_date'];
            $isInvoice = $params['isInvoice'];
            $pay_amount  = $params['pay'];
            $deliver_address  = mysql_real_escape_string($params['address']);
            $customer_name = $params['name'];
            $customer_phone = $params['phone'];
            $ticket_code = $params['code'];
            $call_phone = $params['call_phone'];
            $tax	= $params['tax'];
            $note	= mysql_real_escape_string($params['note']);
            if (!$edit) {
                if ($call_entry_id!='null') {
                    //check if existing call_id
                    $sql = "SELECT id FROM ticket_delivery where call_entry_id = $call_entry_id";
                    $result = mysql_query($sql);
                    $row_count = mysql_num_rows($result);
                    if ($row_count>0)
                        $call_entry_id = 'null';
                }
                // get office where the ticket was purchased
                $office = $this->getAgentOffice($agentid);
                $sql = "INSERT INTO ticket_delivery VALUES (DEFAULT, $agentid, '$purchase_date', '$price', '$tax','$currency_rate',
                        null, '$pay_amount', '$deliver_address', null, 'Mới', $call_entry_id, '$customer_name',
                        '$customer_phone','$call_phone',null,null,null,'$office',1,$isInvoice,now())";
                $result = mysql_query($sql);
                if (!$result){
                    //$this->errMsg = $sql;
                    $this->errMsg = mysql_error($this->con);
                    return false;
                }
                $id = mysql_insert_id();
                // Insert ticket code table
                foreach ($ticket_code as $code) {
                    $res = mysql_query("INSERT INTO ticket_delivery_code VALUES (DEFAULT, $id, '$code')");
                    if (!$res){
                        $this->errMsg = mysql_error($this->con);
                        return false;
                    }
                }
                // insert file attachments
                foreach ($params['attachment'] as $file){
                    $filename = mysql_real_escape_string($file['FileName']);
                    $filepath = mysql_real_escape_string($file['OriginalFileName']);
                    $filesize = $file['FileSize'];
                    $attach_sql = "INSERT INTO ticket_delivery_attachment VALUES
                        (DEFAULT, $id,'$filename','$filepath','$filesize')";
                    $res = mysql_query($attach_sql);
                    if (!$res){
                        $this->errMsg = $attach_sql;//mysql_error($this->con);
                        return false;
                    }
                }
                // log
                $agent_name = $_SESSION['callcenter']['agente_nombre'];
                mysql_query("INSERT INTO ticket_delivery_log(ticket_id, remark, date_log,note)
                    VALUES($id,'$agent_name: Tạo mới yêu cầu giao vé',now(),'$note')");
                return true;
            }
            else{
                $id = $params['ticket_id'];
                $sql = "UPDATE ticket_delivery SET
                    price = '$price', tax = '$tax', currency_rate = '$currency_rate',
                    pay_amount = '$pay_amount', deliver_address = '$deliver_address',
                    customer_name = '$customer_name', customer_phone = '$customer_phone',
                    isInvoice = $isInvoice, purchase_date = '$purchase_date'
                    WHERE id=$id";
                $result = mysql_query($sql);
                if (!$result){
                    //$this->errMsg = $sql;
                    $this->errMsg = mysql_error($this->con);
                    return false;
                }
                // delete old ticket codes
                mysql_query("DELETE FROM ticket_delivery_code WHERE ticket_id=$id");
                // add new ticket codes
                foreach ($ticket_code as $code) {
                    $res = mysql_query("INSERT INTO ticket_delivery_code VALUES (DEFAULT, $id, '$code')");
                    if (!$res){
                        $this->errMsg = mysql_error($this->con);
                        return false;
                    }
                }
                // delete old file attachments
                mysql_query("DELETE FROM ticket_delivery_attachment WHERE ticket_id=$id");
                // add new file attachments
                foreach ($params['attachment'] as $file){
                    $filename = mysql_real_escape_string($file['FileName']);
                    $filepath = mysql_real_escape_string($file['OriginalFileName']);
                    $filesize = $file['FileSize'];
                    $attach_sql = "INSERT INTO ticket_delivery_attachment VALUES
                        (DEFAULT, $id,'$filename','$filepath','$filesize')";
                    $res = mysql_query($attach_sql);
                    if (!$res){
                        $this->errMsg = $attach_sql;//mysql_error($this->con);
                        return false;
                    }
                }
                // log
                $agent_name = $_SESSION['callcenter']['agente_nombre'];
                mysql_query("INSERT INTO ticket_delivery_log(ticket_id, remark, date_log,note)
                    VALUES($id,'$agent_name: Chỉnh sửa',now(),'$note')");
                return true;
            }
        }catch (Exception $e) {
            $this->writeLog('addDelivery: Exception -> ' . $e);
            // show error message
            $this->errMsg = 'Exception:' . $e;
            return false;
        }
    }

    function getNoteCdr($unique_id)
    {
        try{
            //check if existing call_id
            $sql = "SELECT a.* from call_entry_note a
            LEFT JOIN call_entry b
            ON a.call_entry_id = b.id
            WHERE b.uniqueid = '$unique_id'";
            $result = mysql_query($sql);
            if (!$result){
                $this->errMsg = mysql_error($this->con);
                return false;
            }
            $data =  mysql_fetch_array($result);
            return $data;
        }catch (Exception $e) {
            // show error message
            $this->errMsg = 'Exception:' . $e;
            return false;
        }
    }

    function getNote($call_id)
    {
        try{
            //check if existing call_id
            $result = mysql_query("SELECT * from call_entry_note WHERE call_entry_id='$call_id'");
            if (!$result){
                $this->errMsg = mysql_error($this->con);
                return false;
            }
            return mysql_fetch_array($result);
        }catch (Exception $e) {
            $this->writeLog('getNote: Exception -> ' . $e);
            // show error message
            $this->errMsg = 'Exception:' . $e;
            return false;
        }
    }

    function addNote($call_id,$note)
    {
        try{
            //check if existing call_id
            $note = mysql_real_escape_string($note);
            $result = mysql_query("SELECT id from call_entry_note WHERE call_entry_id='$call_id'");
            if (mysql_num_rows($result)>0)
                $result = mysql_query("UPDATE call_entry_note SET note ='$note' WHERE call_entry_id=$call_id");
            else
                $result = mysql_query("INSERT INTO call_entry_note VALUES(DEFAULT,$call_id,'$note')");

            if (!$result){
                $this->errMsg = mysql_error($this->con);
                return false;
            }
            return true;
        }catch (Exception $e) {
            $this->writeLog('addNote: Exception -> ' . $e);
            // show error message
            $this->errMsg = 'Exception:' . $e;
            return false;
        }
    }

    function addExternalNote($sAgent,$sCallerId,$sNote)
    {
        $sNote = mysql_real_escape_string($sNote);
        $id_agent = $this->getAgentId($sAgent);
        $result = mysql_query("INSERT INTO call_agent_note VALUES(DEFAULT,$id_agent,'$sCallerId',now(),'$sNote')");
        if (!$result){
            $this->errMsg = mysql_error($this->con);
            return false;
        }
        return true;
    }

    function checkDeliveryPermission($ticket_id,$agent)
    {
        // get agent_id from delivery
        $agent_id_delivery = $this->runSQL("SELECT agent_id FROM ticket_delivery where id = $ticket_id");
        return $this->checkAgentPermission($agent_id_delivery[0][0],$agent);
    }

	// patch: user can edit note
	function checkPermitNote($call_id,$agent)
	{
		// get agent_id from note
        $agent_id_note = $this->runSQL("SELECT id_agent FROM call_entry where id = $call_id");
        // get agent_id from $_SESSION agent login
        $agent_id_session = $this->getAgentId($agent);
        if (strcmp($agent_id_session,$agent_id_note[0][0])==0)
            return true;
        return false;
	}

    private function checkAgentPermission($agent_id,$number)
    {
        if (is_null($number))
            return false;
        $agent_id_session = $this->getAgentId($number);
        if (strcmp($agent_id_session,$agent_id)==0)
            return true;
        return false;
    }
	
    function addCustomer($params, $customer_id='')
    {
        try{
            // check existence phone number
            foreach ($params[0] as $row) {
                if (substr($row,0,1)!=0)
                    $phone = '%'.$row;
                else
                    $phone = $row;
                if ($customer_id != '')
                    $sql = "SELECT customer_code, customer_name FROM customer_contact a
                    LEFT JOIN customer b ON a.customer_id = b.id
                    WHERE a.phone like '$phone' and b.id <> $customer_id";
                else
                    $sql = "SELECT customer_code, customer_name FROM customer_contact a
                    LEFT JOIN customer b ON a.customer_id = b.id
                    WHERE a.phone like '$phone'";
                $result = $this->runSQL($sql);

                if (count($result)>0){
                    $this->errMsg = "Số điện thoại $row đã được gán cho khách hàng '".$result[0]['customer_name']."'. Mã KH: ".$result[0]['customer_code'];
                    return false;
                }
            }

            $address = mysql_real_escape_string($params[5]);
            $birthday = $params[3]==''?"'$params[3]'":'null';
            $agent = $params[11];
            $date = date('H:i:s d-m-Y',time());

            if ($customer_id != '') {
                $sql= "UPDATE customer SET
                          customer_name='$params[1]',
                          cmnd='$params[6]',
						  passport='$params[7]',
						  birthday=$birthday,
						  birthplace='$params[4]',
						  address='$address',
						  membership='$params[8]',
						  email='$params[9]',
						  agent_id=$params[10],
						  latest_note = 'Cập nhật bời $agent lúc $date'
						  WHERE id=$customer_id";
                $result = mysql_query($sql);
                if (!$result){
                    $this->errMsg = mysql_error($this->con);
                    return false;
                }
                // delete all old number then update the new ones
                mysql_query("DELETE FROM customer_contact WHERE customer_id=$customer_id");
                foreach ($params[0] as $row) {
                    $result = mysql_query("INSERT INTO customer_contact(customer_id,phone) VALUES($customer_id,'$row')");
                    if (!$result){
                        $this->errMsg = mysql_error($this->con);
                        return false;
                    }
                }
                return true;
            }
            // if customer id is not set, do insert the new one
            $note = "Thêm mới bởi $agent lúc $date";
            $sql = "INSERT INTO customer(customer_name,cmnd,passport,birthday,
                        birthplace,address,`type`,membership,email,agent_id,latest_note)
                        VALUES('$params[1]','$params[6]',
						'$params[7]',$birthday,'$params[4]','$address',0,
						'$params[8]','$params[9]','$params[10]','$note')"; //hardcode payment_type
            $result = mysql_query($sql);

            if (!$result){
                $this->errMsg = mysql_error($this->con);
                return false;
            }
            $insert_customer_id = mysql_insert_id();
            foreach ($params[0] as $row) {
                $result = mysql_query("INSERT INTO customer_contact(customer_id,phone) VALUES($insert_customer_id,'$row')");
                if (!$result){
                    $this->errMsg = mysql_error($this->con);
                    return false;
                }
            }
            return true;
        }catch (Exception $e) {
            $this->writeLog('addCustomer: Exception -> ' . $e);
            // show error message
            $this->errMsg = 'Exception:' . $e;
            return false;
        }
    }
    // ** END OF CUSTOMER MODULES

    //Get history calls of this mobile
    public function getCallMobileHistoryArray($mobile,$limit=10) {
        try{
            $arrContent = array();
            //$con = mysql_connect($this->CallcenterDb_host,$this->CallcenterDb_user,$this->CallcenterDb_password);
            //code right there
            $sql = "SELECT a.callerid AS phone, a.datetime_entry_queue AS calldate, a.duration, b.name AS agent, a.status,
				a.trunk, a.id
				FROM call_entry a
				LEFT JOIN agent b ON
				a.id_agent = b.id
				where a.callerid = '$mobile'
				and a.status != 'activa'
				ORDER BY a.datetime_entry_queue DESC
				LIMIT $limit";
            $result = mysql_query($sql);
            if (mysql_num_rows($result)==0){
                $this->writeLog('getCallMobileArray: ' . $mobile . 'has no call log');
                return null;
            }
            else{
                $stt = array(
                    'abandonada'	=> 'Bỏ nhỡ',
                    'activa'		=> 'Đang gọi',
                    'terminada'		=>	'Đã nghe',
                    'en-cola'		=>	'Đang chờ',
                );
                $index = 0;
                while($row = mysql_fetch_array($result)){
                    // get note
                    $arr_note = $this->getNote($row['id']);
                    $note = $arr_note['note'];
                    // get delivery info
                    $arrDelivery = $this->getDelivery($row['id']);
                    $arrContent[$index] = array(
                        'phone'		=>		$row['phone'],
                        'calldate'	=>		date("d-m-Y H:i:s",strtotime($row['calldate'])),
                        'duration'	=>		$this->timestamp_format($row['duration']),
                        'agent'		=>		$row['agent'],
                        'status'	=>		$stt[$row['status']],
                        'trunk'		=>		$row['trunk'],
                        'note'      =>      $note,
                        'delivery'  =>      (count($arrDelivery)>0?$arrDelivery:null),
                        'id'        =>      $row['id'],
                        'delivery_id'   =>  (count($arrDelivery)>0?$arrDelivery[0]['id']:null),
                    );
                    $index ++;
                }
            }
            return $arrContent;
        }
        catch (Exception $e) {
            $this->writeLog('getCallMobileHistoryArray: Exception -> ' . $e);
            return false;
        }
    }
	
	//Get agent_queue
	private function getAgentMainQueue($number)
	{
		if ($number == null)
			return null;
		$search = $this->runSQL("SELECT queue_id FROM agent WHERE number='$number'");				
		if (count($search)>0)
            return $search[0]['queue_id'];
        else
            return null;
	}

    //Get ticket submitted by mobile
    public function getCallHistoryArray($limit=20,$agent=null) {
        try{
            $agent_queue = $this->getAgentMainQueue($agent);
            $private_queue = $this->getAgentQueueId('8'.$agent);
			$arrContent = array();            
            $sql = "SELECT a.callerid AS phone, a.datetime_entry_queue AS calldate, a.duration, b.name AS agent, a.status,
	  		a.trunk, a.id, a.id_agent, c.queue
			FROM call_entry a
			LEFT JOIN agent b ON
			    a.id_agent = b.id
			LEFT JOIN queue_call_entry c ON
			    a.id_queue_call_entry = c.id
			" . ($agent_queue==null?" ":" WHERE a.id_queue_call_entry=$agent_queue OR a.id_queue_call_entry=$private_queue ") .
			"ORDER BY a.datetime_entry_queue DESC
			LIMIT $limit";
            $result = mysql_query($sql);
            $index = 0;
            $stt = array(
                'abandonada'	=> 'Bỏ nhỡ',
                'activa'		=> 'Đang gọi',
                'terminada'		=>	'Đã nghe',
                'en-cola'		=>	'Đang chờ',
            );
            while($row = mysql_fetch_array($result)){
                // get note
                $arr_note = $this->getNote($row['id']);
                $note = $arr_note['note'];
                // get delivery info
                $arrDelivery = $this->getDelivery($row['id']);
                //$date = date("d-m-Y",strtotime($row['calldate']));
                //$time = date("H:i:s",strtotime($row['calldate']));
                $arrContent[$index] = array(
                    'phone'		=>		'<a href="javascript:void(0)" onclick="make_call(\''.$row['phone'].'\')"'.">
                        <img src='modules/agent_console/images/call.png' title='Gọi số ".$row['phone']."'/></a>".
                        $row['phone'],
                    'calldate'	=>		date("H:i:s d/m",strtotime($row['calldate'])),//'<span title="'.$date.'">'.$time.'</span>',
                    'duration'	=>		$row['duration'],
                    'agent'		=>		$row['agent']==''?$row['queue']:$row['agent'].'/'.$row['queue'],
                    'status'	=>		$stt[$row['status']],
                    'trunk'		=>		$row['trunk'],
                    'note'      =>      $note,
                    'delivery'  =>      (count($arrDelivery)>0?$arrDelivery:null),
                    'id'        =>      $row['id'],
                    'delivery_id'   =>  (count($arrDelivery)>0?$arrDelivery[0]['id']:null),
                    'permit_add'    =>  $this->checkAgentPermission($row['id_agent'],$agent)?'1':'0',
                );
                $index++;
            }
            return $arrContent;
        }
        catch (Exception $e) {
            $this->writeLog('getCallHistoryArray: Exception -> ' . $e);
            return false;
        }
    }

    public function getCallHistoryAgentArray($limit=20,$agent) {
        try{
            $id_agent = $this->getAgentId($agent);
            $arrContent = array();
            $sql = "SELECT a.callerid AS phone, a.datetime_entry_queue AS calldate, a.duration, b.name AS agent, a.status,
	  		a.trunk, a.id, a.id_agent, c.queue
			FROM call_entry a
			LEFT JOIN agent b ON
			    a.id_agent = b.id
			LEFT JOIN queue_call_entry c ON
			    a.id_queue_call_entry = c.id
            WHERE a.id_agent=$id_agent
            ORDER BY a.datetime_entry_queue DESC
			LIMIT $limit";
            $result = mysql_query($sql);
            $index = 0;
            $stt = array(
                'abandonada'	=> 'Bỏ nhỡ',
                'activa'		=> 'Đang gọi',
                'terminada'		=>	'Đã nghe',
                'en-cola'		=>	'Đang chờ',
            );
            while($row = mysql_fetch_array($result)){
                // get note
                $arr_note = $this->getNote($row['id']);
                $note = $arr_note['note'];
                // get delivery info
                $arrDelivery = $this->getDelivery($row['id']);
                //$date = date("d-m-Y",strtotime($row['calldate']));
                //$time = date("H:i:s",strtotime($row['calldate']));
                $arrContent[$index] = array(
                    'phone'		=>		'<a href="javascript:void(0)" onclick="make_call(\''.$row['phone'].'\')"'.">
                        <img src='modules/agent_console/images/call.png' title='Gọi số ".$row['phone']."'/></a>".
                        $row['phone'],
                    'calldate'	=>		date("H:i:s d/m",strtotime($row['calldate'])),//'<span title="'.$date.'">'.$time.'</span>',
                    'duration'	=>		$row['duration'],
                    'agent'		=>		$row['agent']==''?$row['queue']:$row['agent'].'/'.$row['queue'],
                    'status'	=>		$stt[$row['status']],
                    'trunk'		=>		$row['trunk'],
                    'note'      =>      $note,
                    'delivery'  =>      (count($arrDelivery)>0?$arrDelivery:null),
                    'id'        =>      $row['id'],
                    'delivery_id'   =>  (count($arrDelivery)>0?$arrDelivery[0]['id']:null),
                    'permit_add'    =>  $this->checkAgentPermission($row['id_agent'],$agent)?'1':'0',
                );
                $index++;
            }
            return $arrContent;
        }
        catch (Exception $e) {
            $this->writeLog('getCallHistoryArray: Exception -> ' . $e);
            return false;
        }
    }

    public function getExternalNote($number,$limit=10)
    {
        try{
            $sql = "SELECT a.*, b.name as agent FROM call_agent_note a LEFT JOIN agent b
            ON a.id_agent = b.id
            WHERE a.callerid = '$number'
            ORDER BY datetime desc
            LIMIT $limit";
            $result = $this->runSQL($sql);
            return $result;
        }
        catch (Exception $e) {
            $this->writeLog('getExternalNote: Exception -> ' . $e);
            return false;
        }
    }
}
?>