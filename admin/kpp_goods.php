<?
require('inc/common.php');

$rubric = "Товары";
$tbl = "kpp_goods";

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
			
			$articuls = array();
			foreach($_POST['articul'] as $key=>$val)
				if($val)
					$articuls[] = array('articul'=>$val, 'coords'=>$_POST['coords'][$key]);
			$articuls = cleanArr($articuls);
			
			$id = update($tbl, "name='{$name}', id_kpp_cat='{$id_kpp_cat}', articuls='{$articuls}', status='{$status}'", $id);
			// загружаем картинку
			if($_FILES['img']['name'])
			{
				remove_kpp_good($id,true,"{$id}.jpg");
				$path = $_SERVER['DOCUMENT_ROOT']."/uploads/{$tbl}/{$id}.jpg";
				@move_uploaded_file($_FILES['img']['tmp_name'],$path);
				@chmod($path,0644);
			}
			if($kpp_reload) { 
				?><script> top.location.href = "?red=<?=$id?>&rand=<?=mt_rand()?>"; </script><?
			} else { 
				?><script>top.location.href = "<?=$script?>?id=<?=$id?>";</script><?
			}
		break;
		// ----------------- обновление статуса
		case "status":
			update_flag($tbl,$_GET['action'],$id);					
		break;
		// ----------------- удаление изображения
		case "pic_del":
			$fname = clean($_GET['fname']);
			remove_kpp_good($id,true,$fname);
			?><script>top.location.href = "<?=$script?>?red=<?=$id?>";</script><?
		break;
		// ----------------- удаление одной записи
		case "del":
			remove_kpp_good($id);
			?><script>top.location.href = "<?=$script?>";</script><?
		break;
		// ----------------- удаление нескольких записей
		case "multidel":
			foreach($_POST['check_del_'] as $k=>$v)
				remove_kpp_good($k);
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

		// ----------------- сортировка вниз
		case "moveup":
			$id_kpp_cat = getField("SELECT id_kpp_cat FROM {$prx}{$tbl} WHERE id='{$id}'");
			sort_moveup($tbl,$id, "id_kpp_cat='{$id_kpp_cat}'");
			?><script>top.location.href = "<?=$script?>?id=<?=$id?>";</script><?
			break;
		// ----------------- сортировка вниз
		case "movedown":
			$id_kpp_cat = getField("SELECT id_kpp_cat FROM {$prx}{$tbl} WHERE id='{$id}'");
			sort_movedown($tbl,$id, "id_kpp_cat='{$id_kpp_cat}'");
			?><script>top.location.href = "<?=$script?>?id=<?=$id?>";</script><?
			break;
			
	}
	exit();
}
// ------------------ РЕДАКТИРОВАНИЕ --------------------
elseif(isset($_GET["red"]))
{
	$id = @$_GET['red'] ? (int)@$_GET['red'] : 0;
	
	$row = getRow("SELECT * FROM {$prx}{$tbl} WHERE id={$id}");
	
	$rubric .= " &raquo; ".($id ? "Редактирование" : "Добавление");
	$page_title .= " :: ".$rubric;
	
	ob_start();
	?>
    <form id="edit_frm" action="?action=save&id=<?=$id?>" method="post" enctype="multipart/form-data" target="ajax">
    <table width="100%" border="0" cellspacing="0" cellpadding="5" class="tab_red">
      <tr>
      	<th class="tab_red_th"></th>
      	<th>Каталог</th>
        <td><?=dllTree("SELECT id,name FROM {$prx}kpp_cat WHERE id_parent='%s' ORDER BY sort,id", 'name="id_kpp_cat" style="width:100%"', $row['id_kpp_cat'], array('0',''))?></td>
      </tr>
      <tr>
        <th class="tab_red_th"></th>
        <th>Название</th>
        <td><?=show_pole('text','name',htmlspecialchars($row['name']))?></td>
      </tr>
      <?=show_tr_img(	'img',
	  									"/uploads/{$tbl}/",
											"{$row['id']}.jpg",
	  									$script."?action=pic_del&id={$id}&fname={$row['id']}.jpg")?>
      <tr>
      	<th class="tab_red_th"></th>
        <th>Артикулы</th>
        <td>
		  		<table>
					<tr valign="top">
						<td>
							<input id="kpp_reload" name="kpp_reload" type="hidden">
							<div style="padding:10px;"><a href="javascript://" onClick="$('#kpp_reload').val('1'); $(this).parents('form:first').submit();">обновить изображение</a></div>
							<img src="/<?=$tbl?>/700x-/<?=$id?>.jpg" id="kpp_img">
							<script type="text/javascript">
								var ias;
								$(document).ready(function () {
									 ias = $('#kpp_img').imgAreaSelect({
										 instance: true,
										  onSelectEnd:function (img, selection) {
											  $('input[name="kpp_r"]:checked:first').parent().parent().find('input:eq(2)').val(selection.x1+','+selection.y1+','+selection.x2+','+selection.y2);
									     }
									 });
								});
							</script>
						</td>
						<td>
							<script>
								function setKppImg(obj)
								{
									var tr = $(obj).parent().parent();
									tr.find('input:eq(0)').attr('checked', true);
									var coords = tr.find('input:eq(2)');
								
									if(coords.val() != '')
									{
										var arr = explode(',', coords.val());
										ias.setSelection(arr[0], arr[1], arr[2], arr[3], true); 
										ias.setOptions({ show: true }); 
									}
									else
										ias.cancelSelection();
									ias.update(); 	
								}
								
								function addKppArt()
								{
									$('#kpp_tbl tr:last').after('<tr><td><input type="radio" name="kpp_r" checked onFocus="setKppImg(this)"></td><td><input name="articul[]" onFocus="setKppImg(this)"></td><td><input name="coords[]" onFocus="setKppImg(this)"></td><td><img src="/admin/img/del.png" onClick="if(confirm(\'Уверены?\')) $(this).parent().parent().remove();"></td></tr>');
									ias.cancelSelection();
									ias.update(); 	
								}
							</script>
							<div style="padding:10px;"><a href="javascript:addKppArt()">добавить артикул</a></div>
							<table id="kpp_tbl">
								<tr>
									<td>&nbsp;</td>
									<th>Артикул</th>
									<th>Область</th>
									<td>&nbsp;</td>
								</tr>
							<?	$articuls = unserialize($row['articuls']);
								if($row['articuls'] && count($articuls))
								{
									foreach((array)$articuls as $articul)
									{	?>
										<tr>
											<td><input type="radio" name="kpp_r" onFocus="setKppImg(this)"></td>
											<td><input name="articul[]" value="<?=$articul['articul']?>" onFocus="setKppImg(this)"></td>
											<td><input name="coords[]" value="<?=$articul['coords']?>" onFocus="setKppImg(this)"></td>
											<td><img src="/admin/img/del.png" onClick="if(confirm('Уверены?')) $(this).parent().parent().remove();"></td>
										</tr>
								<?	}
								}
								else
								{	?>
									<script> addKppArt(); </script>
							<?	}	?>
							</table>
						</td>
					</tr>
				</table>			
			</td>
      </tr>
      <tr>
      	<th class="tab_red_th"></th>
        <th>Статус</th>
        <td><?=dll(array('0'=>'заблокировано','1'=>'активно'),' name="status"',isset($row['status'])?$row['status']:1)?></td>
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
	$cur_page = @$_SESSION['page'] ? @(int)$_SESSION['page'] : 1;
	
	$f_kpp_cat = 	(int)@$_SESSION['f_kpp_cat'];
	
	$where = '';
	if($f_kpp_cat)		$where .= " and id_kpp_cat={$f_kpp_cat} ";

	$page_title .= " :: ".$rubric;
	
	$razdel = array("Добавить"=>"?red=0",
									"Удалить"=>"javascript:multidel(document.red_frm,'check_del_');"
									);
	$subcontent = show_subcontent($razdel);
	
	if($f_kpp_cat)
		$rubric .= " &raquo; Общий список &raquo; ".getField("SELECT name FROM {$prx}kpp_cat WHERE id={$f_kpp_cat}");
	else
		$rubric .= " &raquo; Общий список";
		
	$query = "SELECT * FROM {$prx}{$tbl} WHERE 1{$where}";
	
	$count_obj = getField(str_replace('*','COUNT(*)',$query)); // кол-во объектов в базе
	$count_obj_on_page = 20; // кол-во объектов на странице
	$kol_str = ceil($count_obj/$count_obj_on_page); // количество страниц

	$query .= " ORDER BY sort,id";
		
	$query .= " LIMIT ".($count_obj_on_page*$cur_page-$count_obj_on_page).",".$count_obj_on_page;
	//-----------------------------
	//echo $query;
	
	ob_start();
	
	show_filters($script);
	show_navigate_pages($kol_str,$cur_page,$script);
	
	?>
  <table class="filter_tab" style="margin:5px 0 0 0;">
    <tr>
      <td align="left">Каталог</td>
      <td colspan="2"><?=dllTree("SELECT id,name FROM {$prx}kpp_cat WHERE id_parent='%s' ORDER BY sort,id",'onChange="RegSessionSort(\''.$script.'\',\'id_kpp_cat=\'+this.value);return false;"',$f_kpp_cat,array('remove','-- все --'))?></td>
    </tr>
  </table>

  <form action="?action=multidel" name="red_frm" method="post" target="ajax">
  <input type="hidden" id="cur_id" value="<?=@$_GET['id']?@(int)$_GET['id']:""?>" />
  <table width="100%" border="1" cellspacing="0" cellpadding="0" class="tab1">
    <tr>
      <th width="20" style="padding:0px;"><input type="checkbox" name="check_del" id="check_del" /></th>
      <th width="20" style="padding:0px;">№</th>
      <?
			?><th width="100">Изображение</th><?
			?><th>Наименование</th><?
			?><th>Артикулы</th><?
			?><th nowrap>Статус <?=help('заблокирован данный товар или нет')?></th>
		<?	if($f_kpp_cat) { ?>
	       <th nowrap>Порядок <?=help('параметр с помощью которого можно изменить порядок вывода элемента в клиентской части сайта')?></th>
		<?	}	?>
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
			$articuls = unserialize($row['articuls']);
			?>
			<tr id="<?=$id?>">
			<th style="padding:0;"><input type="checkbox" name="check_del_[<?=$id?>]" id="check_del_<?=$id?>" /></th>
			<th style="padding:0;"><?=$i++?></th>
      <?
			if(file_exists($_SERVER['DOCUMENT_ROOT']."/uploads/{$tbl}/{$id}.jpg")) 
				$src = "/{$tbl}/{$id}.jpg";
			else
				$src = "/uploads/no_image.jpg";
			?>
			<th style="padding:3px 5px;">
				<a href="<?=$src?>" class="highslide" onclick="return hs.expand(this)">
				<img src="/<?=$tbl?>/100x50/<?=$id?>.jpg" align="absmiddle" />
				</a>
				<div class="highslide-caption"><b><?=$row['name']?></b></div>
			</th>
			<?
			?><td><a href="?red=<?=$id?>" class="link1"><?=$row['name']?></a></td><?
			?><td>
			<?	foreach($articuls as $articul)
					echo $articul['articul'].'; '; ?>
			</td><?
			?><td width="50" nowrap align="center"><?=btn_flag($row['status'],$id,'action=status&id=')?></td>
		<?	if($f_kpp_cat) { ?>
        <td nowrap align="center"><?=btn_sort($row['id'])?></td>
		 <?	}	?>
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
  <?
	$content = $subcontent.ob_get_clean();
}

require("tpl/tpl.php");
?>