<?php

class importExcel {
    var $_DB;
    var $errMsg;

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
        $objPHPExcel = $objReader->load($inputFileName);

        // gerneral information
        /**  Use the PHPExcel object's getSheetCount() method to get a count of the number of WorkSheets in the WorkBook  */
        $sheetCount = $objPHPExcel->getSheetCount();
        $sheetNames = $objPHPExcel->getSheetNames();

        //  Get worksheet dimensions
        $sheet = $objPHPExcel->getSheet(0);
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();

        //  Loop through each row of the worksheet in turn
        $arrCustomer = array();
        $preCustomerCode = '@start';
        $index = -1;
        for ($row = 3; $row <= $highestRow; $row++){
            //  Read a row of data into an array
            $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row,NULL,TRUE,FALSE);
            $rowData = $rowData[0];

            if ($preCustomerCode != $rowData[0]) {
                $index++;
                $arrCustomer[$index]['customer_code'] = $rowData[0];
                $arrCustomer[$index]['customer_name'] = $rowData[1];
                $arrCustomer[$index]['type'] = $rowData[2];
                $arrCustomer[$index]['address'] = $rowData[3];
                $arrCustomer[$index]['district_id'] = $rowData[4];
                $arrCustomer[$index]['province_id'] = $rowData[5];
                $arrCustomer[$index]['booker_id'] = $rowData[9];
                $arrCustomer[$index]['accountant_id'] = $rowData[10];
                $arrCustomer[$index]['sale_id'] = $rowData[11];
                $arrCustomer[$index]['membership'] = $rowData[12];
                $preCustomerCode = $rowData[0];
                $index_contact = 0;
            }
            else {
                $arrCustomer[$index]['contact'][$index_contact]['phone'] = $rowData[6];
                $arrCustomer[$index]['contact'][$index_contact]['name'] = $rowData[7];
                $arrCustomer[$index]['contact'][$index_contact]['email'] = $rowData[8];
                $index_contact++;
            }
        }
        return $arrCustomer;
    }


    private function getDisctrictId()
    {

    }

    private function lookupValue($type,$value)
    {
        switch ($type) {
            case 'type':
                switch ($value) {
                    case 'CTY':
                        return 2;
                    case 'DLY':
                        return 3;
                    case 'KLE':
                        return 0;
                    default:
                        return 4;
                }
                break;
            case 'district':

                break;
            case 'province':

                break;
            case 'booker':

                break;
            case 'accountant':

                break;
            case 'sale':

                break;
            default:
                break;
        }
    }
}

$result = new importExcel();
echo '<pre>'.print_r($result->convert2Array('sample.xls'),1).'</pre>';
?>