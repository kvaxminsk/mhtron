<?
require('inc/common.php');
@session_start();

$id_art = (int)@$_POST['id_art'];

if(!$_POST || !$id_art || !isset($_SESSION['user']))
{
	?><script>top.location.href='/';</script><?
	exit();
}

$text = clean(@$_POST['text'],true);

if(!$text)
	errorAlertClient('show','alert','<center>Напишите пожалуйста Ваш комментарий</center>');
	
$id = update("art_comments","id_art={$id_art},id_user=".$_SESSION['user']['id'].",text='{$text}',`date`=NOW()");

if($id)
{
	// журнал
	update("log","`date`=NOW(),text='новый комментарий к статье',link='articles.php?red={$id_art}&id={$id}'");
	
	?>
	<script>
	top.$(document).jAlert('show','alert','<center>Ваш комментарий успешно добавлен</center>',function(){
		top.location.href='/articles/<?=$id_art?>.htm';
	});
	</script>
	<?
}
else
	errorAlertClient('show','alert','Произошла ошибка!<br>Ваш комментарий сохранить не удалось. Приносим свои извинения.<br>В ближайшее время проблема будет устранена.');
?>