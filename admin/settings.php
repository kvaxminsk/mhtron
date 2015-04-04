<?
require('inc/common.php');

$rubric = "Настройки";
$tbl = "settings";

// ------------------- СОХРАНЕНИЕ ------------------------
if(isset($_GET["action"]))
{	
	$id = clean(@$_GET['id']);
	$type = clean(@$_GET['type']);

	switch(@$_GET['action'])
	{
		// ----------------- сохранение
		case "save":
			foreach($_POST as $id=>$value)
			{
				$value = clean($value);
				update($tbl,"value='{$value}'",$id);
			}
			foreach($_FILES as $id=>$file)
			{
				@move_uploaded_file($file['tmp_name'],$_SERVER['DOCUMENT_ROOT']."/uploads/{$tbl}/{$id}.jpg");
				@chmod($_SERVER['DOCUMENT_ROOT']."/uploads/{$tbl}/{$id}.jpg",0644);
				
				$value = basename($file);
				update($tbl,"value='{$value}'",$id);
			}
			
			errorAlert('Изменения успешно сохранены.');
			break;
		// ----------------- сортировка вверх
		case "moveup":
			sort_moveup($tbl,$id,"flag='{$type}'");
			?><script>top.location.href='<?=$script?>?type=<?=$type?>&id=<?=$id?>';</script><?		
			break;
		// ----------------- сортировка вниз
		case "movedown":
			sort_movedown($tbl,$id,"flag='{$type}'");
			?><script>top.location.href='<?=$script?>?type=<?=$type?>&id=<?=$id?>';</script><?			
			break;
		// ----------------- удаление картинки
		case "pic_del":
			$flag = getField("SELECT flag FROM {$prx}{$tbl} WHERE id='{$id}'");	
			if($flag)
			{
				@unlink($_SERVER['DOCUMENT_ROOT']."/uploads/{$tbl}/{$id}.jpg");
				?><script>top.location.href='<?=$script?>?type=<?=$flag?>&id=<?=$id?>';</script><?
			}			
			break;
	}
	
	exit;
}

// ----------------- РЕДАКТИРОВАНИЕ / ПРОСМОТР -------------------
ob_start();

$page_title .= " :: ".$rubric;
	
$razdel = array("Клиентская часть"=>"?type=client","Административная часть"=>"?type=admin");
$subcontent = show_subcontent($razdel);
	
$type = clean(@$_GET['type']);
if(in_array($type,array('client','admin')))
{
	if($type=='client')
		$rubric .= " &raquo; Клиентская часть";
	else
		$rubric .= " &raquo; Административная часть";
	
	$query = "SELECT * FROM {$prx}{$tbl} WHERE flag='{$type}' ORDER BY sort";
	$res = mysql_query($query);
	if(@mysql_num_rows($res))
	{
		?>
    <style>
		.tab_red th span { color:#090; }
		</style>
		<form action="?action=save" name="frm" method="post" enctype="multipart/form-data" style="margin:10px 0 0 0;" target="ajax">
		<input type="hidden" id="cur_id" value="<?=@$_GET['id']?clean($_GET['id']):""?>" />
		<input type="hidden" name="type" value="<?=$type?>" />
		<table width="100%" border="0" cellspacing="0" cellpadding="5" class="tab_red">
		<?
		while($row = mysql_fetch_array($res))
		{
			?>
			<tr id="<?=$row['id']?>">
      	<th class="tab_red_th"><?=$row['help']?help($row['help']):''?></th>
			  <th align="left" nowrap><?=$row['name']?></th>
			  <td width="100%">
				<?	
				switch($row['type'])
				{
					case "text":
					case "password":
					case "checkbox":
					case "textarea":
					case "datetime":
					case "date":												
						echo show_pole($row['type'],$row['id'],$row['type']=='text'?htmlspecialchars($row['value']):$row['value']);
						break;

					case "fck":
						echo showFck($row['id'],$row['value'],"Medium","100%",180);	
						break;

					case "color":
						echo show_pole("color",$row['id'],$row['value']);
						break;
						
					case "file":
						echo show_pole('file',$row['id'],'');	
						break;
				}	
				?>
			  </td>
			  <td nowrap align="center"><?=btn_sort($row['id'],"&type={$type}")?></td>
			</tr>
			<?
		}
		?>
		<tr>
		  <th></th>
		  <td colspan="3" align="center">
			  <input type="button" value="Сохранить" class="but1" onclick="loader(true);check_settings_frm();" />&nbsp;
			  <input type="button" value="Отмена" class="but1" onclick="top.location.href='<?=$script?>'" />
		  </td>
		</tr>
		</table>
		</form>
		<?
	}
}
$content = $subcontent.ob_get_clean();

require("tpl/tpl.php");
?>