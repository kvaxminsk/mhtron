<?
require('inc/common.php');

$rubric = 'Производители';
$tbl = 'kpp_cat';

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
			
			$id = update($tbl,"id_parent='{$id_parent}', name='{$name}',text='{$text}'",$id);

			// загружаем картинку
			if($_FILES['img']['name'])
			{
				remove_kpp_good($id,true,"{$id}.jpg");
				$path = $_SERVER['DOCUMENT_ROOT']."/uploads/{$tbl}/{$id}.jpg";
				@move_uploaded_file($_FILES['img']['tmp_name'],$path);
				@chmod($path,0644);
			}
			
			?><script>top.location.href = "<?=$script?>?id=<?=$id?>";</script><?			
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

		// ----------------- сортировка вниз
		case "moveup":
			sort_moveup($tbl,$id);
			?><script>top.location.href = "<?=$script?>?id=<?=$id?>";</script><?
			break;
		// ----------------- сортировка вниз
		case "movedown":
			sort_movedown($tbl,$id);
			?><script>top.location.href = "<?=$script?>?id=<?=$id?>";</script><?
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
  <form action="?action=save&id=<?=$id?>" method="post" target="ajax" enctype="multipart/form-data">
  <table width="100%" border="0" cellspacing="0" cellpadding="5" class="tab_red">
      <tr>
      	<th class="tab_red_th"></th>
      	<th>Производитель</th>
        <td><?=dll("SELECT id,name FROM {$prx}kpp_cat WHERE id_parent='0' AND id<>'{$id}' ORDER BY sort,id",'name="id_parent"',$row['id_parent'],'')?></td>
      </tr>
    <tr>
      <th class="tab_red_th"></th>
			<th>Название</th>
			<td><?=show_pole('text','name',htmlspecialchars($row['name']))?></td>
    </tr>
      <?=show_tr_img(	'img',
	  									"/uploads/{$tbl}/",
											"{$row['id']}.jpg",
	  									$script."?action=pic_del&id={$id}&fname={$row['id']}.jpg",'Изображение','Для разделов первого уровня (производителей)')?>
    <tr>
			<th class="tab_red_th"><?=help('Для подразделов')?></th>
			<th>Описание</th>
			<td><?=showFck('text',$row['text'],'Medium','100%',200);?></td>
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
	
	$mas = getTree("SELECT * FROM {$prx}{$tbl} WHERE id_parent='%s' ORDER BY sort,id");
	if(sizeof($mas))
	{
	?>
  <form action="?action=multidel" name="red_frm" method="post" target="ajax">
  <input type="hidden" id="cur_id" value="<?=@$_GET['id']?@(int)$_GET['id']:""?>" />
  <table width="100%" border="1" cellspacing="0" cellpadding="0" class="tab1">
    <tr>
      <th><input type="checkbox" name="check_del" id="check_del" /></th>
      <th>№</th>
		<th width="100">Изображение</th>
      <th width="100%"><?=ShowSortPole($script,$cur_pole,$cur_sort,'Название','name');?></th>
      <th nowrap>Кол-во товаров / Артикулей</th>
       <th nowrap>Порядок <?=help('параметр с помощью которого можно изменить порядок вывода элемента в клиентской части сайта')?></th>
      <th style="padding:0 30px;"></th>
    </tr>
  <?
		$i=1;
		foreach($mas as $vetka)
		{
			$row = $vetka['row'];
			$level = $vetka["level"];
			
			$prfx = $prefix===NULL ? getPrefix($level) : str_repeat($prefix, $level);
			$id = $row['id'];
			  ?>
			  <tr id="<?=$id?>">
			  <th><input type="checkbox" name="check_del_[<?=$id?>]" id="check_del_<?=$id?>" /></th>
        <th nowrap><?=$i++?></th>
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
		  <td><?=$prfx?><a href="?red=<?=$id?>" class="link1"><?=$row['name']?></a></td>
        <td align="center">
          <?
			 $res1 = sql("SELECT * FROM {$prx}kpp_goods WHERE id_kpp_cat={$id}");
			 if(mysql_num_rows($res1))
          {
				 $ca = 0;
				while($row1 = mysql_fetch_assoc($res1))
				{
					$articuls = $row1['articuls'] ? unserialize($row1['articuls']) : array();
					$ca += count($articuls);
				}	?>
            <a href="" style="color:#090" onClick="RegSessionSort('kpp_goods.php','id_kpp_cat=<?=$id?>');return false;"><?=mysql_num_rows($res1)?> / <?=$ca?></a><?
          }
          else
            echo '0 / 0';
          ?>
        </td>
        <td nowrap align="center"><?=btn_sort($row['id'])?></td>
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