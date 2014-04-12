<?php /* Smarty version 2.6.14, created on 2014-03-17 23:46:44
         compiled from _common/index.tpl */ ?>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF8" />
        <title>Hồng Ngọc Hà - Contact Center</title>
        <link rel="stylesheet" href="themes/<?php echo $this->_tpl_vars['THEMENAME']; ?>
/styles.css" />
        <link rel="stylesheet" href="themes/<?php echo $this->_tpl_vars['THEMENAME']; ?>
/help.css" />
		<link rel="stylesheet" media="screen" type="text/css" href="themes/<?php echo $this->_tpl_vars['THEMENAME']; ?>
/sticky_note.css" />
	<?php echo $this->_tpl_vars['HEADER_LIBS_JQUERY']; ?>

        <script src="libs/js/base.js"></script>
        <script src="libs/js/iframe.js"></script>
		<script type='text/javascript' src="libs/js/sticky_note.js"></script>
        <?php echo $this->_tpl_vars['HEADER']; ?>

	<?php echo $this->_tpl_vars['HEADER_MODULES']; ?>

    </head>
    <body leftmargin="0" topmargin="0" marginwidth="0" marginheight="0" <?php echo $this->_tpl_vars['BODYPARAMS']; ?>
>
        <?php echo $this->_tpl_vars['MENU']; ?>
 <!-- Viene del tpl menu.tlp-->
                <td align="left" valign="top">
                    <?php if (! empty ( $this->_tpl_vars['mb_message'] )): ?>
                        <!-- Message board -->
                        <table width="100%" border="0" cellspacing="0" cellpadding="0" align="center" class="message_board">
                            <tr>
                                <td valign="middle" class="mb_title">&nbsp;<?php echo $this->_tpl_vars['mb_title']; ?>
</td>
                            </tr>
                            <tr>
                                <td valign="middle" class="mb_message"><?php echo $this->_tpl_vars['mb_message']; ?>
</td>
                            </tr>
                        </table><br />
                        <!-- end of Message board -->
                    <?php endif; ?>
                    <table border="0" cellpadding="6" width="100%" >
			<tr class="moduleTitle">
			  <td width="35%" class="moduleTitle" valign="middle" colspan='2'>&nbsp;&nbsp;<?php if ($this->_tpl_vars['icon'] != null): ?><img src="<?php echo $this->_tpl_vars['icon']; ?>
" border="0" align="absmiddle">&nbsp;&nbsp;<?php endif;  echo $this->_tpl_vars['title']; ?>

                  &nbsp;&nbsp;
              </td>
			</tr>
                        <tr>
                            <td>
                            <?php echo $this->_tpl_vars['CONTENT']; ?>

                            </td>
                        </tr>
                    </table><br />
                    <div align="center" class="copyright">Hệ thống tổng đài dành cho đại lý bán vé Hồng Ngọc Hà </br> Cung cấp bởi <a href="http://cloudteam.vn" target='_blank'>Công ty CP Nhóm Mây</a> - <?php echo $this->_tpl_vars['currentyear']; ?>
.</div>
                    <br>
                </td>
            </tr>
        </table>
		<div id="neo-sticky-note" class="neo-display-none">
		  <div id="neo-sticky-note-text"></div>
		  <div id="neo-sticky-note-text-edit" class="neo-display-none">
			<textarea id="neo-sticky-note-textarea"></textarea>
			<div id="neo-sticky-note-text-char-count"></div>
			<input type="button" value="<?php echo $this->_tpl_vars['SAVE_NOTE']; ?>
" class="neo-submit-button" id="neo-submit-button" onclick="send_sticky_note()" />
			<div id="auto-popup">AutoPopUp <input type="checkbox" id="neo-sticky-note-auto-popup" value="1"></div>
		  </div>
		  <div id="neo-sticky-note-text-edit-delete"></div>
		</div>
		<!-- Neo Progress Bar -->
		<div class="neo-modal-elastix-popup-box">
			<div class="neo-modal-elastix-popup-title"></div>
			<div class="neo-modal-elastix-popup-close"></div>
			<div class="neo-modal-elastix-popup-content"></div>
		</div>
		<div class="neo-modal-elastix-popup-blockmask"></div>
    </body>
</html>