<?
function getCalendar($month="", $date="", $monthf="?month=%s", $datef="?date=%s") // год-месяц календаря, активная дата, формат строки для перехода календаря, формат строки для даты
{
	if(!$month) $month = date("Y-m");
	ob_start();
	//Массив названий месяцев
	$mon_name = array	("Январь","Февраль","Март","Апрель","Май","Июнь","Июль","Август","Сентябрь","Октябрь","Ноябрь","Декабрь");
	//Массив продолжительностей месяцев
	$nod = array (31,28,31,30,31,30,31,31,30,31,30,31);
	//Определение месяца и года для календаря
	list($ac_year,$ac_month) = explode("-",$month);
	if((int)$ac_month != date("n") || $ac_year != date("Y"))
	{
		$ac_j_dom = 1;
		$ac_j_dow = date("w",mktime(0,0,0,$ac_month,1,$ac_year));
	}
	else
	{
		$ac_j_dom = date("j");
		$ac_j_dow = date("w");
	}
	//Корректировка продолжительности февраля в високосном году
	if ($ac_year%4==0) $nod[1] = 29;
	//Определение предыдущих/следующих месяцев/годов
	$ac_month_next = $ac_month+1<13 ? "{$ac_year}-".($ac_month+1) : ($ac_year+1)."-1";
	$ac_month_prev = $ac_month-1>0 ? "{$ac_year}-".($ac_month-1) : ($ac_year-1)."-12";
	$ac_year_next = ($ac_year+1)."-{$ac_month}";
	$ac_year_prev = ($ac_year-1)."-{$ac_month}";
	//Определение названия месяца
	$ac_mon = $mon_name[$ac_month-1];
	//Корректировка номера дня недели из западно-европейской в русскую
	if($ac_j_dow == 0) $ac_j_dow = 7;
	//Определение дня недели первого дня месяца
	$ac_1_dow = $ac_j_dow - ($ac_j_dom%7 - 1);
	if($ac_1_dow < 1) $ac_1_dow += 7;
	if($ac_1_dow > 7) $ac_1_dow -= 7;
	//Определение числа дней месяца
	$ac_nod = $nod[$ac_month-1];
	//Определение количества недель в месяце
	$ac_now = $ac_1_dow-1+$ac_nod<29 ? 4 : ($ac_1_dow-1+$ac_nod>35 ? 6 : 5);
	//Предотвращение вывода текущего дня для нетекущего месяца
	if($ac_month != date("n") || $ac_year != date("Y")) $ac_j_dom = -10;
?>
	<table class="calendar">
		<tr>
			<th colspan="7"><?=$ac_mon?> <?=$ac_year?></th>
		</tr>
		<tr>
			<th>Пн</th><th>Вт</th><th>Ср</th><th>Чт</th><th>Пт</th><th>Сб</th><th>Вс</th>
		</tr>
	<?		
		for ($i=1; $i<=$ac_now*7; $i++)
		{
			if($i%7==1) {	?><tr><?	}	

			$ac_day = $i-$ac_1_dow+1;
			$ac_date = date("Y-m-d", strtotime("{$ac_year}-{$ac_month}-{$ac_day}"));
			$class = "";
			if($i%7==0 || $i%7==6) $class = " weekend ";
			if($ac_date==date("Y-m-d")) $class = " today ";
			if($ac_date==$date) $class = " active ";
		?>
			<td <?=$class ? "class='{$class}'" : ""?>>
			<?	if($i>=$ac_1_dow && $i<$ac_nod+$ac_1_dow) { ?>
					<a href="<?=sprintf($datef, $ac_date)?>"><?=$ac_day?></a>
			<?	}	?>
			</td>
		<?	
			if(!$i%7) {	?></tr><? }	
		}	?>
		<tr>
			<th colspan="7">
				<a href="<?=sprintf($monthf, $ac_year_prev)?>" title="Год назад">&lt;&lt;</a> &nbsp; 
				<a href="<?=sprintf($monthf, $ac_month_prev)?>" title="Месяц назад">&lt;</a> &nbsp; 
				<a href="<?=sprintf($monthf, date("Y-m"))?>" title="Текущий месяц">•</a> &nbsp; 
				<a href="<?=sprintf($monthf, $ac_month_next)?>" title="Месяц вперед">&gt;</a> &nbsp; 
				<a href="<?=sprintf($monthf, $ac_year_next)?>" title="Год вперед">&gt;&gt;</a>
			</th>
		</tr>
	</table>
<?
	return ob_get_clean();
}
?>