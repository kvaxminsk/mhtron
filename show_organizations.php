<?
require('inc/common.php');



if(!$_SESSION['user']['manager'])
{
	header("Location: /");
	exit;
}

if(isset($_GET["action"]))
{
	switch($_GET["action"])
	{
		case "save":
			$organizations = array();
			foreach($_POST['org'] as $i=>$val)
				if($val)
				{
					$organizations[$val] = str_replace(array("'", '"'), '', $_POST['rekv'][$i]);
				}
			$_SESSION['user']['organizations'] = $organizations = serialize($organizations);
			
			$id = update("users","organizations='{$organizations}'",$_SESSION['user']['id']);
						
			errorAlertClient('show','alert','Уважаемый(ая) '.$_SESSION['user']['name'].' '.$_SESSION['user']['surname'].',<br>Данные успешно сохранены.',false);	
		?>	<script> top.topReload(); </script><?
			break;
	}	
	exit;
}

$user = getRow("SELECT * FROM {$prx}users WHERE id=".$_SESSION['user']['id']);

$title = 'Профиль &raquo; Редактирование данных';

ob_start();

?>
<h1>Организации</h1>
<div id="prof_str">
<a href="/profile/" class="link">Изменить данные</a> / <a href="/cart/" class="link">Заказы</a> / <a href="/messages/" class="link">Сообщения</a> / <span>Организации</span>
</div>

<style>
#reg_frm span
{
	font:normal 16px Georgia, "Times New Roman", Times, serif;
	font-style:italic;
}
</style>

<a href="javascript://" onClick="$('#reg_frm table:hidden:first').fadeIn();">Добавить организацию</a>

<form id="reg_frm" action="?action=save" method="post" target="ajax" style="margin:20px 0">
<?	$i = 0;
	$arr = unserialize($user['organizations']);
	if($user['organizations'])	
	foreach((array)$arr as $key=>$val)
	{	?>
		<table width="50%" style="margin:0 0 30px 0">
		  <tr>
			<td width="150"><span>Организация:</span></td>
			<td><input name="org[<?=$i?>]" class="pole2" value="<?=$key?>"></td>
		  </tr>
		  <tr>
			<td colspan="2"><span><b>Реквизиты</b></span></td>
		  </tr>
		  <tr>
			<td colspan="2">
				<textarea name="rekv[<?=$i?>]" style="width:100%;" rows="7"><?=$val?></textarea>
			</td>
			</tr>
		</table>
	<?	$i++;
	}
	
	for($i; $i<10; $i++)
	{	?>
		<table width="50%" style="margin:0 0 30px 0; <?=$i ? 'display:none;' : ''?>">
		  <tr>
			<td width="150"><span>Организация:</span></td>
			<td><input name="org[<?=$i?>]" class="pole2" value=""></td>
		  </tr>
		  <tr>
			<td colspan="2"><span><b>Реквизиты</b></span></td>
		  </tr>
		  <tr>
			<td colspan="2">
				<textarea name="rekv[<?=$i?>]"  style="width:100%;" rows="7">ИНН: 
КПП: 
БИК: 
БАНК: 
Р/С: 
К/С: 

Юридический адрес
Индекс: 
Город: 
Адрес: 

Фактический адрес
Индекс: 
Город: 
Адрес: </textarea>
			</td>
			</tr>
		</table>
<?	}	?>
	<div align="right" style="width:50%;">
		<input type="image" src="/img/btn_save.png" width="121" height="24">
	</div>
</form>
<?

$content = ob_get_clean();

require('tpl/template.php');
?>
