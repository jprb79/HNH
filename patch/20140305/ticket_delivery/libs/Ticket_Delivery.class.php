<?php

class Ticket_Delivery{
    var $_DB;
    var $errMsg;

    function Ticket_Delivery(&$pDB)
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

    function getNumTicket_Delivery($filter)
    {
        $where    = "";
        $arrParam = array();
        $index = 0;
        foreach ($filter as $filter_field=>$filter_value){
            if ($filter_value!="" && $filter_field!=='ticket_code') {
                if ($index==0)
                    $con = " where";
                else
                    $con = " and";
                if ($filter_field=='date_start')
                    $where .= " $con purchase_date >= '$filter_value'";
                elseif ($filter_field=='date_end')
                    $where .= " $con purchase_date < '$filter_value'";
                elseif ($filter_field=='status')
                    if ($filter_value=='Đã hủy')
                        $where .= " $con isActive = 0";
                    else
                        $where .= " $con status = '$filter_value' and isActive = 1";
                else
                    $where .= " $con $filter_field like ?";

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

    private function getOffice($user_id)
    {
        $query   = "SELECT a.office_name  FROM office a
                INNER JOIN user_office b ON a.id = b.office_id
                WHERE b.user_id = $user_id LIMIT 1";
        $result=$this->_DB->fetchTable($query, true);
        if(!$result){
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }
        if (count('result')==0)
            return '';
        return $result[0]['office_name'];
    }

    private function getTicketAttachment($ticket_id)
    {
        $query   = "SELECT filename, filepath from ticket_delivery_attachment where ticket_id=$ticket_id";
        $result=$this->_DB->fetchTable($query, true);
        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }
        return $result;
    }

    function getTicket_Delivery($limit, $offset, $filter, $user_id=null)
    {
        $where    = "";
        $arrParam = null;
        $index = 0;
        foreach ($filter as $filter_field=>$filter_value){
            if ($filter_value!="" && $filter_field!=='ticket_code') {
                if ($index==0)
                    $con = " where";
                else
                    $con = " and";
                if ($filter_field=='date_start')
                    $where .= " $con purchase_date >= '$filter_value'";
                elseif ($filter_field=='date_end')
                    $where .= " $con purchase_date < '$filter_value'";
                elseif ($filter_field=='status')
                    if ($filter_value=='Đã hủy')
                        $where .= " $con isActive = 0";
                    else
                        $where .= " $con status = '$filter_value' and isActive = 1";
                else
                    $where .= " $con $filter_field like ?";

                if ($filter_field!='date_start' && $filter_field!='date_end')
                    $arrParam[] = "%$filter_value%";
                $index++;
            }
        }

        $office_user = $this->getOffice($user_id);

        if (!is_null($user_id))
            if (trim($where)!='')
                $where .= " and office like '%$office_user%'";
            else
                $where = " WHERE office like '%$office_user%'";

        $query   = "SELECT a.*,b.name as agent_name from ticket_delivery a inner join agent b
                    on a.agent_id = b.id
                    $where
                    order by a.id desc
                    LIMIT $limit OFFSET $offset";
        //var_dump($query);
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
            // get ticket attachment
            $result[$index]['ticket_attachment'] = $this->getTicketAttachment($row['id']);
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

    function getTicket_DeliveryById($id)
    {
        $query   = "SELECT a.*,b.name as agent_name from ticket_delivery a inner join agent b
                    on a.agent_id = b.id
                    WHERE a.id = $id";

        $result=$this->_DB->fetchTable($query, true);

        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }
        $index = 0;
        $row = $result[0];
        $result[$index]['ticket_code'] = $this->getTicket_Code($row['id']);
        if (!is_null($row['user_id']))
            $result[$index]['delivery_name'] = $this->getDeliveryName($row['user_id']);
        else
            $result[$index]['delivery_name'] = '(Chưa phân công)';
        // get ticket attachment
        $result[$index]['ticket_attachment'] = $this->getTicketAttachment($row['id']);

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
        $data[0] = '(Chọn nhân viên)';
        foreach ($result as $row)
            $data[$row['id']] = $row['name'];
        return $data;
    }

    function getDeliveryNameByTicket($ticket_id)
    {
        $query = "SELECT b.name from ticket_delivery a left join
                    ticket_delivery_user b on a.user_id = b.id
                    WHERE a.id = $ticket_id";
        $result=$this->_DB->fetchTable($query, true);
        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return null;
        }
        return $result[0]['name'];
    }

    function assignDelivery($ticket_id,$user_id,$note='')
    {
        $delivery_man_old = $this->getDeliveryNameByTicket($ticket_id);
        if ($user_id=='0') {
            $queryUpdate = "update ticket_delivery
                        set status = 'Mới', delivery_date = null,
                        user_id=null
                        where id=$ticket_id";
            $result = $this->_DB->genQuery($queryUpdate);
        }else {
            $queryUpdate = "update ticket_delivery
                        set status = 'Đang giao', delivery_date = now(),
                        user_id=?
                        where id=?";
            $data = array($user_id, $ticket_id);
            $result = $this->_DB->genQuery($queryUpdate, $data);
        }
        if (!$result){
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }
        // log
        $elastix_user = $_SESSION['elastix_user'];
        $delivery_man = $this->getDeliveryName($user_id);
        if ($user_id=='0')
            $remark = $elastix_user.": hủy phân công từ nhân viên ".$delivery_man_old;
        else
            $remark = $elastix_user.": phân công cho nhân viên ".$delivery_man;
        $this->_DB->genQuery("INSERT INTO ticket_delivery_log(ticket_id, remark, date_log,note)
                VALUES($ticket_id,'$remark',now(),'$note')");
        return true;
    }

    function Cash_Collection($ticket_id,$elastix_user_id,$unpaid=false,$note='')
    {
        $elastix_user = $_SESSION['elastix_user'];
        $delivery_man = $this->getDeliveryNameByTicket($ticket_id);
        if (!$unpaid) {
            $queryUpdate = "update ticket_delivery
                        set status = 'Đã nhận tiền', collection_date = now(),
                        accounting_id = $elastix_user_id
                        where id=?";
            $remark = $elastix_user.": Nhận tiền từ nhân viên ".$delivery_man;
        }
        else {
            $queryUpdate = "update ticket_delivery
                          set status = 'Đang giao', collection_date = null,
                          accounting_id=null
                          where id=?";
            $remark = $elastix_user.": Hủy nhận tiền từ nhân viên ".$delivery_man;
        }
        $data = array($ticket_id);
        $result = $this->_DB->genQuery($queryUpdate, $data);

        if (!$result){
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }
        //log
        $this->_DB->genQuery("INSERT INTO ticket_delivery_log(ticket_id, remark, date_log,note)
                    VALUES($ticket_id,'$remark',now(),'$note')");
        return true;
    }

    function getTicketLog($ticket_id)
    {
        // get log detail
        $query = "SELECT date_log, remark, note from ticket_delivery_log WHERE ticket_id=$ticket_id order by date_log";
        $result=$this->_DB->fetchTable($query, true);
        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }
        return $result;
    }

    function TicketExpand($ticket_id)
    {
        $data = array();
        // get price detail
        $res = $this->getTicket_DeliveryById($ticket_id);
        $data['price_detail']['price'] = $res[0]['price'];
        $data['price_detail']['tax'] = $res[0]['tax'];
        $data['price_detail']['discount'] = $res[0]['discount'];
        $data['price_detail']['currency_rate'] = $res[0]['currency_rate'];
        $data['price_detail']['pay_amount'] = $res[0]['pay_amount'];
		// isinvoice checked
		$data['isInvoice'] = $res[0]['isInvoice'];
        // get log detail
        $query = "SELECT date_log, remark, note from ticket_delivery_log WHERE ticket_id=$ticket_id order by date_log";
        $result=$this->_DB->fetchTable($query, true);
        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }
        $data['log'] = $result;
        return $data;
    }

    function TicketProcess($ticket_id,$note='',$type)
    {
        $elastix_user = $_SESSION['elastix_user'];
        $delivery_man = $this->getDeliveryNameByTicket($ticket_id);

        switch ($type) {
            case 'return':
                $queryUpdate = "update ticket_delivery
                          set status = 'Chờ xử lý', delivery_date = null,
                          user_id=null
                          where id=?";
                $remark = $elastix_user.": Trả lại vé giao từ nhân viên ".$delivery_man;
                break;
            case 'enable':
                $queryUpdate = "update ticket_delivery
                          set isActive = 1 where id=?";
                $remark = $elastix_user.": Tạo lại yêu cầu giao vé";
                break;
            case 'disable':
                $queryUpdate = "update ticket_delivery
                          set isActive = 0 where id=?";
                $remark = $elastix_user.": Hủy yêu cầu giao vé";
                break;
            default:
                break;
        }

        $data = array($ticket_id);
        $result = $this->_DB->genQuery($queryUpdate, $data);

        if (!$result){
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }
        //log
        $this->_DB->genQuery("INSERT INTO ticket_delivery_log(ticket_id, remark, date_log,note)
                    VALUES($ticket_id,'$remark',now(),'$note')");
        return true;
    }
}