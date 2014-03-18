<?php
function showStatus($str)
{
    switch ($str){
        case 'Mới':
            $color = '<b><span style="color:orange">'.$str.'</span></b>';
            break;
        case 'Đang giao':
            $color = '<b><span style="color:red">'.$str.'</span></b>';
            break;
        case 'Đã nhận tiền':
            $color = '<b><span style="color:green">'.$str.'</span></b>';
            break;
        case 'Chờ xử lý':
            $color = '<b><span style="color:blue">'.$str.'</span></b>';
            break;
        case 'Đã hủy':
            $color = '<b><span style="color:grey">'.$str.'</span></b>';
            break;
        default:
            break;
    }
    return $color;
}
?>