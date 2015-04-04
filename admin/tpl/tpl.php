<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
	<title><?=@$page_title?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<META NAME="keywords" CONTENT="<?=@$page_keywords?>">
	<META NAME="description" CONTENT="<?=@$page_description?>">
    
  <link rel="icon" href="favicon.ico" type="image/x-icon">
  <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
  
  <link rel="stylesheet" href="css/style.css" type="text/css">
  <link rel="stylesheet" href="css/popup.css" type="text/css">
  
  <script src="/js/jquery-1.4.4.min.js" type="text/javascript"></script>
  <link rel="stylesheet" href="css/ui/jquery-ui-1.8.1.custom.css" type="text/css">
  <script src="/js/jquery-ui-1.8.8.custom.min.js" type="text/javascript"></script>
  <script src="/js/jquery.ui.datepicker-ru.js" type="text/javascript"></script>
  
  <script src="/js/utils.js" type="text/javascript"></script>
  <script src="js/spec.js" type="text/javascript"></script>
  <script src="js/ready.js" type="text/javascript"></script>
  
  <link rel="stylesheet" href="/inc/advanced/tooltip/tooltip.css" type="text/css">
  <script src="/inc/advanced/tooltip/tooltip.js" type="text/javascript"></script>   
  
  <script src="/inc/advanced/highslide/highslide-with-gallery.js" type="text/javascript"></script>
  <link rel="stylesheet" href="/inc/advanced/highslide/highslide.css" type="text/css">
  
  
  <script src="js/imgareaselect/jquery.imgareaselect.pack.js" type="text/javascript"></script>   
  <link rel="stylesheet" href="js/imgareaselect/imgareaselect-default.css" type="text/css">
    
</head>
<body>

<div id="content">
<table width="100%" height="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td height="23" style="background-image:url(img/head.gif); background-repeat:repeat-x;">
    	<table width="100%" height="100%" border="0" cellspacing="0" cellpadding="0">
          <tr>
            <td nowrap style="padding-left:10px;color:#697079;" width="270"><b><a href="/" target="_blank" title="переход на сайт (откроется в новом окне)">запчасти-сто.рф</a> - Администрирование</b></td>
            <td nowrap align="center" style="color:#697079;font:normal 12px Tahoma, Geneva, sans-serif;" >
            	<?=show_hot_statistic()?>
            </td>
            <td nowrap width="45" align="right" style="padding-right:10px; background-color: #69F"><a href="login.php?action=vyhod" style="color:#FF0; text-decoration:none;">Выход</a></td>
          </tr>
        </table>
	</td>
  </tr>
  <tr>
    <td height="100%" valign="top">
    	<table width="100%" height="100%" border="0" cellspacing="0" cellpadding="0">
          <tr>
            <td width="200" bgcolor="#ecf0fb" style="padding:5px;" valign="top">
            	<table width="100%" border="0" cellspacing="0" cellpadding="5" style="border:1px solid #c9d3df; background-color:#dce9fa;">
                  <tr>
                    <td>
                    	<table width="100%" border="0" cellspacing="0" cellpadding="5" style="border:1px solid #c9d3df; background-color:#e0e9fa;">
                          <tr>
                            <td>
                            	<!-- навигация -->
                            	<?=ShowNavigate()?>
                            </td>
                          </tr>
                        </table>
                    </td>
                  </tr>
                </table>
            </td>
            <td bgcolor="#ecf0fb" style="padding:5px;" valign="top">
            	<!-- имя раздела -->
                <table width="100%" height="100%" border="0" cellspacing="0" cellpadding="5" style="border:1px solid #c9d3df; background-color:#dce9fa;">
                  <tr>
                    <td valign="top">
                        <table border="0" cellspacing="0" cellpadding="0" style="font-size:1px;">
                          <tr>
                          	<td width="10" height="30">
                            	<img src="img/rubric_left_corner.gif" border="0" width="10" height="30">
                            </td>
                            <td style="background-image:url(img/rubric_center.gif); background-repeat:repeat-x;">
                                <!-- рубрика -->
                                <span class="rubric"><?=$rubric?></span>
                            </td>
                            <td width="10">
                            	<img src="img/rubric_right_corner.gif" border="0" width="10" height="30">
                            </td>
                          </tr>
                        </table>
                        <!-- контент -->
                        <table width="100%" border="0" cellspacing="0" cellpadding="0" class="tab_content">
                          <tr>
                          	<th width="10" height="10" style="background-image:url(img/content_left.gif); background-repeat:repeat-y;">&nbsp;</th>
                            <th style="background-image:url(img/content_up.gif); background-repeat:repeat-x;">&nbsp;</th>
                            <th width="10">
                            	<img src="img/content_right_up.gif" width="10" height="10">
                            </th>
                          </tr>
                          <tr>
                          	<th style="background-image:url(img/content_left.gif); background-repeat:repeat-y;">&nbsp;</th>
                            <td style="background-color:#FFFFFF;"><?=$content?></td>
                            <th style="background-image:url(img/content_right.gif); background-repeat:repeat-y;">&nbsp;</th>
                          </tr>
                          <tr>
                          	<th height="10">
                            	<img src="img/content_left_bot.gif" width="10" height="10">
                            </th>
                            <th style="background-image:url(img/content_bot.gif); background-repeat:repeat-x;">&nbsp;</th>
                            <th>
                            	<img src="img/content_right_bot.gif" width="10" height="10">
                            </th>
                          </tr>
                        </table>
                    </td>
                  </tr>
                </table>
            </td>
          </tr>
        </table>
    </td>
  </tr>
  <tr>
    <td height="20" style="background-image:url(img/foot.gif); background-repeat:repeat-x; padding-right:10px;" align="right">
    </td>
  </tr>
</table>
</div>

<div id="loader"><img src="/admin/img/loader.gif" title="думаю"></div>
<iframe name="ajax" id="ajax"></iframe>

</body>
</html>