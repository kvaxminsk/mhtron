<?
function getCalendar($month="", $date="", $monthf="?month=%s", $datef="?date=%s") // ���-����� ���������, �������� ����, ������ ������ ��� �������� ���������, ������ ������ ��� ����
{
	if(!$month) $month = date("Y-m");
	ob_start();
	//������ �������� �������
	$mon_name = array	("������","�������","����","������","���","����","����","������","��������","�������","������","�������");
	//������ ������������������ �������
	$nod = array (31,28,31,30,31,30,31,31,30,31,30,31);
	//����������� ������ � ���� ��� ���������
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
	//������������� ����������������� ������� � ���������� ����
	if ($ac_year%4==0) $nod[1] = 29;
	//����������� ����������/��������� �������/�����
	$ac_month_next = $ac_month+1<13 ? "{$ac_year}-".($ac_month+1) : ($ac_year+1)."-1";
	$ac_month_prev = $ac_month-1>0 ? "{$ac_year}-".($ac_month-1) : ($ac_year-1)."-12";
	$ac_year_next = ($ac_year+1)."-{$ac_month}";
	$ac_year_prev = ($ac_year-1)."-{$ac_month}";
	//����������� �������� ������
	$ac_mon = $mon_name[$ac_month-1];
	//������������� ������ ��� ������ �� �������-����������� � �������
	if($ac_j_dow == 0) $ac_j_dow = 7;
	//����������� ��� ������ ������� ��� ������
	$ac_1_dow = $ac_j_dow - ($ac_j_dom%7 - 1);
	if($ac_1_dow < 1) $ac_1_dow += 7;
	if($ac_1_dow > 7) $ac_1_dow -= 7;
	//����������� ����� ���� ������
	$ac_nod = $nod[$ac_month-1];
	//����������� ���������� ������ � ������
	$ac_now = $ac_1_dow-1+$ac_nod<29 ? 4 : ($ac_1_dow-1+$ac_nod>35 ? 6 : 5);
	//�������������� ������ �������� ��� ��� ���������� ������
	if($ac_month != date("n") || $ac_year != date("Y")) $ac_j_dom = -10;
?>
	<table class="calendar">
		<tr>
			<th colspan="7"><?=$ac_mon?> <?=$ac_year?></th>
		</tr>
		<tr>
			<th>��</th><th>��</th><th>��</th><th>��</th><th>��</th><th>��</th><th>��</th>
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
				<a href="<?=sprintf($monthf, $ac_year_prev)?>" title="��� �����">&lt;&lt;</a> &nbsp; 
				<a href="<?=sprintf($monthf, $ac_month_prev)?>" title="����� �����">&lt;</a> &nbsp; 
				<a href="<?=sprintf($monthf, date("Y-m"))?>" title="������� �����">�</a> &nbsp; 
				<a href="<?=sprintf($monthf, $ac_month_next)?>" title="����� ������">&gt;</a> &nbsp; 
				<a href="<?=sprintf($monthf, $ac_year_next)?>" title="��� ������">&gt;&gt;</a>
			</th>
		</tr>
	</table>
<?
	return ob_get_clean();
}
?>