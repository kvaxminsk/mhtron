<?
// ПРОДВИНУТЫЙ INPUT
function aInput($type, $properties, $value="", $spec="") // "тип" поля ввода, свойства поля, значение, переменная для конкректного типа
{
	$rand = rand();
	ob_start();
	switch(strtolower($type))
	{
		// дата время
		case "datetime":
			$time_prop = array(" hh:ii", ", true");
		// дата
		case "date":
		?>
			<link type="text/css" rel="stylesheet" href="/inc/advanced/inc/dhtmlgoodies_calendar.css" media="screen"></link>
			<script type="text/javascript" src="/inc/advanced/inc/dhtmlgoodies_calendar.js"></script>
			<input type="text" <?=$properties?> value="<?=$value?>" onClick="displayCalendar(this,'dd.mm.yyyy<?=$time_prop[0]?>',this<?=$time_prop[1]?>)">
		<?
			break;
		
		// цвет
		case "color":
			?>	
			<link rel="stylesheet" href="/inc/advanced/inc/colorPicker.css" type="text/css"></link>
			<script type="text/javascript" language="javascript" src="/inc/advanced/inc/colorPicker.js"></script>
			<style>
				input.color<?=$rand?> {	
					background-color: <?=$value?>;	
					color: <?=($value && array_sum(html2rgb($value))<500 ? "white" : "black")?>;
				}
			</style>
			<input type="text" <?=$properties?> class="color<?=$rand?>" value="<?=$value?>" onClick="startColorPicker(this);">
			<?
			break;
			
		// цвет hex
		case "picker":
			?>
            <link rel="stylesheet" href="/inc/advanced/colorpicker/css/colorpicker.css" type="text/css" />
            <script type="text/javascript" src="/inc/advanced/colorpicker/js/colorpicker.js"></script>
            <script type="text/javascript" src="/inc/advanced/colorpicker/js/eye.js"></script>
            <script type="text/javascript" src="/inc/advanced/colorpicker/js/utils.js"></script>
            <script type="text/javascript" src="/inc/advanced/colorpicker/js/layout.js?ver=1.0.2"></script>
            <script type="text/javascript">        
			$(document).ready(
			  function()
			  {
				$('.picker').ColorPicker({
					onShow: function (colpkr) {
						$(colpkr).fadeIn(500);
						return false;
					},
					onSubmit: function(hsb, hex, rgb, el) {
						$(el).val(hex);
						$(el).css("background-color","#"+hex);
						$(el).ColorPickerHide();
					},
					onBeforeShow: function () {
						$(this).ColorPickerSetColor(this.value);
					}
				})
				.bind('keyup', function(){
					$(this).ColorPickerSetColor(this.value);
				});
			  }
			);
			</script>
			<input type="text" <?=$properties?> maxlength="6" size="6" class="picker" value="<?=$value?>"<?=$value?' style="text-align:center; background-color:#'.$value.'"':''?> />
			<?
			break;
		
		// цвет hex без js
		case "picker_no_script":
			?>
			<input type="text" <?=$properties?> maxlength="6" size="6" class="picker" value="<?=$value?>"<?=$value?' style="background-color:#'.$value.'"':''?> />
			<?
			break;
		// picker js
		case "picker_script":
			?>
			<link rel="stylesheet" href="/inc/advanced/colorpicker/css/colorpicker.css" type="text/css" />
            <script type="text/javascript" src="/inc/advanced/colorpicker/js/colorpicker.js"></script>
            <script type="text/javascript" src="/inc/advanced/colorpicker/js/eye.js"></script>
            <script type="text/javascript" src="/inc/advanced/colorpicker/js/utils.js"></script>
            <script type="text/javascript" src="/inc/advanced/colorpicker/js/layout.js?ver=1.0.2"></script>
            <script type="text/javascript">        
			$(document).ready(
			  function()
			  {
				$('.picker').ColorPicker({
					onShow: function (colpkr) {
						$(colpkr).fadeIn(500);
						return false;
					},
					onSubmit: function(hsb, hex, rgb, el) {
						$(el).val(hex);
						$(el).css("background-color","#"+hex);
						$(el).ColorPickerHide();
					},
					onBeforeShow: function () {
						$(this).ColorPickerSetColor(this.value);
					}
				})
				.bind('keyup', function(){
					$(this).ColorPickerSetColor(this.value);
				});
			  }
			);
			</script>
			<?
			break;
		
		// ввод числа бегунком
		case "slidernum":
			if(!$spec)
				$spec = array(200, 0, 10); // ширина, минимум, максимум
			preg_match("#onChange\s*=\s*[']+(.*)[']+#iU", $properties, $onChange);  // пример: onChange='alert(\"11\");'
			$onChange = $onChange[1];
		?>	
			<link rel="stylesheet" href="/inc/advanced/inc/dhtmlgoodies_slider.css" type="text/css"></link>
			<script type="text/javascript" language="javascript" src="/inc/advanced/inc/dhtmlgoodies_slider.js"></script>
			<style> input.slidernum { text-align:center; border:none; } </style>
			<div id="slider<?=$rand?>" style="clear:both;">
				<span style="float:left; padding-right:5px; width:<?=$spec[0]?>px;"></span><input type="text" <?=$properties?> size="3" class="slidernum" value="<?=$value?>">
			</div>
			<script>
				_target = $("slider<?=$rand?>").getElementsByTagName('SPAN')[0];
				_input = $("slider<?=$rand?>").getElementsByTagName('INPUT')[0];
				form_widget_amount_slider(_target, _input, <?=$spec[0]?>, <?=$spec[1]?>, <?=$spec[2]?>, '<?=$onChange?>');
			</script>
		<?
			break;
		
		// число (со стрелками вверх/вниз)
		case "arrnum":
			?>
            <table class="arrnum">
              <tr>
                <td><input type="text" <?=$properties?> class="arrnum" value="<?=$value?>" maxlength="2"></td>
                <th><div class="str_up" title="+1"></div><div class="str_down" title="-1"></div></th>
              </tr>
            </table>
			<?
			break;
		
		// антиспам (не забыть учитывать $_SESSION["antispam"] при проверки суммы! - проверять можно функцией antispam())
		case "antispam":
			preg_match("#name\s*=\s*['\"]+(.*)['\"]+#iU", $properties, $name);
			$name = $name[1];
			$v1 = rand(1000,9990);
			$v2 = rand(1,9);
			// немного усложняем жизнь спамерам :)
			if(!@$_SESSION["antispam"]) // дополнительное число
				$_SESSION["antispam"] = rand();
			$str = "{$v1}+{$v2}="; // шифруем строку в html-код
			for($i=0; $i<strlen($str); $i++)
				$html[] = "&#".ord($str[$i]).";";
		?>
			<input type="text" style="border:none; width:54px;" value="<?=implode("", $html)?>" readonly>
			<input type="text" <?=$properties?> value="<?=$value?>" size="10">
			<input type="hidden" value="<?=md5($v1+$v2+$_SESSION["antispam"])?>" name="<?=$name?>_check">
		<?	
			break;
		
		// рейтинг звездами
		case "starrating":
			list($srcStar0, $srcStar1) = array("/inc/advanced/img/star0.gif", "/inc/advanced/img/star1.gif"); // пути к звездам
			list($width, $height) = array("17", "18"); // ширина и высота звезды
			$count = @$spec["count"] ? $spec["count"] : 10; // количесвто звезд
			$active = isset($spec["active"]) ? $spec["active"] : true; // разрешить менять значение
			preg_match("#name\s*=\s*['\"]+(.*)['\"]+#iU", $properties, $name);
			$name = @$name[1];
		?>
			<script>
				function showRating(obj, r) // наведение мышки
				{
					var _img = obj.parentNode.getElementsByTagName('IMG');
					for(var i=0; i<_img.length; i++)
						_img[i].src = i>r ? "<?=$srcStar0?>" : "<?=$srcStar1?>";
				}
				function setRating(obj, r, name) // щелчек мышки
				{
					obj.parentNode.parentNode.getElementsByTagName('DIV')[0].style.width = r*<?=$width?>+'px'; // меняем отображение текущего рейтинга
					$(name).value = r;	// записываем значение в скрытое поле
					//toajax('/setrating.php?id='+name+'&rating='+r); // или обрабатываем по ссылке
				}
			</script>
			<div style="height:<?=$height?>px; width:<?=$width*$count?>px; background-image:url(<?=$srcStar0?>); cursor:<?=$active ? "pointer" : "auto"?>;" onMouseOver="if(<?=$active?>) this.getElementsByTagName('DIV')[1].style.visibility='visible';">
				<div style="height:<?=$height?>px; width:<?=round($value*$width)?>px; background-image:url(<?=$srcStar1?>);"></div>
				<div style="position:relative; height:<?=$height?>px; width:<?=$width*$count?>px; margin-top:-<?=$height?>px; visibility:hidden;" onMouseOut="this.style.visibility='hidden';">
				<?	for($i=0; $i<$count; $i++) {	
						?><img src="<?=$srcStar0?>" onMouseOver="showRating(this,<?=$i?>)" onClick="setRating(this,<?=$i+1?>,'<?=$name?>')"><?
					}
			 ?></div>
			</div>
			<input type="hidden" value="<?=$value?>" name="<?=$name?>" id="<?=$name?>">
		<?
			break;
	}
	return ob_get_clean();
}

// вспомогательная функция для проверки кода у aInput("antispam"...
function antispam($name="code")
{
	$code = (int)$_POST[$name];
	$code_check = mysql_escape_string($_POST["{$name}_check"]);
	return md5($code+$_SESSION["antispam"]) == $code_check;
}

// PHP-КАЛЕНДАРЬ
function calendar($month="", $date="", $monthf="?month=%s", $datef="?date=%s") // год-месяц календаря, активная дата, формат строки для перехода календаря, формат строки для даты
{
	?><link rel="stylesheet" type="text/css" href="/inc/advanced/inc/calendar.css"><?
	require_once("inc/calendar.php");
	return getCalendar($month, $date, $monthf, $datef);
}

// ВЫВОДИМ FCK-РЕДАКТОР
function showFck($name, $value, $toolBar="Default", $width="100%", $height=540)
{	
	require_once($_SERVER['DOCUMENT_ROOT']."/inc/advanced/fck/fckeditor.php");
	
	$oFCKeditor = new FCKeditor($name);
	$oFCKeditor->BasePath = "/inc/advanced/fck/";
	$oFCKeditor->ToolbarSet = $toolBar;
	$oFCKeditor->Height = $height;
	$oFCKeditor->Width = $width;
	$oFCKeditor->Value = stripslashes($value);
	
	return $oFCKeditor->Create();
}

// ОТПРАВКА ПИСЬМА С ВЛОЖЕНИЕМ
function aMail($to, $subject="", $message="", $from="", $files="", $html=true, $charset="windows-1251") // получатель, тема, сообщение, отправитель, файл или массив файлов, в HTML, кодировка
{
	require_once('inc/nomad_mimemail.php');
	$mimemail = new nomad_mimemail();
	
	$mimemail->set_to($to);
	if($from)
		$mimemail->set_from($from);
	$mimemail->set_subject($subject);
	if($html)
		$mimemail->set_html($message);
	else
		$mimemail->set_text($message);
	$mimemail->set_charset($charset);

	if($files)
		foreach((array)$files as $file) 
			$mimemail->add_attachment($file, basename($file));
	
	return $mimemail->send();
}

?>