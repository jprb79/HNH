<?php
/* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Elastix version 0.5                                                  |
  | http://www.elastix.org                                               |
  +----------------------------------------------------------------------+
  | Copyright (c) 2006 Palosanto Solutions S. A.                         |
  +----------------------------------------------------------------------+
  | Cdla. Nueva Kennedy Calle E 222 y 9na. Este                          |
  | Telfs. 2283-268, 2294-440, 2284-356                                  |
  | Guayaquil - Ecuador                                                  |
  | http://www.palosanto.com                                             |
  +----------------------------------------------------------------------+
  | The contents of this file are subject to the General Public License  |
  | (GPL) Version 2 (the "License"); you may not use this file except in |
  | compliance with the License. You may obtain a copy of the License at |
  | http://www.opensource.org/licenses/gpl-license.php                   |
  |                                                                      |
  | Software distributed under the License is distributed on an "AS IS"  |
  | basis, WITHOUT WARRANTY OF ANY KIND, either express or implied. See  |
  | the License for the specific language governing rights and           |
  | limitations under the License.                                       |
  +----------------------------------------------------------------------+
  | The Original Code is: Elastix Open Source.                           |
  | The Initial Developer of the Original Code is PaloSanto Solutions    |
  +----------------------------------------------------------------------+
  $Id: paloSantoCDR.class.php,v 1.1.1.1 2008/01/31 21:31:55 bmacias Exp $
  $Id: paloSantoCDR.class.php,v 1.1.1.1 2008/06/25 16:51:50 afigueroa Exp $
  $Id: index.php,v 1.1 2010/02/04 09:20:00 onavarrete@palosanto.com Exp $
 */

//ini_set("display_errors", true);
if (file_exists("/var/lib/asterisk/agi-bin/phpagi-asmanager.php")) {
require_once "/var/lib/asterisk/agi-bin/phpagi-asmanager.php";
}
global $arrConf; 
//include_once("$arrConf[basePath]/libs/paloSantoACL.class.php");

class paloAdressBook {
    var $_DB;
    var $errMsg;

    function paloAdressBook(&$pDB)
    {
        // Se recibe como parámetro una referencia a una conexión paloDB
        if (is_object($pDB)) {
            $this->_DB =& $pDB;
            $this->errMsg = $this->_DB->errMsg;
        } else {
            $dsn = (string)$pDB;
            $this->_DB = new paloDB($dsn);

            if (!$this->_DB->connStatus) {
                $this->errMsg = $this->_DB->errMsg;
                // debo llenar alguna variable de error
            } else {
                // debo llenar alguna variable de error
            }
        }
    }

/*
This function obtain all records in the table, but, if the param $count is passed as true the function only return
a array with the field "total" containing the total of records.
*/

    function getAddressBook($limit=NULL, $offset=NULL, $field_name=NULL, $field_pattern=NULL, $count=FALSE, $sortname='a.id desc ',$letter=null)
    {
        // SIEMPRE se debe filtrar por usuario activo. Véase bug Elastix #1529.
    	if ($count)
            $sql = 'SELECT COUNT(*) AS total FROM customer a';
        else
            $sql = 'SELECT a.*,b.name as district,c.name as province,
                    d.name as booker, e.name as accountant, f.name as sale FROM customer a
                    LEFT JOIN customer_district b ON a.district_id = b.id
                    LEFT JOIN customer_province c ON a.province_id = c.id
                    LEFT JOIN customer_booker d ON a.booker_id = d.id
                    LEFT JOIN customer_accountant e ON a.accountant_id = e.id
                    LEFT JOIN customer_sale f ON a.sale_id = f.id ';
        $whereFields = null;
        $sqlParams = null;

        // Filtro por campo específico. Por omisión se filtra por id
        if (!is_null($field_name) and !is_null($field_pattern)) {
            // process number search and email
            if ($field_name=='phone'){
                $customer_search = $this->_DB->fetchTable("SELECT customer_id from customer_contact
                  WHERE phone like '$field_pattern'", true);
                if (count($customer_search)>0) {
                    $whereFields = ' a.id in (';
                    foreach ($customer_search as $key=>$row){
                        $customer_search_id = $row['customer_id'];
                        $whereFields .= $customer_search_id;
                        if ($key != (count($customer_search)-1))
                            $whereFields .= ',';
                    }
                    $whereFields .= ')';
                }
                else
                    $whereFields = " 1 <> 1 ";
            }
            elseif ($field_name=='email'){
                $customer_search = $this->_DB->fetchTable("SELECT customer_id from customer_contact
                  WHERE email like '$field_pattern'", true);
                $customer_search2 = $this->_DB->fetchTable("SELECT id as customer_id from customer
                  WHERE email like '$field_pattern'", true);
                $customer_search = array_merge($customer_search,$customer_search2);
                if (count($customer_search)>0) {
                    $whereFields = 'a.id in (';
                    foreach ($customer_search as $key=>$row){
                        $customer_search_id = $row['customer_id'];
                        $whereFields .= $customer_search_id;
                        if ($key != (count($customer_search)-1))
                            $whereFields .= ',';
                    }
                    $whereFields .= ')';
                }
                else
                    $whereFields = " 1 <> 1 ";
            }
            else
                $whereFields = "$field_name LIKE '$field_pattern'";
        }

        // for filter firstname by letter
        if(!is_null($letter)){
            if ($letter!='All') {
                if (trim($whereFields)=='' || is_null($whereFields))
                    $whereFields = " firstname LIKE '".$letter."%' ";
                else
                    $whereFields .= " AND firstname LIKE '".$letter."%' ";
            }
        }

        if (isset($whereFields)) $sql .= " WHERE $whereFields ";
        $sql .= " ORDER BY $sortname";

        if (!is_null($limit)) {
        	$sql .= ' LIMIT ?';
            $sqlParams[] = (int)$limit;
        }
        if (!is_null($offset) && $offset > 0) {
            $sql .= ' OFFSET ?';
            $sqlParams[] = (int)$offset;
        }

        $result = $this->_DB->fetchTable($sql, true,$sqlParams);
        //var_dump($sql);//var_dump($sqlParams);
        if (!is_array($result)) {
        	$this->errMsg = $this->_DB->errMsg;
            return false;
        }
        // return if count record
        if ($count){
            return $result;
        }

        // get number with customer id
        $index = 0;
        foreach ($result as $row) {
            $id = $row['id'];
            $arr_contact = $this->_DB->fetchTable("SELECT * from customer_contact WHERE customer_id=$id", true);
            $index_contact = 0;
            foreach ($arr_contact as $row_contact){
                $result[$index]['contact'][$index_contact]['name'] = $row_contact['name'];
                $result[$index]['contact'][$index_contact]['phone'] = $row_contact['phone'];
                $result[$index]['contact'][$index_contact]['email'] = $row_contact['email'];
                $index_contact++;
            }
            $index++;
        }
        //echo '<pre>',print_r($result,1),'</pre>';
        return $result;
    }

    public function getAccountantList()
    {
        $booker = $this->_DB->fetchTable("SELECT id,name from customer_accountant  order by name", true);
        $data = array();
        $data[0] = '(Chọn kế toán)';
        foreach ($booker as $row)
            $data[$row['id']] = $row['name'];
        return $data;
    }

    public function getBookerList()
    {
        $booker = $this->_DB->fetchTable("SELECT id,name from customer_booker order by name", true);
        $data = array();
        $data[0] = '(Chọn booker)';
        foreach ($booker as $row)
            $data[$row['id']] = $row['name'];
        return $data;
    }

    function getAgentList()
    {
        $booker = $this->_DB->fetchTable("SELECT id,name from agent WHERE estatus='A'", true);
        $data = array();
        $data[0] = '(Chọn booker)';
        foreach ($booker as $row)
            $data[$row['id']] = $row['name'];
        return $data;
    }

    public function getSaleList()
    {
        $booker = $this->_DB->fetchTable("SELECT id,name from customer_sale order by name", true);
        $data = array();
        $data[0] = '(Chọn Sale)';
        foreach ($booker as $row)
            $data[$row['id']] = $row['name'];
        return $data;
    }

    public function getDistrictList()
    {
        $booker = $this->_DB->fetchTable("SELECT id,name from customer_district order by name", true);
        $data = array();
        $data[0] = '(Chọn Quận/Huyện)';
        foreach ($booker as $row)
            $data[$row['id']] = $row['name'];
        return $data;
    }

    public function getProvinceList()
    {
        $booker = $this->_DB->fetchTable("SELECT id,name from customer_province order by name", true);
        $data = array();
        $data[0] = '(Chọn Tỉnh/Thành)';
        foreach ($booker as $row)
            $data[$row['id']] = $row['name'];
        return $data;
    }

    function contactData($id)
    {
        $where = "a.id=?";
        $params = array($id);
        $query   = "SELECT a.*,b.name as district,c.name as province,
                    d.name as booker, e.name as accountant, f.name as sale FROM customer a
                    LEFT JOIN customer_district b ON a.district_id = b.id
                    LEFT JOIN customer_province c ON a.province_id = c.id
                    LEFT JOIN customer_booker d ON a.booker_id = d.id
                    LEFT JOIN customer_accountant e ON a.accountant_id = e.id
                    LEFT JOIN customer_sale f ON a.sale_id = f.id
                    WHERE $where";
        $result=$this->_DB->getFirstRowQuery($query, true, $params);
        if(!$result && $result==null && count($result) < 1)
            return false;
        else {
            $arr_contact = $this->_DB->fetchTable("SELECT * from customer_contact WHERE customer_id=$id", true);
            $result['contact'] =  $arr_contact;
        }

        return $result;
    }

    function addContact($data,$elastix_user)
    {
        // check existence of the numbers
        foreach ($data['contact_phone'] as $row){
            $result = $this->check_phone_duplicate($row,null);
            if ($result){
                $customer_name = $result['customer_name'];$customer_code = $result['customer_code'];
                $this->errMsg = 'Số điện thoại "'.$row.'" đã được gán cho khách hàng: "'.$customer_name.'". Mã KH: '.$customer_code;
                return false;
            }
        }
        // for dealer or company customer
        $date = date('h:i:s d-m-Y');
        $latest_note = "Cập nhật bởi $elastix_user lúc $date";
        $input = array( $data['customer_code'],$data['customer_name'],$data['type'],
            $data['address'],$data['district_id'],$data['province_id'],
            $data['booker_id'],$data['accountant_id'],$data['sale_id'],$data['membership'],
            $data['email'],$data['cmnd'],$data['passport'],$data['birthday'],$data['birthplace'],$latest_note
        );

        $queryInsert = "INSERT INTO customer(customer_code, customer_name, `type`, address, district_id, province_id,
                      booker_id, accountant_id, sale_id, membership, email, cmnd, passport, birthday, birthplace,latest_note)
                      VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";

        $result = $this->_DB->genQuery($queryInsert, $input);
        if (!$result) {
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }
        // insert contact
        $insert_customer_id = $this->_DB->getLastInsertId();
        foreach ($data['contact_name'] as $index => $name) {
            $phone = $data['contact_phone'][$index];
            $email = $data['contact_email'][$index];
            $sql_contact = "INSERT INTO customer_contact(`name`,customer_id,email,phone)
                VALUES('$name',$insert_customer_id,'$email','$phone')";
            $result = $this->_DB->genQuery($sql_contact);
            if (!$result){
                $this->errMsg = $this->_DB->errMsg;
                continue;
            }
        }

        return true;
    }

    private function check_phone_duplicate($number,$id=null)
    {
        try {
            if (substr($number,0,1)!='0')
                $number = '%'.$number;
            if (!is_null($id))
                $sql = "SELECT customer_code, customer_name FROM customer_contact a
                LEFT JOIN customer b ON a.customer_id = b.id
                WHERE a.phone like '$number' and b.id <> $id";
            else
                $sql = "SELECT customer_code, customer_name FROM customer_contact a
                LEFT JOIN customer b ON a.customer_id = b.id
                WHERE a.phone like '$number'";
            $result = $this->_DB->getFirstRowQuery($sql, true);
            //var_dump($sql);
            if(!$result && $result==null && count($result) < 1) {
                return false;
            }
            else {
                return $result;
            }
        }catch (Exception $e) {
            // show error message
            $this->errMsg = 'Exception:' . $e;
            return false;
        }
    }

    private function getContact($customer_id){
        try {
            $result = $this->_DB->fetchTable("SELECT * FROM customer_contact
                WHERE customer_id = $customer_id", true);
            if(!$result && $result==null && count($result) < 1) {
                return false;
            }
            else {
                return $result;
            }
        }catch (Exception $e) {
            // show error message
            $this->errMsg = 'Exception:' . $e;
            return false;
        }
    }

    function updateContact($data,$id,$elastix_user)
    {
        // check existence of the numbers
        foreach ($data['contact_phone'] as $row){
            $result = $this->check_phone_duplicate($row,$id);
            if ($result){
                $customer_name = $result['customer_name'];$customer_code = $result['customer_code'];
                $this->errMsg = 'Số điện thoại "'.$row.'" đã được gán cho khách hàng: "'.$customer_name.'". Mã KH: '.$customer_code;
                return false;
            }
        }
        // for dealer or company customer
        $date = date('h:i:s d-m-Y');
        $latest_note = "Cập nhật bởi $elastix_user lúc $date";
        $input = array( $data['customer_code'],$data['customer_name'],$data['type'],
            $data['address'],$data['district_id'],$data['province_id'],
            $data['booker_id'],$data['accountant_id'],$data['sale_id'],$data['membership'],
            $data['email'],$data['cmnd'],$data['passport'],$data['birthday'],$data['birthplace'],$latest_note
        );

        $queryUpdate = "UPDATE customer SET
              customer_code=?,customer_name=?,`type`=?,
              address=?,district_id=?,province_id=?,
              booker_id=?,accountant_id=?,sale_id=?,membership=?,
              email=?,cmnd=?,passport=?,birthday=?,birthplace=?,latest_note=?
              WHERE id = $id";
        $result = $this->_DB->genQuery($queryUpdate, $input);
        if (!$result){
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }
        // delete contact
        $this->_DB->genQuery("DELETE FROM customer_contact WHERE customer_id = $id");
        // insert contact
        $insert_customer_id = $id;
        foreach ($data['contact_name'] as $index => $name) {
            $phone = $data['contact_phone'][$index];
            $email = $data['contact_email'][$index];
            $sql_contact = "INSERT INTO customer_contact(`name`,customer_id,email,phone)
                VALUES('$name',$insert_customer_id,'$email','$phone')";
            $result = $this->_DB->genQuery($sql_contact);
            if (!$result){
                $this->errMsg = $this->_DB->errMsg;
                return false;
            }
        }
        return true;
    }

    function existContact($phone)
    {
        $query =     " SELECT count(*) as total FROM customer "
                    ." WHERE phone=?";
	    $arrParam = array($phone);
        $result=$this->_DB->getFirstRowQuery($query, true, $arrParam);
        if(!$result)
            $this->errMsg = $this->_DB->errMsg;
        return $result;
    }

    function deleteContact($id)
    {
        $params = array($id);
        $query = "DELETE FROM customer WHERE id=?";
        $result = $this->_DB->genQuery($query, $params);
        if(!$result) {
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }
        // delete contact relative if any
        $result = $this->_DB->genQuery("DELETE FROM customer_contact WHERE customer_id = $id");
        if (!$result){
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }
        return true;
    }

    function Call2Phone($data_connection, $origen, $destino, $channel, $description)
    {
        $command_data['origen'] = $origen;
        $command_data['destino'] = $destino;
        $command_data['channel'] = $channel;
        $command_data['description'] = $description;
        return $this->AsteriskManager_Originate($data_connection['host'], $data_connection['user'], $data_connection['password'], $command_data);
    }

    function TranferCall($data_connection, $origen, $destino, $channel, $description)
    {
        exec("/usr/sbin/asterisk -rx 'core show channels concise' | grep ^$channel",$arrConsole,$flagStatus);
        if($flagStatus == 0){
            $arrData = explode("!",$arrConsole[0]);
            $command_data['origen']  = $origen;
            $command_data['destino'] = $destino;
            $command_data['channel'] = $arrData[12]; // $arrData[0] tiene mi canal de conversa, $arrData[12] tiene el canal con quies estoy conversando
            $command_data['description'] = $description;
            return $this->AsteriskManager_Redirect($data_connection['host'], $data_connection['user'], $data_connection['password'], $command_data);
        }
        return false;
    }

    function AsteriskManager_Redirect($host, $user, $password, $command_data) {
        global $arrLang;
        $astman = new AGI_AsteriskManager();

        if (!$astman->connect("$host", "$user" , "$password")) {
            $this->errMsg = $arrLang["Error when connecting to Asterisk Manager"];
        } else{
            $salida = $astman->Redirect($command_data['channel'], "", $command_data['destino'], "from-internal", "1");

            $astman->disconnect();
            if (strtoupper($salida["Response"]) != "ERROR") {
                return explode("\n", $salida["Response"]);
            }else return false;
        }
        return false;
    }

    function AsteriskManager_Originate($host, $user, $password, $command_data) {
        global $arrLang;
        $astman = new AGI_AsteriskManager();

        if (!$astman->connect("$host", "$user" , "$password")) {
            $this->errMsg = $arrLang["Error when connecting to Asterisk Manager"];
        } else{
            $parameters = $this->Originate($command_data['origen'], $command_data['destino'], $command_data['channel'], $command_data['description']);
            $salida = $astman->send_request('Originate', $parameters);

            $astman->disconnect();
            if (strtoupper($salida["Response"]) != "ERROR") {
                return explode("\n", $salida["Response"]);
            }else return false;
        }
        return false;
    }

    function Originate($origen, $destino, $channel="", $description="")
    {
        $parameters = array();
        $parameters['Channel']      = $channel;
        $parameters['CallerID']     = "$description <$origen>";
        $parameters['Exten']        = $destino;
        $parameters['Context']      = "from-internal";
        $parameters['Priority']     = 1;
        $parameters['Application']  = "";
        $parameters['Data']         = "";

        return $parameters;
    }
}
?>
