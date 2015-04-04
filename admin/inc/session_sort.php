<?
@session_start();

// страницы
if($_GET['page'])
{
	if($_GET['page']=="remove")
		session_unregister('page');
	else
		$_SESSION['page'] = $_GET['page'];
}
// сортировка в колонках
if($_GET['sort'])
{
	if($_GET['sort']=="remove")
		session_unregister('sort');
	else
		$_SESSION['sort'] = $_GET['sort'];
}
// раздел каталога
if($_GET['cat'])
{
	if($_GET['cat']=="remove")
		session_unregister('cat');
	else
	{
		$_SESSION['cat'] = $_GET['cat'];
		session_unregister('page');
		session_unregister('sort');
		session_unregister('letter');
		session_unregister('context');
	}
}
// производитель
if($_GET['maker'])
{
	if( ($_GET['maker']=="remove") or ($_GET['maker']==-1) )
		session_unregister('maker');
	else
	{
		$_SESSION['maker'] = $_GET['maker'];
		session_unregister('page');
		session_unregister('sort');
		session_unregister('letter');
		session_unregister('context');
	}
}

if($_GET['id_kpp_cat'])
{
	if( ($_GET['id_kpp_cat']=="remove") or ($_GET['id_kpp_cat']==-1) )
		session_unregister('f_kpp_cat');
	else
	{
		$_SESSION['f_kpp_cat'] = $_GET['id_kpp_cat'];
		session_unregister('page');
		session_unregister('sort');
		session_unregister('letter');
		session_unregister('context');
	}
}


// менеджер
if($_GET['fmanager'])
{
	if($_GET['fmanager']=="remove")
		session_unregister('fmanager');
	else
	{
		$_SESSION['fmanager'] = $_GET['fmanager'];
		session_unregister('page');
	}
}
// буква
if(isset($_GET['letter']) && $_GET['letter']!='')	
{	
	if($_GET['letter']=="remove")
		session_unregister('letter');
	else
	{
		$_SESSION['letter'] = $_GET['letter'];
		session_unregister('page');
	}
}
// контекстный поиск
if(isset($_GET['context']) && $_GET['context']!='')
{
	if($_GET['context']=="remove")
		session_unregister('context');
	else
	{
		$_SESSION['context'] = $_GET['context'];
		session_unregister('page');
	}
}
// пользователь
if($_GET['cur_user'])
{
	if($_GET['cur_user']=="remove")
		session_unregister('cur_user');
	else
	{
		$_SESSION['cur_user'] = $_GET['cur_user'];
		session_unregister('page');
		session_unregister('sort');
		session_unregister('cat');
		session_unregister('maker');
		session_unregister('fmanager');
		session_unregister('letter');
		session_unregister('context');
	}
}
// удаление всех фильтров
if($_GET['filter'] and $_GET['filter']=="remove")
{
	session_unregister('page');
	session_unregister('sort');
	session_unregister('cat');
	session_unregister('maker');
	session_unregister('fmanager');
	session_unregister('letter');
	session_unregister('context');
	session_unregister('cur_user');
}

preg_match("/&location=(.*)/",$_SERVER['REQUEST_URI'],$mathces);
$location = $mathces[1];
?>
<script>
top.location.href = '../<?=$location?>';
</script>
<?
exit;
?>