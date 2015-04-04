<?
require('inc/common.php');

$rubric = "Баннеры";
$tbl = "banners";

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
			
			if($id = update($tbl,"link='{$link}',`top`={$top},`right`={$right}",$id))
			{				
				// загружаем картинку для рубрики
				if($_FILES['img']['name'])
				{				
					// расширение файла
					$fe = getFileExtension(basename($_FILES['img']['name']));
					
					// проверка
					if($fe!='jpg' && $fe!='gif' && $fe!='png' && $fe!='swf')
						errorAlert('Нарушение требований к баннеру!\n(см. примечание)');
					
					// мочим старый
					@unlink($_SERVER['DOCUMENT_ROOT']."/uploads/{$tbl}/{$id}.{$fe}");
					
					$path = $_SERVER['DOCUMENT_ROOT']."/uploads/{$tbl}/{$id}.{$fe}";
					if(@move_uploaded_file($_FILES['img']['tmp_name'],$path))
						chmod($path,0644);
					else
					{
						update($tbl,'',$id);
						errorAlert('не удалось загрузить файл!');
					}
				}
			}			
			?><script>top.location.href = "<?=$script?>?id=<?=$id?>";</script><?
			break;
		// ----------------- обновление статуса
		case "top":
		case "right":
			update_flag($tbl,'`'.$_GET['action'].'`',$id);
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
		case "swf_del":
		case "del":
			// удаляем запись в БД
			update($tbl,'',$id);
			// удвляем файл
			$fe = getFileFormat($_SERVER['DOCUMENT_ROOT']."/uploads/{$tbl}/{$id}.*");
			@unlink($_SERVER['DOCUMENT_ROOT']."/uploads/{$tbl}/{$id}.{$fe}");
			?><script>top.location.href = "<?=$script?>";</script><?
		break;
		// ----------------- удаление нескольких записей
		case "multidel":
			foreach($_POST['check_del_'] as $k=>$v)
			{
				// удаляем запись в БД
				update($tbl,'',$k);
				// удвляем файл
				$fe = getFileFormat($_SERVER['DOCUMENT_ROOT']."/uploads/{$tbl}/{$k}.*");
				@unlink($_SERVER['DOCUMENT_ROOT']."/uploads/{$tbl}/{$k}.{$fe}");
			}
			?><script>top.location.href = "<?=$script?>";</script><?
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
      <th>Баннер</th>
      <td align="left">
        <table border="0" cellspacing="0" cellpadding="0">
          <tr>
            <td style="border:none;"><input type="file" size="30" name="img" /></td>
            <td align="left" style="border:none;">
              <?
              $fe = getFileFormat($_SERVER['DOCUMENT_ROOT']."/uploads/{$tbl}/{$id}.*");
              if($fe)
              {
                ?>
                <table border="0" cellspacing="0" cellpadding="0">
                  <tr>
                    <td style="padding:0 5px 0 20px; border:none;">
                      <?
                      if($fe=='swf')
                      {
                        ?>
                        <a href="/<?=$tbl?>/<?=$id?>.<?=$fe?>" target="_blank">
                        <?=flash("/{$tbl}/{$id}.swf",'width="20" height="20"')?>
                        </a>
                        <?
                      }
                      else
                      {
                        ?>
                        <a href="/<?=$tbl?>/<?=$id?>.<?=$fe?>" class="highslide" onclick="return hs.expand(this)">
                        <img src="/<?=$tbl?>/<?=$id?>.<?=$fe?>" width="20" height="20">
                        </a>
                        <?
                      }
                      ?>
                    </td>
                    <td style="padding:0 0 0 5px; border:none;">
                      <a href="<?=$script?>?action=swf_del&id=<?=$id?>" target="ajax" style="border:none;">
                      <img src="img/del_pic.png" width="20" height="20" border="0" />
                      </a>
                    </td>
                  </tr>
                </table>
                <?
              }
              ?>
            </td>
          </tr>
        </table>
      </td>
    </tr>	
    <tr>
      <th>Ссылка <?=help('Flash баннеру ссылка не нужна')?></th>
      <td><?=show_pole('text','link',$row['link'])?></td>
    </tr>
    <tr>
      <th>Под производителями</th>
      <td><?=dll(array('0'=>'нет','1'=>'да'),' name="top"',isset($row['top'])?$row['top']:0)?></td>
    </tr>
    <tr>
      <th>Справа</th>
      <td><?=dll(array('0'=>'нет','1'=>'да'),' name="right"',isset($row['right'])?$row['right']:0)?></td>
    </tr>
    <tr>
      <th class="tab_red_th"></th>
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
        <th width="50%">Баннер</th>
        <th width="50%">Ссылка</th>
        <th nowrap>Размер <?=help('ширина x высота (в пикселях)')?></th>
        <th nowrap>Под производителями</th>
        <th nowrap>Справа</th>
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
			  	<?
					if($fe = getFileFormat($_SERVER['DOCUMENT_ROOT']."/uploads/{$tbl}/{$id}.*"))
					{
						$size = getimagesize($_SERVER['DOCUMENT_ROOT']."/uploads/{$tbl}/{$id}.{$fe}");
						
						if($fe=='swf')
							echo flash("/{$tbl}/{$id}.swf",'height="100"');
						else
							echo "<img src=\"/{$tbl}/{$id}.{$fe}\" width=\"80\">";
					}
					?>
        </td>
        <td width="100%"><a href="<?=$row['link']?>" class="green_link" target="_blank"><?=$row['link']?></a></td>
        <td nowrap align="center">
					<?
					if($fe)
					{
						$size = getimagesize($_SERVER['DOCUMENT_ROOT']."/uploads/{$tbl}/{$id}.{$fe}");
						echo $size[0].' x '.$size[1];
					}
					?>
        </td>
        <td nowrap align="center"><?=btn_flag($row['top'],$row['id'],'action=top&id=')?></td>
        <td nowrap align="center"><?=btn_flag($row['right'],$row['id'],'action=right&id=')?></td>
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