<?
require('inc/common.php');
var_dump($_SESSION);
if(!isset($_SESSION['user']))
{
	header("Location: /");
	exit;
}

$user = $_SESSION['user'];
$schet = $user['manager'] ? (int)$_REQUEST['schet'] : 0;

// ------------------СОХРАНЕНИЕ---------------------
if(@$_GET["action"])
{
	switch($_GET["action"])
	{
		case "tocart":
			
			$id_ost = (int)$_GET['id'];
			$kol = (int)$_GET['kol'];
				$kol = $kol ? $kol : 1;
			
			if(!$id_ost) exit;
			if(!$ost = gtv('ost','*',$id_ost)) exit;
			
			/*if(array_key_exists($id_ost,(array)$_SESSION['cart']['ost']))
			{
				if($_SESSION['cart']['ost'][$id_ost]==$kol)
				{
					?>
					<script>
					msg  = 'Превышено допустимое количество заказываемых единиц товара по данному остатку.\n';
					msg += 'В вашей корзине уже <?=get_cart_ost($_SESSION['cart']['ost'][$id_ost])?> шт.\n';
					msg += 'Лимит по остатку - <?=$ost['kol']?> шт.';
					alert(msg);
          </script>
					<?
					exit;
				}
			}*/
				
			$_SESSION['cart']['ost'][$id_ost] = $kol;
			$_SESSION['cart']['itogo'] = getItogo();
			
			$count_goods = 0;
			if(@$_SESSION['cart']['ost'])
				foreach($_SESSION['cart']['ost'] as $k=>$v)
					$count_goods += $v;
			
			?>
			<script>
				if(top.$('#cart_b1').is(':visible'))
				{
					top.$('#cart_b1').hide();
					top.$('#cart_b2').show();
				}
				top.$("#cart_count").html("<?=$count_goods?>");
				top.$("#cart_goods").html("<?=get_cart_goods($count_goods)?>");
				top.$("#cart_itogo").html("<?=$_SESSION['cart']['itogo']?>");
				top.$(document).jAlert('show','alert','<center>Товар добавлен в корзину.</center>');
			</script>
			<?
			break;
			
		case 'calc':
			unset($_SESSION['cart']);
		
			foreach($_POST['kol'] as $id_ost=>$kol)
				$_SESSION['cart']['ost'][$id_ost] = $kol;

			foreach($_POST['price'] as $id_ost=>$val)
				$_SESSION['cart']['price'][$id_ost] = $val;
			
			if(isset($_SESSION['cart']))
				$_SESSION['cart']['itogo'] = getItogo();
				
			?><script>top.location.href = '/cart/<?=$_POST['schet'] ? '&schet=1' : ''?>&gr='+top.$('#group').val();</script><?
			break;
			
		case 'del':
			$id = (int)$_GET['id'];					
			unset($_SESSION['cart']['ost'][$id]);	
			
			$_SESSION['cart']['itogo'] = getItogo();
			if(!$_SESSION['cart']['itogo'])
				unset($_SESSION['cart']);
				
			?><script>top.location.href = '/cart/&gr='+top.$('#group').val();</script><?
			break;
			
		case 'send':
			if(!sizeof($_POST)) exit;
			// ----------- order_info
			ob_start();
			?>
      <table class="subtab">
      	<tr>
          <th width="20">№</th>
          <th>Артикул</th>
          <th>Наименование</th>
          <th>Производитель</th>
          <th>Цена (руб.)</th>
          <th>Кол-во (шт.)</th>
          <th>Стоимость (руб.)</th>
        </tr>
			<?
			$order_info = ob_get_clean();
			
			$bonus = $itogo = $itogo1 = 0;
			$n=1;
			$ost_array = array();
			foreach($_SESSION['cart']['ost'] as $id_ost=>$kol)
			{
				$ost = gtv('ost','*',$id_ost);
				$good = getRow("SELECT A.*,B.link as mlink FROM {$prx}goods A LEFT JOIN {$prx}makers B ON A.id_maker=B.id WHERE articul='{$ost['articul']}'");
		
				$price1 = get_ost_price($ost);
				$price = $schet && $_SESSION['cart']['price'][$id_ost] ? $_SESSION['cart']['price'][$id_ost] : $price1;
				$kol = $kol>$ost['kol'] ? $ost['kol'] : $kol;
				$cost = $price*$kol;
				$cost1 = $price1*$kol;
				$itogo += $cost;
				$itogo1 += $cost1;
				ob_start();
				?>
				<tr>
					<th><?=$n++?></th>
          <td align="left"><?=$good['articul']?></td>
					<td align="left"><a href="http://<?=$_SERVER['SERVER_NAME']?>/makers/<?=$good['mlink']?>/<?=$good['link']?>.htm"><?=$good['name']?></a></td>
					<td align="left"><?
          	if($maker = gtv('makers','name',$good['id_maker']))
							echo $maker;
					?></td>
          <td align="right"><?=number_format($price,0,',',' ')?></td>
					<td align="center"><?=$kol?></td>
					<td align="right"><?=number_format($cost,0,',',' ')?></td>
				</tr>
				<?
				$order_info .= ob_get_clean();
				
				$ost_array[$id_ost] = array('ost'=>$ost,'good'=>$good,'price'=>$price,'kol'=>$kol);
			}
			
			ob_start();
			?>
        <tr>
          <td align="right" colspan="6"><b>Итого:</b></td>
          <td align="right"><b><?=number_format($itogo,0,',',' ')?></b></td>
        </tr>
	 <?	if($schet) 
	 		{
				$bonus = $itogo-$itogo1; ?>
			  <tr>
				 <td align="right" colspan="6"><b>Бонус:</b></td>
				 <td align="right"><b><?=number_format($bonus,0,',',' ')?></b></td>
	        </tr>
		<?	}	?>
      </table>
			<?
			$order_info .= ob_get_clean();
			
			// ----------- user_info
			$user_info = '';
			$mas_fields = array('id','id_manager','pass','note','count_orders','status','manager','organizations','showmaker');
			foreach($_SESSION['user'] as $field=>$val)
			{
				if(!in_array($field,$mas_fields))
					$user_info .= "<b>".getStructureTable('users',$field)."</b>: {$val}<br>";
			}
			
			$id_order = update("orders","	id_user=".$_SESSION['user']['id'].",
																		order_info='".clean($order_info)."',
																		user_info='".clean($user_info)."',
																		cost='".$itogo."',
																		bonus='{$bonus}',
																		`date`=NOW(),
																		notes='".clean($_POST['notes'],true)."'");
			
			if(!$id_order)
				errorAlertClient('show','alert','Во время сохранения Вашего заказа произошла ошибка!<br>Приносим свои извинения.<br>В ближайшее время проблема будет устранена.');
				
			if($bonus)
				update('bonus', "date=NOW(), id_users='{$_SESSION['user']['id']}', pay='{$bonus}', note='Бонус за заказ №{$id_order}'");
							
			// ------ отправляем письма
			$tema = "Заказ №".$id_order." от ".date("d.m.Y")." с сайта ".$_SERVER['SERVER_NAME'];
			$text = "<b>Ваши данные:</b><br>{$user_info}<br><br><b>Ваш заказ:</b><br>{$order_info}";
			$admin_mail = set('admin_email');
			mailTo($admin_mail,$tema,$text,$_SESSION['user']['mail']); // админу
			mailTo($_SESSION['user']['mail'],$tema,$text,set('title')); // клиенту
			
			// -------- фиксируем в журнале
			update("log","`date`=NOW(),text='новый заказ',link='orders.php?red={$id_order}'");
			
			// ----------- заполняем таблицу order_goods
			foreach($_SESSION['cart']['ost'] as $id_ost=>$kol)
			{
				$ost = $ost_array[$id_ost]['ost'];
				$good = $ost_array[$id_ost]['good'];
				$price = $ost_array[$id_ost]['price'];
				$kol = $ost_array[$id_ost]['kol'];
				
				update('order_goods',"id_order='".$id_sorder."',
															id_good='".$good['id']."',
															articul='".clean($good['articul'])."',
															name='".clean($good['name'])."',
															maker='".clean($ost['maker'])."',
															price='".$price."',
															kol='".$kol."'");
				// обновляем остатки
				if($new_kol = $ost['kol']-$kol)
					update('ost',"kol='{$new_kol}'",$ost['id']);
				else
					update('ost','',$ost['id']);
			}
			
			unset($_SESSION['cart']);
			?>
			<script>
			btn_pos = top.absPosition(top.$('#btn_pos')[0]);
			top.$('#pop_order').css({
				'left' : btn_pos.x-80+'px',
				'top'  : btn_pos.y-100+'px'
			});
			top.$('#id_order').html('<?=$id_order?>');	
			top.$('#pop_order').show("slide", { direction: "right" }, 400);
			top.$('#pop_order_exit').bind('click',function(){
				top.location.href = '/cart/';
			});
			top.$('#aSchet').hide();
			</script>
			<?
			break;
	}
	exit();
}

// ------------------ПРОСМОТР---------------------
$title = 'Профиль &raquo; Заказы';

ob_start();

?>
<h1>Профиль</h1>
<?=set('profile_text')?>
<div id="prof_str">
<a href="/profile/" class="link">Изменить данные</a> / <span>Заказы</span> / <a href="/messages/" class="link">Сообщения</a>
<? if($_SESSION['user']['manager']) { ?> / <a href="/organizations/" class="link">Организации</a><? } ?>
</div>
<?

$group = in_array($_GET['gr'],array('orders','pos')) ? $_GET['gr'] : 'orders';
?>
<input type="hidden" id="group" value="<?=$group?>" />

<? // -------------- ТЕКУЩИЙ ЗАКАЗ -------------- ?>
<h3><b>Ваш текущий заказ</b></h3>
<?
if(isset($_SESSION['cart']))
{
	$count_goods = 0;
	foreach($_SESSION['cart']['ost'] as $k=>$v)
		$count_goods ++;
	$rowspan = $count_goods*2-1 + 2;
	
	?>
  <style>
	.pole2 { width:40px; text-align:center; }
	.del { cursor:pointer; }
	</style>
    
  <script type="text/javascript" src="/js/cart.js"></script> 
    
  <form id="order_frm" method="post" target="ajax">
  	<input type="hidden" name="schet" value="<?=$schet?>">
  <table class="list_tab" width="100%">
    <tr class="tr_head">
      <td class="td1_left" height="32"><?=get_tr('left','1')?></td>
      <td style="background-color:#fff; padding:0 5px;"><span>Артикул</span></td>
      <th rowspan="<?=$rowspan?>"></th>
      <td width="100%" style="background-color:#fff;"><span>Наименование товара</span></td>
      <th rowspan="<?=$rowspan?>"></th>
      <td style="background-color:#fff; padding:0 5px;"><span>Производитель</span></td>
      <th rowspan="<?=$rowspan?>"></th>
      <td nowrap style="background-color:#fff; padding:0 5px;"><span>Цена, руб.</span></td>
      <th rowspan="<?=$rowspan?>"></th>
      <td nowrap style="background-color:#fff; padding:0 5px;"><span>Кол-во, шт.</span></td>
      <th rowspan="<?=$rowspan?>"></th>
      <td style="background-color:#fff; padding:0 5px;"><span>Стоимость</span></td>
      <th rowspan="<?=$rowspan?>"></th>
      <td style="background-color:#fff;"></td>
      <td class="td1_right"><?=get_tr('right','1')?></td>
    </tr>
    <tr>
      <td colspan="13" class="sep_head"></td>
    </tr>
	<?
	$itogo=$i=0;
	foreach($_SESSION['cart']['ost'] as $id_ost=>$kol)
	{
		$ost = gtv('ost','*',$id_ost);
		$good = getRow("SELECT A.*,B.link as mlink FROM {$prx}goods A LEFT JOIN {$prx}makers B ON A.id_maker=B.id WHERE articul='{$ost['articul']}'");

		$price = get_ost_price($ost);
		$itogo += $cost = ($schet && $_SESSION['cart']['price'][$id_ost] ? $_SESSION['cart']['price'][$id_ost] : $price) * $kol;
		
		$num = ++$i%2==1 ? 2 : 1;
		$color = $num==2 ? '#fff' : '#fff';
		?>  
    <tr class="tr_str">
      <td class="td<?=$num?>_left" height="32"><?=get_tr('left',$num)?></td>
      <td style="background-color:<?=$color?>; text-align:left; padding:0 5px;"><?=$good['articul']?></td>
      <td style="background-color:<?=$color?>; text-align:left; padding:0 5px;">
        <a href="/makers/<?=$good['mlink']?>/<?=$good['link']?>.htm"><?=$good['name']?></a>
      </td>
      <td style="background-color:<?=$color?>; text-align:left; padding:0 5px;">
        <a href="/makers/<?=$good['mlink']?>/"><?=gtv('makers','name',$good['id_maker'])?></a>
      </td>
      <td style="background-color:<?=$color?>; padding:0 5px 2px;" nowrap>
		<?	if($schet) { ?>
				<?=$price?> - <?=get_ost_price($ost, true)?><br>
				<input name="price[<?=$id_ost?>]" title="Своя цена" onKeyPress="if(!$('#btn_calc').is(':visible'))	$('#btn_order').fadeOut('fast',function(){ $('#btn_calc').fadeIn('fast') });" style="border:1px solid #99CF16; width:90px; text-align:center;" value="<?=$_SESSION['cart']['price'][$id_ost] ? $_SESSION['cart']['price'][$id_ost] : $_SESSION['cart']['price'][$id_ost]=$price?>">
		<?	}
			else
				echo $price;	?>
		</td>
      <td nowrap style="background-color:<?=$color?>; padding:0 5px;">
      	<div class="chkol">
          <div class="str"><a href="" class="more"><div title="+1"></div></a><a href="" class="less"><div title="-1"></div></a></div>
          <div class="field"><input type="text" name="kol[<?=$id_ost?>]" maxval="<?=$ost['kol']?>" value="<?=$kol?>"></div>
        </div>
      </td>
      <td nowrap style="background-color:<?=$color?>; padding:0 5px;"><?=$cost?></td>
      <td style="background-color:<?=$color?>; padding:0 0 0 5px;">
        <img src="/img/dell.png" width="18" height="19" class="del" align="absmiddle" title="удалить товар из заказа" />
        <input type="hidden" value="<?=$id_ost?>" />
      </td>
      <td class="td<?=$num?>_right"><?=get_tr('right',$num)?></td>
    </tr>
    <tr><td colspan="13" class="sep_tr"></td></tr>
		<?		
		}
		$color = '#fff' ? '#fff' : '#fff';
		?>
    <tr class="tr_str">
      <td height="32" colspan="11"></td>
      <td style="background-color:<?=$color?>; padding:0 5px;">
        <b><?=$itogo?></b>
      </td>
      <td colspan="3"></td>
    </tr>
<?	if($schet) { ?>
    <tr><td colspan="13" class="sep_tr"></td></tr>
    <tr class="tr_str">
      <td height="32" colspan="9"></td>
		<td style="background-color:<?=$color?>; padding:0 5px;">Бонус:</td>
      <th rowspan="<?=$rowspan?>"></th>
      <td style="background-color:<?=$color?>; padding:0 5px;">
        <b style="color:red;"><?=$itogo-$_SESSION['cart']['itogo']?></b>
      </td>
      <td colspan="3"></td>
    </tr>
<?	}	?>
  </table>

<?	if($schet) {
		$organizations = unserialize($_SESSION['user']['organizations']);
		$orgs = array();
		foreach((array)$organizations as $key=>$val)
			//if($val)
				$orgs[$val] = $key;	?>
		Организация: <?=dll($orgs, 'onChange="$(\'#notes\').val(\'Организация: \' + this.options[this.selectedIndex].text + \'\n\n\' + this.value)"', '', '')?>
<?	}	?>  
  <table width="100%">
    <tr>
      <td>
        <h3 style="font-size:14px">Комментарии к заказу:</h3>
        <textarea name="notes" id="notes" style="width:100%;" rows="7"></textarea>
      </td>
      <td width="305" id="btn_pos" align="right" valign="bottom">
		<?	if($user['manager'])
			{
				if($schet) { ?>
					<a href="/cart/" id="aSchet">Вернуться к заказу</a>
			<?	} else {	?>
					<a href="/cart/&schet=1" id="aSchet">Сформировать счет</a>
			<?	}	?>
				&nbsp; &nbsp; &nbsp; 
		<?	}	?>
        <input type="image" id="btn_order" src="/img/btn_order.png" width="90" height="24" style="vertical-align:middle;" />
        <input type="image" id="btn_calc" src="/img/btn_calc.png" width="121" height="24" style="display:none; vertical-align:middle;" />
      </td>
    </tr>
  </table>
  </form>
	<?    
}
else
{
	?><div style="padding-left:20px"><i>Ваша корзина пуста</i></div><?
}
?>

<div style="background-color: #fff; padding:5px; color:#000; display:inline-block; margin-top:20px;">
Группировка
<select onchange="location.href='/cart/&gr='+this.value">
	<option value="orders"<?=$group=='orders'?' selected':''?>>по заказам</option>
  <option value="pos"<?=$group=='pos'?' selected':''?>>по позициям</option>
</select>
</div>

<?
if($group=='orders')
{
	// -------------- ДЕЙСТВУЮЩИЕ ЗАКАЗЫ -------------- ?>
  <h3 style="margin-top:20px"><b>Действующие заказы</b></h3>
  <?
	$res = mysql_query("SELECT * FROM {$prx}orders WHERE id_user=".$_SESSION['user']['id']." and status=1 ORDER BY `date` DESC");
	if(@mysql_num_rows($res))
	{
		while($row = mysql_fetch_assoc($res))
		{
			?>
			<h2 style="padding-left:20px; font-size:14px; margin-bottom:5px;">
				Заказ № <span class="numer"><?=$row['id']?></span> от <span class="numer"><?=date('d.m.Y',strtotime($row['date']))?></span>
			</h2>
			<?
			$q = "SELECT A.*,G.link,M.link as mlink FROM {$prx}order_goods A
						INNER JOIN {$prx}goods G ON A.id_good=G.id
						LEFT JOIN {$prx}makers M ON G.id_maker=M.id
						WHERE A.id_order={$row['id']}";
			$r = mysql_query($q);
			$count_goods = @mysql_num_rows($r);
			$rowspan = $count_goods*2-1 + 2;
			
			?>
			<table class="list_tab" width="100%">
			<?=head_list_tr1($rowspan)?>
			<?
			$i=0;
			while($arr = mysql_fetch_assoc($r))
			{
				$num = ++$i%2==1 ? 2 : 1;
				$color = $num==2 ? '#fff' : '#fff';
				?>
				<tr class="tr_str">
					<td class="td<?=$num?>_left" height="32"><?=get_tr('left',$num)?></td>
					<td style="background-color:<?=$color?>; text-align:left; padding:0 5px;"><?=$arr['articul']?></td>
					<td style="background-color:<?=$color?>; text-align:left; padding:0 5px;">
						<a href="/makers/<?=$arr['mlink']?>/<?=$arr['link']?>.htm"><?=$arr['name']?></a>
					</td>
					<td style="background-color:<?=$color?>; padding:0 5px;"><?=$arr['price']?></td>
					<td nowrap style="background-color:<?=$color?>; padding:0 5px;"><?=$arr['kol']?></td>
					<td nowrap style="background-color:<?=$color?>; padding:0 5px;"><?=$arr['price']*$arr['kol']?></td>
					<td nowrap style="background-color:<?=$color?>; padding:0 5px;">
            <?
            if(getField("SELECT COUNT(*) FROM {$prx}og_messages WHERE id_og={$arr['id']} and read=0"))
            {
              ?><a href="" class="mess" og="<?=$arr['id']?>"><img src="/img/nmess.png" /></a><?
            }
            elseif(getField("SELECT COUNT(*) FROM {$prx}og_messages WHERE id_og={$arr['id']} and read=1"))
            {
              ?><a href="" class="mess" og="<?=$arr['id']?>"><img src="/img/mess.png" /></a><?
            }
            ?>
          </td>
          <td style="background-color:<?=$color?>; padding:0 0 0 5px;">
						<?
						switch($arr['status'])
						{
							case "1":
								?><img src="/img/sg1.png" width="24" height="24" align="absmiddle" /><?
								break;
							case "2":
								?><img src="/img/sg2.png" width="24" height="24" align="absmiddle" title="<?=date('d.m.Y H:i:s',strtotime($arr['ds2']))?>" /><?
								break;
							case "3":
								?><img src="/img/sg3.png" width="24" height="24" align="absmiddle" title="<?=date('d.m.Y H:i:s',strtotime($arr['ds3']))?>" /><?
								break;
						}
						?>
						</td>
						<td class="td<?=$num?>_right"><?=get_tr('right',$num)?></td>
					</tr>
					<tr><td colspan="11" class="sep_tr"></td></tr>
					<?		
					}
					$color = '#87c4d9' ? '#fff' : '#fff';
					?>
					<tr class="tr_str">
						<td height="32" colspan="9"></td>
						<td style="background-color:<?=$color?>; padding:0 5px;"><b><?=$row['cost']?></b></td>
						<td colspan="3"></td>
					</tr>
			<?	if($row['bonus']) 
				{ ?>
					<tr><td colspan="11" class="sep_tr"></td></tr>
				<?	$color = '#87c4d9' ? '#fff' : '#fff';	?>
					<tr class="tr_str">
						<td height="32" colspan="7"></td>
						<td style="background-color:<?=$color?>; padding:0 5px;">Бонус:</td>
						<th></th>
						<td style="background-color:<?=$color?>; padding:0 5px; color:red;"><b><?=$row['bonus']?></b></td>
						<td colspan="3"></td>
					</tr>
			<?	}	?>
				</table>
			<?
		}
	}
	else
	{
		?><div style="padding-left:20px"><i>действующих заказов не найдено</i></div><?
	}
}
else
{
	$query = "SELECT A.*,B.id as orderID,B.date as orderDATE,G.link,M.link as mlink FROM {$prx}order_goods A
						INNER JOIN {$prx}orders B ON A.id_order=B.id
						INNER JOIN {$prx}goods G ON A.id_good=G.id
						LEFT JOIN {$prx}makers M ON G.id_maker=M.id
						WHERE B.id_user=".$_SESSION['user']['id']."
						ORDER BY A.id DESC";
	$res = mysql_query($query);
	if($count_goods = @mysql_num_rows($res))
	{
		$rowspan = $count_goods*2-1 + 2;
		?>
    <table class="list_tab" width="100%" style="margin-top:20px">
    <?=head_list_tr3($rowspan)?>
    <?
    $i=0;
    while($arr = mysql_fetch_assoc($res))
    {
      $num = ++$i%2==1 ? 2 : 1;
      $color = $num==2 ? '#fff' : '#fff';
      ?>
      <tr class="tr_str">
        <td class="td<?=$num?>_left" height="32"><?=get_tr('left',$num)?></td>
        <td style="background-color:<?=$color?>; text-align:left; padding:0 5px;"><?=$arr['articul']?></td>
        <td style="background-color:<?=$color?>; text-align:left; padding:0 5px;">
          <a href="/makers/<?=$arr['mlink']?>/<?=$arr['link']?>.htm"><?=$arr['name']?></a>
        </td>
        <td style="background-color:<?=$color?>; padding:0 5px;"><?=$arr['price']?></td>
        <td nowrap style="background-color:<?=$color?>; padding:0 5px;"><?=$arr['kol']?></td>
        <td nowrap style="background-color:<?=$color?>; padding:0 5px;"><?=$arr['price']*$arr['kol']?></td>
        <td nowrap style="background-color:<?=$color?>; padding:0 5px; font-size:11px;">№<?=$arr['orderID']?> от <?=date('d.m.Y',strtotime($arr['orderDATE']))?></td>
        <td nowrap style="background-color:<?=$color?>; padding:0 5px;">
					<?
					if(getField("SELECT COUNT(*) FROM {$prx}og_messages WHERE id_og={$arr['id']} and read=0"))
					{
						?><a href="" class="mess" og="<?=$arr['id']?>"><img src="/img/nmess.png" /></a><?
					}
					elseif(getField("SELECT COUNT(*) FROM {$prx}og_messages WHERE id_og={$arr['id']} and read=1"))
					{
						?><a href="" class="mess" og="<?=$arr['id']?>"><img src="/img/mess.png" /></a><?
					}
					?>
				</td>
        <td style="background-color:<?=$color?>; padding:0 0 0 5px;">
          <?
          switch($arr['status'])
          {
            case "1":
              ?><img src="/img/sg1.png" width="24" height="24" align="absmiddle" /><?
              break;
            case "2":
              ?><img src="/img/sg2.png" width="24" height="24" align="absmiddle" title="<?=date('d.m.Y H:i:s',strtotime($arr['ds2']))?>" /><?
              break;
            case "3":
              ?><img src="/img/sg3.png" width="24" height="24" align="absmiddle" title="<?=date('d.m.Y H:i:s',strtotime($arr['ds3']))?>" /><?
              break;
          }
          ?>
				</td>
				<td class="td<?=$num?>_right"><?=get_tr('right',$num)?></td>
			</tr>
			<tr><td colspan="11" class="sep_tr"></td></tr>
			<?		
		}
		?></table><?
	}
	else
	{
		?><div style="padding-left:20px"><i>действующих заказов не найдено</i></div><?
	}
}


if($group=='orders')
{
	// -------------- ПРОШЛЫЕ ЗАКАЗЫ -------------- ?>
	<h3 style="margin-top:20px"><b>Прошлые заказы</b></h3>
	<?
	$res = mysql_query("SELECT * FROM {$prx}orders WHERE id_user=".$_SESSION['user']['id']." and status=2 ORDER BY `date` DESC");
	if(@mysql_num_rows($res))
	{
		while($row = mysql_fetch_assoc($res))
		{
			?>
			<h2 style="padding-left:20px; font-size:14px; margin-bottom:5px;">
				Заказ № <span class="numer"><?=$row['id']?></span> от <span class="numer"><?=date('d.m.Y',strtotime($row['date']))?></span>
			<?	if(getField("SELECT COUNT(*) FROM {$prx}order_messages WHERE id_order={$row['id']}")) { ?>
					&nbsp; <a href="/messages/" class="mess" og="<?=$arr['id']?>"><img src="/img/nmess.png" align="absmiddle" /></a>
			<?	}	?>
			</h2>
			<?
			$q = "SELECT A.*,G.link,M.link as mlink FROM {$prx}order_goods A
						INNER JOIN {$prx}goods G ON A.id_good=G.id
						LEFT JOIN {$prx}makers M ON G.id_maker=M.id
						WHERE A.id_order={$row['id']}";
			$r = mysql_query($q);
			$count_goods = @mysql_num_rows($r);
			$rowspan = $count_goods*2-1 + 2;
			
			?>
			<table class="list_tab" width="100%">
			<?=head_list_tr2($rowspan)?>
			<?
			$i=0;
			while($arr = mysql_fetch_assoc($r))
			{
				$num = ++$i%2==1 ? 2 : 1;
				$color = $num==2 ? '#fff' : '#fff';
				?>
				<tr class="tr_str">
					<td class="td<?=$num?>_left" height="32"><?=get_tr('left',$num)?></td>
					<td style="background-color:<?=$color?>; text-align:left; padding:0 5px;"><?=$arr['articul']?></td>
					<td style="background-color:<?=$color?>; text-align:left; padding:0 5px;">
						<a href="/makers/<?=$arr['mlink']?>/<?=$arr['link']?>.htm"><?=$arr['name']?></a>
					</td>
					<td style="background-color:<?=$color?>; padding:0 5px;"><?=$arr['price']?></td>
					<td nowrap style="background-color:<?=$color?>; padding:0 5px;"><?=$arr['kol']?></td>
					<td nowrap style="background-color:<?=$color?>; padding:0 5px;"><?=$arr['price']*$arr['kol']?></td>
         <td style="background-color:<?=$color?>; padding:0 0 0 5px;">
            <?
            if(getField("SELECT COUNT(*) FROM {$prx}og_messages WHERE id_og={$arr['id']} and read=0"))
            {
              ?><a href="" class="mess" og="<?=$arr['id']?>"><img src="/img/nmess.png" /></a><?
            }
            elseif(getField("SELECT COUNT(*) FROM {$prx}og_messages WHERE id_og={$arr['id']} and read=1"))
            {
              ?><a href="" class="mess" og="<?=$arr['id']?>"><img src="/img/mess.png" /></a><?
            }
            ?>
          </td>
					<td class="td<?=$num?>_right"><?=get_tr('right',$num)?></td>
				</tr>
				<tr><td colspan="11" class="sep_tr"></td></tr>
				<?		
				}
				$color = '#87c4d9' ? '#fff' : '#fff';
				?>
				<tr class="tr_str">
					<td height="32" colspan="9"></td>
					<td style="background-color:<?=$color?>; padding:0 5px;"><b><?=$row['cost']?></b></td>
					<td colspan="3"></td>
				</tr>
			<?	if($row['bonus']) 
				{ ?>
					<tr><td colspan="11" class="sep_tr"></td></tr>
				<?	$color = '#87c4d9' ? '#fff' : '#fff';	?>
					<tr class="tr_str">
						<td height="32" colspan="7"></td>
						<td style="background-color:<?=$color?>; padding:0 5px;">Бонус:</td>
						<th></th>
						<td style="background-color:<?=$color?>; padding:0 5px; color:red;"><b><?=$row['bonus']?></b></td>
						<td colspan="3"></td>
					</tr>
			<?	}	?>
			</table>
			<?
		}
	}
	else
	{
		?><div style="padding-left:20px"><i>заказы отсутствуют</i></div><?
	}
}

if($_SESSION['user']['manager'])
{
	// -------------- БОНУСЫ -------------- ?>
	<h3 style="margin-top:20px; cursor:pointer;" onClick="$(this).find('span').toggle().end().next().slideToggle();"><b><span>+</span><span style="display:none;">&ndash;</span> <u>Бонусы</u></b> &nbsp; &nbsp; Итого: <b style="font-family:'Times New Roman'; color:red; font-style:normal;"><?=getField("SELECT SUM(pay) AS s FROM {$prx}bonus WHERE id_users='".$_SESSION['user']['id']."'")?></b> ед.</h3>
	<div style="display:none;">
		<?
		$res = mysql_query("SELECT * FROM {$prx}bonus WHERE id_users=".$_SESSION['user']['id']." ORDER BY `date` DESC");
		if(@mysql_num_rows($res))
		{	?>
			<table class="list_tab" width="100%">
			 <tr class="tr_head">
				<td class="td1_left" height="32"><?=get_tr('left','1')?></td>
				<td style="background-color:#fff; padding:0 5px;"><span>Дата</span></td>
				<th></th>
				<td style="background-color:#fff;" nowrap><span>Сумма, ед.</span></td>
				<th></th>
				<td nowrap style="background-color:#fff; padding:0 5px;" width="100%"><span>Комментарий</span></td>
				<td class="td1_right"><?=get_tr('right','1')?></td>
			 </tr>
			 <tr><td colspan="7" class="sep_head"></td></tr>
		<?	while($row = mysql_fetch_assoc($res))
			{
				$i=0;
					?>
				<tr class="tr_str">
					<td class="td<?=$num?>_left" height="32"><?=get_tr('left',$num)?></td>
					<td style="background-color:<?=$color?>; text-align:left; padding:0 5px;" nowrap><?=date('d.m.Y H:i', strtotime($row['date']))?></td>
					<th></th>
					<td style="background-color:<?=$color?>; text-align:right; padding:0 5px;"><?=$row['pay']?></td>
					<th></th>
					<td nowrap style="background-color:<?=$color?>; padding:0 5px; text-align:left;"><?=$row['note']?></td>
					<td class="td<?=$num?>_right"><?=get_tr('right',$num)?></td>
				</tr>
						<tr><td colspan="7" class="sep_tr"></td></tr>
		<?		}
					?>
				</table>
				<?
		}
		else
		{
			?><div style="padding-left:20px"><i>у Вас нет бонусов</i></div><?
		}	?>
	</div>
<?
}	


// --------- ПОЯСНЕНИЯ -------------------
?>
<table style="margin:40px 0 0 0">
<tr>
  <td height="24" style="padding:0 5px 0 20px"><img src="/img/sg1.png" width="24" height="24" align="absmiddle" /></td>
  <td><i>&mdash; ожидает обработки / в пути</i></td>
</tr>
<tr>
  <td height="24" style="padding:0 5px 0 20px"><img src="/img/sg2.png" width="24" height="24" align="absmiddle" /></td>
  <td><i>&mdash; готов к выдаче</i></td>
</tr>
<tr>
  <td height="24" style="padding:0 5px 0 20px"><img src="/img/sg3.png" width="24" height="24" align="absmiddle" /></td>
  <td><i>&mdash; отправлен клиенту</i></td>
</tr>
<tr>
  <td height="24" style="padding:1px 5px 1px 20px"><img src="/img/nmess.png" align="absmiddle" /></td>
  <td><i>&mdash; показать сообщения по позиции (у вас новое сообщение)</i></td>
</tr>
<tr>
  <td height="24" style="padding:0 5px 0 20px"><img src="/img/mess.png" align="absmiddle" /></td>
  <td><i>&mdash; показать сообщения по позиции</i></td>
</tr>
</table>
<?

$content = ob_get_clean();

require("tpl/template.php");
?>