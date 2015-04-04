<?
require('inc/common.php');
@session_start();

if(!$_POST)
{
	?><script>top.location.href='/'</script><?
	exit();
}

foreach($_POST as $key=>$value)
	$$key = clean($value);
	
$text = clean($text,true);

if(!$name || !$tema || !$text)
	errorAlertClient('show','alert','<center>Заполните пожалуйста все поля</center>');
	
if(!check_mail($mail))
	errorAlertClient('show','alert','Вы ввели неверный e-mail.<br>Пожалуйста повторите попытку.');

if(!check_phone($phone))
	errorAlertClient('show','alert','Вы ввели неверный телефон.<br>Пожалуйста повторите попытку.<br> Формат - 7923887030');
// captcha
//if($kod!=$_SESSION['number_test'])
	//errorAlert('Введенный Вами код\nне совпадает с символами на картинке!');
	
$id = update("messages","fio='{$name}',mail='{$mail}',phone='{$phone}',tema='{$tema}',text='{$text}',`date`=NOW(),note=''");

if($id)
{
	// журнал
	update("log","`date`=NOW(),text='новое сообщение',link='messages.php?red={$id}'");
	
	?>
	<script>
	top.$(document).jAlert('show','alert','<center>Ваше сообщение успешно сохранено</center>',function(){
		top.location.href='/';
	});
	</script>
	<?
}
else
	errorAlertClient('show','alert','Произошла ошибка!<br>Ваше сообщение сохранить не удалось. Приносим свои извинения.<br>В ближайшее время проблема будет устранена.');
?>