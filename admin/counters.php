<?
require('inc/common.php');

$rubric = "Счетчики";
$tbl = "counters";

// -------------------СОХРАНЕНИЕ----------------------
if(isset($_GET["action"]))
{
	$id = @$_GET['id'] ? (int)@$_GET['id'] : 0;
	
	switch($_GET['action'])
	{
		// ----------------- сохранение
		case "save":
			foreach($_POST as $key=>$val)
				$$key = clean($val);

			$id = update($tbl,"html='{$html}',notes=".($notes?"'{$notes}'":'NULL').",status='{$status}'",$id);

			?><script>top.location.href = "<?=$script?>?id=<?=$id?>";</script><?
			break;
		// ----------------- обновление статуса
		case "status":
			update_flag($tbl,'status',$id);
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
	exit;
}
// ------------------РЕДАКТИРОВАНИЕ--------------------
if(isset($_GET["red"]))
{
	$id = @$_GET['red'] ? (int)@$_GET['red'] : 0;
	
	$rubric .= " &raquo; ".($id ? "Редактирование" : "Добавление");
	$page_title .= " :: ".$rubric;
	
	$row = getRow("SELECT * FROM {$prx}{$tbl} WHERE id={$id}");
	
	ob_start();
	?>
    <form action="?action=save&id=<?=$id?>" method="post" enctype="multipart/form-data" target="ajax">
    <table width="100%" border="0" cellspacing="0" cellpadding="5" class="tab_red">
      <tr>
      	<th class="tab_red_th"></th>
      	<th>Код счетчика</th>
        <td><?=show_pole('textarea','html',$row['html'])?></td>
      </tr>
      <tr>
      	<th class="tab_red_th"></th>
        <th>Примечание</th>
        <td><?=show_pole('textarea','notes',$row['notes'])?></td>
      </tr>	
      <tr>
      	<th class="tab_red_th"></th>
        <th>Статус</th>
        <td><?=dll(array('0'=>'заблокировано','1'=>'активно'),'name="status"',isset($row['status'])?$row['status']:1)?></td>
      </tr>
      <tr>
      	<th class="tab_red_th"></th>
        <th></th>
        <td align="center">
        	<input type="submit" value="<?=($id ? "Сохранить" : "Добавить")?>" class="but1" onclick="loader(true)" />&nbsp;
        	<input type="button" value="Отмена" class="but1" onclick="location.href='<?=$script?>'" />
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
	ob_start();
	
	$page_title .= " :: ".$rubric; 
	$rubric .= " &raquo; Общий список"; 
	
	$razdel = array("Добавить"=>"?red=0","Удалить"=>"javascript:multidel(document.red_frm,'check_del_','');");
	$subcontent = show_subcontent($razdel);
	
	ob_start();
	?>
    <form action="?action=multidel" name="red_frm" method="post" target="ajax">
    <input type="hidden" id="cur_id" value="<?=isset($_GET['id'])?(int)$_GET['id']:""?>" />
    <table width="100%" border="1" cellspacing="0" cellpadding="0" class="tab1">
      <tr>
        <th><input type="checkbox" name="check_del" id="check_del" /></th>
        <th>№</th>
        <th width="50%">Счетчик</th>
        <th width="50%">Примечание</th>
        <th>Статус</th>
        <th>Порядок</th>
        <th style="padding:0 30px;"></th>
      </tr>
    <?
	$res = sql("SELECT * FROM {$prx}{$tbl} ORDER BY sort");
	if(@mysql_num_rows($res))
	{
		$i=1;
        while($row = mysql_fetch_array($res))
        {
		  ?>
		  <tr id="<?=$row['id']?>">
            <th><input type="checkbox" name="check_del_[<?=$row['id']?>]" id="check_del_<?=$row['id']?>" /></th>
            <th nowrap><?=$i++?></th>
            <td><a href="" class="link1"><?=$row["html"]?></a></td>
            <td><a href="" class="link1"><?=nl2br($row["notes"])?></a></td>
            <td nowrap align="center"><?=btn_flag($row['status'],$row['id'],'action=status&id=')?></td>
            <td nowrap align='center'><?=btn_sort($row['id'])?></td>
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
          по вашему запросу ничего не найдено.<?=help('нет ни одной записи отвечающей критериям вашего запроса,<br>возможно вы установили неверные фильтры')?>
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