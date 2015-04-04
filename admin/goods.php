<?
require('inc/common.php');

$rubric = "Товары";
$tbl = "goods";

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
			
			if(!$name) errorAlert('укажите Наименование !');
			if(!$articul) errorAlert('введите Артикул !');
			
			$da = $da ? formatDateTime($da) : '';
			
			$where = $id ? " and id<>{$id}" : '';
			if($articul && getField("SELECT id FROM {$prx}{$tbl} WHERE articul='{$articul}'{$where}"))
				errorAlert('данный артикул уже есть в базе!');
			
			if($link)
			{
				if(getField("SELECT id FROM {$prx}{$tbl} WHERE link='{$link}'{$where}"))
					$updateLink = true;
			}
			else
			{
				$link = makeUrl($articul.'-'.$name);
				if(getField("SELECT id FROM {$prx}{$tbl} WHERE link='{$link}'{$where}"))
					$updateLink = true;
			}
			
			$analogues = str_replace('; ',';', $analogues);
			
			for($i=1; $i<=10; $i++)
				${"price{$i}"} =(int)${"price{$i}"};
			
			$set = "id_maker='{$id_maker}',
							name='{$name}',soput='{$soput}',
							articul='{$articul}', analogues='{$analogues}'";/*,
							analogues=NULL";*/
			for($i=1; $i<=10; $i++)
				$set .= ",price{$i}='".${"price{$i}"}."'";
			$set .= ",da=".($da?"'{$da}'":"NULL").",
								status={$status},
								title=".($title?"'{$title}'":"NULL").",
								keywords=".($keywords?"'{$keywords}'":"NULL").",
								description=".($description?"'{$description}'":"NULL");
			if(!$updateLink)
				$set .= ",link='{$link}'";
			
			if(!$id = update($tbl,$set,$id))
				errorAlert('Во время сохранения данных произошла ошибка.');

			if($updateLink)
				update($tbl,"link='".($link.'_'.$id)."'",$id);

			/*// ------------ аналоги товара
			$ids_analogues = array();
			foreach((array)$_POST['ids_analogues'] as $id_good)
			{
				if($id_good!=$id)
					$ids_analogues[] = $id_good;
			}
			if($ids_analogues)
				update($tbl,"analogues='".implode(';',$ids_analogues)."'",$id);
			*/
			// загружаем картинку
			if($_FILES['img']['name'])
			{
				// мочим старую картинку
				remove_good($id,true,"{$articul}.jpg");
				
				$path = $_SERVER['DOCUMENT_ROOT']."/uploads/{$tbl}/{$articul}.jpg";
				@move_uploaded_file($_FILES['img']['tmp_name'],$path);
				@chmod($path,0644);
			}
			
			?><script>top.location.href = "<?=$script?>?id=<?=$id?>";</script><?
		break;
		// ----------------- обновление статуса
		case "status":
			update_flag($tbl,$_GET['action'],$id);					
		break;
		// ----------------- удаление изображения
		case "pic_del":
			$fname = clean($_GET['fname']);
			remove_good($id,true,$fname);
			?><script>top.location.href = "<?=$script?>?red=<?=$id?>";</script><?
		break;
		// ----------------- удаление одной записи
		case "del":
			remove_good($id);
			?><script>top.location.href = "<?=$script?>";</script><?
		break;
		// ----------------- удаление нескольких записей
		case "multidel":
			foreach($_POST['check_del_'] as $k=>$v)
				remove_good($k);
			?><script>top.location.href = "<?=$script?>";</script><?
		break;
		// ---------------- настройка критериев --------------
		case "set_criteria":
			?>
			<script>
			top.show_popup_window('Настройка отображения полей','inc/set_criteria.php?tab=<?=$tbl?>&location=<?=$script?>');
			</script>
			<?
			break;
	}
	exit();
}
// ------------------ РЕДАКТИРОВАНИЕ --------------------
elseif(isset($_GET["red"]))
{
	$id = @$_GET['red'] ? (int)@$_GET['red'] : 0;
	
	$row = getRow("SELECT * FROM {$prx}{$tbl} WHERE id={$id}");
	
	$rubric .= " &raquo; ".($id ? "Редактирование товара" : "Добавление товара");
	$page_title .= " :: ".$rubric;
	
	ob_start();
	?>
  <script>
	$(function(){
		/*$('#add_analogues').click(function(){
			var ids = '';
			$('#ids_analogues option').each(function(){
				if($(this).val()!='')
					ids += (ids?',':'')+$(this).val();
			});
			show_popup_window('Выбор аналогов','inc/add_goods.php?list=ids_analogues&ids='+ids);
		});*/
		$('#btn_cancel').click(function(){
			location.href = '<?=$script?>';
		});		
		$('#btn_save').click(function(){
			//$('#ids_analogues').find('option').each(function(){ $(this).attr('selected',true) });
			$('#edit_frm').submit();		
		});
	});
	</script>
    <form id="edit_frm" action="?action=save&id=<?=$id?>" method="post" enctype="multipart/form-data" target="ajax">
    <table width="100%" border="0" cellspacing="0" cellpadding="5" class="tab_red">
      <tr>
      	<th class="tab_red_th"></th>
      	<th>Производитель</th>
        <td><?=dll("SELECT id,name FROM {$prx}makers ORDER BY name",'name="id_maker" style="width:100%"',$row['id_maker'],'нет связи')?></td>
      </tr>
      <tr>
      	<th class="tab_red_th"></th>
        <th>Артикул</th>
        <td><?=show_pole('text','articul',$row['articul'])?></td>
      </tr>
      <tr>
        <th class="tab_red_th"></th>
        <th>Наименование</th>
        <td><?=show_pole('text','name',htmlspecialchars($row['name']))?></td>
      </tr>
      <tr>
      <th class="tab_red_th"><?=help('ссылка формируется автоматически,<br>значение данного поля можно изменить')?></th>
      <th>Ссылка</th>
      <td><?=show_pole('text','link',$row['link'])?></td>
    </tr>
      <?=show_tr_img(	'img',
	  									"/uploads/{$tbl}/",
											"{$row['articul']}.jpg",
	  									$script."?action=pic_del&id={$id}&fname={$row['articul']}")?>
      <?
			for($i=1; $i<=10; $i++)
			{
				$help = '';
				if($i==1) $help = 'закупочная цена';
				if($i==10) $help = 'дилерская цена';
				?>
        <tr>
        	<th class="tab_red_th"><?=$help?help($help):''?></th>
          <th>Цена <?=$i?></th>
          <td><?=show_pole('text',"price{$i}",$row["price{$i}"])?></td>
        </tr>
        <?
			}
			?>
      <tr>
      	<th class="tab_red_th"><?=help('Артикулы, через - ;')?></th>
        <th>Аналоги</th>
        <td><?=show_pole('text','analogues',$row['analogues'])?>
					<? /*
          <input id="add_analogues" type="button" class="but1" value="добавить" style="margin:5px 10px 10px 0;">
          <input type="button" class="but1" value="удалить" style="margin:5px 0 10px 0;" onClick="del_options($('#ids_analogues'));">
          <select id="ids_analogues" name="ids_analogues[]" size="10" style="width:100%" multiple>
          <?
					if($row['analogues'])
					{
						foreach((array)explode(';',$row['analogues']) as $id_analog)
						{
							$analog = getRow("SELECT id,articul,name FROM {$prx}goods WHERE id={$id_analog}");
							?><option value="<?=$analog['id']?>"><?=$analog['articul']?> - <?=$analog['name']?></option><?
						}
					}
          ?>
          </select>*/
					?>
        </td>
      </tr>
      <tr>
      	<th class="tab_red_th"><?=help('Артикулы сопутствующих товаров, разделитель - ;')?></th>
        <th>Сопутствующие</th>
        <td><?=show_pole('text','soput',htmlspecialchars($row['soput']))?></td>
      </tr>
      <tr>
      	<th class="tab_red_th"></th>
      	<th>Дата прихода</th>
        <td><input type="text" class="datepicker" name="da" value="<?=(isset($row['da']) ? date("d.m.Y",strtotime($row['da'])) : '')?>" /></td>
      </tr>
      <tr>
      	<th class="tab_red_th"></th>
        <th>Статус</th>
        <td><?=dll(array('0'=>'заблокировано','1'=>'активно'),' name="status"',isset($row['status'])?$row['status']:1)?></td>
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
          <input type="button" id="btn_save" value="<?=($id ? "Сохранить" : "Добавить")?>" class="but1" onclick="loader(true)" />
          <input type="button" id="btn_cancel" value="Отмена" class="but1" />
        </td>
      </tr>
    </table>
    </form>
    <?=popup_modul()?>
    <?
	
	$content = ob_get_clean();
}
// -----------------ПРОСМОТР-------------------
else
{
	$cur_page = @$_SESSION['page'] ? @(int)$_SESSION['page'] : 1;
	
	$f_maker = 	(int)@$_SESSION['maker'];
	$f_letter = (string)@$_SESSION['letter'];
	$f_context = htmlspecialchars(stripslashes($_SESSION['context']));
	
	$where = '';
	if($f_maker)		$where .= " AND A.id_maker={$f_maker} ";
	if($f_letter)		$where .= " AND A.name like ('{$f_letter}%') ";
	if($f_context)	$where .= " AND ( A.articul LIKE '%".clean($f_context)."%' OR A.name LIKE '%".clean($f_context)."%' )";
	
	$page_title .= " :: ".$rubric;
	
	$razdel = array("Добавить"=>"?red=0",
									"Удалить"=>"javascript:multidel(document.red_frm,'check_del_');",
									"Настройка"=>"javascript:toajax('?action=set_criteria');"
									);
	$subcontent = show_subcontent($razdel);
	
	if($f_maker)
		$rubric .= " &raquo; Общий список &raquo; ".getField("SELECT name FROM {$prx}makers WHERE id={$f_maker}");
	else
		$rubric .= " &raquo; Общий список";
		
	$query = "SELECT A.*,B.link as mlink FROM {$prx}{$tbl} A
						LEFT JOIN {$prx}makers B ON A.id_maker=B.id
						WHERE 1{$where}";
	
	$r = sql($query);
	$count_obj = @mysql_num_rows($r); // кол-во объектов в базе
	$count_obj_on_page = set("count_{$tbl}_admin"); // кол-во объектов на странице
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
		$query .= " ORDER BY A.id";
		
	$query .= " LIMIT ".($count_obj_on_page*$cur_page-$count_obj_on_page).",".$count_obj_on_page;
	//-----------------------------
	//echo $query;
	
	ob_start();
	
	show_filters($script);
	show_letter_navigate($script,$tbl,'name',$where);
	show_navigate_pages($kol_str,$cur_page,$script);
	
	?>
  <table class="filter_tab" style="margin:5px 0 0 0;">
    <tr>
      <td align="left">Производитель</td>
      <td colspan="2"><?=dll("SELECT id,name FROM {$prx}makers ORDER BY name",'onChange="RegSessionSort(\''.$script.'\',\'maker=\'+this.value);return false;"',$f_maker,array('remove','-- все --'))?></td>
    </tr>
    <tr>
      <td>контекстный поиск <?=help('поиск осуществляется только по<br /><b>артикулу</b> и <b>наименованию</b><br />товара')?></td>
      <td><input type="text" id="search_txt" value="<?=$f_context?>" style="width:200px;"></td>
      <td><a href="" target="ajax" class="link" onClick="RegSessionSort('<?=$script?>','context='+$('#search_txt').val());return false;">найти</a></td>
    </tr>
  </table>
  <?
	$criteria = get_criteria($tbl);
	?>
  <form action="?action=multidel" name="red_frm" method="post" target="ajax">
  <input type="hidden" id="cur_id" value="<?=@$_GET['id']?@(int)$_GET['id']:""?>" />
  <table width="100%" border="1" cellspacing="0" cellpadding="0" class="tab1">
    <tr>
      <th width="20" style="padding:0px;"><input type="checkbox" name="check_del" id="check_del" /></th>
      <th width="20" style="padding:0px;">№</th>
      <?
			if(in_array('img',$criteria))
			{
				?><th width="100">Изображение</th><?
			}
			if(in_array('articul',$criteria))
			{
				?><th><?=ShowSortPole($script,$cur_pole,$cur_sort,'Артикул','articul')?></th><?
			}
			if(in_array('name',$criteria))
			{
				?><th><?=ShowSortPole($script,$cur_pole,$cur_sort,'Наименование','name')?></th><?
			}
			if(in_array('link',$criteria))
			{
				?><th><?=ShowSortPole($script,$cur_pole,$cur_sort,'Ссылка','link')?></th><?
			}
			if(in_array('id_maker',$criteria))
			{
				?><th>Произодитель</th><?
			}
			for($i=1; $i<=10; $i++)
			{
				if(in_array("price{$i}",$criteria))
				{
					?><th><?=ShowSortPole($script,$cur_pole,$cur_sort,"Цена {$i}","price{$i}")?></th><?
				}
			}
			if(in_array('da',$criteria))
			{
				?><th nowrap><?=ShowSortPole($script,$cur_pole,$cur_sort,'Дата прихода','da')?></th><?
			}
			if(in_array('status',$criteria))
			{
				?><th nowrap><?=ShowSortPole($script,$cur_pole,$cur_sort,'Статус','status')?> <?=help('заблокирован данный товар или нет')?></th><?
			}
			if(in_array('id',$criteria))
			{
				?><th><?=ShowSortPole($script,$cur_pole,$cur_sort,'ID','id')?></th><?
			}
			?>
      <th width="40"></th>
    </tr>
  <?
	$res = sql($query);
	if(@mysql_num_rows($res))
	{
		$i=1;
		while($row = mysql_fetch_array($res))
		{
			$id = $row['id'];
			$articul = $row['articul'];
			?>
			<tr id="<?=$id?>">
			<th style="padding:0;"><input type="checkbox" name="check_del_[<?=$id?>]" id="check_del_<?=$id?>" /></th>
			<th style="padding:0;"><?=$i++?></th>
      <?
			if(in_array('img',$criteria))
			{
				if(file_exists($_SERVER['DOCUMENT_ROOT']."/uploads/goods/{$articul}.jpg")) 
					$src = "/goods/{$articul}.jpg";
				else
					$src = "/uploads/no_image.jpg";
				?>
				<th style="padding:3px 5px;">
					<a href="<?=$src?>" class="highslide" onclick="return hs.expand(this)">
					<img src="/goods/100x50/<?=$articul?>.jpg" align="absmiddle" />
					</a>
					<div class="highslide-caption"><b><?=$row['name']?></b></div>
				</td>
				<?
			}
			if(in_array('articul',$criteria))
			{
				?><td width="120" align="center"><?=$row['articul']?></td><?
			}
			if(in_array('name',$criteria))
			{
				?><td><a href="?red=<?=$id?>" class="link1"><?=$row['name']?></a></td><?
			}
			if(in_array('link',$criteria))
			{
				?><td align="center"><a href="/makers/<?=$row['mlink']?>/<?=$row['link']?>.htm" target="_blank" title="/makers/<?=$row['mlink']?>/<?=$row['link']?>.htm" style="color:#090"><img src="img/link.png" /></a></td><?
			}
			if(in_array('id_maker',$criteria))
			{
				?><td><a href="makers.php?red=<?=$id?>" class="link1"><?=getField("SELECT name FROM {$prx}makers WHERE id={$row['id_maker']}")?></a></td><?
			}
			for($n=1; $n<=10; $n++)
			{
				if(in_array("price{$n}",$criteria))
				{
					?><td nowrap width="60" align="right"><?=$row["price{$n}"]?></td><?
				}
			}
			if(in_array('da',$criteria))
			{
				?><td width="50" nowrap align="center"><?=isset($row['da'])?date('d.m.Y',strtotime($row['da'])):''?></td><?
			}
			if(in_array('status',$criteria))
			{
				?><td width="50" nowrap align="center"><?=btn_flag($row['status'],$id,'action=status&id=')?></td><?
			}
			if(in_array('id',$criteria))
			{
				?><th><?=$id?></th><?
			}
			?>
      <td nowrap align="center"><?=btn_edit($id)?></td>
			</tr>
			<?
		}		  
	}
	else
	{
		?>
    <tr>
      <td colspan="100" align="center">
      по вашему запросу ничего не найдено.<?=help('нет ни одной записи отвечающей критериям вашего запроса,<br>возможно вы установили неверные фильтры')?>
      </td>
    </tr>
    <?
	}	
	?>
  </table>
  </form>
  <?=popup_modul()?>
  <?
	$content = $subcontent.ob_get_clean();
}

require("tpl/tpl.php");
?>