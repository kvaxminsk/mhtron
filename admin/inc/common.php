<?
	ini_set('display_errors', 1);
	ini_set('max_execution_time', '10000');
	ini_set('upload_max_filesize', '100M');
	ini_set('post_max_size', '100M');
	//ini_set('memory_limit', '32M');
	ini_set('session.gc_maxlifetime', '86400'); // время жизни сессии
	
	@session_start();
	
	if( !isset($_SESSION['admin']) && !isset($_SESSION['manager']) )
	{
		header("Location: login.php?action=vhodc&urlback=".$_SERVER['REQUEST_URI']);
		exit;
	}
	
	if(isset($_SESSION['manager']))
	{
		$_SESSION['manager']['mmp'] = $mmp = explode(',',$_SESSION['manager']['priv']);
		$cur_script = basename($_SERVER['SCRIPT_FILENAME']);
		
		if(!in_array($cur_script,array('log.php','statistics.php','visit.php')))
		{
			if( in_array($cur_script,array('managers.php','settings.php')) || !in_array($cur_script,$mmp) )
			{
				header("Location: statistics.php");
				exit;
			}
		}
	}

	// модули специально для админки
	require('spec.php'); // постоянные
	require('func.php'); // переменные
	// ------------------------------
	
	// общие модули
	require($_SERVER['DOCUMENT_ROOT'].'/inc/db.php'); // коннектимся к базе
	require($_SERVER['DOCUMENT_ROOT'].'/inc/utils.php'); // разные полезные функции
	require($_SERVER['DOCUMENT_ROOT'].'/inc/tree.php'); // функции для дерева
	require($_SERVER['DOCUMENT_ROOT'].'/inc/advanced/advanced.php'); // "навороты" к сайту
	// ------------------------------
	
	// функции, константы, переменные
	$page_title = "Администрирование";
	$script = basename($_SERVER['SCRIPT_FILENAME']);
	// ------------------------------	
?>