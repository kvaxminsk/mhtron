<?
require('inc/common.php');

$rubric = "Тягачи";
$tbl = "avto";

$features1_name = array('Тип техники', 'Марка', 'Модель', 'Пробег', 'Год выпуска');
$features2_name = array('Цвет кузова', 'Тип двигателя', 'Объем двигателя', 'Мощность двигателя', 'Тип КПП', 'Тип подвески', 
'Тип тормозов', 'Колесная формула', 'Количество колес', 'Размер шин', 'Высота седла', 'Тип кабины', 'Экологичность двигателя', 
'Общее состояние', 'Цена', 'Дополнительная информация');


// -------------------СОХРАНЕНИЕ----------------------
if(isset($_GET["action"]))
{
	$id = (int)@$_GET['id'];
	
	switch($_GET['action'])
	{
		// ----------------- сохранение
		case "save":
			foreach($_POST as $key=>$val)
				$$key = clean($val);
			
			$features1 = serialize($_POST['features1']);
			$features2 = serialize($_POST['features2']);
			if($id = update($tbl,"features1='{$features1}',`features2`='{$features2}'",$id))
			{				
				// загружаем картинку
				for($i=1; $i<9; $i++)
					if($_FILES['img'.$i]['name'])
					{
						$path = $_SERVER['DOCUMENT_ROOT']."/uploads/{$tbl}/{$id}_{$i}.jpg";
						@move_uploaded_file($_FILES['img'.$i]['tmp_name'],$path);
						@chmod($path,0644);
					}
			}			
			?><script>top.location.href = "<?=$script?>?id=<?=$id?>";</script><?
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
		// ----------------- удаление банера
		case "del":
			// удаляем запись в БД
			update($tbl,'',$id);
			// удвляем файл
			for($i=1; $i<9; $i++)
			{
				@unlink($_SERVER['DOCUMENT_ROOT']."/uploads/{$tbl}/{$id}_{$i}.jpg");
				// мочим уменьшенные копии
				$mas_dir = get_dir_list($_SERVER['DOCUMENT_ROOT']."/uploads/{$tbl}/");
				if(sizeof($mas_dir))
					foreach($mas_dir as $dir)
						@unlink($dir."{$id}_{$i}.jpg");
			}
			?><script>top.location.href = "<?=$script?>";</script><?
		break;
		// ----------------- удаление нескольких записей
		case "multidel":
			foreach($_POST['check_del_'] as $k=>$v)
			{
				// удаляем запись в БД
				update($tbl,'',$k);
				// удвляем файл
				for($i=1; $i<9; $i++)
				{
					@unlink($_SERVER['DOCUMENT_ROOT']."/uploads/{$tbl}/{$k}_{$i}.jpg");
					// мочим уменьшенные копии
					$mas_dir = get_dir_list($_SERVER['DOCUMENT_ROOT']."/uploads/{$tbl}/");
					if(sizeof($mas_dir))
						foreach($mas_dir as $dir)
							@unlink($dir."{$k}_{$i}.jpg");
				}
			}
			?><script>top.location.href = "<?=$script?>";</script><?
		break;
		
		// ----------------- удаление изображения
		case "pic_del":
			$fname = clean($_GET['fname']);
			@unlink($_SERVER['DOCUMENT_ROOT']."/uploads/{$tbl}/{$fname}.jpg");
			// мочим уменьшенные копии
			$mas_dir = get_dir_list($_SERVER['DOCUMENT_ROOT']."/uploads/{$tbl}/");
			if(sizeof($mas_dir))
				foreach($mas_dir as $dir)
					@unlink($dir.$fname);
			?><script>top.location.href = "<?=$script?>?red=<?=$id?>";</script><?
		break;
		
	}
	exit;
}
// ------------------РЕДАКТИРОВАНИЕ--------------------
if(isset($_GET["red"]))
{
	$id = (int)@$_GET['red'];
	
	$rubric .= " &raquo; ".($id ? "Редактирование" : "Добавление");
	$page_title .= " :: ".$rubric;
	
	$row = gtv($tbl,'*',$id);
	
	ob_start();
	?>
  <form action="?action=save&id=<?=$id?>" method="post" enctype="multipart/form-data" target="ajax">
  <table width="100%" border="0" cellspacing="0" cellpadding="5" class="tab_red">
    <tr>
      <th class="tab_red_th"></th>
      <th>Характеристики</th>
      <td>
			<table>
			<?	$features1 = unserialize($row['features1']);
				foreach($features1_name as $name1) { ?>
					<tr>
						<th style="width:180px;"><?=$name1?></th>
						<td style="width:180px;"><?=show_pole('text',"features1[{$name1}]",$features1[$name1])?></td>
					</tr>
			<?	}	?>
			</table>			
		</td>
    </tr>
    <tr>
      <th class="tab_red_th"></th>
      <th>Дополнительные<br>характеристики</th>
      <td>
			<table>
			<?	$features2 = unserialize($row['features2']);
				foreach($features2_name as $name2) { ?>
					<tr>
						<th style="width:180px;"><?=$name2?></th>
						<td style="width:180px;"><?=show_pole('text',"features2[{$name2}]",$features2[$name2])?></td>
					</tr>
			<?	}	?>
			</table>			
		</td>
    </tr>
	 <?	for($i=1; $i<9; $i++)
				echo show_tr_img('img'.$i,	"/uploads/{$tbl}/", "{$id}_{$i}.jpg",	$script."?action=pic_del&id={$id}&fname={$id}_{$i}");	?>
    <tr>
      <th class="tab_red_th"></th>
		<td></td>
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
	
	$res = sql("SELECT * FROM {$prx}{$tbl} ORDER BY sort,id");
	if(mysql_num_rows($res))
	{
		?>
		<form action="?action=multidel" name="red_frm" method="post" enctype="multipart/form-data" style="margin:0;" target="ajax">
		<input type="hidden" id="cur_id" value="<?=@$_GET['id']?@(int)$_GET['id']:""?>" />
    <table width="100%" border="1" cellspacing="0" cellpadding="0" class="tab1">
		  <tr>
        <th><input type="checkbox" name="check_del" id="check_del" onclick="check_uncheck('check_del')" /></th>
        <th>№</th>
        <th>Изображение</th>
        <th>Характеристики</th>
        <th nowrap>Порядок <?=help('параметр с помощью которого можно изменить порядок вывода элемента в клиентской части сайта')?></th>
        <th style="padding:0 30px;"></th>
		  </tr>
		<?
		$i=1;
		while($row = mysql_fetch_array($res))
		{
			$id = $row['id'];
			?>
			<tr id="<?=$row['id']?>">
			  <th><input type="checkbox" name="check_del_[<?=$row['id']?>]" id="check_del_<?=$row['id']?>" /></th>
			  <th nowrap><?=$i++?></th>
			  <td align="center">
				<a href="/uploads/avto/<?=$id?>_1.jpg" class="highslide" onclick="return hs.expand(this)">
						<img src="/avto/100x50/<?=$id?>_1.jpg" align="absmiddle" />
				</a>
        	</td>
        <td>
		  		<table width="100%">
			  <?	$features1 = unserialize($row['features1']);
					foreach($features1 as $key=>$val)
						if($val)
						{	?>
							<tr>
								<th align="left" style="border:none;"><?=$key?></th>
								<td align="right" style="border:none;"><?=$val?></td>
							</tr>
					<?	}	?>		  
				</table>
		  </td>
        <td nowrap align="center"><?=btn_sort($row['id'])?></td>
			  <td nowrap align="center"><?=btn_edit($row['id'])?></td>
			</tr>
			<?
		}
		?>
		</table>
		</form>
		<?
	}
	$content = $subcontent.ob_get_clean();
}

require("tpl/tpl.php");
?>