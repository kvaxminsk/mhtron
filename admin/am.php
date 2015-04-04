<?
require('inc/common.php');

$rubric = "Настройка меню";
$tbl = "am";

// ------------------- СОХРАНЕНИЕ ------------------------
if(isset($_GET["action"]))
{
	$id = @$_GET['id'] ? (int)@$_GET['id'] : 0;
	
	switch(@$_GET['action'])
	{
		// ----------------- сохранение
		case "save":
		
			foreach($_POST as $key=>$value)
				$$key = clean($value);
			
			$id = update($tbl,"name='{$name}',id_parent='{$id_parent}',link='{$link}',pic='{$pic}'",$id);
			
			?><script>top.location.href = "<?=$script?>?id=<?=$id?>";</script><?			
		break;
		// ----------------- сортировка вверх
		case "moveup":
			sort_moveup($tbl,$id);
			?><script>top.location.href = "<?=$script?>?id=<?=$id?>";</script><?
			break;
		// ----------------- сортировка вниз
		case "movedown":
			sort_movedown($tbl,$id);
			?><script>top.location.href = "<?=$script?>?id=<?=$id?>";</script><?
			break;
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
// ------------------ РЕДАКТИРОВАНИЕ --------------------
elseif(isset($_GET["red"]))
{
	$id = @$_GET['red'] ? (int)@$_GET['red'] : 0;
	
	$rubric .= " &raquo; ".($id ? "Редактирование" : "Добавление");
	$page_title .= " :: ".$rubric;
	
	$row = getRow("SELECT * FROM {$prx}{$tbl} WHERE id={$id}");
	
	ob_start();
	?>
    <form id="edit_frm" action="?action=save&id=<?=$id?>" method="post" target="ajax">
    <input type="hidden" name="old_date" value="<?=date("d.m.Y",strtotime($row['date']))?>">
    <table width="100%" border="0" cellspacing="0" cellpadding="5" class="tab_red">
      <tr>
      	<th class="tab_red_th"></th>
        <th>Подчинение</th>
        <td><?=dllTree("SELECT id,name FROM {$prx}{$tbl} WHERE id_parent='%s' ORDER BY sort,id", 'name="id_parent" style="width:100%"', $row['id_parent'], array('0','без подчинения'), $id)?></td>
      </tr>
      <tr>
      	<th class="tab_red_th"></th>
      	<th>Название</th>
        <td><?=show_pole('text','name',htmlspecialchars($row['name']))?></td>
      </tr>
      <tr>
      	<th class="tab_red_th"></th>
        <th>Ссылка</th>
        <td><?=show_pole('text','link',$row['link'])?></td>
      </tr>
      <tr>
      	<th class="tab_red_th"></th>
        <th>Изображение</th>
        <td><?=show_pole('text','pic',$row['pic'])?></td>
      </tr>
      <tr>
      	<th class="tab_red_th"></th>
        <th></th>
        <td align="center">
        	<input type="submit" id="btn_save" value="<?=($id ? "Сохранить" : "Добавить")?>" class="but1" />&nbsp;
        	<input type="button" id="btn_cancel" value="Отмена" class="but1" onclick="top.location.href='<?=$script?>'" />
        </td>
      </tr>
    </table>
    </form>    
    <?
	$content = ob_get_clean();
}
// -----------------ПРОСМОТР-------------------
else
{
	$page_title .= " :: ".$rubric;
	
	$razdel = array("Добавить"=>"?red=0","Удалить"=>"javascript:multidel(document.red_frm,'check_del_','');");
	$subcontent = show_subcontent($razdel);
	
	ob_start();
	?>	
	<form action="?action=multidel" name="red_frm" method="post" target="ajax">
	<input type="hidden" id="cur_id" value="<?=@$_GET['id']?@(int)$_GET['id']:""?>" />
	<table width="100%" border="1" cellspacing="0" cellpadding="0" class="tab1">
	  <tr>
		<th><input type="checkbox" name="check_del" id="check_del" /></th>
		<th>№</th>
        <th></th>
		<th width="50%">Название</th>
		<th width="50%">Ссылка</th>
        <th>Порядок</th>
		<th style="padding:0 30px;"></th>
	  </tr>
	<?
	$mas = getTree("SELECT * FROM {$prx}{$tbl} WHERE id_parent='%s' ORDER BY sort,id");
	if(sizeof($mas))
	{
		$i=1;
		foreach($mas as $vetka)
		{
			$row = $vetka['row'];
			$level = $vetka["level"];
			
			$prfx = $prefix===NULL ? getPrefix($level) : str_repeat($prefix, $level);
			
			?>
			<tr id="<?=$row['id']?>">
			<th><input type="checkbox" name="check_del_[<?=$row['id']?>]" id="check_del_<?=$row['id']?>" /></th>
			<th><?=$i++?></th>
            <th><img src="img/navigate/<?=$row['pic']?>" width="25" height="22" /></th>
			<td><?=$prfx?><a href="?red=<?=$row['id']?>" class="link1"><?=$row['name']?></a></td>
			<td><a href="" onclick="RegSessionSort('<?=$row['link']?>','filter=remove');return false;" style="color:#090"><?=$row['link']?></a></td>
			<td nowrap align="center"><?=btn_sort($row['id'])?></td>
			<td nowrap align="center"><?=btn_edit($row['id'])?></td>
			</tr>
			<?
		}	
	}
	else
	{
		?><tr><td colspan="7" align="center">по вашему запросу ничего не найдено.</td></tr><?
	}
	?>
	</table>
	</form>
	<?
	$content = $subcontent.ob_get_clean();
}

require("tpl/tpl.php");
?>