<html>
<head>
<title>Contact Center - {$PAGE_NAME}</title>
<!--<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">-->
<link rel="stylesheet" href="themes/{$THEMENAME}/styles.css">
</head>

<body bgcolor="#ffffff" leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">
  <table cellspacing=0 cellpadding=0 width="100%" border=0>
    <tr>
      <td>
        <table cellSpacing="0" cellPadding="0" width="100%" border="0">
          <tr>
            <td class="menulogo" width=380><img src="images/logo.png" /></td> 
          </tr>
        </table>
      </td>
    </tr>
  </table>
<form method="POST">
<p>&nbsp;</p>
<p>&nbsp;</p>
<table width="400" border="0" cellspacing="0" cellpadding="0" align="center">
  <tr>
    <td width="498" class="menudescription">
      <table width="100%" border="0" cellspacing="0" cellpadding="4" align="center">
        <tr>
          <td>
              <div align="left"><font color="#ffffff">&nbsp;&raquo;&nbsp; Hồng Ngọc Hà Contact Center </font></div>
          </td>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td width="498" bgcolor="#ffffff">
      <table width="100%" border="0" cellspacing="0" cellpadding="8" class="tabForm">
        <tr>
          <td colspan="2">
            <div align="center">Đăng nhập hệ thống<br><br></div>
          </td>
        </tr>
        <tr>
          <td>
              <div align="right">Tài khoản:</div>
          </td>
          <td>
            <input type="text" id="input_user" name="input_user" style="color:#000000; FONT-FAMILY: verdana, arial, helvetica, sans-serif; FONT-SIZE: 8pt;
             font-weight: none; text-decoration: none; background: #fbfeff; border: 1 solid #000000;">
          </td>
        </tr>
        <tr>
          <td>
              <div align="right">Mật khẩu:</div>
          </td>
          <td>
            <input type="password" name="input_pass" style="color:#000000; FONT-FAMILY: verdana, arial, helvetica, sans-serif; FONT-SIZE: 8pt;
             font-weight: none; text-decoration: none; background: #fbfeff; border: 1 solid #000000;">
          </td>
        </tr>
        <tr>
          <td colspan="2" align="center">
            <input type="submit" name="submit_login" value="{$SUBMIT}" class="button">
          </td>
        </tr>
        <tr>
            <td colspan="2"><img src="themes/{$THEMENAME}/images/0.gif" width="1" height="5"></td>
        </tr>
      </table>
    </td>
  </tr>
</table>
</form>
<br>
<div align="center" class="copyright">Hệ thống tổng đài dành cho đại lý bán vé Hồng Ngọc Hà </br> Cung cấp bởi <a href="http://cloundteam.vn" target='_blank'>Công ty CP Nhóm Mây</a> - {$currentyear}.</div>
<br>
<script type="text/javascript">
    document.getElementById("input_user").focus();
</script>
</body>
</html>