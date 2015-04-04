<?
require('inc/common.php');

$rubric = "Журнал";
$tbl = "log";

// ------------------- СОХРАНЕНИЕ ------------------------
if(isset($_GET["action"]))
{
	$id = (int)@$_GET['id'];
	
	switch($_GET['action'])
	{
		// ----------------- удаление одной записи
		case "del":
			update($tbl,"",$id);
			?><script>top.location.href = "<?=$script?>";</script><?
		break;
		// ----------------- удаление нескольких записей
		case "multidel":
			foreach($_POST['check_del_'] as $k=>$v)
				update($tbl,"",$k);
			?><script>top.location.href = "<?=$script?>";</script><?
		break;
	}
	exit;
}
// -----------------ПРОСМОТР-------------------
else
{
	$cur_page = $_SESSION['page'] ? $_SESSION['page'] : 1;
	
	$page_title .= " :: ".$rubric; 
	
	$razdel = array("Удалить"=>"javascript:multidel(document.red_frm,'check_del_','');");
	$subcontent = show_subcontent($razdel);
	
	$count_obj = getField("SELECT count(*) FROM {$prx}{$tbl}"); // кол-во объектов в базе
	$count_obj_on_page = set("count_{$tbl}_admin"); // кол-во объектов на странице
	$kol_str = ceil($count_obj/$count_obj_on_page); // количество страниц

	ob_start();
	// проверяем текущую сортировку
	// и формируем соответствующий запрос
	$query = "SELECT * FROM {$prx}{$tbl} ";
	if($_SESSION['sort']) 
	{
		$sort = explode(":",$_SESSION['sort']);
		$cur_pole = $sort[0];
		$cur_sort = $sort[1];

		$query .= "ORDER BY {$cur_pole} ";
		if($cur_sort=="up")
			$query .= "DESC ";
		else
			$query .= "ASC ";
	}
	else
		$query .= "ORDER BY date DESC ";
	
	$query .= "limit ".($count_obj_on_page*$cur_page-$count_obj_on_page).",".$count_obj_on_page;
	//-----------------------------
	//echo $query;
	
	show_filters($script);
	show_navigate_pages($kol_str,$cur_page,$script);
	
	?>
    <form action="?action=multidel" name="red_frm" method="post" target="ajax">
    <table width="100%" border="1" cellspacing="0" cellpadding="0" class="tab1">
      <tr>
        <th><input type="checkbox" name="check_del" id="check_del" /></th>
        <th>№</th>
        <th nowrap><?=ShowSortPole($script,$cur_pole,$cur_sort,'Дата','date')?></th>
        <th width="50%">Событие</th>
        <th width="50%">Ссылка</th>
        <th style="padding:0 30px;"></th>
      </tr>
    <?
	$res = mysql_query($query);
	if(@mysql_num_rows($res))
	{
		$i=1;
		while($row = mysql_fetch_array($res))
		{
			?>
			<tr>
              <th><input type="checkbox" name="check_del_[<?=$row['id']?>]" id="check_del_<?=$row['id']?>" /></th>
              <th nowrap><?=$i++?></th>
              <td nowrap align="center"><?=date('d.m.Y H:i:s',strtotime($row['date']))?></td>
              <td><?=$row['text']?></td>
              <td><a href="<?=$row['link']?>" style="color:#090"><?=$row['link']?></a></td>
              <td nowrap align="center">
                  <img src="img/del.png" width="16" height="16" alt="удалить" title="удалить запись" style="cursor:pointer;" onclick="if(confirm('Уверены?')) toajax('?action=del&id=<?=$row['id']?>')" />
              </td>
			</tr>
			<?
		}	
	}
	else
	{
		?>
        <tr>
          <td colspan="6" align="center">журнал пуст</td>
        </tr>
        <?
	}
	?>
    </table>
    </form>
    <?	
	$content = $subcontent.ob_get_clean();
}

require("tpl/tpl.php");
?>