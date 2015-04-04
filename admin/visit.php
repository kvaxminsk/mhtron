<?
require('inc/common.php');

$rubric = "Статистика посещений";
$tbl = "visit";

$graf = @$_GET["graf"];
if(!$date1 = @$_GET["date1"])
{
	$date1 = date("Y-m-d", mktime(0,0,0,date("m")-2,date("d"),date("Y")));
	$date1 = date("d.m.Y", strtotime($date1));
}
if(!$date2 = @$_GET["date2"])
{
	$date2 = getField("SELECT MAX(date) AS m FROM {$prx}users_visit");
	$date2 = date("d.m.Y", strtotime($date2));
}
if(formatDateTime($date1) > formatDateTime($date2))
{
	$tmp = $date1;
	$date1 = $date2;
	$date2 = $tmp;
}

// -------------------СОХРАНЕНИЕ----------------------
if(isset($_GET["action"]))
{
	switch($_GET['action'])
	{
		case "del":
			sql("TRUNCATE TABLE {$prx}visit_day");
			sql("TRUNCATE TABLE {$prx}visit_all");
			?><script>top.topReload();</script><?
			break;
		
		// данные для диаграммы
		case "getdata":
			//http://www.simplecoding.org/open-flash-chart-stroim-grafiki.html
			//http://kurilka.co.ua/archives/open-flash-chart/
			
			$date1 = formatDateTime($date1);
			$date2 = formatDateTime($date2);
			$minDate = getField("SELECT MIN(date) FROM {$prx}users_visit WHERE date>={$date1}");
			if($date1 < $minDate) $date1 = $minDate;
			$maxDate = getField("SELECT MAX(date) FROM {$prx}users_visit");
			if($date2 > $maxDate) $date2 = $maxDate;

			// создаем массивы с данными
			$r = sql("SELECT date,COUNT(ip) as kol FROM {$prx}users_visit WHERE date>='{$date1}' AND date<='{$date2}' GROUP BY DATE ORDER BY date");
			while($arr = @mysql_fetch_assoc($r))
				$unic[$arr['date']] = $arr['kol'];
			$maxY = getField("SELECT COUNT(ip) AS kol FROM {$prx}users_visit GROUP BY DATE ORDER BY kol DESC LIMIT 1");
			$maxY = round($maxY+$maxY*0.05);
			$maxY = $maxY>=5 ? $maxY : 5;
			
			while($date2 >= $date1)
			{
				list($y,$m,$d) = explode("-", $date1);
				$date1 = date("Y-m-d", mktime(0,0,0,$m,$d+1,$y));
			}
			// подключаем класс со вспомогательными функциями для построения графика
			include_once("../inc/advanced/open-flash-chart/open-flash-chart.php");
			$g = new graph();
			
			$g->set_data(($unic)); // добавляем данные в первый график
			$g->area_hollow( 2, 3, 25, '#003399', 'Уникальные посетители', 11 );
			
			$ch = round(sizeof($unic)/12);
			$i = 0;
			foreach($unic as $key=>$val)
			{
				list($y,$m,$d) = explode("-", $key);
				$labelsX[] = ++$i%$ch==0 ? getRusDate('d m',$key) : ''; // подписи по оси Х
			}
			$g->set_x_labels(($labelsX));
			
			$g->set_y_max($maxY); // максимальное и минимальное значение по оси Y
			$g->set_y_min(0);
			$g->set_num_decimals(0); // количество десятичных знаков
			$g->set_is_thousand_separator_disabled(true); // отключить разделитель тысячей
			//$g->y_label_steps(12); // количество меток по оси Y
			
			$g->bg_colour = '#FAFAFA'; // цвет фона 
			//$g->set_x_label_style(10, '#898989'); // размер и цвет шрифта меток по осям X и Y соответственно
			//$g->set_y_label_style(10, '#898989');
			$g->x_axis_colour('#808080', '#E4E4E4'); // цвета линии осей и сетки
			$g->y_axis_colour('#808080', '#E4E4E4');

			echo $g->render();	// отображаем данные
			break;
	}
	exit;
}

ob_start();
// ------------------ПРОСМОТР--------------------

?>
<div style="width:50%"><?=stat_around(show_stat_visit())?></div>
<br>
<br>
<form>
<div style="padding:0 0 0 20px; font-size:14px; color:#15428B;">
	Диаграмма посещений сайта 
  от <input name="date2" value="<?=$date1?>" class="datepicker" > до <input name="date1" value="<?=$date2?>" class="datepicker">
  <input type="submit" value="Показать" class="but1" onclick="loader(true)" />
</div>
<table class="content" width="100%">
  <tr>
    <th>
      
    </th>
  </tr>
  <tr>
    <td style="padding:20px;">
      <?
        include_once("../inc/advanced/open-flash-chart/open_flash_chart_object.php");
        open_flash_chart_object("100%", 250, "?action=getdata&graf={$graf}&date1={$date1}&date2={$date2}", false, "../inc/advanced/open-flash-chart/");
      ?>
    </td>
  </tr>
</table>
</form>
<?

$content = ob_get_clean();

require("tpl/tpl.php");
?>