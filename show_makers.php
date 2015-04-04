<?
require('inc/common.php');
$pageID = 25;

if(!isset($_GET['mlink']))
{
	ob_start();
	echo show_makers_list();
	$content = ob_get_clean();
	require('tpl/template.php');
	exit;
}

$link = clean($_GET['mlink']);
if(!$maker = getRow("SELECT * FROM {$prx}makers WHERE link='{$link}'"))
{ header("HTTP/1.0 404 Not Found"); $code = '404'; require('errors.php'); exit; }

ob_start();

$navigate = ' &raquo; <a href="/makers/">Производители</a> &raquo; <span>'.$maker['name'].'</span>';
$title = "Каталог &raquo; Производители &raquo; {$maker['name']}";

foreach(array('title','keywords','description') as $val)
	if($maker[$val]) $$val = $maker[$val];

$cur_page = (int)@$_GET['page'] ? $_GET['page'] : 1;

$f_nalich = isset($_GET['nalich']) ? 1 : 0;

if($f_nalich=='')
	$query = "SELECT A.*, (SELECT SUM(kol) AS kol FROM {$prx}ost WHERE {$prx}ost.articul=A.articul) AS kol FROM {$prx}goods A
						WHERE A.STATUS=1 and A.id_maker='{$maker['id']}'";
else
	$query = "SELECT A.*, (SELECT SUM(kol) AS kol FROM {$prx}ost WHERE {$prx}ost.articul=A.articul) AS kol FROM {$prx}goods A
						WHERE A.STATUS=1 and A.id_maker='{$maker['id']}' HAVING kol>0";

$r = sql($query);
$count_obj = @mysql_num_rows($r); // кол-во объектов в базе
$count_obj_on_page = set("count_goods_client"); // кол-во объектов на странице
$kol_str = ceil($count_obj/$count_obj_on_page); // количество страниц

$query .= " ORDER BY A.name";
$query .= " LIMIT ".($count_obj_on_page*$cur_page-$count_obj_on_page).",".$count_obj_on_page;
//echo $query;

?>
<h1>Каталог</h1>
<?=show_navigate()?>
<div class="htext">Запчасти <?=$maker['name']?></div>
<table id="filters">
	<tr>
		<th><input type="checkbox" name="nalich"<?=$f_nalich?' checked':''?>></th>
		<td>Есть на складе</td>
	</tr>
</table>

<?
$res = sql($query);
$count_goods = @mysql_num_rows($res);
$rowspan = $count_goods*2-1 + 2;
?>
	
<table class="list_tab" width="100%" style="margin:20px 0 10px 0">
	<tr class="tr_head">
		<td class="td1_left" height="32"><?=get_tr('left','1')?></td>
		<td style="background-color:#fff; padding:0 5px;"><span>Артикул</span></td>
		<th rowspan="<?=$rowspan?>"></th>
		<td width="100%" style="background-color:#fff; padding:0 5px;"><span>Наименование товара</span></td>
		<th rowspan="<?=$rowspan?>"></th>
		<td style="background-color:#fff; padding:0 5px;"><span>Количество</span></td>
		<th rowspan="<?=$rowspan?>"></th>
		<td nowrap style="background-color:#fff; padding:0 5px;"><span>Ориентир. цена, руб.</span></td>
		<td class="td1_right"><?=get_tr('right','1')?></td>
	</tr>
	<tr>
		<td colspan="13" class="sep_head"></td>
	</tr>
<?
if($count_goods)
{
	$i=0;
	while($arr = @mysql_fetch_assoc($res))
	{
		$articul = $arr['articul'];
		$price = get_good_price($arr);
		$kol = get_good_kol($arr);			
		$status = get_good_status($arr);
		
		$num = ++$i%2==1 ? 2 : 1;
		$color = $num==2 ? '#fff' : '#fff';
		
		?>  
		<tr class="tr_str">
			<td class="td<?=$num?>_left" height="22"><?=get_tr('left',$num)?></td>
			<td style="background-color:<?=$color?>; text-align:left; padding:0 5px;"><?=$articul?></td>
			<td style="background-color:<?=$color?>; text-align:left; padding:0 5px;">
				<a href="/makers/<?=$maker['link']?>/<?=$arr['link']?>.htm"><?=$arr['name']?></a>
			</td>
			<td style="background-color:<?=$color?>; padding:0 5px;"><?=$kol?></td>
			<td style="background-color:<?=$color?>; padding:0 5px;"><?=$price?></td>
			<td class="td<?=$num?>_right"><?=get_tr('right',$num)?></td>
		</tr>
		<tr>
			<td colspan="13" class="sep_tr"></td>
		</tr>
		<?
	}
}
else
{
	?>
	<tr class="tr_str">
		<td colspan="9">товары не найдены</td>
	</tr>
	<?
}
?>
</table>
<?=show_navigate_pages($kol_str,$cur_page,"/makers/{$maker['link']}/".($f_nalich?'&nalich=1':''));?>
			
<script>
$(function(){
	$('input[name=nalich]').click(function(){
		var nalich = $(this).is(':checked') ? 1 : 0;
		location.href = '/makers/<?=$maker['link']?>/'+(nalich?'&nalich=1':'');
	});
})
</script>
<?

$content = ob_get_clean();

require("tpl/template.php");
?>