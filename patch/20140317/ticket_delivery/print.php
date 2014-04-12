<?php
include_once "libs/Ticket_Delivery.class.php";
include_once "../../libs/paloSantoDB.class.php";

function html_ticket($ticket_id,$pDB)
{
    $pTicket_Delivery = new Ticket_Delivery($pDB);
    $arrResult =$pTicket_Delivery->getTicket_DeliveryById($ticket_id);
    $count = 0;
    $ticket = '';
    foreach ($arrResult[0]['ticket_code'] as $code){
        if ($count == count($arrResult[0]['ticket_code'])-1)
            $ticket .= $code;
        else
            $ticket .= $code . '<br/>';
        $count++;
    }

    $html = '<p><table>
    <tr>
        <td width="20%"><img src="images/logo.png" alt="test alt attribute" border="0" /></td>
        <td width="65%">
            <b>Công ty TNHH XD-TM-DL Hồng Ngọc Hà</b><br/>&nbsp;
            CN1 : 178 Lê Thánh Tôn, Q1, TP HCM -  Hotline: (08)38 273 880<br/>&nbsp;
            CN2: 268 Cô Bắc Q1, TP HCM - Hotline: (08) 38 365 318
        </td>
        <td width="15%">
            STT: <b>' . $ticket_id . '</b>
        </td>
    </tr>
    </table>
    <br/>
    <h2 style="text-align:center;">PHIẾU GIAO VÉ KIÊM PHIẾU THU</h2>
    <table cellpadding="5">
    <tbody>
        <tr>
            <td width="18%"><b>Khách hàng:</b></td>
            <td width="22%">'.$arrResult[0]['customer_name'].'</td>
            <td width="20%"><b>Địa chỉ:</b></td>
            <td width="50%">'.$arrResult[0]['deliver_address'].'</td>
        </tr>
        <tr>
            <td><b>Số điện thoại:</b></td>
            <td>'.$arrResult[0]['customer_phone'].'</td>
            <td><b>Nhân viên:</b></td>
            <td>'.$arrResult[0]['agent_name'].'</td>
        </tr>
        <tr>
            <td><b>Mã vé:</b></td>
            <td>'.$ticket.'</td>
            <td><b>Ngày xuất vé:</b></td>
            <td>'.date("d/m/Y",strtotime($arrResult[0]['purchase_date'])).'</td>
        </tr>
        <tr>
            <td><b>Giá vé:</b></td>
            <td>'.$arrResult[0]['price'].'</td>
            <td><b>Thuế:</b></td>
            <td>'.$arrResult[0]['tax'].'</td>
        </tr>
        <tr>
            <td><b>Tỉ giá:</b></td>
            <td>'.$arrResult[0]['currency_rate'].'</td>
        </tr>
        <tr>
            <td><b>Tổng cộng:</b></td>
            <td>'.$arrResult[0]['pay_amount'].'</td>
        </tr>
    </tbody></table><br/>
    <br/>
    <table>
        <tbody>
        <tr>
            <td align="center">
                Người giao
            </td>
            <td align="center">
                Người nhận
            </td>
        </tr>
        </tbody>
    </table>
    <br/><br/><br/><br/><br/><br/>
    <hr></p>
	<br/>
    <p><table>
    <tr>
        <td width="20%"><img src="images/logo1.png" alt="test alt attribute" border="0" /></td>
        <td width="65%">
            <b>Công ty TNHH XD-TM-DL Hồng Ngọc Hà</b><br/>&nbsp;
            CN1 : 178 Lê Thánh Tôn, Q1, TP HCM -  Hotline: (08)38 273 880<br/>&nbsp;
            CN2: 268 Cô Bắc Q1, TP HCM - Hotline: (08) 38 365 318
        </td>
        <td width="15%">
            STT: <b>' . $ticket_id . '</b>
        </td>
    </tr>
    </table>
    <br/>
    <h2 style="text-align:center;">PHIẾU GIAO VÉ KIÊM PHIẾU THU</h2>
    <table cellpadding="5">
    <tbody>
        <tr>
            <td width="18%"><b>Khách hàng:</b></td>
            <td width="22%">'.$arrResult[0]['customer_name'].'</td>
            <td width="20%"><b>Địa chỉ:</b></td>
            <td width="50%">'.$arrResult[0]['deliver_address'].'</td>
        </tr>
        <tr>
            <td><b>Số điện thoại:</b></td>
            <td>'.$arrResult[0]['customer_phone'].'</td>
            <td><b>Nhân viên:</b></td>
            <td>'.$arrResult[0]['agent_name'].'</td>
        </tr>
        <tr>
            <td><b>Mã vé:</b></td>
            <td>'.$ticket.'</td>
            <td><b>Ngày đặt vé:</b></td>
            <td>'.date("d/m/Y",strtotime($arrResult[0]['purchase_date'])).'</td>
        </tr>
        <tr>
            <td><b>Giá vé:</b></td>
            <td>'.$arrResult[0]['price'].'</td>
            <td><b>Thuế:</b></td>
            <td>'.$arrResult[0]['tax'].'</td>
        </tr>
        <tr>
            <td><b>Tỉ giá:</b></td>
            <td>'.$arrResult[0]['currency_rate'].'</td>
        </tr>
        <tr>
            <td><b>Tổng cộng:</b></td>
            <td>'.$arrResult[0]['pay_amount'].'</td>
        </tr>
    </tbody></table><br/>
    <br/>
    <table>
        <tbody>
        <tr>
            <td align="center">
                Người giao
            </td>
            <td align="center">
                Người nhận
            </td>
        </tr>
        </tbody>
    </table>
    <br/><br/><br/><br/><br/>
    </p>';
    return $html;
}

function array_fill_keys($target, $value = '') {
    if(is_array($target)) {
        foreach($target as $key => $val) {
            $filledArray[$val] = is_array($value) ? $value[$key] : $value;
        }
    }
    return $filledArray;
}

global $arrConf;
$arrConf['elastix_dbdir'] = '/var/www/db';
$arrConf['elastix_dsn'] = array(
    "acl"       =>  "sqlite3:///$arrConf[elastix_dbdir]/acl.db",
    "settings"  =>  "sqlite3:///$arrConf[elastix_dbdir]/settings.db",
    "menu"      =>  "sqlite3:///$arrConf[elastix_dbdir]/menu.db",
    "samples"   =>  "sqlite3:///$arrConf[elastix_dbdir]/samples.db",
);
$arrConf['basePath'] = '/var/www/html';
$arrConf['theme'] = 'default'; //theme personal para los modulos esencialmente
// Verifico si las bases del framework están, debido a la migración de dichas bases como archivos .db a archivos .sql
$arrConf['cadena_dsn'] = "mysql://asterisk:asterisk@localhost/call_center";

$pDB = new paloDB($arrConf['cadena_dsn']);
global $arrConf;
$sTicketId = $_REQUEST['ticket_id'];
//$sTicketId2 = $_REQUEST['ticket_id2'];
//$sTicketId3 = $_REQUEST['ticket_id3'];
// generate html for each ticket
$html = html_ticket($sTicketId,$pDB);
//if (!is_null($sTicketId2))
//    $html .= '<br/><br/>'.html_ticket($sTicketId2,'logo1.png',$pDB);
//if (!is_null($sTicketId3))
//   $html .= '<br/><br/>'.html_ticket($sTicketId3,'logo2.png',$pDB);
require 'libs/tcpdf/tcpdf.php';
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
// set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor(PDF_AUTHOR);
$pdf->SetTitle('Hồng Ngọc Hà');
$pdf->SetSubject('Phiếu giao vé');
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
$pdf->SetKeyWords('hongngocha, vemaybay, hong ngoc ha, ve may bay, ve, may bay');
// set margins
$pdf->SetMargins(PDF_MARGIN_LEFT, 7, PDF_MARGIN_RIGHT);
// set font
$pdf->SetFont('dejavusans', '', 10);

$filename = "giaove_id_$sTicketId".(is_null($sTicketId2)?"":"-$sTicketId2").(is_null($sTicketId3)?"":"-$sTicketId3").".pdf";
// add a page
$pdf->AddPage();;
$pdf->writeHTML($html, true, false, true, false, '');
$pdf->lastPage();
$pdf->Output($filename, 'I');
?>