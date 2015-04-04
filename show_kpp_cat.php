<?
require('inc/common.php');

ob_start();

$id = (int)@$_GET['id'];
$row = getRow("SELECT * FROM {$prx}kpp_cat WHERE id={$id}");
if(!$row)
{
	ob_start();
	
	?>
    <h1 style="padding:0">Список каталогов</h1>
	<table id="ml">
    <tr valign='top'>
    <?    
	$count_columns = 4;
	$c = getField("SELECT COUNT(*) FROM {$prx}kpp_cat");
	$c = ceil($c/$count_columns);
	for($i; $i<$count_columns+1; $i++)
	{    
		?><td width="<?=round(100/$count_columns)?>%"><?
		$res = sql("SELECT id,name FROM {$prx}kpp_cat WHERE id_parent='0' ORDER BY sort,id LIMIT ".$i*$c.", {$c}");
		while($row = mysql_fetch_array($res))
		{
			?>
			<a href="/kpp_cat/<?=$row['id']?>/" style="display:inline-block; text-align:center;">
				<div style="width:120px; height:80px; background:url(/kpp_cat/120x80/<?=$row['id']?>.jpg) center no-repeat;"></div>
				<?=$row["name"]?>
			</a>
			<br><br><? 
		} 
		?></td><?
	}    
	?>
    </tr>
	</table>
    <?
	
	echo around_makers_list(ob_get_clean());
			
}
elseif(!$row['id_parent'])
{
	$navigate = ' &raquo; <a href="/kpp_cat/">Список каталогов КПП</a> &raquo; <span>'.$row['name'].'</span>';
	$title = "Список каталогов КПП &raquo; {$row['name']}";

	ob_start();
	
	?>
    <h1 style="padding:0">Список каталогов &raquo; <?=$row['name']?></h1>
	<table id="ml">
	<?	$i = 0;
		$res = sql("SELECT * FROM {$prx}kpp_cat WHERE id_parent='{$row['id']}' ORDER BY sort,id");
		while($row = mysql_fetch_array($res))
		{
			if($i++) {	?>
				<tr><td height="10" style="font-size:0;"></td></tr>
		<?	}	?>				
			<tr valign="top">
				<td nowrap><a href="/kpp_cat/<?=$row['id']?>/" style="font-family:Verdana;"><?=$row["name"]?></a></td>
				<td style="padding-left:20px;" width="100%"><?=$row["text"]?></td>
			</tr>
	<?	} ?>
	</table>
    <?
	
	echo around_makers_list(ob_get_clean());
}
else
{	
	$rowp = getRow("SELECT * FROM {$prx}kpp_cat WHERE id='{$row['id_parent']}'");

	$navigate = ' &raquo; <a href="/kpp_cat/">Список каталогов</a> &raquo; <a href="/kpp_cat/'.$rowp['id'].'/">'.$rowp['name'].'</a> &raquo; <span>'.$row['name'].'</span>';
	$title = "Список каталогов &raquo; {$rowp['name']} &raquo; {$row['name']}";
	
	foreach(array('title','keywords','description') as $val)
		if($row[$val]) $$val = $row[$val];
	
	$cur_page = (int)@$_GET['page'] ? $_GET['page'] : 1;
	
	$query = "SELECT * FROM {$prx}kpp_goods WHERE STATUS=1 and id_kpp_cat='{$row['id']}'";
	
	$count_obj = getField(str_replace('*','COUNT(*)',$query)); // кол-во объектов в базе
	$count_obj_on_page = 1; // кол-во объектов на странице
	$kol_str = ceil($count_obj/$count_obj_on_page); // количество страниц
	
	$query .= " ORDER BY sort,id";
	//echo $query;
	
	?>
  <h1>Список каталогов</h1>
  <?=show_navigate()?>
	<script>
		function explode(delimiter, string, limit) 
		{
			var emptyArray = { 0: '' };
			// third argument is not required
			if (arguments.length < 2 || typeof arguments[0] == 'undefined' || typeof arguments[1] == 'undefined')
				return null;
			if (delimiter === '' || delimiter === false || delimiter === null)
				return false;
			if (typeof delimiter == 'function' || typeof delimiter == 'object' || typeof string == 'function' || typeof string == 'object')
				return emptyArray;
			if (delimiter === true)
				delimiter = '1';
			if (!limit)
				return string.toString().split(delimiter.toString());
			else 
			{    // support for limit argument
				var splitted = string.toString().split(delimiter.toString());
				var partA = splitted.splice(0, limit - 1);
				var partB = splitted.join(delimiter.toString());
				partA.push(partB);
				return partA;
			}
		}
		
		// УВЕЛИЧЕНИЕ ИЗОБРАЖЕНИЯ. 
		// Пример: <img src="/uploads/goods/200x-/<?=$row['id']?>.jpg" zoom="100x50;200x100" width="100" style="position:absolute;">
		//			  $(window).load(function(){ $('img[zoom]').imgZoom(); });
		$.fn.imgZoom = function(){
			return this.each(function(){
				var zoom = explode(';',$(this).attr('zoom'));
				var wh1 = explode('x', zoom[0]);
				var wh2 = explode('x', zoom[1]);
				$(this).css({left:this.offsetLeft, top:this.offsetTop}).attr({left:this.offsetLeft, top:this.offsetTop}).hover(
					function(){
						var left = Math.round((wh2[0]-wh1[0])/2);
						var top = Math.round((wh2[1]-wh1[1])/2);
						$(this).stop().css({ 'z-index':1000 }).animate({width:wh2[0]+'px', height:wh2[1]+'px', left:(this.offsetLeft-left)+'px', top:(this.offsetTop-top)+'px' }, 200);
					},
					function(){
						$(this).stop().css({ 'z-index':999 }).animate({width:wh1[0]+'px', height:wh1[1]+'px', left:$(this).attr('left')+'px', top:$(this).attr('top')+'px' }, 200);
					}
				);
			});
		}
		$(window).load(function(){ $('img[zoom]').imgZoom(); });
	</script>
   <div style="background:white; margin-bottom:20px;">
	<?	$i = 0;
		$link = "/kpp_cat/{$id}/".($f_nalich?'&nalich=1':'');
		$res1 = sql($query);
		while($row1 = mysql_fetch_assoc($res1))
		{	?>
			<a href="<?=$link?>&page=<?=(++$i)?>" title="страница <?=$i?>" style="float:left; display:block; width:96px; height:60px; border:1px solid <?=$i==$cur_page ? 'green' : 'white'?>"><img src="/kpp_goods/192x120/<?=$row1['id']?>.jpg" zoom="96x60;192x120" style="position:absolute;" width="96" height="60" alt="<?=$i?>"></a>
	<?	}	?>
		<div style="clear:both;"></div>
    </div>	
<?
	$res = sql($query." LIMIT ".($count_obj_on_page*$cur_page-$count_obj_on_page).",".$count_obj_on_page);
	$row = mysql_fetch_assoc($res);

	$articuls = unserialize($row['articuls']);	?>
	<div align="center">
		<script>
			function setKppArt(articul,link)
			{
				$('a.kpp_a').removeClass('active');
				$('a.kpp_a[articul="'+articul+'"]').addClass('active');
				$.get(
					"/ajax_goods_analog.phtml",
					{
						link: link
					},
					showAnalogs
				);
				function showAnalogs(data) {
					if(data) {
						$('.analogs_active').hide();
						$('.analogs_thbody_active').hide();
						$('.tr_sep_head_active').hide();
						$('.analogs[articul="'+articul+'"] td').empty();
						$('.analogs_active').removeClass('analogs_active');
						$('.analogs_thbody_active').removeClass('analogs_thbody_active');
						$('.tr_sep_head_active').removeClass('tr_sep_head_active');
						$('.analogs[articul="'+articul+'"]').show();
						$('.analogs_thbody[articul="'+articul+'"]').show();
						$('.tr_sep_head[articul="'+articul+'"]').show();
						$('.analogs[articul="'+articul+'"]').addClass('analogs_active');
						$('.analogs_thbody[articul="'+articul+'"]').addClass('analogs_thbody_active');
						$('.tr_sep_head[articul="'+articul+'"]').addClass('tr_sep_head_active');
						$('.analogs[articul="'+articul+'"] td').append(data);
					}

				}
			}

		</script>
		<img src="/kpp_goods/700x-/<?=$row['id']?>.jpg" usemap="#Map" class="map" alt="<?=$row['name']?>">
		<map name="Map">
		<?	$arts = "'---'";
			foreach((array)$articuls as $articul) 
			{
				$arts .= ",'{$articul['articul']}'";
				$arts2 = "'---','{$articul['articul']}'";
				$res2 = sql("SELECT A.*, (SELECT SUM(kol) AS kol FROM {$prx}ost WHERE {$prx}ost.articul=A.articul) AS kol, M.link as mlink FROM {$prx}goods A
							LEFT JOIN {$prx}makers M ON A.id_maker=M.id
							WHERE A.STATUS=1 and A.articul IN ({$arts2})");
				$arr2 = @mysql_fetch_assoc($res2);
		?>
				<area shape="rect" coords="<?=$articul['coords']?>" href="#<?=$articul['articul']?>" onClick="setKppArt('<?=$articul['articul']?>','<?=$arr2['link']?>')" title="<?=$articul['articul']?>">
		<?	}	?>
		</map>	
		<script> $('img.map').maphilight({alwaysOn: true, fillOpacity: 0.1, strokeColor: '3EA304'}); </script>	
	</div>

  <?
	$res1 = sql("SELECT A.*, (SELECT SUM(kol) AS kol FROM {$prx}ost WHERE {$prx}ost.articul=A.articul) AS kol, M.link as mlink FROM {$prx}goods A
							LEFT JOIN {$prx}makers M ON A.id_maker=M.id
							WHERE A.STATUS=1 and A.articul IN ({$arts})");

	$count_goods = @mysql_num_rows($res1);
	$rowspan = $count_goods*3-1 + 2;
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
		while($arr = @mysql_fetch_assoc($res1))
		{
			$articul = $arr['articul'];
			$price = get_good_price($arr);
			$kol = get_good_kol($arr);			
			$status = get_good_status($arr);
			
			$num = ++$i%2==1 ? 2 : 1;
			$color = $num==2 ? '#fff' : '#fff';
			
			?>  
      <tr class="tr_str">
        <td class="td<?=$num?>_left" height="22"><a name="<?=$articul?>"></a><?=get_tr('left',$num)?></td>
        <td style="background-color:<?=$color?>; text-align:left; padding:0 5px;"><?=$articul?></td>
        <td style="background-color:<?=$color?>; text-align:left; padding:0 5px;">
          <a href="/makers/<?=$arr['mlink']?>/<?=$arr['link']?>.htm" class="kpp_a" articul="<?=$articul?>"><?=$arr['name']?></a>
        </td>
        <td style="background-color:<?=$color?>; padding:0 5px;"><?=$kol?></td>
        <td style="background-color:<?=$color?>; padding:0 5px;"><?=$price?></td>
        <td class="td<?=$num?>_right"><?=get_tr('right',$num)?></td>
      </tr>
      <tr>
        <td colspan="13" class="sep_tr"></td>
      </tr>
			<?php
			//$good = getRow("SELECT * FROM {$prx}goods WHERE status=1 AND link='" . $arr['link'] . "'")

			?>
		<tr class="tr_str analogs_thbody"  articul="<?=$articul?>" style="display:none">
			<td class="td1_left" height="32"><?=get_tr('left','1')?></td>
			<td style="background-color:#fff; padding:0 5px;" colspan="7"><h2 style="padding-left:20px">Аналоги</h2></td>
			<td class="td1_right"><?=get_tr('right','1')?></td>
		</tr>
		<tr class="tr_sep_head" articul="<?=$articul?>" style="display:none" >
			<td class="sep_head" colspan="13"></td>
		</tr>
		<tr class="analogs" articul="<?=$articul?>" style="display:none" >
			<td colspan="9">

			</td>
		</tr>

      <?
			//break;
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
<?	
}

$content = ob_get_clean();

require("tpl/template.php");
?>