<?
require('inc/common.php');

$rubric = 'Производители';
$tbl = 'makers';

// ------------------- СОХРАНЕНИЕ ------------------------
if(isset($_GET["action"]))
{
	$id = @$_GET['id'] ? (int)@$_GET['id'] : 0;
	
	switch($_GET['action'])
	{
		// ----------------- сохранение
		case "save":
			
			foreach($_POST as $key=>$val)
				$$key = clean($val);
			
			if(!$name) errorAlert('необходимо указать название !');
			
			$where = $id ? " AND id<>{$id}" : '';	
			if(getField("SELECT id FROM {$prx}{$tbl} WHERE name='{$name}'{$where}"))
				errorAlert('данный производитель уже есть в справочнике!');
			if($code && getField("SELECT id FROM {$prx}{$tbl} WHERE code='{$code}'{$where}"))
				errorAlert('данный код уже есть в справочнике!');
			
			if($link)
			{
				if(getField("SELECT id FROM {$prx}{$tbl} WHERE link='{$link}'{$where}"))
					$updateLink = true;
			}
			else
			{
				$link = makeUrl($name);
				if(getField("SELECT id FROM {$prx}{$tbl} WHERE link='{$link}'{$where}"))
					$updateLink = true;
			}
			
			$set = "name='{$name}',code='{$code}'";
			if(!$updateLink)
				$set .= ",link='{$link}'";
				
			if(!$id = update($tbl,$set,$id))
				errorAlert('Во время сохранения данных произошла ошибка.');
				
			if($updateLink)
				update($tbl,"link='".($link.'_'.$id)."'",$id);
			
			?><script>top.location.href = "<?=$script?>?id=<?=$id?>";</script><?			
			break;
		// ----------------- удаление одной записи
		case "del":
			// обновление товаров
			sql("UPDATE {$prx}goods SET id_maker=0 WHERE id_maker='{$id}'");
			// удаление производителя
			update($tbl,'',$id);
			?><script>top.location.href = "<?=$script?>";</script><?
			break;
		// ----------------- удаление нескольких записей
		case "multidel":
			foreach($_POST['check_del_'] as $k=>$v)
			{
				// обновление товаров
				sql("UPDATE {$prx}goods SET id_maker=0 WHERE id_maker='{$k}'");
				// удаление производителя
				update($tbl,'',$id);
			}
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
  <form action="?action=save&id=<?=$id?>" method="post" target="ajax">
  <table width="100%" border="0" cellspacing="0" cellpadding="5" class="tab_red">
    <tr>
      <th class="tab_red_th"></th>
			<th>Название</th>
			<td><?=show_pole('text','name',htmlspecialchars($row['name']))?></td>
    <tr>
    	<th class="tab_red_th"><?=help('необходим для импорта товаров')?></th>
      <th>Код</th>
      <td><?=show_pole('text','code',$row['code'])?></td>
    </tr>
    <tr>
      <th class="tab_red_th"><?=help('ссылка формируется автоматически,<br>значение данного поля можно изменить')?></th>
      <th>Ссылка</th>
      <td><?=show_pole('text','link',$row['link'])?></td>
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
	$cur_page = @$_SESSION['page'] ? (int)$_SESSION['page'] : 1;
	$letter = isset($_SESSION['letter']) ? (string)$_SESSION['letter'] : '';
	
	$where = '';
	if($letter!='')		$where .= "and name like ('{$letter}%') ";
	
	$page_title .= " :: ".$rubric; 
	$rubric .= " &raquo; Общий список"; 
	
	$razdel = array("Добавить"=>"?red=0","Удалить"=>"javascript:multidel(document.red_frm,'check_del_','');");
	$subcontent = show_subcontent($razdel);
	
	$query = "SELECT * FROM {$prx}{$tbl}";
	
	$count_obj = getField(str_replace('*','COUNT(*)',$query)); // кол-во объектов в базе
	$count_obj_on_page = set("count_{$tbl}_admin"); // кол-во объектов на странице
	$kol_str = ceil($count_obj/$count_obj_on_page); // количество страниц
	
	ob_start();
	// проверяем текущую сортировку
	// и формируем соответствующий запрос
	if($_SESSION['sort']) 
	{
		$sort = explode(":",$_SESSION['sort']);
		$cur_pole = $sort[0];
		$cur_sort = $sort[1];

		$query .= " ORDER BY {$cur_pole}";
		$query .= $cur_sort=='up' ? ' DESC' : ' ASC';
	}
	else
		$query .= " ORDER BY name";
	$query .= " LIMIT ".($count_obj_on_page*$cur_page-$count_obj_on_page).",".$count_obj_on_page;
	//-----------------------------
	//	echo $query;
	
	show_filters($script);
	show_letter_navigate($script,$tbl,'name',$where);
	show_navigate_pages($kol_str,$cur_page,$script);
	
	?>
  <form action="?action=multidel" name="red_frm" method="post" target="ajax">
  <input type="hidden" id="cur_id" value="<?=@$_GET['id']?@(int)$_GET['id']:""?>" />
  <table width="100%" border="1" cellspacing="0" cellpadding="0" class="tab1">
    <tr>
      <th><input type="checkbox" name="check_del" id="check_del" /></th>
      <th>№</th>
      <th><?=ShowSortPole($script,$cur_pole,$cur_sort,'Код','code');?></th>
      <th width="50%"><?=ShowSortPole($script,$cur_pole,$cur_sort,'Название','name');?></th>
      <th nowrap width="50%"><?=ShowSortPole($script,$cur_pole,$cur_sort,'Ссылка','link')?></th>
      <th nowrap>Кол-во товаров</th>
      <th style="padding:0 30px;"></th>
    </tr>
  <?
	$res = mysql_query($query);
	if(@mysql_num_rows($res))
	{
		  $i=1;
		  while($row = mysql_fetch_array($res))
		  {
				$id = $row['id'];
			  ?>
			  <tr id="<?=$id?>">
			  <th><input type="checkbox" name="check_del_[<?=$id?>]" id="check_del_<?=$id?>" /></th>
        <th nowrap><?=$i++?></th>
        <td nowrap align="center"><?=$row['code']?></td>
			  <td><a href="?red=<?=$id?>" class="link1"><?=$row['name']?></a></td>
        <td>/makers/<a href="/makers/<?=$row['link']?>/" target="_blank" style="color:#090"><?=$row['link']?></a>/</td>
        <td align="center">
          <?
          if($count_goods = getField("SELECT COUNT(*) FROM {$prx}goods WHERE id_maker={$id}"))
          {
            ?><a href="" style="color:#090" onClick="RegSessionSort('goods.php','maker=<?=$id?>');return false;"><?=$count_goods?></a><?
          }
          else
            echo '0';
          ?>
        </td>
			  <td nowrap align="center"><?=btn_edit($id)?></td>
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