<?php
define('END_COLUMN','M');
define('START_ROW',3);

class importExcel {
    var $_DB;
    var $errMsg;

    var $num_error = 0;
    var $num_district = 0;
    var $num_province = 0;
    var $num_booker = 0;
    var $num_accountant = 0;
    var $num_sale = 0;
    var $arrError = array();
    var $index_error = 0;


    function importExcel(&$pDB)
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

    private function getCustomerByCode($customer_code)
    {
        try{
            $result = $this->_DB->getFirstRowQuery("SELECT * from customer WHERE customer_code = '$customer_code'", true);
            if(!$result && $result==null && count($result) < 1) {
                return null;
            }
            else {
                $id = $result['id'];
                $arr_contact = $this->_DB->fetchTable("SELECT * from customer_contact WHERE customer_id=$id ORDER BY id", true);
                $result['contact'] =  $arr_contact;
                //if ($customer_code == 'AVC')
                //    echo '<pre>'.print_r($result,1).'</pre>';
                return $result;
            }
        }catch (Exception $e) {
            // show error message
            $this->errMsg = 'Exception:' . $e;
            return false;
        }
    }

    private function checkCustomerChanges($result,$record)
    {
        $index_contact = 0;

        //remove element 'excel_row'
        for ($i=0;$i<count($result['contact']);$i++)
            unset($record['contact'][$i]['excel_row']);

        foreach ($result['contact'] as $contact){
            $arrContact[$index_contact]['phone'] = $contact['phone'];
            $arrContact[$index_contact]['name'] = $contact['name'];
            $arrContact[$index_contact]['email'] = $contact['email'];
            $index_contact++;
        }
        $arrCurrent = array(
            'customer_code' =>  $result['customer_code'],
            'customer_name' =>  $result['customer_name'],
            'type'          =>  $result['type'],
            'address'       =>  $result['address'],
            'district_id'   =>  $result['district_id'],
            'province_id'   =>  $result['province_id'],
            'booker_id'     =>  $result['booker_id'],
            'accountant_id' =>  $result['accountant_id'],
            'sale_id'       =>  $result['sale_id'],
            'membership'    =>  $result['membership'],
            'contact'       =>  $arrContact,

        );
        //echo '<pre>'.print_r($arrCurrent,1).'</pre>';
        //echo '<pre>'.print_r($record,1).'</pre>';die;
        //var_dump($record != $arrCurrent);
        if ($record == $arrCurrent)
            return false;
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

    public function import2Db($filename)
    {
        $arr = $this->convert2Array($filename);
        if (!$arr)
            return false;

        $num_update = 0;
        $arr_update = array();
        $num_new = 0;
        $arr_new = array();

        foreach ($arr as $record){
            try{
                $customer_code = $record['customer_code'];
                $result = $this->getCustomerByCode($customer_code);
                 // check existence of customer_code
                $customer_name = $record['customer_name'];
                $type = $record['type'];
                $address = $record['address'];
                $district_id = $record['district_id'];
                $province_id = $record['province_id'];
                $booker_id = $record['booker_id'];
                $accountant_id = $record['accountant_id'];
                $sale_id = $record['sale_id'];
                $membership = $record['membership'];
                $customer_id = $result['id'];
                if (!is_null($result)){
                    // if exist, check if any changes from this record
                    if ($this->checkCustomerChanges($result,$record)){
                        // change process -> update query
                        $sql = "UPDATE customer SET
                              customer_name = '$customer_name',
                              `type` = $type,
                              address = '$address',
                              district_id = $district_id,
                              province_id = $province_id,
                              booker_id = $booker_id,
                              accountant_id = $accountant_id,
                              sale_id = $sale_id,
                              membership = '$membership'
                              WHERE customer_code = '$customer_code'";
                        $this->_DB->genQuery($sql);
                        // update contact
                        for ($i=0;$i<count($record['contact']);$i++){
                            $phone = $record['contact'][$i]['phone'];
                            $name = $record['contact'][$i]['name'];
                            $email = $record['contact'][$i]['email'];
                            $index_exception = $record['contact'][$i]['excel_row'];
                            $id = $result['contact'][$i]['id'];
                            $check_phone =$this->check_phone_duplicate($phone,$customer_id);
                            if (isset($id)) {
                                if ($check_phone) {
                                    $this->arrError[$this->index_error]['row'] = $index_exception;
                                    $this->arrError[$this->index_error]['error'] = 'Trùng số điện thoại KH: ' . $check_phone['customer_code'].'-'.$check_phone['customer_name'];
                                    $this->num_error ++;
                                    $this->index_error ++;
                                }
                                else {
                                    $sql = "UPDATE customer_contact SET
                                      phone = '$phone',`name` = '$name',email = '$email' WHERE id = $id";
                                    $this->_DB->genQuery($sql);
                                }
                            }
                            else{ // add contact
                                if ($check_phone) {
                                    $this->arrError[$this->index_error]['row'] = $index_exception;
                                    $this->arrError[$this->index_error]['error'] = 'Trùng số điện thoại KH: ' . $check_phone['customer_code'].'-'.$check_phone['customer_name'];
                                    $this->num_error ++;
                                    $this->index_error ++;
                                }
                                else {
                                    $this->_DB->genQuery("INSERT INTO customer_contact(phone,`name`,email,customer_id) VALUES(
                                      '$phone','$name','$email',$customer_id)");
                                }
                            }
                        }
                        // if num of record list less than result list, do delete remaining rows
                        if ($i < count($result['contact']))
                            for ($j=$i;$j<count($result['contact']);$j++) {
                                $id_delete = $result['contact'][$j]['id'];
                                $index_exception = $record['contact'][$j]['excel_row'];
                                $sql = "DELETE FROM customer_contact WHERE id=$id_delete";
                                $this->_DB->genQuery($sql);
                            }
                        $arr_update[$num_update] = $customer_code;
                        $num_update ++;
                    }
                }// if no exist, insert query
                else {
                    $sql = "INSERT INTO customer(customer_code,customer_name,`type`,address,district_id,
                            province_id, booker_id, accountant_id, sale_id, membership)
                            VALUES('$customer_code','$customer_name',$type,'$address',$district_id,$province_id,$booker_id,
                            $accountant_id,$sale_id,'$membership')";
                    $this->_DB->genQuery($sql);
                    // add new contact
                    $insert_id = $this->_DB->getLastInsertId();
                    foreach ($record['contact'] as $row) {
                        $phone = $row['phone'];
                        $name = $row['name'];
                        $email = $row['email'];
                        $index_exception = $row['contact']['excel_row'];
                        $check_phone = $this->check_phone_duplicate($phone,$insert_id);
                        if ($check_phone) {
                            $this->arrError[$this->index_error]['row'] = $index_exception;
                            $this->arrError[$this->index_error]['error'] = 'Trùng số điện thoại KH: ' . $check_phone['customer_code'].'-'.$check_phone['customer_name'];
                            $this->num_error ++;
                            $this->index_error ++;
                        }
                        else {
                            $this->_DB->genQuery("INSERT INTO customer_contact(phone,`name`,email,customer_id) VALUES(
                                  '$phone','$name','$email',$insert_id)");
                        }
                    }
                    $arr_new[$num_new] = $customer_code;
                    $num_new++;
                }
            }catch (Exception $e) {
                // show error message
                $this->arrError[$this->index_error]['row'] = $index_exception;
                $this->arrError[$this->index_error]['error'] = 'Lỗi: ' . $e;
                $this->num_error ++;
                $this->index_error ++;
            }
        }
        // return: #update & #new & #false & #new district, province, booker, accountant, sale list
        return array(
            'num_update'    =>  $num_update,
            'arr_update'    =>  $arr_update,
            'num_new'       =>  $num_new,
            'arr_new'       =>  $arr_new,
            'num_error'     =>  $this->num_error,
            'arr_error'     =>  $this->arrError,
            'num_district'  =>  $this->num_district,
            'num_province'  =>  $this->num_province,
            'num_booker'    =>  $this->num_booker,
            'num_accountant'    =>  $this->num_accountant,
            'num_sale'      =>  $this->num_sale );
    }

    public function convert2Array($filename)
    {
        /** Include path **/
        set_include_path(get_include_path() . PATH_SEPARATOR . '/Classes/');

        /** PHPExcel_IOFactory */
        include 'PHPExcel/IOFactory.php';

        $inputFileType = 'Excel5';
        $inputFileName = $filename;

        /**  Create a new Reader of the type defined in $inputFileType  **/
        $objReader = PHPExcel_IOFactory::createReader($inputFileType);
        /**  Load $inputFileName to a PHPExcel Object  **/
        $objReader->setReadDataOnly(true);
        try{
            $objPHPExcel = $objReader->load($inputFileName);
        }catch (Exception $e) {
            // show error message
            $this->errMsg = 'File phải theo định dạng Excel 2003. Exception: ' . $e;
            return false;
        }
        // general information
        /**  Use the PHPExcel object's getSheetCount() method to get a count of the number of WorkSheets in the WorkBook  */
        //$sheetCount = $objPHPExcel->getSheetCount();
        //$sheetNames = $objPHPExcel->getSheetNames();

        //  Get worksheet dimensions
        $sheet = $objPHPExcel->getSheet(0);
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();

        if ($highestColumn != END_COLUMN) {
            $this->errMsg = "Cột trong file không đúng quy định. Cột cuối cùng phải là '" . END_COLUMN ."'. Cột cuối trong file import là '$highestColumn'";
            return false;
        }

        //  Loop through each row of the worksheet in turn
        $arrCustomer = array();
        $preCustomerCode = '@start';
        $index = -1;
        for ($row = START_ROW; $row <= $highestRow; $row++){
            //  Read a row of data into an array
            $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row,NULL,TRUE,FALSE);
            $rowData = $rowData[0];

            if (trim($rowData[0])=='') {
                $this->arrError[$this->index_error]['row'] = $row;
                $this->arrError[$this->index_error]['error'] = 'Không có mã khách hàng';
                $this->num_error ++;
                $this->index_error ++;
                continue;
            }

            if ($preCustomerCode != $rowData[0]) {
                // search if any duplicate customer_code
                for ($i=0;$i<count($arrCustomer);$i++) {
                    if ($arrCustomer[$i]['customer_code'] == $rowData[0]) {
                        $index_next = count($arrCustomer[$i]['customer_code']['contact']);
                        $arrCustomer[$i]['contact'][$index_next]['phone'] = $rowData[6];
                        $arrCustomer[$i]['contact'][$index_next]['name'] = $rowData[7];
                        $arrCustomer[$i]['contact'][$index_next]['email'] = $rowData[8];
                        $arrCustomer[$i]['contact'][$index_next]['excel_row'] = $row;
                        break;
                    }
                }
                if ($i < count($arrCustomer) && count($arrCustomer)>0)
                    continue;

                $index++;
                // validate phone
                if (!is_numeric($rowData[6]) && trim($rowData[6])=='') {
                    $this->arrError[$this->index_error]['row'] = $row;
                    if (trim($rowData[6])=='')
                        $this->arrError[$this->index_error]['error'] = 'Không có số diện thoại';
                    else
                        $this->arrError[$this->index_error]['error'] = 'Điện thoại không hợp lệ';
                    $this->num_error ++;
                    $this->index_error ++;
                    continue;
                }

                $arrCustomer[$index]['customer_code'] = $rowData[0];

                if (trim($rowData[1])=='')
                    $arrCustomer[$index]['customer_name'] = $rowData[1];
                else
                    $arrCustomer[$index]['customer_name'] = $rowData[7];

                $arrCustomer[$index]['type'] = $this->lookupValue('type',$rowData[2]);
                $arrCustomer[$index]['address'] = $rowData[3];
                $arrCustomer[$index]['district_id'] = $this->lookupValue('district',$rowData[4]);
                $arrCustomer[$index]['province_id'] = $this->lookupValue('province',$rowData[5]);
                $arrCustomer[$index]['booker_id'] = $this->lookupValue('booker',$rowData[9]);
                $arrCustomer[$index]['accountant_id'] = $this->lookupValue('accountant',$rowData[10]);
                $arrCustomer[$index]['sale_id'] = $this->lookupValue('sale',$rowData[11]);
                $arrCustomer[$index]['membership'] = $rowData[12];
                $preCustomerCode = $rowData[0];
                $index_contact = 0;
                $arrCustomer[$index]['contact'][$index_contact]['phone'] = $rowData[6];
                $arrCustomer[$index]['contact'][$index_contact]['name'] = $rowData[7];
                $arrCustomer[$index]['contact'][$index_contact]['email'] = $rowData[8];
                $arrCustomer[$index]['contact'][$index_contact]['excel_row'] = $row;

            }
            else {
                $index_contact++;
                $arrCustomer[$index]['contact'][$index_contact]['phone'] = $rowData[6];
                $arrCustomer[$index]['contact'][$index_contact]['name'] = $rowData[7];
                $arrCustomer[$index]['contact'][$index_contact]['email'] = $rowData[8];
                $arrCustomer[$index]['contact'][$index_contact]['excel_row'] = $row;
            }
        }
        return $arrCustomer;
    }


    private function getDistrictId($value)
    {
        try {
            $result = $this->_DB->getFirstRowQuery("SELECT id from customer_district WHERE `name` = '$value'", true);
            if(!$result && $result==null && count($result) < 1) {
                // insert new entry on district list
                $this->_DB->genQuery("INSERT INTO customer_district(`name`) VALUES('$value')");
                $this->num_district++;
                return $this->_DB->getLastInsertId();
            }
            else {
                return $result['id'];
            }
        }catch (Exception $e) {
            // show error message
            $this->errMsg = 'Exception:' . $e;
            return false;
        }
    }

    private function getProvinceId($value)
    {
        try {
            $result = $this->_DB->getFirstRowQuery("SELECT id from customer_province WHERE `name` = '$value'", true);
            if(!$result && $result==null && count($result) < 1) {
                // insert new entry on district list
                $this->_DB->genQuery("INSERT INTO customer_province(`name`) VALUES('$value')");
                $this->num_province++;
                return $this->_DB->getLastInsertId();
            }
            else {
                return $result['id'];
            }
        }catch (Exception $e) {
            // show error message
            $this->errMsg = 'Exception:' . $e;
            return false;
        }
    }

    private function getBookerId($value)
    {
        try {
            $result = $this->_DB->getFirstRowQuery("SELECT id from customer_booker WHERE `name` = '$value'", true);
            if(!$result && $result==null && count($result) < 1) {
                // insert new entry on district list
                $this->_DB->genQuery("INSERT INTO customer_booker(`name`) VALUES('$value')");
                $this->num_booker++;
                return $this->_DB->getLastInsertId();
            }
            else {
                return $result['id'];
            }
        }catch (Exception $e) {
            // show error message
            $this->errMsg = 'Exception:' . $e;
            return false;
        }
    }

    private function getAccountantId($value)
    {
        try {
            $result = $this->_DB->getFirstRowQuery("SELECT id from customer_accountant WHERE `name` = '$value'", true);
            if(!$result && $result==null && count($result) < 1) {
                // insert new entry on district list
                $this->_DB->genQuery("INSERT INTO customer_accountant(`name`) VALUES('$value')");
                $this->num_accountant++;
                return $this->_DB->getLastInsertId();
            }
            else {
                return $result['id'];
            }
        }catch (Exception $e) {
            // show error message
            $this->errMsg = 'Exception:' . $e;
            return false;
        }
    }

    private function getSaleId($value)
    {
        try {
            $result = $this->_DB->getFirstRowQuery("SELECT id from customer_sale WHERE `name` = '$value'", true);
            if(!$result && $result==null && count($result) < 1) {
                // insert new entry on district list
                $this->_DB->genQuery("INSERT INTO customer_sale(`name`) VALUES('$value')");
                $this->num_sale++;
                return $this->_DB->getLastInsertId();
            }
            else {
                return $result['id'];
            }
        }catch (Exception $e) {
            // show error message
            $this->errMsg = 'Exception:' . $e;
            return false;
        }
    }

    private function lookupValue($type,$value)
    {
        switch ($type) {
            case 'type':
                switch ($value) {
                    case 'CTY':
                        return 2;
                    case 'DL':
                        return 3;
                    case 'KLE':
                        return 0;
                    default:
                        return 4;
                }
            case 'district':
                return $this->getDistrictId($value);
            case 'province':
                return $this->getProvinceId($value);
            case 'booker':
                return $this->getBookerId($value);
            case 'accountant':
                return $this->getAccountantId($value);
            case 'sale':
                return $this->getSaleId($value);
            default:
                return $value;
        }
    }
}
?>