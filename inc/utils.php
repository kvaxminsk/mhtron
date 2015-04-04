<?
// ПОДГОТОВКА МАССИВА К СОХРАНЕНИЮ В ТАБЛИЦЕ
function cleanArr($arr)
{
	$spec = array('°'=>'&deg;', 'º'=>'&deg;', 'φ'=>'&phi;');
	foreach((array)$arr as $key=>$val)
	{
		if(is_array($val))
		{
			foreach($val as $k=>$v)
				$val[$k] = str_replace(array_keys($spec), array_values($spec), htmlspecialchars($v));
		}
		else
			$arr[$key] = str_replace(array_keys($spec), array_values($spec), htmlspecialchars($val));
	}
	return clean(serialize($arr));
}

function online() 
{
	global $prx;
	
	$ip = getenv("REMOTE_ADDR");
	$sid = session_id();

	$find_sid = getField("SELECT sid FROM {$prx}users_online WHERE sid='{$sid}'");

	$cur_time = time();

	if($find_sid)
		sql("UPDATE {$prx}users_online SET time='{$cur_time}' WHERE sid='{$find_sid}'");
	else
		sql("INSERT INTO {$prx}users_online (ip, time, sid) VALUES ('{$ip}', '{$cur_time}', '{$sid}')");
}
function users_online() 
{
	global $prx;
	
	$past = time()-180;

	sql("DELETE FROM {$prx}users_online WHERE time < {$past}");

	return (int)getField("SELECT COUNT(*) FROM {$prx}users_online");;
}
// ПОЛУЧАЕТ МАССИВ ВСЕГО ЗАПРОСА
function getArr($sql, $simple=true) // $simple=true - возвратит "простой" массив (без привязки к полям запроса)
{
	$res = sql($sql);
	
	if($simple)
	{
		while($row = mysql_fetch_row($res))
			if(mysql_num_fields($res)>1)
				$arr[$row[0]] = $row[1];
			else
				$arr[] = $row[0];
	}
	else
		while($row = mysql_fetch_assoc($res))
			$arr[] = $row;

	return (array)$arr;
}
// ВЫПАДАЮЩИЙ СПИСОК/МУЛЬТИСПИСОК для таблицы/массива
function dll($obj, $properties, $value='', $default=NULL) // запрос/массив, св-ва списка, значение (может быть массивом), "пустое" значение(может быть массивом)
{ 
	ob_start();
?>
	<select <?=$properties?>>
	<?	if($default !== NULL)
			echo is_array($default)
				? "<option value='{$default[0]}'>{$default[1]}</option>"
				: "<option value=''>{$default}</option>";
		$arr = (is_array($obj) || !$obj) ? $obj : getArr($obj);
		foreach((array)$arr as $key=>$val) { 
			$selected = is_array($value) ? in_array($key, $value) : strcasecmp($key,$value)==0;
		?>	<option value="<?=$key?>"<?=($selected ? ' selected' : '')?>><?=$val?></option>
	<? } ?>
	</select>
<? 	
	return ob_get_clean();
}
// ПЕРЕВОД ДАТЫ В ФОРМАТ БАЗЫ ДАННЫХ
function formatDateTime($datetime="00.00.0000 00:00:00") // можно передавать только дату
{
	$datetime = explode(" ", str_replace(",",".",$datetime)); 
	$date = explode(".",$datetime[0]); 
	$time = explode(":",$datetime[1]); 
	$res = @mktime((int)$time[0],(int)$time[1],(int)$time[2],$date[1],$date[0],$date[2]);
	if(!$res)
		return "";
		
	$datetime = @$datetime[1] ? @date("Y-m-d H:i:s", $res) : @date("Y-m-d", $res);
	
	return $datetime;
}
// ТРАНСЛИТЕРАЦИЯ СТРОКИ
/*function trans($str) { 
	$str = strtr($str,  
		"абвгдежзийклмнопрстуфхыэАБВГДЕЖЗИЙКЛМНОПРСТУФХЫЭ", 
		"abvgdegziyklmnoprstufhieABVGDEGZIYKLMNOPRSTUFHIE" 
	); 
	$str = strtr($str, array( 
		'ё'=>"yo",	'ц'=>"ts",	'ч'=>"ch",	'ш'=>"sh",	'щ'=>"shch",
		'ъ'=>"",		'ь'=>"",		'ю'=>"yu",	'я'=>"ya", 
		'Ё'=>"Yo",	'Ц'=>"Ts",	'Ч'=>"Ch",	'Ш'=>"Sh",	'Щ'=>"Shch",
		'Ъ'=>"",		'Ь'=>"",		'Ю'=>"Yu",	'Я'=>"Ya" 
	)); 
	return $str;
}*/
function trans($title, $rtl_standard = 'gost')
{
    static $gost, $iso;
 
    switch ($rtl_standard)
    {
      case 'off':
        return $title;
 
      case 'gost':
        if (empty($gost))
        {
            $gost = array(
                'Є'=>'EH','І'=>'I','і'=>'i','№'=>'#','є'=>'eh',
                'А'=>'A','Б'=>'B','В'=>'V','Г'=>'G','Д'=>'D',
                'Е'=>'E','Ё'=>'JO','Ж'=>'ZH',
                'З'=>'Z','И'=>'I','Й'=>'JJ','К'=>'K','Л'=>'L',
                'М'=>'M','Н'=>'N','О'=>'O','П'=>'P','Р'=>'R',
                'С'=>'S','Т'=>'T','У'=>'U','Ф'=>'F','Х'=>'KH',
                'Ц'=>'C','Ч'=>'CH','Ш'=>'SH','Щ'=>'SHH','Ъ'=>'\'',
                'Ы'=>'Y','Ь'=>'','Э'=>'EH','Ю'=>'YU','Я'=>'YA',
                'а'=>'a','б'=>'b','в'=>'v','г'=>'g','д'=>'d',
                'е'=>'e','ё'=>'jo','ж'=>'zh',
                'з'=>'z','и'=>'i','й'=>'jj','к'=>'k','л'=>'l',
                'м'=>'m','н'=>'n','о'=>'o','п'=>'p','р'=>'r',
                'с'=>'s','т'=>'t','у'=>'u','ф'=>'f','х'=>'kh',
                'ц'=>'c','ч'=>'ch','ш'=>'sh','щ'=>'shh','ъ'=>'',
                'ы'=>'y','ь'=>'','э'=>'eh','ю'=>'yu','я'=>'ya',
                '«'=>'"','»'=>'"','"'=>'"','"'=>'"','—'=>'-');
        }
        return strtr($title, $gost);
 
      default:
        if (empty($iso))
        {
            $iso = array(
                'Є'=>'YE','І'=>'I','Ѓ'=>'G','і'=>'i','№'=>'#','є'=>'ye','ѓ'=>'g',
                'А'=>'A','Б'=>'B','В'=>'V','Г'=>'G','Д'=>'D',
                'Е'=>'E','Ё'=>'YO','Ж'=>'ZH',
                'З'=>'Z','И'=>'I','Й'=>'J','К'=>'K','Л'=>'L',
                'М'=>'M','Н'=>'N','О'=>'O','П'=>'P','Р'=>'R',
                'С'=>'S','Т'=>'T','У'=>'U','Ф'=>'F','Х'=>'X',
                'Ц'=>'C','Ч'=>'CH','Ш'=>'SH','Щ'=>'SHH','Ъ'=>'\'',
                'Ы'=>'Y','Ь'=>'','Э'=>'E','Ю'=>'YU','Я'=>'YA',
                'а'=>'a','б'=>'b','в'=>'v','г'=>'g','д'=>'d',
                'е'=>'e','ё'=>'yo','ж'=>'zh',
                'з'=>'z','и'=>'i','й'=>'j','к'=>'k','л'=>'l',
                'м'=>'m','н'=>'n','о'=>'o','п'=>'p','р'=>'r',
                'с'=>'s','т'=>'t','у'=>'u','ф'=>'f','х'=>'x',
                'ц'=>'c','ч'=>'ch','ш'=>'sh','щ'=>'shh','ъ'=>'',
                'ы'=>'y','ь'=>'','э'=>'e','ю'=>'yu','я'=>'ya',
                '«'=>'"','»'=>'"','"'=>'"','"'=>'"','—'=>'-'
            );
        }
        return strtr($title, $iso);
    }
}
// ПРИВОДИМ ТЕКСТ К ПРИГОДНОМУ ДЛЯ ССЫЛКИ
function makeUrl($str)
{
	$str = trans($str);
	$str = str_replace(array(' ','.',','),'-',$str);
	$str = strtolower($str);
	$str = preg_replace('/\-+/','-',$str); // убираем повторяющиеся '-'
	$str = preg_replace('#[^a-z0-9_\-]#isU','',$str); // оставляем только буквы, цифры, - и _
	return $str; 
}
// АНАЛОГ ФУНКЦИЯМ strtolower И strtoupper
function strto($str,$case="lower",$convert_first_char=true)
{
	$str = trim($str);
	$firstchar = $str[0];
	
	$uc = 'ABCDEFGHIJKLMNOPQRSTUVWXYZАБВГДЕЁЖЗИЙКЛМНОПРСТУФХЦЧШЩЪЫЬЭЮЯ';
	$lc = 'abcdefghijklmnopqrstuvwxyzабвгдеёжзийклмнопрстуфхцчшщъыьэюя';
		
	$outstr = $case=="lower" ? strtr($str,$uc,$lc) : strtr($str,$lc,$uc);
	if(!$convert_first_char)
		$outstr[0] = $firstchar;
	
	return $outstr;
}
// ПОЛУЧАЕТ ОДНО ЗНАЧЕНИЕ ИЗ ТАБЛИЦЫ
function getField($sql)
{
	$res = mysql_query($sql); 
	$field = @mysql_result($res,0,0);
	return $field;
}	
// ПОЛУЧАЕТ МАССИВ ПЕРВОЙ СТРОКИ ТАБЛИЦЫ
function getRow($sql)
{
	$res = mysql_query($sql); 
	$row = @mysql_fetch_assoc($res);
	return $row;
}
// ЗНАЧЕНИЕ ПОЛЯ В ОПРЕДЕЛЁННОЙ СТРОКЕ ТАБЛИЦЫ
function gtv($tab,$pole,$id)
{
	global $prx;
	if($pole=='*' || mb_strpos($pole,',')!==false)
		return getRow("SELECT {$pole} FROM {$prx}{$tab} WHERE id='{$id}'");
	else
		return getField("SELECT {$pole} FROM {$prx}{$tab} WHERE id='{$id}'");
}
// ВЫПАДАЮЩИЙ СПИСОК ДЛЯ ПОЛЯ enum
function dllEnum($tab,$field,$properties,$value="")
{
	global $prx;
	
	ob_start();
	?>
	<select <?=$properties?>>
		<? 
		$res = sql("SHOW COLUMNS FROM {$prx}{$tab} LIKE '{$field}'");
		$val = mysql_result($res,0,1);
		$val = str_replace(array("enum(",")","'"), "", $val);
		$arr = explode(",",$val);
		foreach($arr as $val) 
		{ 
			?><option value="<?=$val?>" <?=($val==$value ? "selected" : "")?>><?=$val?></option><? 
		} 
	?>
	</select>
	<? 	
	return ob_get_clean();
}
// ВОЗВРАЩАЕТ ЗНАЧЕНИЕ ПЕРЕМЕННОЙ ИЗ ТАБЛИЦЫ settings
function set($name)
{
	global $prx;
	$sql = "SELECT value FROM {$prx}settings WHERE id='{$name}'";
	$res = sql($sql);

	if(!@mysql_num_rows($res) && @$_SESSION['priv'])
		echo(nl2br($sql));

	$value = @mysql_result($res,0,0);
	return $value;
}	
// ОБНОВЛЕНИЕ / ДОБАВЛЕНИЕ / УДАЛЕНИЕ ЗАПИСИ В ТАБЛИЦЕ
function update($tbl, $set="", $id=0) // таблица, обновляемые поля, id (для удаления id может быть массивом, строкой через ',' или NULL)
{
	global $prx;
	if(!$set)
	{
		if(is_null($id))
			sql("TRUNCATE TABLE {$prx}{$tbl}");
		if(is_array($id))
			sql("DELETE FROM {$prx}{$tbl} WHERE id IN ('".implode("','",$id)."')");
		elseif(strpos(",",$id))
			sql("DELETE FROM {$prx}{$tbl} WHERE id IN (".trim($id,",").")");
		else
			sql("DELETE FROM {$prx}{$tbl} WHERE id='{$id}'");
		return;
	}
	if($id)
		sql("UPDATE {$prx}{$tbl} SET {$set} WHERE id='{$id}'");
	else
	{
		sql("INSERT INTO {$prx}{$tbl} SET {$set}");
		$id = mysql_insert_id();
	}
	return $id;
}
//ОЧИСТКА СТРОКИ ДЛЯ ВИДА ПРИГОДНОГО К ПЕРЕДАЧИ В JAVASCRIPT
function cleanJS($str) 
{
	$str = preg_replace('#[\n\r]+#', '\\n', $str); // убираем переносы строк
	$str = str_replace("'","\"",$str); // убираем '
	$str = str_replace("script>","scr'+'ipt>",$str); // чтобы можно было вставить скрипт
   return $str;
}
// ВЫВОД ALERT ОБ ОШИБКЕ (и прерывание выполнения)
function errorAlert($msg,$exit=true)
{
	?><script>alert("<?=$msg?>");top.loader(false);</script><?
	if($exit) exit;
}
// ЗАМЕНА mysql_query - ВЫВОДИТ ТЕКСТ ЗАПРОСА В СЛУЧАИ НЕУДАЧИ
function sql($sql, $debug=false)
{
	global $debugSql;
	$res = mysql_query($sql);
	if((!$res && @$_SESSION['admin']) || $debugSql || $debug)
	{
		$text = $sql."\r\n".mysql_error()."\r\n";
		echo nl2br($text);
		$text = cleanJS($text);
		?>
		<script>
		if(top.window !== window && <?=(!$debugSql && !$debug ? "true" : "false")?>) // если мы во фрейме, то выводим алерт и прерываем фрейм
		{
			alert('<?=$text?>');
			location.href = "/inc/none.html";
		}
		else
			alert('<?=$text?>');
		top.loader(false);
		</script><?
	}
	return $res;
}
// ПОДГОТОВКА СТРОКИ К СОХРАНЕНИЮ В ТАБЛИЦЕ
function clean($str, $strong=false) 
{
	if(is_array($str))
		return $str;
	$str = trim((string)$str, " \r\n");
	$str = stripslashes($str);
	if($strong)
	{
		$str = preg_replace('#\s+#us', ' ', $str); // убираем повторяющиеся пробелы
		$str = htmlspecialchars(htmlspecialchars_decode($str)); //преобразоваваем теги html
		$str = strtr($str, array('"'=>'&quot;', "'"=>'&#0039;'));
	}
	else
		$str = addslashes($str);
	return $str;
}
// разбиение текста из textarea по строкам
function break_to_str($text)
{	
	return strtr($text,array("\r\n"=>"<br>"));
}
// разбиение текста из textarea по строкам
function break_to_str1($text)
{	
	return strtr($text,array("\r\n"=>"\n","\r"=>"\n"));
}
// ОТПРАВКА HTML ПИСЬМА
function mailTo($to, $subject, $message, $from="", $charset="utf-8")
{
	$subject = "=?{$charset}?B?".base64_encode($subject)."?=";
	$headers  = "Content-type: text/html; charset={$charset} \r\n";
	if($from)
		$headers .= "From: {$from}\r\nReply-To: {$from}\r\n";
	return @mail($to, $subject, $message, $headers);
}
// ОТПРАВКА HTML ПИСЬМА С ВЛОЖЕНИЯМИ
function mailToFiles($to, $subject, $message, $from='', $files=array(), $charset='utf-8')
{
	$subject = "=?{$charset}?B?".base64_encode($subject)."?=";
	require_once($_SERVER['DOCUMENT_ROOT'].'/inc/nomad_mimemail.php');								
	$mimemail = new nomad_mimemail();
	$mimemail->set_charset($charset);
	$mimemail->set_to($to);
	if($from)	$mimemail->set_from($from);
	$mimemail->set_subject($subject);
	$mimemail->set_html("<HTML><HEAD></HEAD><BODY>{$message}</BODY></HTML>");
	foreach($files as $file)
		$mimemail->add_attachment($file,basename($file));

	return $mimemail->send();
}
// ОТПРАВКА HTML ПИСЬМА С ИЗОБРАЖЕНИЯМИ
function mailToImg($to, $subject, $message, $from='', $charset='utf-8')
{
	require_once($_SERVER['DOCUMENT_ROOT'].'/inc/simple_html_dom.php');
	$html = str_get_html($message);
	foreach($html->find('img') as $img)
	{
		$img_src = str_replace('"','',$img->src);
		$src[$img_src] = $img->src = basename($img_src);
	}
	$message = $html->innertext;
	$html->__destruct();
	
	require_once($_SERVER['DOCUMENT_ROOT'].'/inc/nomad_mimemail.php');								
	$mimemail = new nomad_mimemail();
	$mimemail->set_charset($charset);
	$mimemail->set_to($to);
	if($from)	$mimemail->set_from($from);
	$mimemail->set_subject($subject);
	$mimemail->set_html("<HTML><HEAD><link rel='stylesheet' type='text/css' href='style.css'></HEAD><BODY>{$message}</BODY></HTML>");
	$mimemail->add_attachment($_SERVER['DOCUMENT_ROOT'].'/inc/style.css', 'style.css');
	//$mimemail->set_html("<HTML><HEAD></HEAD><BODY>{$message}</BODY></HTML>");
	foreach((array)@$src as $a=>$b)
		$mimemail->add_attachment($_SERVER['DOCUMENT_ROOT'].$a, $b);
	return $mimemail->send();
}

// проверка mail адреса
function check_mail($mail)
{
	$shablon = "/[0-9a-z_]+@[0-9a-z_^\.]+\.[a-z]{2,3}/i";

	if(preg_match($shablon,$mail))
		return true;
	else
		return false;
}
// проверка mail адреса
function check_phone($phone)
{
	$shablon = "/[0-9]{7,15}/i";

	if(preg_match($shablon,$phone))
		return true;
	else
		return false;
}
// ГЕНЕРАТОР ПАРОЛЯ
// $pass_length - длина пароля
function get_new_pass($pass_length=6)
{
	$simvols = array ("0","1","2","3","4","5","6","7","8","9",
                  "a","b","c","d","e","f","g","h","i","j","k","l","m","n","o","p","q","r","s","t","u","v","w","x","y","z",
                  "A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z");
	
	for ($i = 0; $i < $pass_length; $i++)
	{
		shuffle($simvols);
		$string = $string.$simvols[1];
	}
		
	return $string;	
}
// ВСТАВКА FLASH
// пусть к флехе, свойства (ширина, высота...)
function flash($src, $properties="") 
{
	ob_start();
	?><embed src='<?=$src?>' <?=$properties?> quality='high' wmode='transparent' type='application/x-shockwave-flash' pluginspage='http://www.macromedia.com/go/getflashplayer'></embed><?
	return ob_get_clean();
}
// ВОЗВРАЩАЕТ РАЗМЕРЫ В СООТВЕТСТВИИ С ПРОПОРЦИЯМИ
function getMinRatioSize($size=array("320","240"), $sizeto=array("160","120"))
{
	list($width, $height) = $sizeto;
	if(!$width || $width > $size[0])
		$width = $size[0];
	if(!$height || $height > $size[1])
		$height = $size[1];
	
	$x_ratio = $width / $size[0];
	$y_ratio = $height / $size[1];
	
	$ratio = min($x_ratio, $y_ratio);
	$use_x_ratio = ($x_ratio == $ratio);
	
	$width   = $use_x_ratio  ? $width  : floor($size[0] * $ratio);
	$height  = !$use_x_ratio ? $height : floor($size[1] * $ratio);
	
	return array($width, $height);
}
// ВОДЯНОЙ ЗНАК НА КАРТИНКЕ
function watermark($src) // абсолютный путь к картинке
{
	$dr = $_SERVER['DOCUMENT_ROOT'];
	$foto_wh = getimagesize($dr.$src);
	$i = $foto_wh[0] < 386 ? 165 : 386; // водяной знак для большой или маленькой картинки
	$znak_wh = getimagesize($dr."/img/watermark{$i}.png");
	
	$znak = imagecreatefrompng($dr."/img/watermark{$i}.png");
	$foto = imagecreatefromjpeg($dr.$src);
	
	imagecopy($foto, $znak, round(($foto_wh[0]-$znak_wh[0])/2), round(($foto_wh[1]-$znak_wh[1])/2), 0, 0, $znak_wh[0], $znak_wh[1]);
	imagejpeg($foto, $dr.$src, "90");
}
// ИЗМЕНЕНИЕ РАЗМЕРОВ КАРТИНКИ
// путь к картинки, ширина, высота, путь для сохранения (если не задан, возвращается контент картинки), качество сжатия
function imgResize($src, $width, $height=0, $src_save="", $quality=90)
{
	$size = @getimagesize($src);
	if($size === false) 
		return false;
	
	$type = $size['mime'];
	$format = strtolower(substr($type, strpos($type, '/')+1));
	if($format == "bmp")
		include("bmp.php");

	$icfunc = "imagecreatefrom" . $format;
	if (!function_exists($icfunc))
		return false;
	
	if(!$width || $width > $size[0])
		$width = $size[0];
	if(!$height || $height > $size[1])
		$height = $size[1];
	
	$x_ratio = $width / $size[0];
	$y_ratio = $height / $size[1];
	
	$ratio = min($x_ratio, $y_ratio);
	$use_x_ratio = ($x_ratio == $ratio);
	
	$width   = $use_x_ratio  ? $width  : floor($size[0] * $ratio);
	$height  = !$use_x_ratio ? $height : floor($size[1] * $ratio);
	
	if($width==$size[0] || $height==$size[1])
	{
		if($src_save)
			copy($src, $src_save);
		return true;
	}

	$isrc = $icfunc($src);
	$idest = imagecreatetruecolor($width, $height);
	
	imagecopyresampled($idest, $isrc, 0, 0, 0, 0, $width, $height, $size[0], $size[1]);
	
	$ir = imagejpeg($idest, $src_save, $quality);
	
	imagedestroy($isrc);
	imagedestroy($idest);

	return $ir;
}
// РАСШИРЕНИЕ ФАЙЛА
function getFileExtension($filename) 
{
	return end(explode(".",$filename));
}

function getFileFormat($mask,$array=false)
{
	// $mask = $_SERVER['DOCUMENT_ROOT']."/uploads/news/10.*";
	$images = glob($mask, GLOB_NOSORT);
	if($images)
	{
		if($array)
		{
			$res = array();
			foreach($images as $val)
				if(!is_dir($val))
					$res[] = $val;
					
			return (array)$res;
		}
		else
			return end(explode('.',$images[0]));
	}
	else
		return false;
}
// Возвращает массив всех директорий,
// находящихся в $path
function get_dir_list($path)
{
	$res = array();
	
	if(is_dir($path))
	{
		$dh = opendir($path);
	  	while (false !== ($dir = readdir($dh))) 
	  	{
		 	if(is_dir($path.$dir) && $dir!=='.' && $dir!=='..') 
		 	{
			 	$subdir = $path.$dir.'/';
			 	$res[] = $subdir;
			 	get_dir_list($subdir);
		 	} 
		 	else
				next;
	  	}
	  	closedir($dh);
	} 
	/*else
	   print "Директорий не найдено";*/
   
   	return $res;
}
// Возвращает массив всех файлов,
// находящихся в $dir
function get_file_list($dir)
{
	$res = array();
	
   	if($dh=opendir($dir))
   	{
    	while(($file=readdir($dh))!==false)
      	{
        	if($file!=='.' && $file!=='..')
          	{
             	$current_file = "{$dir}/{$file}";
             	if(is_file($current_file))
                	$res[] = $file;
          	}
       	}
		closedir($dh);
   	}
	
	return $res;
}
// УДАЛЕНИЕ ДИРЕКТОРИИ
function delete_dir($dirname,$files_only=false)
{
	if(is_dir($dirname))
		$dir_handle = opendir($dirname);
	if(!$dir_handle)
		return false;
	
	while($file = readdir($dir_handle))
	{
		if($file!="." && $file!="..")
		{
			if(!is_dir($dirname."/".$file))
				@unlink($dirname."/".$file);
			else
				delete_dir($dirname.'/'.$file);
		}
	}
	closedir($dir_handle);
	
	if(!$files_only)
		rmdir($dirname);
	
	return true;
}
function getStructureTable($tableName,$pole='')
{
	global $prx;
	/*
	Параметры: $tableName-имя таблицы БД
	Возвращает: ассоциативный массив:
		 [<имя поля>] => Array(
			  [Field] => id                                       //-имя поля (соответствует ключу массива)
			  [Type] => int(5)                                    //-тип поля
			  [Collation] => cp1251_general_ci                    //-кодировка
			  [Null] => NO
			  [Key] => PRI
			  [Default] => 1
			  [Extra] => auto_increment
			  [Privileges] => select,insert,update,references
			  [Comment] => 
			  [number] => 0
		 )
	*/
	$res = mysql_query("SHOW FULL FIELDS FROM {$prx}{$tableName}");
	if(!$res)
		return false;
	else
	{
		$mas = array();
		$i=0;
		while($row = mysql_fetch_assoc($res))
		{
			if($pole && $pole==$row['Field'])
				return 	$row['Comment'];
				
			$mas[$row['Field']] = $row;
			$mas[$row['Field']]['number'] = $i;
			$i++;
		 }
	}
	
	return count($mas)?$mas:false;
}

// $mask - маска:
// d - день месяца
// m - месяц
// y - год
// w - день недели
function getRusDate($mask,$date='')
{
	$date = $date ? $date : date('d.m.Y');
	
	$mas = explode('.',date('D.d.m.Y',strtotime($date)));	
	$dayofweek = strto($mas[0]);
	$day = $mas[1];
	$month = $mas[2];
	$year = $mas[3];
	
	$masEng = array('mon','tue','wed','thu','fri','sat','sun');
	$masRus = array('понедельник','вторник','среда','четверг','пятница','суббота','воскресение');
	$dayofweek = $masRus[array_search($dayofweek,$masEng)];
	
	$masM 			= array('01','02','03','04','05','06','07','08','09','10','11','12');
	$masM_small 	= array('янв','фев','мар','апр','май','июн','июл','авг','сен','окт','ноя','дек');
	$masM_big 		= array('января','февраля','марта','апреля','мая','июня','июля','августа','сентября','октября','ноября','декабря');
	$month_small 	= $masM_small[array_search($month,$masM)];
	$month_big 		= $masM_big[array_search($month,$masM)];
	
	return strtr($mask,array(	'd'=>$day,
								'm'=>$month_small,
								'M'=>$month_big,
								'y'=>$year,
								'w'=>$dayofweek
								));
}

// PHP конвертер из Windows-1251 в UTF-8
function win2utf($text, $iconv=true) // текст (в кодировке Windows-1251), флаг что сначала попытаться использовать функцию iconv
{
	if(detectUTF8($text))
		return $text;
		
	if(!$iconv || !function_exists('iconv'))
	{
		for($i=0, $m=strlen($text); $i<$m; $i++)
		{
			$c=ord($text[$i]);
			if($c<=127) {
				@$t.=chr($c); continue; 
			}
			if($c>=192 && $c<=207) {
				@$t.=chr(208).chr($c-48); continue; 
			}
			if($c>=208 && $c<=239) {
				@$t.=chr(208).chr($c-48); continue; 
			}
			if($c>=240 && $c<=255) {
				@$t.=chr(209).chr($c-112); continue; 
			}
			if($c==184) { 
				@$t.=chr(209).chr(209);	continue; 
			}
			if($c==168) { 
				@$t.=chr(208).chr(129);	continue; 
			}
		}
		return $t;
	}
	else
		return iconv('windows-1251', 'utf-8', $text);
}
// PHP конвертер из UTF-8 в Windows-1251
function utf2win($text, $iconv=true) // текст (в кодировке UTF-8), флаг что сначала попытаться использовать функцию iconv
{
	if(!$iconv || !function_exists('iconv'))
	{
		$out = $c1 = '';
		$byte2 = false;
		for($c=0; $c<strlen($text); $c++)
		{
			$i = ord($text[$c]);
			if ($i <= 127)
				$out .= $text[$c];
	
			if($byte2) 
			{
				$new_c2 = ($c1 & 3) * 64 + ($i & 63);
				$new_c1 = ($c1 >> 2) & 5;
				$new_i = $new_c1 * 256 + $new_c2;
				$out_i = $new_i == 1025
					? 168
					: ($new_i==1105 ? 184 : $new_i-848);
				$out .= chr($out_i);
				$byte2 = false;
			}
			if(($i >> 5) == 6) 
			{
				$c1 = $i;
				$byte2 = true;
			}
		}
		return $out;
	}
	else
		return iconv('utf-8', 'windows-1251', $text);
}
// ОПРЕДЕЛЕНИЕ ЧТО КОДИРОВКА ТЕКСТА В UTF8
function detectUTF8($str)
{
	return preg_match('//u', $str);
}
?>