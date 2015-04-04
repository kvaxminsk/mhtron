<?
	//$mysql_conn=array("host"=>"localhost","login"=>"root","pwd"=>"","db"=>"autopartrus2");
	$mysql_conn=array("host"=>"localhost","login"=>"root","pwd"=>"","db"=>"restart1_db");

	$dblink = mysql_connect($mysql_conn['host'],$mysql_conn['login'],$mysql_conn['pwd']) or exit ("Database connection error");
	mysql_select_db($mysql_conn['db'], $dblink);
	
	mysql_query("set names utf8");
	mysql_query("set character_set_results=utf8");
	mysql_query("set character_set_connection=utf8");
	mysql_query("set character_set_client=utf8");
	mysql_query("set character_set_database=utf8");
	
	mb_internal_encoding('UTF-8');
	setlocale(LC_ALL, 'ru_RU.UTF-8');
	
	$prx = "auto_";	
?>