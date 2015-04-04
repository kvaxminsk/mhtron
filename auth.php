<?
require('inc/common.php');

// ------------------ СОХРАНЕНИЕ ---------------------
if(@$_GET["action"])
{
	switch($_GET["action"])
	{
		case "exit":
			// выходим
			unset($_SESSION['user'], $_SESSION['cart']);
			setcookie("inUser");
			?><script>top.location.href='/'</script><?
			break;
		
		case "enter":
			// авторизация
			$user_login = @$_POST['secret']['login'] ? clean(@$_POST['secret']['login']) : "";
			$user_pass  = @$_POST['secret']['pass']  ? clean(@$_POST['secret']['pass']) : "";
			$save_user  = @$_POST['secret']['save']  ? true : false;
			
			if(!setPriv($user_login,$user_pass))
				errorAlertClient('show','alert','Неверный логин и/или пароль.');
			
			if($save_user) // куки
				setcookie("inUser",$user_login."/".$user_pass,time()+3456000); 
			else
				setcookie("inUser");
			
			$location = $_POST['back'] ? $_POST['back'] : '/';
			
			?><script>top.location.href = '<?=$location?>';</script><?
			break;
			
		case "reg":
		
			$mas_fields = array('login','pass','org','director','director_type','family','name','surname','phone','fax','mail','inn','kpp','bik','bank','rs','ks',
								'address_ur','address_fact','okpo','dostavka');
			//'index_ur','city_ur','index_fact','city_fact',
			
			// проверка полей
			foreach($mas_fields as $field)
			{
				$$field = clean(@$_POST['user'][$field],true);
				if(!$$field && $field!='director_type')
					errorAlert('Заполните пожалуйста все поля !');
			}
				
			if(getField("SELECT login FROM {$prx}users WHERE login='{$login}'"))
				errorAlert('Пользователь с таким логином уже существует !');
			
			if(!check_mail($mail))
				errorAlert('Вы ввели неверный e-mail !\nПожалуйста повторите попытку.');
			
			$query = '';			
			foreach($mas_fields as $field)
				$query .= ($query?',':'')."{$field}='{$$field}'";
				
			$id = update("users",$query);
				
			if($id)
			{
				// отправляем письмо пользователю
				$admin_mail = set('admin_email');
				$user = $name.' '.$surname;
				$user_mail = $mail;
				$tema  = "Регистрация на сайте {$_SERVER['SERVER_NAME']}";
				$text  = "Уважаемый(ая) <b>{$user}</b>,<br><br>Вы зарегистрировались на сайте <a href='http://{$_SERVER['SERVER_NAME']}'>{$_SERVER['SERVER_NAME']}</a>.<br>";
				$text .= "Ваш логин: <b>{$login}</b><br>";
				$text .= "Ваш пароль: <b>{$pass}</b>";
				
				mailTo($user_mail,$tema,$text,set('title')); // клиенту
				
				// журнал
				update("log","`date`=NOW(),text='зарегистрирован новый пользователь',link='users.php?red={$id}'");
				
				?>
				<script>
				alert('Уважаемый(ая) <?=$name.' '.$surname?>,\nрегистрация прошла успешно.\nНа указанный Вами e-mail было выслано уведомление\n с логином и паролем для авторизации на нашем сайте.');
				top.location.href = "/";
				</script>
				<?	
			}
			else
			{
				?>
				<script>
				alert('Уважаемый(ая) <?=$name.' '.$surname?>,\nво время сохранения данных произошла ошибка!\nПриносим свои извинения.\nВ ближайшее время проблема будет устранена.')
				</script>
				<?
			}
			break;
			
		case "remind":
						
			if($_SESSION['user']) { header("location: /"); exit(); }			
			if(!$mail = clean($_POST['user']['mail']))
				errorAlert('Введите пожалуйста E-mail!');
			if(!$user = getRow("SELECT login,name,surname,pass,mail FROM {$prx}users WHERE mail='{$mail}'"))
				errorAlert('Пользователь с таким E-mail не найден !');
			
			$admin_mail = set('admin_email');
			$tema  = "Восстановление пароля на сайте {$_SERVER['SERVER_NAME']}";
			$text  = "Уважаемый(ая) <b>".($user['name'].' '.$user['surname'])."</b>,<br><br>Вы запросили восстановление пароля на сайте <a href='http://{$_SERVER['SERVER_NAME']}'>{$_SERVER['SERVER_NAME']}</a>.<br>";
			$text .= "Ваш логин: <b>{$user['login']}</b><br>";
			$text .= "Ваш пароль: <b>{$user['pass']}</b>";
				
			mailTo($user['mail'],$tema,$text,$admin_mail);
			
			?>
			<script>
			alert("Пароль был выслан на указанный Вами e-mail");
			top.location.href = '/';
			</script>
			<?
			
			break;
	}
	exit;
}
// ------------------ ПРОСМОТР ---------------------
elseif(@$_GET["show"])
{
	ob_start();
	
	if($_SESSION['user'])
	{
		header("location: /");
		exit();
	}
	
	switch($_GET['show'])
	{		
		case "reg":
			
			function input($field,$type='text')
			{
				ob_start();
				?><input type="<?=$type?>" name="user[<?=$field?>]" class="pole2"><?
				return ob_get_clean();
			}
			
			$title .= "Регистрация";
			?>
            <h1>Регистрация</h1>
            <?=set('reg_text')?>
            <style>
			#reg_frm span
			{
				font:normal 16px Georgia, "Times New Roman", Times, serif;
				font-style:italic;
			}
			</style>
			<form id="reg_frm" action="?action=reg" method="post" target="ajax" style="margin:20px 0">
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
					<td height="25"><span>ОКПО:</span></td>
					<td><?=input('okpo')?></td>
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
				
            <div align="right" style="margin-top:20px"><input type="image" src="/img/btn_reg.png" width="121" height="24"></div>
			</form>
            
            <script>
			$(function(){
				$('input[name^=user]').val('');
			})
			</script>
           	<?
				
			break;
		
		case "remind":
		
			$title .= "Восстановление пароля";
			?>
			<h1>Восстановление пароля</h1>
			<center>
      <style>
			#rem_frm span
			{
				font:normal 16px Georgia, "Times New Roman", Times, serif;
				font-style:italic;
				color:#FFF;
			}
			</style>
      <form id="rem_frm" action="?action=remind" method="post" target="ajax">
      <table width="300" style="margin-bottom:20px">
        <tr>
          <td height="25" nowrap><span>Ваш E-mail:</span></td>
          <td><input type="text" name="user[mail]" class="pole2"></td>
        </tr>
        <tr>
          <td colspan="2" align="right" style="padding:20px 0 0 0">
            <input type="image" src="/img/btn_ok.png" width="50" height="24">
          </td>
        </tr>
      </table>
			</form>
      </center>
			<?
			break;
	}	
	$content = ob_get_clean();
}
else
{
	header("location: /");
	exit();
}

require("tpl/template.php");
?>