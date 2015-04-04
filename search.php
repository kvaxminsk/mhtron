<?
require('inc/common.php');

// ------------------------------- ФУНКЦИИ ------------------------------
// ФОРМИРУЕМ WHERE ДЛЯ ПОИСКА
function getWhere($fields, $all=true) // список полей через запятую, искать все слова или любое слово
{
	global $search;
	if(!$search)
		return 0;
	// общий массив where по каждому полю
	foreach(explode(",", $fields) as $field)
		foreach(explode(" ", $search) as $word)
			$where_field[$field][] = " {$field} LIKE '%{$word}%' ";
	// складываем в строку where по каждому полю
	foreach($where_field as $field)
		$where[] = implode(($all ? "AND" : "OR"), $field);
	// складываем в строку where
	return implode(" OR ", $where);
}
// ВЫДЕЛЯЕМ РЕЗУЛЬТАТЫ ПОИСКА
function boldSearch($text, $chop=false) // где ищем, обрезать результат поиска
{	
	$text = trim(preg_replace('#\s+#us', ' ', strip_tags($text)));
	$text = explode(" ", $text);
	
	if($chop) // обрезаем текст с результатом поиска
	{
		$max_word = 14; // число слов в строке
		$max_line = 3; // число строк
		$max_word = ceil($max_word/2)*2; // приводим к четному числу
		$arr = array();
		$line = 1;
		$min_st = 0;
		$n = count($text);
		for($i=0; $i<$n; $i++)
		{
			// формируем строку
			$st = $i-$max_word/2<$min_st ? $min_st : $i-$max_word/2; // с какого слова
			$min_st = $i = $st+$max_word<$n ? $st+$max_word : $n; // по какое слово
			for($j=$st; $j<$i; $j++)
				$arr[$line] .= $text[$j]." ";
			if(++$line > $max_line)
				break;
		}
		$text = implode("<br>", $arr);
	}
	else
		$text = implode(" ", $text);
	
	return $text;
}

function get_head_block($name)
{
	ob_start();
	
	?><h1><?=$name?></h1><?
	
	return ob_get_clean();
}

// -----------------ПРОСМОТР-------------------
$search1 = @$_GET["search1"] == 'Артикул' ? '' : clean(@$_GET["search1"]);
$search2 = @$_GET["search2"] == 'Название' ? '' : clean(@$_GET["search2"]);

$search = $search1 ? $search1 : $search2;

$title = "Результат поиска &raquo; {$search}";
$navigate = " &raquo; {$title}";

ob_start();

if(mb_strlen($search)<3)
{
	//header("Location: /");
	//exit;
	?><center>Слишком короткий запрос (меньше 3-x символов)</center><?
}
else
{	
	$_SESSION['search'][] = $search.'#'.($search1?'st1':'st2');
	$_SESSION['search'] = array_unique($_SESSION['search']);
	if(sizeof($_SESSION['search']) > 7)
		array_shift($_SESSION['search']);
	// товары
	$txt = '';
	$len = strlen($search);
	for($i=0; $i<$len; $i++)
	{
		$symbol = mb_substr($search,$i,1);
		// если символ цифра или буква
		if(preg_match("/\d/i",$symbol) || preg_match("/\w/i",$symbol))
			$txt .= $symbol.($i<$len-1?'[^a-zа-я0-9]*':'');
	}
	/*	
	$query = "SELECT B.kol,A.* FROM {$prx}goods A
						LEFT JOIN (SELECT articul,SUM(kol) AS kol FROM {$prx}ost GROUP BY articul) B ON A.articul=B.articul
						WHERE A.STATUS=1 and A.articul RLIKE '{$txt}' OR A.analogues RLIKE '{$txt}' OR".getWhere("A.name");
	*/
	$query = "SELECT B.kol,A.*,M.link as mlink FROM {$prx}goods A
						LEFT JOIN (SELECT articul,SUM(kol) AS kol FROM {$prx}ost GROUP BY articul) B ON A.articul=B.articul
						LEFT JOIN {$prx}makers M ON A.id_maker=M.id
						WHERE A.STATUS=1 and (".getWhere("A.name").")";

	if($search1) // ищем только по артикулу
	{
		//$txt = preg_replace('#[^0-9A-Za-zА-Яа-я]#isU','',$search); // оставляем только буквы и цифры;
		$query = "SELECT B.kol,A.*,M.link as mlink FROM {$prx}goods A
					LEFT JOIN (SELECT articul,SUM(kol) AS kol FROM {$prx}ost GROUP BY articul) B ON A.articul=B.articul
					LEFT JOIN {$prx}makers M ON A.id_maker=M.id
					WHERE A.STATUS=1 and (A.articul RLIKE '{$txt}' OR A.analogues RLIKE '{$txt}')";
	}

	
	$res = sql($query);
	//$res = sql("SELECT * FROM {$prx}goods WHERE articul RLIKE '{$txt}' OR analogues RLIKE '{$txt}' OR".getWhere("name"));
	$count_goods = @mysql_num_rows($res);
	$rowspan = $count_goods*2-1 + 2;
	if($count_goods)
	{
		echo get_head_block('Каталог');
		?>
    <div class="search_res" style="margin:0 0 20px 0;">
		<table class="list_tab" width="100%">
		  <tr class="tr_head">
        <td class="td1_left" height="32"><?=get_tr('left','1')?></td>
        <td style="background-color:#fff; padding:0 5px;"><span>Артикул</span></td>
        <th rowspan="<?=$rowspan?>"></th>
        <td width="100%" style="background-color:#fff;"><span>Наименование товара</span></td>
        <th rowspan="<?=$rowspan?>"></th>
        <td style="background-color:#fff; padding:0 5px;"><span>Производитель</span></td>
        <th rowspan="<?=$rowspan?>"></th>
		 <? if($user_showmaker) { ?>
			  <td style="background-color:#fff; padding:0 5px;"><span>Поставщик</span></td>
			  <th rowspan="<?=$rowspan?>"></th>
		<?	}	?>
        <td style="background-color:#fff; padding:0 5px;"><span>Количество</span></td>
				<th rowspan="<?=$rowspan?>"></th>
        <td nowrap style="background-color:#fff; padding:0 5px;"><span>Ориентир. цена, руб.</span></td>
        <td class="td1_right"><?=get_tr('right','1')?></td>
      </tr>
      <tr>
        <td colspan="13" class="sep_head"></td>
      </tr>
		<?
		$i=0;
		while($arr = mysql_fetch_assoc($res))
		{	
			$articul = $arr['articul'];
			$price = get_good_price($arr);
			$kol = get_good_kol($arr);
			$status = get_good_status($arr);
			
			$num = ++$i%2==1 ? 2 : 1;
			$color = $num==2 ? '#fff' : '#fff';
			
			$maker_postav = getArr("SELECT DISTINCT maker FROM {$prx}ost WHERE articul='{$articul}'");
		?>  
			<tr class="tr_str">
			  <td class="td<?=$num?>_left" height="32"><?=get_tr('left',$num)?></td>
			  <td style="background-color:<?=$color?>; text-align:left; padding:0 5px;"><?=boldSearch($articul)?></td>
			  <td style="background-color:<?=$color?>; text-align:left; padding:0 5px;">
					<a href="/makers/<?=$arr['mlink']?>/<?=$arr['link']?>.htm"><?=$arr['name']?></a>
			  </td>
        <td style="background-color:<?=$color?>; text-align:left; padding:0 5px;">
					<?
					if($maker = gtv('makers','link,name',$arr['id_maker']))
					{
						?><a href="/makers/<?=$maker['link']?>/"><?=$maker['name']?></a><?
					}
					?>
			  </td>
	 <? if($user_showmaker) { ?>
	        <td style="background-color:<?=$color?>; text-align:left; padding:0 5px;"><?=implode('<br>', (array)$maker_postav)?></td>
	<?	}	?>
        <td style="background-color:<?=$color?>; padding:0 5px;"><?=$kol?></td>
			  <td style="background-color:<?=$color?>; padding:0 5px;"><?=$price?></td>
			  <td class="td<?=$num?>_right"><?=get_tr('right',$num)?></td>
      </tr>
      <tr>
        <td colspan="13" class="sep_tr"></td>
      </tr>
			<?	
		}
		?>
    </table>
    </div>
		<?
	}
	
	// страницы
	$res = sql("SELECT * FROM {$prx}pages WHERE ".getWhere("name,text"));
	if(@mysql_num_rows($res)>0)
	{
		echo get_head_block('Страницы');
		while($row = mysql_fetch_assoc($res))
		{
			if($row['type']=='link')
				$link = $row['link'];
			else
				$link = $row['link']=="/" ? $row['link'] : "/pages/".$row['link'];
			?>
			<div class="search_res" style="margin:0 0 20px 0;">
				<a href="<?=$link?>"><?=boldSearch($row["name"])?></a><br>
				<div style="margin-top:5px;"><?=boldSearch($row["text"], true)?></div>
			</div>
			<?	
		}
	}
	/*
	// новости
	$res = sql("SELECT * FROM {$prx}news WHERE ".getWhere("name,preview,text")." or DATE_FORMAT(date,'%d.%m.%Y') like '%{$search}%'");
	if(@mysql_num_rows($res))
	{
		echo get_head_block('Новости');
		while($row = mysql_fetch_assoc($res))
		{	
			?>
			<div class="search_res" style="margin:0 0 20px 0;">
				<?=nh($row['date'],$row['avtor'])?>
				<a href="/news/<?=$row["id"]?>.htm"><?=boldSearch($row["name"])?></a><br>
				<div style="margin-top:5px;"><?=boldSearch($row["preview"]." ".$row["text"], true)?></div>
			</div>
			<?	
		}
	}
	
	// статьи
	$res = sql("SELECT * FROM {$prx}articles WHERE ".getWhere("name,preview,text")." or DATE_FORMAT(date,'%d.%m.%Y') like '%{$search}%'");
	if(@mysql_num_rows($res))
	{
		echo get_head_block('Статьи');
		while($row = mysql_fetch_assoc($res))
		{	
			$count_comments = getField("SELECT count(*) FROM {$prx}art_comments WHERE id_art={$row['id']}");
			?>
			<div class="search_res" style="margin:0 0 20px 0;">
				<?=nh($row['date'],$row['avtor'],$count_comments)?>
				<a href="/articles/<?=$row["id"]?>.htm"><?=boldSearch($row["name"])?></a><br>
				<div style="margin-top:5px;"><?=boldSearch($row["preview"]." ".$row["text"], true)?></div>
			</div>
			<?
		}
	}
	*/
}

$data = ob_get_clean();

ob_start();
	
if($data)
{
	?>
  <?=show_navigate()?>
  <?=$data?>
	<script type="text/javascript" src="/js/jquery.highlight.js"></script>
	<script>$(function(){ $(".search_res").highlight('<?=$search?>') })</script>
	<style>span.highlight {color:#3EA304; font-weight:bold; }</style>
	<?
}
else
{
	?><center>По Вашему запросу ничего не найдено.</center><?
}

$content = ob_get_clean();

require('tpl/template.php');
?>