<?php

class paloSantoMissedCalls{
    var $_DB;
    var $_cdrDB;
    var $errMsg;

    function paloSantoMissedCalls(&$pDB,&$_cdrDB)
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

        if (is_object($_cdrDB)) {
            $this->_cdrDB =& $_cdrDB;
            $this->errMsg = $this->_cdrDB->errMsg;
        } else {
            $dsn = (string)$_cdrDB;
            $this->_cdrDB = new paloDB($dsn);

            if (!$this->_cdrDB->connStatus) {
                $this->errMsg = $this->_cdrDB->errMsg;
                // debo llenar alguna variable de error
            } else {
                // debo llenar alguna variable de error
            }
        }

    }

    private function getPrivateByQueue($queue){
        $query   = "SELECT a.number FROM agent a LEFT JOIN queue_call_entry b
                    ON a.queue_id = b.id
                    WHERE b.queue = '$queue'";
        $result=$this->_DB->fetchTable($query, true);
        return $result;
    }
    /*HERE YOUR FUNCTIONS*/

    function getNumCallingReport($date_start, $date_end, $filter_field, $filter_value)
    {
        $where = "";
        $arrParam = array();
        if(isset($filter_field) & $filter_field !="" & $filter_field!='status'){
            if ($filter_field=='queue' && $filter_value!=''){
                $queue_arr = $this->getPrivateByQueue($filter_value);
                $where = " AND queue in ('$filter_value'";
                foreach ($queue_arr as $queue){
                    $tmp = '8'.$queue['number'];
                    $where .= ",'$tmp'";
                }
                $where .= ") ";
            }
            elseif ($filter_field=='queue_dst') {
                $filter_field = 'queue';
                $where    = " AND $filter_field like ? ";
                $arrParam = array("$filter_value%");
            }
            else {
                $where    = " AND $filter_field like ? ";
                $arrParam = array("$filter_value%");
            }
        }
        $dates = array($date_start, $date_end);
        $arrParam = array_merge($dates,$arrParam);

        $query   = "SELECT count(*) FROM call_entry a
                  LEFT JOIN queue_call_entry c ON a.id_queue_call_entry = c.id
                  where a.status='abandonada' AND a.datetime_entry_queue  >= ? AND
                  a.datetime_entry_queue <= ? $where order by a.datetime_entry_queue asc";
        $result=$this->_DB->getFirstRowQuery($query, false, $arrParam);
        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }
        return $result[0];
    }

    function getCallingReport($date_start, $date_end, $filter_field, $filter_value)
    {
        $where    = "";
        $arrParam = array();
        if(isset($filter_field) & $filter_field !="" & $filter_field!='status'){
            if ($filter_field=='queue' && $filter_value!=''){
                $queue_arr = $this->getPrivateByQueue($filter_value);
                $where = " AND queue in ('$filter_value'";
                foreach ($queue_arr as $queue){
                    $tmp = '8'.$queue['number'];
                    $where .= ",'$tmp'";
                }
                $where .= ") ";
            }
            elseif ($filter_field=='queue_dst') {
                $filter_field = 'queue';
                $where    = " AND $filter_field like ? ";
                $arrParam = array("$filter_value%");
            }
            else {
                $where    = " AND $filter_field like ? ";
                $arrParam = array("$filter_value%");
            }
        }

        $dates = array($date_start, $date_end);
        $arrParam = array_merge($dates,$arrParam);

        $query   = "SELECT a.*, c.queue, c.script FROM call_entry a
                  LEFT JOIN queue_call_entry c ON a.id_queue_call_entry = c.id
                  where a.status='abandonada' AND a.datetime_entry_queue  >= ? AND
                  a.datetime_entry_queue <= ? $where order by a.datetime_entry_queue asc";

        $result=$this->_DB->fetchTable($query, true, $arrParam);

        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            // no result
            return array();
        }
        return $result;
    }

    private function getCustomer($number)
    {
        // handle the case prefix is 08
        if (substr($number,0,2)=='08')
            $number = substr($number,2,strlen($number)-2);
        // search number im customer_contact table
        $query   = "SELECT customer_id from customer_contact WHERE phone like '$number'";
        $result = $this->_DB->getFirstRowQuery($query, true);

        if (count($result)>0)
            return $result['customer_id'];
        return $number;
    }

    private function getCustomerId($number)
    {
        // handle the case prefix is 08
        if (substr($number,0,2)=='08')
            $number = substr($number,2,strlen($number)-2);
        // search number im customer_contact table
        $query   = "SELECT customer_id from customer_contact WHERE phone like '$number'";
        $result = $this->_DB->getFirstRowQuery($query, true);

        if (count($result)>0)
            return $result['customer_id'];
        return null;
    }

    private function getCustomerName($number)
    {
        // handle the case prefix is 08
        if (substr($number,0,2)=='08')
            $number = substr($number,2,strlen($number)-2);
        // search number im customer_contact table
        $query   = "SELECT customer_name, customer_code from customer a
            INNER JOIN customer_contact b ON a.id = b.customer_id
            WHERE phone = '$number'";
        $result = $this->_DB->getFirstRowQuery($query, true);

        if (count($result)>0)
            return trim($result['customer_name']=='')?$result['customer_code']:$result['customer_name'];
        return $number;
    }

    private function sksort(&$array, $subkey="id", $sort_ascending=false) {

        if (count($array))
            $temp_array[key($array)] = array_shift($array);

        foreach($array as $key => $val){
            $offset = 0;
            $found = false;
            foreach($temp_array as $tmp_key => $tmp_val)
            {
                if(!$found and strtolower($val[$subkey]) > strtolower($tmp_val[$subkey]))
                {
                    $temp_array = array_merge(    (array)array_slice($temp_array,0,$offset),
                        array($key => $val),
                        array_slice($temp_array,$offset)
                    );
                    $found = true;
                }
                $offset++;
            }
            if(!$found) $temp_array = array_merge($temp_array, array($key => $val));
        }

        if ($sort_ascending) $array = array_reverse($temp_array);

        else $array = $temp_array;
    }

    function showDataReport($arrData, $total,$date_start,$date_end,$filter_field,$filter_value)
    {
        $result = array();
        $arrTmpData = array();

        if(is_array($arrData) && $total>0){
            foreach($arrData as $key => $value){
                $keyTmp = trim($this->getCustomer($value['callerid'])."-".$value['queue']);
                if (!array_key_exists($keyTmp,$arrTmpData)){
                    // latest value
                    $arrTmpData[$keyTmp]['datetime'] = date('d-M-Y H:i:s',strtotime($value['datetime_entry_queue']));
                    $arrTmpData[$keyTmp]['src'] = $value['callerid'];
                    $arrTmpData[$keyTmp]['customer_name'] = $this->getCustomerName($value['callerid']);
                    $arrTmpData[$keyTmp]['dst'] = $value['queue'];
                    $arrTmpData[$keyTmp]['dst_name'] = $value['script'];
                    $arrTmpData[$keyTmp]['duration_wait'] = $value['duration_wait'];
                    $arrTmpData[$keyTmp]['dst_name'] = $value['script'];
                    // keep search date and uniqueid
                    $arrTmpData[$keyTmp]['start_date'] = $date_start;
                    $arrTmpData[$keyTmp]['end_date'] = $date_end;
                    $arrTmpData[$keyTmp]['uniqueid'][] = $value['uniqueid'];
                    if (!in_array($value['callerid'],$arrTmpData[$keyTmp]['phone']))
                        $arrTmpData[$keyTmp]['phone'][] = $value['callerid'];

                    // number of call
                    $arrTmpData[$keyTmp]['attempts'] = 1;
                } // update latest info if existing
                else{
                    $arrTmpData[$keyTmp]['attempts'] ++;
                    $arrTmpData[$keyTmp]['src'] = $value['callerid'];
                    $arrTmpData[$keyTmp]['datetime'] = date('d-M-Y H:i:s',strtotime($value['datetime_entry_queue']));
                    $arrTmpData[$keyTmp]['duration_wait'] = $value['duration_wait'];
                    $arrTmpData[$keyTmp]['uniqueid'][] = $value['uniqueid'];
                    if (!in_array($value['callerid'],$arrTmpData[$keyTmp]['phone']))
                        $arrTmpData[$keyTmp]['phone'][] = $value['callerid'];
                }
            }
        }
        // sort the result
        $this->sksort($arrTmpData,'datetime',false);
        // process status and filter if any
        foreach($arrTmpData as $key=>$value){
            if ($filter_field=='status'){
                $arrTmpData[$key]['status'] = $this->process($arrTmpData[$key],$filter_value);
            }
            else
                $arrTmpData[$key]['status'] = $this->process($arrTmpData[$key]);
            //remove if status is not matched
            if (is_null($arrTmpData[$key]['status']))
                unset($arrTmpData[$key]);
        }
        //var_dump(count($arrTmpData));
        // generate data from joining array
        $i = 0;
        foreach($arrTmpData as $key=>$value){
            $phone_list = '';
            $phone_list_call = '';
            foreach ($value['phone'] as $phone){
                $phone_list .= $phone . '&#13;';
                $phone_list_call .= '<a onclick="make_call(\''.$phone.'\')" href="javascript:void(0)">
                        <img title="Gọi số '.$phone.'" src="modules/agent_console/images/call.png"></a>&nbsp'.$phone .'<br>';
            }
            $result[$i][] = $value['datetime'];
            $result[$i][] = "<label title='".trim($phone_list)."'>".$value['customer_name']."</label>";
            $result[$i][] = trim($phone_list_call);
            $result[$i][] = $value['dst'];
            $result[$i][] = $value['dst_name'];
            $result[$i][] = $this->getTimeToLastCall($value['datetime']);
            $result[$i][] = "<label title='".$value['duration_wait']." giây' style='color:blue'>".$this->SecToHHMMSS($value['duration_wait'])."</label>";
            $result[$i][] = $value['attempts'];
            $result[$i][] = $value['status'];
            $i++;
        }
        return $result;
    }

    private function process($arrData,$filter_value="no")
    {
        /*
         * status of processing missed calls:
         *  Chưa gọi lại
         *  Đã gọi lại
         *  ---------------------------------
         *  a, Nếu một số điện thoại gọi vào tổng đài bị lỡ, sau đó gọi vào nhiều lần nữa bị lỡ thì gom nhóm lại
         *      thành 1 (có hiện số lần gọi lỡ).
            b, Nếu một hoặc nhiều số điện thoại gọi vào tổng đài bị lỡ, nhưng cùng nằm trong dãy số thuộc 1 công ty,
                1 đại lý thì sẽ gom nhóm lại là 1.
            c, Nếu một số điện thoại gọi vào tổng đài bị lỡ, sau đó có booker gọi ra cho số đó thì cuộc gọi lỡ
                đó không hiện lên nữa.
            d, Nếu một số điện thoại gọi vào tổng đài bị lỡ, sau đó gọi vào lần nữa và đã được bắt máy thì các
                cuộc gọi lỡ số đó sẽ không hiện nữa.
        */

        $after_datetime = date('Y-m-d H:i:s',strtotime($arrData['datetime']));
        $end_date = $arrData['end_date'];
        $number = $arrData['src'];
        // get all customer phones
        $customer_id = $this->getCustomerId($number);

        if (!is_null($customer_id))
            $number_array = $this->_DB->fetchTable("SELECT phone FROM customer_contact WHERE customer_id = $customer_id",true);
        else
            $number_array[] = array('phone'=>$number);
        $count = 0;

        foreach ($number_array as $phone){
            $phone_value = $phone['phone'];
            if ($count==0)
                $number_in = "dst LIKE '%$phone_value'";
            else
                $number_in .= " OR dst LIKE '%$phone_value' ";
            $count++;
        }
        $count = 0;
        foreach ($number_array as $phone){
            $phone_value = $phone['phone'];
            if ($count==0)
                $number_in_src = "src LIKE '%$phone_value'";
            else
                $number_in_src .= " OR src LIKE '%$phone_value' ";
            $count++;
        }

        //$number_in .= ")";
        // 1. Search in cdr table if any successfully outbound call to that number
        $query = "SELECT * FROM cdr WHERE calldate > '$after_datetime' AND calldate <= '$end_date'
          AND ($number_in) AND disposition = 'ANSWERED' ORDER BY calldate desc";
        //echo "<pre>";print_r($query,0);echo "</pre>";
        $result1 = $this->_cdrDB->getFirstRowQuery($query,true);
        // 2. Search in call_entry table if any successfully inbound call again from that number
        $query = "SELECT * FROM cdr WHERE calldate > '$after_datetime' AND calldate <= '$end_date'
          AND ($number_in_src) AND disposition = 'ANSWERED' ORDER BY calldate desc";
        $result2 = $this->_cdrDB->getFirstRowQuery($query,true);

        // compare calldate to identify the first call to/from this number
        if (count($result1)>0 && count($result2)>0) {
            if (strtotime($result1['calldate'] < $result2['calldate']))
                $result = $result1;
            else
                $result = $result2;
        }
        elseif (count($result1)>0)
            $result = $result1;
        else
            $result = $result2;

        if (count($result)>0){
            if ($filter_value=='0') {
                return null;}
            $result_detail = "Gọi lại lúc " . date('d-M-Y H:i:s',strtotime($result['calldate'])) . " bởi " .$result['src'];
            return "<label title='".$result_detail."' style='color:green'>Đã gọi lại</label>";
        }
        if ($filter_value=='1'){
            return null;}
        return "<label style='color:red'>Chưa gọi lại</label>";
    }

    private function SecToHHMMSS($sec)
    {
        $HH = 0;$MM = 0;$SS = 0;
        $segundos = $sec;

        if( $segundos/3600 >= 1 ){ $HH = (int)($segundos/3600);$segundos = $segundos%3600;} if($HH < 10) $HH = "0$HH";
        if(  $segundos/60 >= 1  ){ $MM = (int)($segundos/60);  $segundos = $segundos%60;  } if($MM < 10) $MM = "0$MM";
        $SS = $segundos; if($SS < 10) $SS = "0$SS";

        return "$HH:$MM:$SS";
    }

    function getTimeLastCallDestination($arrData, $callsid)
    {
        $exts = explode("-",$callsid);
        $src  = $exts[0];
        $dst  = $exts[1];
        $timeLimit = "";
        $calls = "$dst-$src";
        if(in_array("$dst-$src",$arrData)){
            foreach($arrData[$calls] as $key => $value){
                $calldate    = date('d-M-Y H:i:s',strtotime($value[0]));//calldate
                $src2        = trim(($value[1]!="")?$value[1]:_tr("UNKNOWN"));//src
                $dst2        = trim(($value[2]!="")?$value[2]:_tr("UNKNOWN"));//dst
                $lastapp     = trim(strtoupper($value[3]));//lastapp
                $billsec     = trim($value[4]);//billsec
                $disposition = trim(strtoupper($value[5]));//disposition
                if($lastapp === "DIAL" & $disposition == "ANSWERED" & $billsec > 0){
                    if($src == $dst2 && $dst == $src2){
                        $timeLimit = $calldate;
                        break;
                    }
                }
            }
        }
        return $timeLimit;
    }

    function getTimeToLastCall($time)
    {
        $anios    = "";
        $meses    = "";
        $dias     = "";
        $horas    = "";
        $minutos  = "";
        $segundos = "";
        $result   = "";
        $now = strtotime(date('Y-m-d H:i:s'));
        $time = $now - strtotime($time);
        if($time >= 31104000){//esta en años
            //convirtiendo segundos en años
            $anios    = ($time/31104000);
            //convirtiendo años decimales a meses
            $meses    = ($anios - floor($anios)) * 12;
            //convirtiendo meses decimales a dias
            $dias     = ($meses - floor($meses)) * 30;
            //convirtiendo dias decimales a horas
            $horas    = ($dias - floor($dias)) * 24;
            //convirtiendo horas decimales a minutos
            $minutos  = ($horas - floor($horas)) * 60;
            //convirtiendo minutos decimales a segundos
            $segundos = ($minutos - floor($minutos)) * 60;
            $result   = floor($anios)." "._tr("year(s)")." ".floor($meses)." "._tr("month(s)")." ".floor($dias)." "._tr("day(s)")." ".floor($horas)." "._tr("hour(s)")." ".floor($minutos)." "._tr("minute(s)")." ".floor($segundos)." "._tr("second(s)");
        }elseif($time < 31104000 && $time >= 2592000){//esta en meses
            //convirtiendo segundos a meses
            $meses    = ($time/2592000);
            //convirtiendo meses decimales a dias
            $dias     = ($meses - floor($meses)) * 30;
            //convirtiendo dias decimales a horas
            $horas    = ($dias - floor($dias)) * 24;
            //convirtiendo horas decimales a minutos
            $minutos  = ($horas - floor($horas)) * 60;
            //convirtiendo minutos decimales a segundos
            $segundos = ($minutos - floor($minutos)) * 60;
            $result   = floor($meses)." "._tr("month(s)")." ".floor($dias)." "._tr("day(s)")." ".floor($horas)." "._tr("hour(s)")." ".floor($minutos)." "._tr("minute(s)")." ".floor($segundos)." "._tr("second(s)");
        }elseif($time < 2592000 && $time >= 86400){//esta en dias
            //convirtiendo segundos a dias
            $dias     = ($time/86400);
            //convirtiendo dias decimales a horas
            $horas    = ($dias - floor($dias)) * 24;
            //convirtiendo horas decimales a minutos
            $minutos  = ($horas - floor($horas)) * 60;
            //convirtiendo minutos decimales a segundos
            $segundos = ($minutos - floor($minutos)) * 60;
            $result   = floor($dias)." "._tr("day(s)")." ".floor($horas)." "._tr("hour(s)")." ".floor($minutos)." "._tr("minute(s)")." ".floor($segundos)." "._tr("second(s)");
        }elseif($time < 86400 && $time >= 3600){//esta en horas
            //convirtiendo segundos a horas
            $horas    = ($time/3600);
            //convirtiendo horas decimales a minutos
            $minutos  = ($horas - floor($horas)) * 60;
            //convirtiendo minutos decimales a segundos
            $segundos = ($minutos - floor($minutos)) * 60;
            $result   = floor($horas)." "._tr("hour(s)")." ".floor($minutos)." "._tr("minute(s)")." ".floor($segundos)." "._tr("second(s)");
        }elseif($time < 3600 && $time >= 60){//esta en minutos
            //convirtiendo segundos a minutos
            $minutos  = ($time/60);
            //convirtiendo minutos decimales a segundos
            $segundos = ($minutos - floor($minutos)) * 60;
            $result   = floor($minutos)." "._tr("minute(s)")." ".floor($segundos)." "._tr("second(s)");
        }else{//esta en segundo
            $result   = floor($time)." "._tr("second(s)");
        }
        return $result;
    }

    function is_date($str)
    {
        $stamp = strtotime($str);
        if (!is_numeric($stamp))
            return FALSE;

        $month = date('m', $stamp);
        $day   = date('d', $stamp);
        $year  = date('Y', $stamp);
        if (checkdate($month, $day, $year))
            return TRUE;

        return FALSE;
    }

    function getDataByPagination($arrData, $limit, $offset)
    {
        $arrResult = array();
        $limitInferior = "";
        $limitSuperior = "";
        if($offset == 0){
            $limitInferior = $offset;
            $limitSuperior = $offset + $limit -1;
        }else{
            $limitInferior = $offset + 1;
            $limitSuperior = $offset + $limit + 1;
        }
        $cont = 0;
        foreach($arrData as $key => $value){
            if($key > $limitSuperior){
                $cont = 0;
                break;
            }
            if($key >= $limitInferior & $key <= $limitSuperior){
                $arrResult[]=$arrData[$key]; //echo $key."<br />";
            }

        }

        return $arrResult;
    }
}
?>
