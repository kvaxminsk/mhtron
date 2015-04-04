<?
	@session_start();

	//header("Content-Type: text/html; charset=utf-8");
	
	// модули специально для клиентской части
	require($_SERVER['DOCUMENT_ROOT'].'/inc/spec.php'); //функции
	// ------------------------------
	
	// общие модули
	require($_SERVER['DOCUMENT_ROOT'].'/inc/db.php'); // коннектимся к базе
	require($_SERVER['DOCUMENT_ROOT'].'/inc/utils.php'); // разные полезные функции
	require($_SERVER['DOCUMENT_ROOT'].'/inc/tree.php'); // функции для дерева
	require($_SERVER['DOCUMENT_ROOT'].'/inc/advanced/advanced.php'); // "навороты" к сайту
	// ------------------------------
	
	if(!@$_SESSION['user'] && @$_COOKIE['inUser'])
	{
		$user = explode("/",$_COOKIE['inUser']);
		setPriv($user[0],$user[1]);
	}
	
	// функции, константы, переменные
	//$title = set("title");
	$keywords = set("keywords");
	$description = set("description");
	
	// определяем количество пользователей на сайте
	online(); 
	
	// СТАТИСТИКА ПОСЕЩЕНИЙ САЙТА
	if(!getField("SELECT COUNT(*) AS c FROM {$prx}users_visit WHERE ip='{$_SERVER['REMOTE_ADDR']}' and `date`='".date("Y-m-d")."'"))
		update("users_visit","date=NOW(), ip='{$_SERVER['REMOTE_ADDR']}'");
	// ------------------------------
	$user_showmaker = $_SESSION['user']['showmaker'];// || $_SESSION['admin'] || $_SESSION['manager'];

?>