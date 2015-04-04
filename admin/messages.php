<?
require('inc/common.php');

$rubric = 'Сообщения';
$tbl = 'messages';

// ------------------- СОХРАНЕНИЕ ------------------------
if(isset($_GET["action"]))
{
	$id = (int)$_GET['id'];
	
	switch($_GET['action'])
	{
		// ----------------- сохранение
		case "save":
			
			if(!$id) exit;
			
			foreach($_POST as $key=>$val)
				$$key = clean($val);
			
			update($tbl,"note='{$note}'",$id);
			
			?><script>top.location.href = "<?=$script?>?id=<?=$id?>";</script><?			
			break;
		// ----------------- удаление одной записи
		case "del":
			update($tbl,'',$id);
			?><script>top.location.href = "<?=$script?>";</script><?
			break;
		// ----------------- удаление нескольких записей
		case "multidel":
			foreach($_POST['check_del_'] as $k=>$v)
				update($tbl,'',$k);			
			?><script>top.location.href = "<?=$script?>";</script><?
			break;
	}
	exit();
}
// ------------------ РЕДАКТИРОВАНИЕ --------------------
elseif(isset($_GET["red"]))
{
	$id = (int)$_GET['red'];
	
	$row = getRow("SELECT * FROM {$prx}{$tbl} WHERE id={$id}");
	if(!$row)
	{
		header("Location: {$script}");
		exit;
	}
	
	$rubric .= " &raquo; Просмотр";
	$page_title .= " :: ".$rubric;
	
	
	ob_start();
	?>
	<style type="text/css">
	table.tab1 th
	{
		text-align:left;
		vertical-align:top;
	}
	table.tab1 td
	{
		width:100%;
	}
	</style>
	<form action="?action=save&id=<?=$id?>" method="post" target="ajax">
	<table width="100%" border="0" cellspacing="0" cellpadding="5" class="tab1">
	  <tr>
		<th>Номер</th>
		<td><b><?=$id?></b></td>
	  </tr>
	  <tr>
		<th>Дата</th>
		<td><?=date("d.m.Y, H:i:s", strtotime($row['date']))?></td>
	  </tr>
	  <tr>
		<th>ФИО</th>
		<td><?=$row['fio']?></td>
	  </tr>
	  <tr>
		<th>E-mail</th>
		<td><?=$row['mail']?></td>
	  </tr>
	  <tr>
		<th>Телефон</th>
		<td><?=$row['phone']?></td>
	  </tr>
	  <tr>
		<th>Тема</th>
		<td><?=$row['tema']?></td>
	  </tr>
	  <tr>
		<th>Сообщение</th>
		<td><?=break_to_str($row['text'])?></td>
	  </tr>
	  <tr>
		<th>Примечание</th>
		<td><?=show_pole('textarea','note',$row['note'])?></td>
	  </tr>
	  <tr>
		<th></th>
		<th align="center">
			<input type="submit" value="Сохранить" class="but1" />&nbsp;
			<input type="button" value="Отмена" class="but1" onclick="top.location.href='<?=$script?>'" />
		</th>
	  </tr>
	</table>
	</form>
	<?
	$content = ob_get_clean();
}
// -----------------ПРОСМОТР-------------------
else
{
	$cur_page = $_SESSION['page'] ? $_SESSION['page'] : 1;
	
	$page_title .= " :: ".$rubric; 
	
	$razdel = array("Удалить"=>"javascript:multidel(document.red_frm,'check_del_','?action=multidel');");
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
    <input type="hidden" id="cur_id" value="<?=@$_GET['id']?@(int)$_GET['id']:""?>" />
    <table width="100%" border="1" cellspacing="0" cellpadding="0" class="tab1">
      <tr>
        <th><input type="checkbox" name="check_del" id="check_del" /></th>
        <th>№</th>       
        <th width="25%"><?=ShowSortPole($script,$cur_pole,$cur_sort,'ФИО','fio');?></th>
        <th width="25%"><?=ShowSortPole($script,$cur_pole,$cur_sort,'Тема','tema');?></th>
        <th width="25%"><?=ShowSortPole($script,$cur_pole,$cur_sort,'E-mail','mail');?></th>
	  	<th width="10%"><?=ShowSortPole($script,$cur_pole,$cur_sort,'Телефон','phone');?></th>
	 	<th width="25%"><?=ShowSortPole($script,$cur_pole,$cur_sort,'Дата','date');?></th>
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
            <tr id="<?=$row['id']?>">
			<th><input type="checkbox" name="check_del_[<?=$row['id']?>]" id="check_del_<?=$row['id']?>" /></th>
			<th><?=$i++?></th>
			<td><?=$row["fio"]?></td>
            <td><?=$row["tema"]?></td>
            <td><?=$row["mail"]?></td>
			<td align="center">
				<? if(!$row["phone"]) {
					echo "-";
				}
				else {
					echo $row["phone"];
				}
				?>
			</td>
			<td align="center"><?=date('d.m.Y H:i:s',strtotime($row['date']))?></td>
			<td nowrap align="center"><?=btn_edit($row['id'])?></td>
			</tr>
			<?
		}		  	
	}
	else
	{
		?>
        <tr>
          <td colspan="7" align="center">
          по вашему запросу ничего не найдено.<?=help('нет ни одной записи отвечающей критериям вашего запроса, возможно вы установили неверные фильтры')?>
          </td>
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