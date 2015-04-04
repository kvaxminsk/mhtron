<?
require('inc/common.php');

$rubric = 'Вопросы-ответы';
$tbl = 'faq';

// ------------------- СОХРАНЕНИЕ ------------------------
if(isset($_GET["action"]))
{
	$id = (int)$_GET['id'];
	
	switch($_GET['action'])
	{
		// ----------------- сохранение
		case "save":
			foreach($_POST as $key=>$val)
				$$key = clean($val);
			
			if(!$name) errorAlert('необходимо указать название !');
			
			$id = update($tbl,"id_parent={$id_parent}, name='{$name}', text='{$text}', status={$status}",$id);
						
			?><script>top.location.href = "<?=$script?>?id=<?=$id?>";</script><?		
		break;
		// ----------------- обновление в меню
		case "status":
			update_flag($tbl,$_GET['action'],$id);
		break;
		// ----------------- сортировка вверх
		case "moveup":
			$id_parent = (int)getField("SELECT id_parent FROM {$prx}{$tbl} WHERE id={$id}");
			sort_moveup($tbl,$id,"id_parent={$id_parent}");
			?><script>top.location.href = "<?=$script?>?id=<?=$id?>";</script><?
		break;
		// ----------------- сортировка вниз
		case "movedown":
			$id_parent = (int)getField("SELECT id_parent FROM {$prx}{$tbl} WHERE id={$id}");
			sort_movedown($tbl,$id,"id_parent={$id_parent}");
			?><script>top.location.href = "<?=$script?>?id=<?=$id?>";</script><?
		break;
		// ----------------- удаление одной записи
		case "del":
			// обновляем подчинённые страницы
			sql("UPDATE {$prx}{$tbl} SET id_parent=0 WHERE id_parent={$id}");
			// удаляем текущую страницу
			update($tbl,'',$id);
			?><script>top.location.href = "<?=$script?>";</script><?
		break;
		// ----------------- удаление нескольких записей
		case "multidel":
			foreach($_POST['check_del_'] as $k=>$v)
			{
				// обновляем подчинённые страницы
				sql("UPDATE {$prx}{$tbl} SET id_parent=0 WHERE id_parent={$k}");
				// удаляем текущую страницу
				update($tbl,'',$k);
			}
			?><script>top.location.href = "<?=$script?>";</script><?
		break;
	}
}
// ------------------ РЕДАКТИРОВАНИЕ --------------------
elseif(isset($_GET["red"]))
{
	$id = (int)$_GET['red'];
	
	$rubric .= " &raquo; ".($id ? "Редактирование" : "Добавление");
	$page_title .= " :: ".$rubric;
	
	$row = getRow("SELECT * FROM {$prx}{$tbl} WHERE id={$id}");
	
	ob_start();
	?>
  <form action="?action=save&id=<?=$id?>" method="post" target="ajax">
  <table width="100%" border="0" cellspacing="0" cellpadding="5" class="tab_red">
    <tr>
    	<th class="tab_red_th"></th>
      <th>Подчинение</th>
      <td><?=dllTree("SELECT id,name FROM {$prx}{$tbl} WHERE id_parent='%s' ORDER BY sort,name", 'name="id_parent" style="width:100%"', $row['id_parent'], array('0','без подчинения'), $id)?></td>
    </tr>
    <tr>
			<th class="tab_red_th"></th>
			<th>Название</th>
			<td><?=show_pole('text','name',htmlspecialchars($row['name']))?></td>
		</tr>
    <tr>
    	<th class="tab_red_th"></th>
      <th>Текст</th>
      <td><?=showFck('text',$row['text'],'Default','100%',400);?></td>
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
	$cur_page = $_SESSION['page'] ? $_SESSION['page'] : 1;
	
	$page_title .= " :: ".$rubric; 
	$rubric .= " &raquo; Общий список"; 
	
	$razdel = array("Добавить"=>"?red=0","Удалить"=>"javascript:multidel(document.red_frm,'check_del_','');");
	$subcontent = show_subcontent($razdel);
	
	ob_start();
	
	// проверяем текущую сортировку
	// и формируем соответствующий запрос
	$query = "SELECT * FROM {$prx}{$tbl} WHERE id_parent='%s' ";	
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
		$query .= "ORDER BY sort ";
	//-----------------------------
	//	echo $query;
	
	show_filters($script);
	
	?>
  <form action="?action=multidel" name="red_frm" method="post" target="ajax">
  <input type="hidden" id="cur_id" value="<?=@$_GET['id']?@(int)$_GET['id']:""?>" />
  <table width="100%" border="1" cellspacing="0" cellpadding="0" class="tab1">
    <tr>
      <th><input type="checkbox" name="check_del" id="check_del" /></th>
      <th>№</th>
      <th width="100%"><?=ShowSortPole($script,$cur_pole,$cur_sort,'Название','name');?></th>
      <th><?=ShowSortPole($script,$cur_pole,$cur_sort,'Статус','status');?></th>
      <?
      if(!$_SESSION['sort'])
      {
          ?><th>Порядок</th><?
      }
      ?>
      <th nowrap style="padding:0 30px;"></th>
    </tr>
  <?
	$mas = getTree($query);
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
			<td width="50%"><a href="?red=<?=$row['id']?>" class="link1"><?=$prfx.$row['name']?></a></td>
			<td align="center"><?=btn_flag($row['status'],$row['id'],'action=status&id=')?></td>              
			<? 
			if(!$_SESSION['sort'])
			  echo "<td align='center'>".btn_sort($row['id'])."</td>";
			?>
			<td nowrap align="center"><?=btn_edit($row['id'])?></td>
			</tr>
			<?
		}	
	}
	else
	{
		?>
    <tr>
      <td colspan="10" align="center">
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