<?
require('common.php');

$action = $_GET['action'];

switch($action)
{
	case 'zapros_catalog':
		$tema = 'Запрос каталога';
	case 'zapros_shassi':
		if(!$tema)
			$tema = 'Запрос детали по номеру шасси';
		$notes = "<b>{$tema}</b><br>";
		$id_user = (int)$_SESSION['user']['id'];
		foreach($_POST['info'] as $key=>$val)
			if($val)
				$notes .= "<i>{$key}</i>: ".clean($val, true).'<br>';
			else
				errorAlert('Пожалуйста заполните все поля');
		
		$id = update('zapros',"id_user={$id_user},notes='{$notes}',date=NOW()");
		
		$admin_mail = set('admin_email');
		mailTo($admin_mail,$tema,$notes,$_SESSION['user']['mail']); // админу		
		
		?><script>
			top.$pop_<?=$action?>.animate({left:-700}, 100, function(){ $(this).hide() });
			top.$(document).jAlert('show','alert','Запрос успешно отправлен!<br>Номер Вашего запроса: <b><?=$id?></b><br>В близжайшее время наш менеджер с Вами свяжется.',function(){
				top.topReload();
			});
		</script><?
		break;
}
?>