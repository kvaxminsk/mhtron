<?
require('inc/common.php');

$rubric = "Статистика";
$page_title = "Администрирование :: ".$rubric;

// ----------------------------------- СОХРАНЕНИЕ --------------------------------
if(isset($_GET["action"]))
{
	switch($_GET['action'])
	{		
		case "del":
			sql("TRUNCATE TABLE {$prx}users_visit");			
			?><script>top.location.href='statistics.php';</script><?
			break;
	}
	exit;
}

// ----------------------------------- ПРОСМОТР --------------------------------
ob_start();
?>
<table width="100%" border="0" cellspacing="0" cellpadding="10">
  <tr>
    <td colspan="2">
			<?
				include_once("../inc/advanced/open-flash-chart/open_flash_chart_object.php");
				ob_start();
				?>
        <div style="color:#697079;font:normal 14px Tahoma, Geneva, sans-serif; margin-bottom:10px;" align="left">
					<? $date_start = getField("SELECT MIN(date) FROM {$prx}users_visit"); ?>
          <a href="visit.php" style="font-size:14px">Статистика посещения сайта</a> ( доступна c <?=date("d.m.Y", $date_start ? strtotime($date_start) : time())?> )
        </div>
				<?
				open_flash_chart_object("100%", 250, "visit.php?action=getdata", false, "../inc/advanced/open-flash-chart/");
				echo stat_around(ob_get_clean());
			?>
    </td>
  </tr>
  <tr>
    <td width="50%" valign="top"><?=stat_around(show_stat_count())?></td>
    <td valign="top"><?=stat_around(show_stat_order())?></td>
  </tr>
</table>
<?
$content = ob_get_clean();

require("tpl/tpl.php");
?>