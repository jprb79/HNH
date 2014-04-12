<?php

class Cash_Collection{
    var $_DB;
    var $errMsg;

    function Cash_Collection(&$pDB)
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

    /*HERE YOUR FUNCTIONS*/

    function getNumCash_Collection($filter)
    {
        $where    = "";
        $arrParam = array();
        $index = 0;
        foreach ($filter as $filter_field=>$filter_value){
            if ($filter_value!="" && $filter_field!=='ticket_code') {
                if ($index==0){
                    if ($filter_field=='date_start')
                        $where .= " where purchase_date >= '$filter_value'";
                    elseif ($filter_field=='date_end')
                        $where .= " where purchase_date < '$filter_value'";
                    else
                        $where .= " where $filter_field like ?";
                }
                else{
                    if ($filter_field=='date_start')
                        $where .= " and purchase_date > '$filter_value'";
                    elseif ($filter_field=='date_end')
                        $where .= " and purchase_date < '$filter_value'";
                    else
                        $where    .= " and $filter_field like ?";
                }
                if ($filter_field!='date_start' && $filter_field!='date_end')
                    $arrParam[] = "%$filter_value%";
                $index++;
            }
        }

        $query   = "SELECT COUNT(*) from ticket_delivery $where";
        $result=$this->_DB->getFirstRowQuery($query, false, $arrParam);

        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return 0;
        }
        // find ticket code
        if ($result[0]>0 && $filter['ticket_code']!=""){
            $filter_ticket = $filter['ticket_code'];
            $ticket_query = "SELECT count(*) from ticket_delivery a inner join ticket_delivery_code b
                              on a.id = b.ticket_id
                              $where and b.ticket_code like '%$filter_ticket%'";
            $ticket_count = $this->_DB->getFirstRowQuery($ticket_query, false, $arrParam);

            if($ticket_count==FALSE){
                $this->errMsg = $this->_DB->errMsg;
                return 0;
            }
            return $ticket_count[0];
        }

        return $result[0];
    }
    private function getDeliveryName($user_id)
    {
        $query = "SELECT name from ticket_delivery_user WHERE id=?";
        $result=$this->_DB->getFirstRowQuery($query, true, array("$user_id"));
        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return null;
        }
        return $result['name'];
    }
    private function getTicket_Code($ticket_id)
    {
        $query   = "SELECT ticket_code from ticket_delivery_code where ticket_id=$ticket_id";
        $result=$this->_DB->fetchTable($query, true);
        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }
        foreach ($result as $row)
            $data[] = $row['ticket_code'];
        return $data;
    }

    function getCash_Collection($limit, $offset, $filter)
    {
        $where    = "";
        $arrParam = null;
        $index = 0;
        foreach ($filter as $filter_field=>$filter_value){
            if ($filter_value!="" && $filter_field!=='ticket_code') {
                if ($index==0){
                    if ($filter_field=='date_start')
                        $where .= " where purchase_date >= '$filter_value'";
                    elseif ($filter_field=='date_end')
                        $where .= " where purchase_date < '$filter_value'";
                    else
                        $where .= " where $filter_field like ?";
                }
                else{
                    if ($filter_field=='date_start')
                        $where .= " and purchase_date > '$filter_value'";
                    elseif ($filter_field=='date_end')
                        $where .= " and purchase_date < '$filter_value'";
                    else
                        $where    .= " and $filter_field like ?";
                }
                if ($filter_field!='date_start' && $filter_field!='date_end')
                    $arrParam[] = "%$filter_value%";
                $index++;
            }
        }

        $query   = "SELECT a.*,b.name as agent_name from ticket_delivery a inner join agent b
                    on a.agent_id = b.id
                    $where
                    order by a.purchase_date desc
                    LIMIT $limit OFFSET $offset
                    ";

        $result=$this->_DB->fetchTable($query, true, $arrParam);

        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }
        $index = 0;
        foreach ($result as $row) {
            $result[$index]['ticket_code'] = $this->getTicket_Code($row['id']);
            if (!is_null($row['user_id']))
                $result[$index]['delivery_name'] = $this->getDeliveryName($row['user_id']);
            else
                $result[$index]['delivery_name'] = '(Chưa phân công)';
            $index++;
        }
        // search ticket code
        if ($index>0 && $filter['ticket_code']!=""){
            $temp = array();
            foreach ($result as $row) {
                if (in_array($filter['ticket_code'],$row['ticket_code']))
                    $temp[] = $row;
            }
            if (count($temp) > 0)
                return $temp;
        }
        return $result;
    }

    function getCash_CollectionById($id)
    {
        $query = "SELECT * from ticket_delivery WHERE id=?";

        $result=$this->_DB->getFirstRowQuery($query, true, array("$id"));

        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return null;
        }
        return $result;
    }

    function getDeliveryMan()
    {
        $query = "SELECT * from ticket_delivery_user WHERE isActive=1 and isDeliveryMan=1";
        $result=$this->_DB->fetchTable($query, true);
        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return null;
        }
        $data = array();
        foreach ($result as $row)
            $data[$row['id']] = $row['name'];
        return $data;
    }

    function assign_CashCollection($ticket_id,$elastix_user_id,$paid=true)
    {
        if ($paid)
            $queryUpdate = "update ticket_delivery
                        set status = 'Đã nhận tiền', collection_date = now(),
                        accounting_id = $elastix_user_id
                        where id=?";
        else
            $queryUpdate = "update ticket_delivery
                          set status = 'Đang giao', collection_date = null,
                          where id=?";
        $data = array($ticket_id);
        $result = $this->_DB->genQuery($queryUpdate, $data);
        var_dump($queryUpdate);
        if (!$result){
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }
        return true;
    }
}
