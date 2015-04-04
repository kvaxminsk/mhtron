<?
require_once('inc/common.php');

$code = @$code ? $code : @$_GET['code'];
if(!$code) $code = 404;

ob_start();
switch($code)
{
	case 403:
		?>
		<div class="nofind">Доступ к данной странице запрещен<div><a href="/">перейти на главную страницу</a></div></div>
		<?
		break;
	case 404:
		?>
		<div class="nofind">Запрашиваемая страница не найдена<div><a href="" onclick="$('#st1').focus();return false;">воспользуйтесь поиском</a></div></div>
		<?
		break;
}
$content = ob_get_clean();
require('tpl/template.php');
?>