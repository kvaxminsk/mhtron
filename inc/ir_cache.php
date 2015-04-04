<?
require("utils.php");

$path = $_GET['path'];
$fname = $_GET['fname'];
$ext = $_GET['ext'] ? $_GET['ext'] : "jpg";

$width = (int)$_GET['width'];
$height = (int)$_GET['height'];

$fon = $width && $height ? true : false;
$rgb = isset($_GET['bkg']) ? hexdec($_GET['bkg']) : 0xffffff;
$quality = isset($_GET['quality']) ? $_GET['quality'] : 90;
$border = isset($_GET['border']) ? 1 : 0;
$noimage = isset($_GET['noimage']) ? $_GET['noimage'] : 'no_image';

// директория запрашиваемого изображения
$dir = $_SERVER['DOCUMENT_ROOT'].$path.$width.'x'.$height."/";

// проверяем есть ли запрашиваемое изображение (маленькое)
if($fe = getFileFormat($dir.$fname.".*"))
{
	$src = $dir.$fname.".".$fe;
	header("Content-type:image/jpeg");
	echo file_get_contents($src);
	exit;
}

// -------------------- формируем картинку ----------------------
$big_src = '';
// проверяем на месте ли большая картинка из которой собираемся делать копию
$fe = getFileFormat($_SERVER['DOCUMENT_ROOT'].$path.$fname.".*");

// если есть
if($fe)
{
	$src = $dir.$fname.".".$fe;
	$big_src = $_SERVER['DOCUMENT_ROOT'].$path.$fname.".".$fe;
}
// если нет
else
{
	// вместо реального (большого) изображения подсовываем no_image.jpg
	if(file_exists($_SERVER['DOCUMENT_ROOT']."/uploads/{$noimage}.{$ext}"))
	{
		$src = $_SERVER['DOCUMENT_ROOT']."/uploads/{$noimage}".$width."x".$height.'.'.$ext;
		// если уже есть маленькая картинка no_image
		if(file_exists($src))
		{
			header("Content-type:image/jpeg");
			echo file_get_contents($src);
			exit;
		}
		$big_src = $_SERVER['DOCUMENT_ROOT']."/uploads/{$noimage}.{$ext}";
	}
}

$size = getimagesize($big_src);
// на всякий случай
if($size === false) die();

$format = strtolower(substr($size['mime'], strpos($size['mime'], '/')+1));
$icfunc = "imagecreatefrom" . $format;

if(!function_exists($icfunc)) die();

$mas = getMinRatioSize(array($size[0],$size[1]),array($width,$height));
$new_width  = $mas[0];
$new_height = $mas[1];

$isrc = $icfunc($big_src);
$idest = imagecreatetruecolor($new_width, $new_height);

imagefill($idest, 0, 0, $rgb);
imagecopyresampled($idest, $isrc, 0, 0, 0, 0, $new_width, $new_height, $size[0], $size[1]);
if($border)
	imagerectangle($idest,0,0,$width-1,$height-1,0x90b7e3);

// -------------------- сохраняем результат ----------------------
// проверяем есть ли папка
// если нет такой папки создаём её
if(!is_dir($dir))
	@mkdir($dir,0777);

// создаем файл
ob_start();
	header("Content-type:image/jpeg");
	imagejpeg($idest, "", 90);
$im = ob_get_clean();

file_put_contents($src,$im);
@chmod($src,0644);

// ---------------- выплёвываем картинку ---------------------
if(file_exists($src))
{
	header("Content-type:image/jpeg");
	echo file_get_contents($src);
}
else
{
	header("Content-type:image/jpeg");
	imagejpeg($idest, "", $quality);
}

imagedestroy($isrc);
imagedestroy($idest);
?>