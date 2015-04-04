<?
require('inc/common.php');

$rubric = 'Страницы';
$tbl = 'pages';

// ------------------- СОХРАНЕНИЕ ------------------------
if(isset($_GET["action"]))
{
	$id = @$_GET['id'] ? (int)@$_GET['id'] : 0;
	
	switch(@$_GET['action'])
	{
		// ----------------- сохранение
		case "save":
			foreach($_POST as $key=>$val)
				$$key = clean($val);
				
			if(!$name) errorAlert('необходимо указать название !');
				
			if($locked)
				$id = update($tbl,"text='{$text}', title=".($title?"'{$title}'":"NULL").",	keywords=".($keywords?"'{$keywords}'":"NULL").", description=".($description?"'{$description}'":"NULL"),$id);
			else
			{
				$where = $id ? " and id<>{$id}" : "";	
				if($link)
				{
					if(getField("SELECT id FROM {$prx}{$tbl} WHERE link='{$link}'{$where}"))
						errorAlert('объект с данной ссылкой уже существует !');
				}
				else
				{
					$link = makeUrl($name);
					if(getField("SELECT id FROM {$prx}{$tbl} WHERE link='{$link}'{$where}"))
						errorAlert('ссылка автоматически сформированна - '.$link.',\nно объект с данной ссылкой уже существует!');
				}
				
				$set = "id_parent='{$id_parent}',
								name='{$name}',
								link='{$link}',
								text='{$text}',
								type='{$type}',
								to_menu='{$to_menu}',
								status='{$status}',
								title=".($title?"'{$title}'":"NULL").",
								keywords=".($keywords?"'{$keywords}'":"NULL").",
								description=".($description?"'{$description}'":"NULL");
				
				$id = update($tbl,$set,$id);
			}
						
			?><script>top.location.href = "<?=$script?>?id=<?=$id?>";</script><?		
		break;
		// ----------------- обновление в меню
		case "to_menu":
		case "status":
			update_flag($tbl,$_GET['action'],$id);
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
			if(gtv($tbl,"locked",$id))
				errorAlert("данная страница защищена от удаления!");
			else
				update($tbl,"",$id);
			?><script>top.location.href = "<?=$script?>";</script><?
		break;
		// ----------------- удаление нескольких записей
		case "multidel":
			foreach($_POST['check_del_'] as $k=>$v)
				if(!gtv($tbl,"locked",$k))
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
  <form action="?action=save&id=<?=$id?>" method="post" target="ajax">
  <input type="hidden" name="locked" value="<?=$row['locked']?>" />
  <table width="100%" border="0" cellspacing="0" cellpadding="5" class="tab_red">
    <tr>
    	<th class="tab_red_th"></th>
      <th>Подчинение</th>
      <td><?=dllTree("SELECT id,name FROM {$prx}{$tbl} WHERE id_parent='%s' ORDER BY sort,name", 'name="id_parent" style="width:100%"', $row['id_parent'], array('0','без подчинения'), $id)?></td>
    </tr>
    <tr>
    	<th class="tab_red_th"></th>
      <th>Название</th>
      <td><?=show_pole('text','name',htmlspecialchars($row['name']),$row['locked'])?></td>
    </tr>
    <tr>
    	<th class="tab_red_th"><?=help('формируется автоматически')?></th>
      <th>Ссылка</th>
      <td><?=show_pole('text','link',$row['link'],$row['locked'])?></td>
    </tr>
    <tr>
    	<th class="tab_red_th"></th>
      <th>Текст</th>
      <td><?=showFck("text",$row['text'],"Default","100%",400);?></td>
    </tr>
    <?
	  if(!$row['locked'])
	  {
		  ?>
      <tr>
      	<th class="tab_red_th"></th>
        <th>Тип</th>
        <td><?=dll(array('page'=>'страница','link'=>'ссылка'),' name="type"',$row['type'])?></td>
      </tr>
      <tr>
      	<th class="tab_red_th"></th>
        <th nowrap>В меню</th>
        <td><?=dll(array('0'=>'нет','1'=>'да'),' name="to_menu"',$row['to_menu'])?></td>
      </tr>
      <tr>
      	<th class="tab_red_th"></th>
        <th>Статус</th>
        <td><?=dll(array('0'=>'заблокировано','1'=>'активно'),' name="status"',isset($row['status'])?$row['status']:1)?></td>
      </tr>
      <?
	  }
	  ?>
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
        <input type="button" value="Отмена" class="but1" onclick="top.location.href='<?=$script?>'" />
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
		$query .= "ORDER BY sort,id ";
	//-----------------------------
	//	echo $query;
	
	show_filters($script);
	
	?>
  <form action="?action=multidel" name="red_frm" method="post" target="ajax">
  <input type="hidden" id="cur_id" value="<?=@$_GET['id']?@(int)$_GET['id']:""?>" />
  <table width="100%" cellspacing="0" cellpadding="0" class="tab1">
    <tr>
    	<th><input type="checkbox" name="check_del" id="check_del" /></th>
      <th>№</th>
      <th width="50%"><?=ShowSortPole($script,$cur_pole,$cur_sort,'Название','name');?></th>
      <th width="50%"><?=ShowSortPole($script,$cur_pole,$cur_sort,'Ссылка','link');?></th>
      <th nowrap><?=ShowSortPole($script,$cur_pole,$cur_sort,'Тип','type');?></th>
      <th nowrap><?=ShowSortPole($script,$cur_pole,$cur_sort,'В меню','to_menu');?><?=help('отображение ссылки в меню')?></th>
      <th nowrap><?=ShowSortPole($script,$cur_pole,$cur_sort,'Статус','status');?></th>
      <?
      if(!$_SESSION['sort'])
      {
          ?><th nowrap>Порядок</th><?
      }
      ?>
      <th style="padding:0 30px;"></th>
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
		
			if($row['type']=='link')
				$link = $row['link'];
			else
				$link = $row['link']=='/' ? '/' : "/pages/{$row['link']}.htm";
		  
			?>
			<tr id="<?=$row['id']?>">
			<th>
			  <?
			  if(!$row['locked'])
				{
					?><input type="checkbox" name="check_del_[<?=$row['id']?>]" id="check_del_<?=$row['id']?>" /><?
				}
			  ?>
			</th>
			<th nowrap><?=$i++?></th>
			<td><a href="?red=<?=$row['id']?>" class="link1"><?=$row['name']?></a></td>
			<td><?=$row['type']=='page'?'/pages/':''?><a href="<?=$link?>" style="color:#090"><?=$row['link']?></a><?=$row['type']=='page'?'.htm':''?></td>
			<td align="center"><?=$row['type']=='page'?"страница":"ссылка"?></td>
			<td align="center"><?=btn_flag($row['to_menu'],$row['id'],'action=to_menu&id=',$row['locked'])?></td>
			<td align="center"><?=btn_flag($row['status'],$row['id'],'action=status&id=',$row['locked'])?></td>
			<? 
			if(!$_SESSION['sort'])
			  echo '<td nowrap align="center">'.btn_sort($row['id']).'</td>';
			?>
      <td nowrap align="center"><?=btn_edit($row['id'],$row['locked'])?></td>
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