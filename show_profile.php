<?
require('inc/common.php');

if(!isset($_SESSION['user']))
{
	header("Location: /");
	exit;
}

if(isset($_GET["action"]))
{
	switch($_GET["action"])
	{
		case "save":
		
			$mas_fields = array('login','pass','org','director','director_type','family','name','surname','phone','fax','mail','inn','kpp','bik','bank','rs','ks',
								'address_ur','address_fact','okpo','dostavka');
			//'index_ur','city_ur','index_fact','city_fact',
			// проверка полей
			foreach($mas_fields as $field)
			{
				$$field = clean(@$_POST['user'][$field],true);
				if(!$$field && $field!='director_type')
					errorAlertClient('show','alert','<center>Заполните пожалуйста все поля</center>');
			}
				
			if(getField("SELECT login FROM {$prx}users WHERE login='{$login}' and id<>".$_SESSION['user']['id']))
				errorAlertClient('show','alert','<center>Пользователь с таким логином уже существует</center>');
			
			if(!check_mail($mail))
				errorAlertClient('show','alert','Вы ввели неверный e-mail !<br>Пожалуйста повторите попытку.');
				
			$query = '';			
			foreach($mas_fields as $field)
				$query .= ($query?',':'')."{$field}='{$$field}'";
			
			$id = update("users",$query,$_SESSION['user']['id']);
			
			if($id)
			{
				// если пользователь изменил логин или пароль
				// отправляем письмо пользователю
				if($login!=$_SESSION['user']['login'] || $pass!=$_SESSION['user']['pass'])
				{
					$admin_mail = set('admin_email');
					$user = $name.' '.$surname;
					$user_mail = $mail;
					$tema  = "Изменение профиля на сайте {$_SERVER['SERVER_NAME']}";
					$text  = "Уважаемый <b>{$user}</b>,<br><br>Вы изменили свой профиль на сайте <a href='http://{$_SERVER['SERVER_NAME']}'>{$_SERVER['SERVER_NAME']}</a>.<br>";
					$text .= "Ваш логин: <b>{$login}</b><br>";
					$text .= "Ваш пароль: <b>{$pass}</b>";
					
					mailTo($user_mail,$tema,$text,$admin_mail);
				}	
				errorAlertClient('show','alert','Уважаемый(ая) '.$name.' '.$surname.',<br>Данные успешно сохранены.');	
			}
			else
				errorAlertClient('show','alert','Уважаемый(ая) '.$name.' '.$surname.',<br>во время сохранения данных произошла ошибка!<br>Приносим свои извинения.<br>В ближайшее время проблема будет устранена.');			
			break;
	}	
	exit;
}

$userRow = getRow("SELECT * FROM {$prx}users WHERE id=".$_SESSION['user']['id']);

function input($field,$type='text')
{
	global $userRow;
	ob_start();
	?><input type="<?=$type?>" name="user[<?=$field?>]" class="pole2" value="<?=$userRow[$field]?>"><?
	return ob_get_clean();
}

$title = 'Профиль &raquo; Редактирование данных';

ob_start();

?>
<h1>Профиль</h1>
<?=set('profile_text')?>
<div id="prof_str">
<span>Изменить данные</span> / <a href="/cart/" class="link">Заказы</a> / <a href="/messages/" class="link">Сообщения</a>
<? if($_SESSION['user']['manager']) { ?> / <a href="/organizations/" class="link">Организации</a><? } ?>
</div>

<style>
#reg_frm span
{
	font:normal 16px Georgia, "Times New Roman", Times, serif;
	font-style:italic;
}
</style>

<form id="reg_frm" action="?action=save" method="post" target="ajax" style="margin:20px 0">
<table style="margin-bottom:30px">
  <tr>
	<td width="65"><span>Логин:</span></td>
	<td width="200"><?=input('login')?></td>
	<td style="padding:0 10px 0 50px"><span>Пароль:</span></td>
	<td width="200"><?=input('pass','password')?></td>
  </tr>
</table>
<table width="100%" style="margin:0 0 30px 0">
  <tr>
	 <td><span>Организация:</span></td>
	 <td colspan="2" width="100%"><?=input('org')?></td>
  </tr>
  <tr><td height="6"><div style="width:150px;"></div></td></tr>
  <tr>
	 <td width="150"><span>Директор:</span></td>
	 <td><?=dll(array('генеральный'=>'генеральный'), 'name="user[director_type]"', $userRow['director_type'], '')?></td>
	 <td width="100%"><?=input('director')?></td>
  </tr>
</table>
<table width="100%">
  <tr>
	<td colspan="2" width="50%" style="padding-bottom:10px"><span><b>Контактное лицо</b></span></td>
	<td colspan="2" style="padding:0 0 10px 90px"><span><b>Контактные данные</b></span></td>
  </tr>
  <tr>
	<td height="25" style="padding-right:50px"><span>Фамилия:</span></td>
	<td width="50%"><?=input('family')?></td>
	<td style="padding:0 50px 0 90px"><span>Телефон:</span></td>
	<td width="50%"><?=input('phone')?></td>
  </tr>
  <tr>
	<td height="25"><span>Имя:</span></td>
	<td><?=input('name')?></td>
	<td style="padding-left:90px"><span>Факс:</span></td>
	<td><?=input('fax')?></td>
  </tr>
  <tr>
	<td height="25"><span>Отчество:</span></td>
	<td><?=input('surname')?></td>
	<td style="padding-left:90px"><span>E-mail:</span></td>
	<td><?=input('mail')?></td>
  </tr>
  <tr>
	<td colspan="4" style="padding:20px 0 10px 0"><span><b>Реквизиты</b></span></td>
  </tr>
  <tr>
	<td height="25"><span>ИНН:</span></td>
	<td><?=input('inn')?></td>
	<td style="padding-left:90px"><span>БАНК:</span></td>
	<td><?=input('bank')?></td>
  </tr>
  <tr>
	<td height="25"><span>КПП:</span></td>
	<td><?=input('kpp')?></td>
	<td style="padding-left:90px"><span>Р/С:</span></td>
	<td><?=input('rs')?></td>
  </tr>
  <tr>
	<td height="25"><span>БИК:</span></td>
	<td><?=input('bik')?></td>
	<td style="padding-left:90px"><span>К/С:</span></td>
	<td><?=input('ks')?></td>
  </tr>
  <tr>
	<td height="25"><span>ОКПО:</span></td>
	<td><?=input('okpo')?></td>
  </tr>
  <tr>
	<td colspan="2" width="50%" style="padding:20px 0 10px 0"><span><b>Юридический адрес</b></span></td>
	<td colspan="2" style="padding:20px 0 10px 90px"><span><b>Фактический адрес</b></span></td>
  </tr>
  <!--tr>
	<td height="25"><span>Индекс:</span></td>
	<td><?=input('index_ur')?></td>
	<td style="padding-left:90px"><span>Индекс:</span></td>
	<td><?=input('index_fact')?></td>
  </tr>
  <tr>
	<td height="25"><span>Город:</span></td>
	<td><?=input('city_ur')?></td>
	<td style="padding-left:90px"><span>Город:</span></td>
	<td><?=input('city_fact')?></td>
  </tr-->
  <tr>
	<td height="25"><span>Адрес:</span></td>
	<td><?=input('address_ur')?></td>
	<td style="padding-left:90px"><span>Адрес:</span></td>
	<td><?=input('address_fact')?></td>
  </tr>
</table>
<br>
<table width="100%" style="margin:0 0 0 30px 0">
  <tr>
	<td width="125"><span>Доставка:</span></td>
	<td><?=input('dostavka')?></td>
  </tr>
</table>

<div align="right" style="margin-top:20px"><input type="image" src="/img/btn_save.png" width="121" height="24"></div>
</form>
<?

$content = ob_get_clean();

require('tpl/template.php');
?>
