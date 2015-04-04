<?
require('inc/common.php');

$rubric = 'Статьи';
$tbl = 'articles';

// ------------------- СОХРАНЕНИЕ ------------------------
if(isset($_GET["action"]))
{
	$id = (int)$_GET['id'];
	
	switch($_GET['action'])
	{
		// ----------------- сохранение
		case "save":
		
			foreach($_POST as $key=>$value)
				$$key = clean($value);
			
			if(!$name) errorAlert('необходимо указать название !');
			
			$date_change = false;
			// если дата поста изменилась
			if($date!=$old_date)
			{
				$date = $date ? formatDateTime($date." ".date("H:i:s")) : date("Y-m-d H:i:s");
				$date_change = true;
			}
			$avtor = isset($_SESSION['admin']) ? 'Администратор' : $_SESSION['manager']['name'];
			
			$set = "name='{$name}',
							preview='{$preview}',
							text='{$text}',
							avtor='{$avtor}',
							status={$status},
							title=".($title?"'{$title}'":"NULL").",
							keywords=".($keywords?"'{$keywords}'":"NULL").",
							description=".($description?"'{$description}'":"NULL");
			$set .= $date_change ? ",`date`='{$date}'" : '';
			
			$id = update($tbl,$set,$id);
			
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
			update($tbl,"",$id);
			?><script>top.location.href = "<?=$script?>";</script><?
			break;
		// ----------------- удаление нескольких записей
		case "multidel":
			foreach($_POST['check_del_'] as $k=>$v)
				update($tbl,"",$k);
			?><script>top.location.href = "<?=$script?>";</script><?
			break;
		// ----------------- удаление сообщения
		case "del_comment":
			$id_art = (int)@$_GET['id_art'];
			update('art_comments','',$id);
			?><script>top.location.href = "<?=$script?>?red=<?=$id_art?>";</script><?
			break;
	}
	exit;
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
  <form action="?action=save&id=<?=$id?>" method="post" enctype="multipart/form-data" target="ajax">
  <input type="hidden" name="old_date" value="<?=date("d.m.Y",strtotime($row['date']))?>">
  <table width="100%" border="0" cellspacing="0" cellpadding="5" class="tab_red">
    <tr>
			<th class="tab_red_th"></th>
			<th>Название</th>
			<td><?=show_pole('text','name',htmlspecialchars($row['name']))?></td>
		</tr>
    <tr>
			<th class="tab_red_th"></th>
			<th>Дата</th>
			<td><input type="text" class="datepicker" name="date" value="<?=(isset($row['date']) ? date("d.m.Y",strtotime($row['date'])) : date("d.m.Y"))?>" /></td>
		</tr>
    <tr>
			<th class="tab_red_th"></th>
			<th>Краткое<br>описание</th>
			<td><?=showFck('preview',$row['preview'],'Medium','100%',200);?></td>
		</tr>
		<tr>
			<th class="tab_red_th"></th>
			<th>Основной<br>текст</th>
			<td><?=showFck('text',$row['text'],'Default','100%',400);?></td>
		</tr>
    <tr>
			<th class="tab_red_th"></th>
			<th>Статус</th>
			<td><?=dll(array('0'=>'заблокировано','1'=>'активно'),'name="status"',isset($row['status'])?$row['status']:1)?></td>
		</tr>
		<tr>
			<th class="tab_red_th"></th>
			<th>title</th>
			<td><?=show_pole('text','title',htmlspecialchars($row['title']))?></td>
		</tr>
		<tr>
			<th class="tab_red_th"></th>
			<th>keywords</th>
			<td><?=show_pole('text','keywords',htmlspecialchars($row['keywords']))?></td>
		</tr>
		<tr>
			<th class="tab_red_th"></th>
			<th>description</th>
			<td><?=show_pole('textarea','description',$row['description'])?></td>
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
	
	// ---------------------- КОММЕНТАРИИ ПОЛЬЗОВАТЕЛЕЙ ---------------------------
	?><h1 style="margin:10px 0;">Комментарии пользователей</h1><?
	
	$cur_page = $_SESSION['page'] ? $_SESSION['page'] : 1;
	
	$query = "SELECT * FROM {$prx}art_comments WHERE id_art={$id}";
	
	$count_obj = getField(str_replace('*','COUNT(*)',$query)); // кол-во объектов в базе
	$count_obj_on_page = set("count_art_comments_admin"); // кол-во объектов на странице
	$kol_str = ceil($count_obj/$count_obj_on_page); // количество страниц
	
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
		$query .= " ORDER BY `date`";
	$query .= " LIMIT ".($count_obj_on_page*$cur_page-$count_obj_on_page).",".$count_obj_on_page;
	//-----------------------------
	//echo $query;

	show_filters($script."?red={$id}");
	show_navigate_pages($kol_str,$cur_page,$script."?red={$id}");
	
	?>
  <input type="hidden" id="cur_id" value="<?=@$_GET['id']?(int)$_GET['id']:''?>" />
	<table width="100%" border="1" cellspacing="0" cellpadding="0" class="tab1">
	  <tr>
		<th><?=ShowSortPole($script."?red={$id}",$cur_pole,$cur_sort,'Дата','date')?></th>
    <th>Пользователь</th>
		<th width="100%"><?=ShowSortPole($script."?red={$id}",$cur_pole,$cur_sort,'Комментарий','text')?></th>
		<th style="padding:0 30px;"></th>
	  </tr>
    <?
	$res = mysql_query($query);
	if(@mysql_num_rows($res))
	{
		while($arr = mysql_fetch_assoc($res))
		{
			$user = getRow("SELECT id,name,surname FROM {$prx}users WHERE id={$arr['id_user']}");
			?>
      <tr id="<?=$arr['id']?>">
        <td nowrap><?=date("d.m.Y H:i:s",strtotime($arr['date']))?></td>
        <td nowrap><a href="users.php?red=<?=$user['id']?>"><?=$user['name']?> <?=$user['surname']?></a></th>
        <td align="justify"><?=$arr['text']?></th>
        <td nowrap align="center">
          <img src="img/del.png" width="16" height="16" alt="удалить" title="удалить комментарий" style="cursor:pointer;" onclick="if(confirm('Уверены?')) toajax('?action=del_comment&id_art=<?=$id?>&id=<?=$arr['id']?>')" />
        </td>
      </tr>
      <?
		}
	}
	else
	{
		?>
    <tr>
      <td colspan="4" align="center">комментарии отсутствуют</td>
    </tr>
    <?
	}
	?></table><?
	
	$content = ob_get_clean();
}
// -----------------ПРОСМОТР-------------------
else
{
	$cur_page = @$_GET['page'] ? @(int)$_GET['page'] : 1;
	
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
		$query .= " ORDER BY sort";
	$query .= " LIMIT ".($count_obj_on_page*$cur_page-$count_obj_on_page).",".$count_obj_on_page;
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
      <th width="100%"><?=ShowSortPole($script,$cur_pole,$cur_sort,'Название','name');?></th>
      <th><?=ShowSortPole($script,$cur_pole,$cur_sort,'Дата','date')?></th>
      <th>Комментарии</th>
      <th><?=ShowSortPole($script,$cur_pole,$cur_sort,'Статус','status');?></th>
      <?
      if(!$_SESSION['sort'])
      {
        ?><th nowrap>Порядок<?=help('параметр с помощью которого можно изменить порядок вывода элемента в клиентской части сайта')?></th><?
      }
      ?>
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
			<th><?=$i?></th>
			<td width="100%"><a href="?red=<?=$row['id']?>" class="link1"><?=$row['name']?></a></td>
			<td align="center"><?=date('d.m.Y',strtotime($row['date']))?></td>
			<td align="center"><?=getField("SELECT count(*) FROM {$prx}art_comments WHERE id_art={$row['id']}")?></td>
			<td align="center"><?=btn_flag($row['status'],$row['id'],'action=status&id=')?></td>
			<? 
			if(!$_SESSION['sort'])
				echo "<td nowrap align='center'>".btn_sort($row['id'])."</td>";
			?>
			<td nowrap align="center"><?=btn_edit($row['id'])?></td>
			</tr>
			<?
			$i++;
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