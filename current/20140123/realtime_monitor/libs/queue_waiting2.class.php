<?php
class queue_waiting{
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

    private function runSQL($sql)
    {
        try{
            $result = mysql_query($sql);
            if (!$result){
                $this->errMsg = mysql_error($this->con);
                return false;
            }
            $index = 0;
            $data = array();
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

    public function showQueue()
    {
        $sqlQueue = "SELECT a.callerid as phone_number,
                    CAST((now() - a.datetime_entry_queue) AS UNSIGNED)  as wait_time,
                    b.queue
                    FROM call_center.call_entry a
                    LEFT JOIN queue_call_entry b ON a.id_queue_call_entry = b.id
                    where status = 'en-cola'
                    order by datetime_entry_queue desc
                    limit 30";
        $arrData=$this->runSQL($sqlQueue);
        return $arrData;
    }
}
?>