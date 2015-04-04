<?
require('inc/common.php');

if(isset($_GET['action']))
{
	switch($_GET['action'])
	{
		case 'zapros':
			$id_good = (int)$_POST['id'];
			$kol = (int)$_POST['kol'];
			$notes = clean($_POST['notes']);
			$id_user = (int)$_SESSION['user']['id'];
			
			if($id_good && $kol && $id_user)
			{
				if($id = update('zapros',"id_user={$id_user},id_good={$id_good},kol='{$kol}',notes=".($notes?"'{$notes}'":'NULL').",date=NOW()"))
				{
					?>
					<script>
					top.$pop_zapros.animate({left:-500}, 100, function(){ $(this).hide() });
          top.$(document).jAlert('show','alert','Запрос успешно отправлен!<br>Номер Вашего запроса: <b><?=$id?></b><br>В близжайшее время наш менеджер с Вами свяжется.',function(){
						top.topReload();
					});
          </script>
					<?
				}
			}
			else
			{
				?>
				<script>
				top.$pop_zapros.animate({left:-500}, 100, function(){ $(this).hide() });
				top.$(document).jAlert('show','alert','Произошла ошибка!<br>Ваше сообщение сохранить не удалось. Приносим свои извинения.<br>В ближайшее время проблема будет устранена.',function(){
					top.topReload();
				});
				</script>
        <?
			}
			break;
	}
}

$pageID = 25;

$link = clean($_GET['glink']);
if(!$good = getRow("SELECT * FROM {$prx}goods WHERE status=1 AND link='{$link}'"))
{ header("HTTP/1.0 404 Not Found"); $code = '404'; require('errors.php'); exit; }

$mlink = clean($_GET['mlink']);
if(!$maker = getRow("SELECT * FROM {$prx}makers WHERE link='{$mlink}'"))
{ header("HTTP/1.0 404 Not Found"); $code = '404'; require('errors.php'); exit; } 

if($good['id_maker']!=$maker['id'])
{ header("HTTP/1.0 404 Not Found"); $code = '404'; require('errors.php'); exit; }

$navigate  = ' &raquo; <a href="/makers/">Производители</a>';
$navigate .= ' &raquo; <a href="/makers/'.$maker['link'].'/">'.$maker['name'].'</a>';
$navigate .= ' &raquo; <span>'.$good['name'].'</span>';
$title = "Каталог &raquo; Производители &raquo; {$maker['name']} &raquo; {$good['name']}";

// ---------- ПЕРЕМЕННЫЕ ------------
$articul = $good['articul'];
// ----------------------------------

ob_start();

?>	
<h1>Каталог</h1>
<?=show_navigate()?>
<div class="htext"><?=$good['name']?></div>

<table width="100%" style="margin-top:20px;">
  <tr>
    <?
		if(file_exists($_SERVER['DOCUMENT_ROOT']."/uploads/goods/{$articul}.jpg"))
		{
			?>
      <td width="200" align="left" valign="top">
        <a href="/goods/<?=$articul?>.jpg" class="highslide" onclick="return hs.expand(this)">
        <img src="/goods/160x160/<?=$articul?>.jpg" />
        </a>
        <div class="highslide-caption"><b><?=$good['name']?> (арт. <?=$articul?>)</b></div>
      </td>
			<?
		}
		?>
    <td valign="top" align="right">
    	<?
			$rowspan = $good['analogues'] ? 12 : 10;
			?>
    <table class="list_tab">
    <?
		// Артикул
		$num = 1;
		$color = '#fff';
		?>
      <tr class="tr_str">
        <td class="td<?=$num?>_left" height="32"><?=get_tr('left',$num)?></td>
        <td style="background-color:<?=$color?>; text-align:left; padding:0 5px 0 0;"><span>Артикул</span></td>
        <th rowspan="<?=$rowspan?>"></th>
        <td width="100%" style="background-color:<?=$color?>; text-align:left; padding:0 0 0 5px;"><?=$articul?></td>
        <td class="td<?=$num?>_right"><?=get_tr('right',$num)?></td>
      </tr>
      <tr><td colspan="5" class="sep_tr"></td></tr>
    <?
		// Наименование
		$num = $num==1 ? 2 : 1;
		$color = $color=='#87c4d9' ? '#fff' : '#fff';
		?>  
      <tr class="tr_str">
        <td class="td<?=$num?>_left" height="32"><?=get_tr('left',$num)?></td>
        <td style="background-color:<?=$color?>; text-align:left; padding:0 5px 0 0;"><span>Наименование</span></td>
        <td style="background-color:<?=$color?>; text-align:left; padding:0 0 0 5px;"><?=$good['name']?></td>
        <td class="td<?=$num?>_right"><?=get_tr('right',$num)?></td>
      </tr>
      <tr><td colspan="13" class="sep_tr"></td></tr>
    <?
		// Производитель/Поставщик
		$num = $num==1 ? 2 : 1;
		$color = $color=='#87c4d9' ? '#fff' : '#fff';
		$maker_postav = getArr("SELECT DISTINCT maker FROM {$prx}ost WHERE articul='{$articul}'");
		?>  
      <tr class="tr_str">
        <td class="td<?=$num?>_left" height="32"><?=get_tr('left',$num)?></td>
        <td style="background-color:<?=$color?>; text-align:left; padding:0 5px 0 0;"><span>Производитель/Поставщик</span></td>
        <td style="background-color:<?=$color?>; text-align:left; padding:0 0 0 5px;"><?=$maker['name']?><?=$maker_postav && $user_showmaker ? ' / '.implode(', ', $maker_postav) : ''?></td>
        <td class="td<?=$num?>_right"><?=get_tr('right',$num)?></td>
      </tr>
      <tr><td colspan="13" class="sep_tr"></td></tr>  
    <?
		// Уникальный № производителя
		if($good['analogues'])
		{
			$num = $num==1 ? 2 : 1;
			$color = $color=='#87c4d9' ? '#fff' : '#fff';
			?> 
			<tr class="tr_str">
			  <td class="td<?=$num?>_left" height="32"><?=get_tr('left',$num)?></td>
			  <td style="background-color:<?=$color?>; text-align:left; padding:0 5px 0 0;"><span>Уникальный № производителя</span></td>
			  <td style="background-color:<?=$color?>; text-align:left; padding:0 0 0 5px;"><?=str_replace(';','; ',$good['analogues'])?></td>
			  <td class="td<?=$num?>_right"><?=get_tr('right',$num)?></td>
			</tr>
			<tr><td colspan="13" class="sep_tr"></td></tr>
			<?
		}
		// Розничная цена центрального склада, в руб. с НДС
		$num = $num==1 ? 2 : 1;
		$color = $color=='#87c4d9' ? '#fff' : '#fff';
		?>  
      <tr class="tr_str">
        <td class="td<?=$num?>_left" height="32"><?=get_tr('left',$num)?></td>
        <td nowrap style="background-color:<?=$color?>; text-align:left; padding:0 35px 0 0;"><span>Цена центрального склада, в руб. с НДС</span></td>
        <td style="background-color:<?=$color?>; text-align:left; padding:0 0 0 5px;"><?=round(get_good_price($good))?></td>
        <td class="td<?=$num?>_right"><?=get_tr('right',$num)?></td>
      </tr>
    
    </table>
        
    </td>
  </tr>
</table>

<?
// ----------- ОСТАТКИ -------------------
$res = mysql_query("SELECT * FROM {$prx}ost WHERE articul='{$good['articul']}'");
if($count = @mysql_num_rows($res))
{
	$rowspan = $count*2-1 + 2;
	
	?>
  <style>
  .pole2 { width:40px; text-align:center; }
  </style>
  <h2 style="padding:20px 0 0 20px">Остатки</h2>
	<table class="list_tab" style="margin:0 0 10px 0">
    <tr class="tr_head">
      <td class="td1_left" height="32"><?=get_tr('left','1')?></td>
	<?	if($user_showmaker) { ?>
			<td style="background-color:#fff; padding:0 5px;"><span>Поставщик</span></td>
			<th rowspan="<?=$rowspan?>"></th>
	<?	}	?>
      <td style="background-color:#fff; padding:0 5px;"><span>В наличии, ед.</span></td>
      <th rowspan="<?=$rowspan?>"></th>
      <td style="background-color:#fff; padding:0 5px;"><span>Цена, руб. за ед.</span></td>
      <th rowspan="<?=$rowspan?>"></th>
      <td style="background-color:#fff; padding:0 5px;"><span>Количество</span></td>
      <th rowspan="<?=$rowspan?>"></th>
      <td style="background-color:#fff;"></td>
    	<td class="td1_right"><?=get_tr('right','1')?></td>
    </tr>
    <tr>
      <td colspan="13" class="sep_head"></td>
    </tr>
  <?
	$i=0;
	while($arr = @mysql_fetch_assoc($res))
	{		
		$num = ++$i%2==1 ? 2 : 1;
		$color = $num==2 ? '#fff' : '#fff';
		$price = get_ost_price($arr);		
		
		?>  
		<tr class="tr_str">
			<td class="td<?=$num?>_left" height="32"><?=get_tr('left',$num)?></td>
		<?	if($user_showmaker) { ?>
		      <td style="background-color:<?=$color?>; padding:0 5px; text-align:left;"><?=$arr['maker']?></td>
		<?	}	?>
	      <td style="background-color:<?=$color?>; text-align:center; padding:0 5px;"><?=$arr['kol']?></td>
			<td style="background-color:<?=$color?>; padding:0 5px;">
				<?
        if($arr['spec'])
				{
					?><div class="sale" title="распродажа"></div><?
				}
				?>
				<?=$price?>
			</td>
      <td style="background-color:<?=$color?>; text-align:center; padding:0 5px;">
      	<div class="chkol">
          <div class="str"><a href="" class="more"><div title="+1"></div></a><a href="" class="less"><div title="-1"></div></a></div>
          <div class="field"><input type="text" id="kol<?=$arr['id']?>" maxval="<?=$arr['kol']?>" value="1"></div>
        </div>
      </td>
      <td style="background-color:<?=$color?>; padding:0 0 0 10px;">
      	<?
				if(!isset($_SESSION['user']))
				{
					?><a href="/auth.php?show=reg" title="необходима регистрация"><img src="/img/cart_.png" width="34" height="28" align="absmiddle" /></a><?
				}
				else
				{
					?><input type="hidden" value="<?=$arr['id']?>" /><?
					?><img class="tocart" src="/img/cart.png" width="34" height="28" align="absmiddle" /><?
				}
				?>
      </td>
			<td class="td<?=$num?>_right"><?=get_tr('right',$num)?></td>
		</tr>
		<tr>
			<td colspan="13" class="sep_tr"></td>
		</tr>
		<?
	}
	?>
  </table>
  
  <div style="padding:20px 0 20px 0;">
  Вы можете воспользоваться функцией <span id="zapros">запросить</span>,<br>
  в случае если Вам необходимо больше единиц товара, чем имеется на складе в данный момент
  </div>
	<?
}
else
{
	?>
  <div style="padding:20px 0 20px 0;">
  В данный момент товар находится в пути.<br>
Вы можете обратиться к менеджеру, либо воспользоваться функцией <span id="zapros">уточнить срок поставки</span>.<br><?
  if($good['da'])
	{
		?>Ориентировочная дата прихода: <?=date('d.m.Y',strtotime($good['da']))?><br><?
	}
	?>
  </div>
	<?
}

if($_SESSION['user'])
{
	ob_start();
	echo show_zapros($good);
	$body = ob_get_clean();
}



// СОПУТСТВУЮЩИЕ
if(sizeof($good['soput']))
{
	$good['soput'] = str_replace('/', ';', $good['soput']);
	$soput = explode(';', $good['soput']);
	foreach($soput as $key=>$val) $soput[$key] = trim($val);
	
	$query = "SELECT B.kol,A.*,M.link as mlink FROM {$prx}goods A
						LEFT JOIN (SELECT articul,SUM(kol) AS kol FROM {$prx}ost GROUP BY articul) B ON A.articul=B.articul
						LEFT JOIN {$prx}makers M ON A.id_maker=M.id
						WHERE A.STATUS=1 and A.articul IN('".implode("','",$soput)."')";
	$res = mysql_query($query);
	
	if(mysql_num_rows($res))
	{	
		?><h2 style="padding-left:20px;"><span style="border-bottom:1px dotted; cursor:pointer;" onClick="$(this).parent().next().slideToggle();">Сопутствующие товары</span></h2><?
		$rowspan = sizeof($soput)*2-1 + 2;
		?>
		<div style="display:none;">
		 <table class="list_tab" width="100%" style="margin:0 0 20px 0;">
			<tr class="tr_head">
			  <td class="td1_left" height="32"><?=get_tr('left','1')?></td>
			  <td style="background-color:#fff; padding:0 5px;"><span>Артикул</span></td>
			  <th rowspan="<?=$rowspan?>"></th>
			  <td width="100%" style="background-color:#fff;"><span>Наименование товара</span></td>
			  <th rowspan="<?=$rowspan?>"></th>
			  <td style="background-color:#fff; padding:0 5px;"><span>Производитель</span></td>
					<th rowspan="<?=$rowspan?>"></th>
			  <td style="background-color:#fff; padding:0 5px;"><span>Количество</span></td>
					<th rowspan="<?=$rowspan?>"></th>
			  <td nowrap style="background-color:#fff; padding:0 0 0 5px;"><span>Ориентир. цена, руб.</span></td>
			  <td class="td1_right"><?=get_tr('right','1')?></td>
			</tr>
			<tr>
			  <td colspan="13" class="sep_head"></td>
			</tr>
		 <?
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
			  <td class="td<?=$num?>_left" height="32"><?=get_tr('left',$num)?></td>
			  <td style="background-color:<?=$color?>; text-align:left; padding:0 5px;"><?=$articul?></td>
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
			<td style="background-color:<?=$color?>; padding:0 5px;"><?=$kol?></td>
			  <td style="background-color:<?=$color?>; padding:0 0 0 5px;"><?=$price?></td>
			  <td class="td<?=$num?>_right"><?=get_tr('right',$num)?></td>
		 </tr>
		 <tr>
			<td colspan="13" class="sep_tr"></td>
		 </tr>
			<?
		}	?>
		</table>
		</div>
<?	}
}
?>

<script>
$(function(){
	$('.chkol').each(function(){
		var mval = $(this).find('input').attr('maxval');
		$(this).Chkol({maxlen:mval.length,maxval:mval,color:true});
	});
	
	$('.tocart').click(function(){
		id = $(this).prev('input').val();
		kol = $('input#kol'+id).val();
		if(id&&kol) toCart(id,kol);
	});
	
	$('span#zapros').click(function(){
		<?
		if($_SESSION['user'])
		{
			?>
			$pop_zapros = $('#pop_zapros');
			if($pop_zapros.size() && !$pop_zapros.is(':visible'))
			{
				var bs = BodySize();
				$pop_zapros.css('top',Math.round( (bs.height/2) - ($pop_zapros.height()/2) + $('body').scrollTop() ));
				setTimeout(function(){
					$pop_zapros.show().animate({left: Math.round((bs.width/2) - ($pop_zapros.width()/2)) + 'px'}, 100);
				},400);
								
				$('#pop_zapros_exit').bind('click',function(){
					$pop_zapros.animate({left:-500}, 100, function(){ $(this).hide() });
				});
			}
			<?
		}
		else
		{
			?>$(document).jAlert('show','alert','<center>Данной услугой могут воспользоваться<br>только зарегистрированные пользователи</center>');<?
		}
		?>
	});
})
</script>
<?


foreach(array('title','keywords','description') as $val)
	if($good[$val]) $$val = $good[$val];

$content = ob_get_clean();

require("tpl/template.php");
?>