<?
require('inc/common.php');

$rubric = "YML";
$page_title = "Администрирование :: ".$rubric;

// ----------------------------------- ПРОСМОТР --------------------------------
ob_start();
?>
<table width="100%" border="0" cellspacing="0" cellpadding="10">
  <tr>
    <td valign="top">
		<a href="/ya/goods.php?out" target="_blank">формирование нового xml файла</a>
		<?
		if(file_exists($_SERVER['DOCUMENT_ROOT']."/ya/goods.xml"))
		{
			?><br><br><a href="/ya/goods.xml" target="_blank">прямая ссылка на файл</a><?
		}
		?>
    </td>
  </tr>
</table>
<?
$content = ob_get_clean();

require("tpl/tpl.php");
?>