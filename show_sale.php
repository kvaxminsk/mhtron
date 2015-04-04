<?
require('inc/common.php');
$pageID = 29;

ob_start();

$row = getRow("SELECT * FROM {$prx}pages WHERE id=29");

$navigate = ' &raquo; <a href="/makers/">Производители</a> &raquo; <span>Распродажа</span>';
$title = "Каталог &raquo; Производители &raquo; {$row['name']}";

foreach(array('title','keywords','description') as $val)
	if($row[$val]) $$val = $row[$val];

$cur_page = (int)@$_GET['page'] ? $_GET['page'] : 1;

$query = "SELECT A.*,B.price FROM {$prx}goods A
					INNER JOIN {$prx}ost B ON A.articul=B.articul
					WHERE A.status=1 and B.spec=1
					GROUP BY B.articul";

$count_obj = getField(str_replace('A.*,B.price','COUNT(*)',$query)); // кол-во объектов в базе
$count_obj_on_page = set("count_goods_client"); // кол-во объектов на странице
$kol_str = ceil($count_obj/$count_obj_on_page); // количество страниц

$query .= " ORDER BY A.name";
$query .= " LIMIT ".($count_obj_on_page*$cur_page-$count_obj_on_page).",".$count_obj_on_page;
//echo $query;

?>
<h1>Распродажа</h1>
<?=show_navigate()?>

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
    <td width="100%" style="background-color:#fff;"><span>Наименование товара</span></td>
    <th rowspan="<?=$rowspan?>"></th>
    <td style="background-color:#fff; padding:0 5px;"><span>Производитель</span></td>
    <th rowspan="<?=$rowspan?>"></th>
    <td nowrap style="background-color:#fff; padding:0 0 0 5px;"><span>Ориентир. цена, руб.</span></td>
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
		$price = $arr['price'];
		$status = get_good_status($arr);
		
		$num = ++$i%2==1 ? 2 : 1;
		$color = $num==2 ? '#fff' : '#fff';
		
		?>  
    <tr class="tr_str">
      <td class="td<?=$num?>_left" height="32"><?=get_tr('left',$num)?></td>
      <td style="background-color:<?=$color?>; text-align:left; padding:0 5px;"><?=$articul?></td>
      <td style="background-color:<?=$color?>; text-align:left; padding:0 5px;">
        <a href="/goods/<?=$arr['id']?>.htm"><?=$arr['name']?></a>
      </td>
      <td style="background-color:<?=$color?>; text-align:left; padding:0 5px;">
        <?
				if($maker = gtv('makers','name',$arr['id_maker']))
				{
					?><a href="/makers/<?=$arr['id_maker']?>.htm"><?=$maker?></a><?
				}
				?>
      </td>
      <td style="background-color:<?=$color?>; padding:0 0 0 5px;"><?=$price?></td>
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
<?=show_navigate_pages($kol_str,$cur_page,'/sale/');?>
        
<script>
$(function(){
	$('input[name=nalich]').click(function(){
		var nalich = $(this).is(':checked') ? 1 : 0;
		location.href = '/makers/<?=$row['id']?>/'+(nalich?'&nalich=1':'')+'<?=$f_sale?'&sale=1':''?>';
	});
})
</script>
<?

$content = ob_get_clean();

require("tpl/template.php");
?>