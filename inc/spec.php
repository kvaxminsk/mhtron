<?
function setPriv($login,$pass)
{
	global $prx;
	
	unset($_SESSION['user']);
	
	$row = getRow("SELECT * FROM {$prx}users WHERE login='{$login}' and pass='{$pass}' and status=1");
	if(@$row)
		$_SESSION['user'] = $row;
	
	return isset($_SESSION['user']);	
}
// МЕНЮ
function show_menu()
{	
	global $prx, $pageID;
	
	ob_start();
	
	$addres = $_SERVER['REQUEST_URI'];
	$ids = getArrParents("SELECT id,id_parent FROM {$prx}pages WHERE id='%s'",$pageID);
	
	$mas = getTree("SELECT * FROM {$prx}pages WHERE id_parent='%s' AND to_menu=1 AND status=1 ORDER BY sort");
	if(sizeof($mas))
	{
		?><table id="mtab"><?
		foreach($mas as $vetka)
		{
			$row = $vetka['row'];
			$level = $vetka["level"];
			
			if( $level>0 && !in_array($row['id'],$ids) && !in_array($row['id_parent'],$ids) )
				continue;
			
			$link = $row['link'];
			$lsize = $level>0 ? '14' : '16';
			$otstup = $level>0 ? ' style="padding-left:'.($level*16).'px;"' : '';
			
			$class = $row['id']==$pageID ? 'mlink_' : 'mlink';
			
			if($row['type']=='link')
				$link = $row['link'];
			else
				$link = $row['link']=='/' ? '/' : "/pages/{$row['link']}.htm";
			?>
      <tr>
        <td<?=$otstup?>><a href="<?=$link?>" class="<?=$class?>" style="font-size:<?=$lsize?>px"><?=$row['name']?></a></td>
      </tr>
      <?
		}
		?></table><?
	}
	
	return ob_get_clean();
}
// ИНФОРМАЦИЯ О МЕНЕДЖЕРАХ
function show_manager_info()
{
	global $prx;
	ob_start();
	
	?>
  <h1>Консультация:</h1>
  <?
  $res = $_SESSION['user']['id_manager'] && getField("SELECT id FROM {$prx}managers WHERE `show`='1' AND id=".$_SESSION['user']['id_manager'])
  	? sql("SELECT * FROM {$prx}managers WHERE id=".$_SESSION['user']['id_manager'])
	: sql("SELECT * FROM {$prx}managers WHERE `show`='1' ORDER BY name");
  while($row = @mysql_fetch_assoc($res))
  {
    ?>
	 	<div class="manager"><?=$row['name']?><?=$row['dolgnost'] ? ', '.$row['dolgnost'] : ''?></div>
        <table style="border-collapse:separate; margin-bottom:15px;" cellspacing="5">
		 <? if($row['phone']) { ?>
				<tr>
				 <td>Тел.:</td>
				 <td style="font-family:Arial;"><?=$row['phone']?></td>
			  </tr>
		<?	}
			if($row['icq']) { ?>
				<tr>
				 <td>ICQ:</td>
				 <td><a href="http://wwp.icq.com/scripts/contact.dll?msgto=<?=$row['icq']?>" target="_blank" style="font-family:Arial;"><img src="http://wwp.icq.com/scripts/online.dll?icq=<?=$row['icq']?>&img=5" width="18" height="18" align="absmiddle" title="<?=$row['icq']?>"> <?=$row['icq']?></a></td>
			  </tr>
		<?	}
			if($row['mail']) { ?>
				<tr>
				 <td>Email:</td>
				 <td><a href="mailto:<?=$row['mail']?>" style="font-family:Arial;"><?=$row['mail']?></a></td>
			  </tr>
		<?	}	?>
        </table>
    <?
  }
  ?>
	<h1 style="margin:10px 0 0 0; font-size:16px; color:#603b1d; font-style:normal; font-family:Arial, Helvetica, sans-serif;">Тел.: <?=set('phone')?></h1>
	<h1 style="font-size:16px; color:#603b1d; margin:0; font-style:normal; font-family:Arial, Helvetica, sans-serif;">Факс: <?=set('fax')?></h1>
	<div style="color:#603b1d; font-weight:normal; font:16px Arial; margin-top:7px;">СТО приемка:</div>
	<h1 style="font-size:16px; color:#603b1d; margin:5px 0 0 0; font-style:normal; font-family:Arial, Helvetica, sans-serif;">Тел: <?=set('sto')?></h1>
  <?
	
	return ob_get_clean();
}
// АВТОРИЗАЦИЯ
function show_auth()
{
	ob_start();
	
	if(!isset($_SESSION['user']))
	{
		?>( <a id="enter_link" href="" class="link">Вход</a> )&nbsp;&nbsp;&nbsp;
		<a href="/auth.php?show=reg" class="link">Регистрация</a><?
	}
	else
	{
		?>Доброго дня <a href="/cart/" class="link"><?=$_SESSION['user']['name']?> <?=$_SESSION['user']['surname']?>!</a>&nbsp;&nbsp;
        <a href="/auth.php?action=exit" class="link" target="ajax">Выйти?</a><?
	}
		
	return ob_get_clean();
}
function show_auth_pop()
{
	ob_start();
	?>
	<form action="/auth.php?action=enter" method="post" target="ajax" style="width:100%; height:100%">
  <input type="hidden" name="back" value="<?=$_SERVER['REQUEST_URI']?>">
	<table width="100%" height="100%">
	  <tr>
		<td height="60" colspan="2" style="padding:18px 20px 0 13px" valign="top">
			<a href="/auth.php?show=reg" class="link" style="color:#00437f">Регистрация</a>
		</td>
	  </tr>
	  <tr>
		<td height="27" colspan="2" style="color:#000; font-size:12px; padding:0 0 0 13px">
			<i>Представьтесь, кто вы?</i> <a href="/auth.php?show=remind" class="link" style="color:#00437f; font-size:12px;">Или забыли?</a>
		</td>
	  </tr>
	  <tr>
		<td height="28" style="color:#000; font-size:10px; padding:0 15px 0 13px"><i>Логин</i></td>
		<td width="100%" style="padding:0 20px 0 0"><input type="text" name="secret[login]" class="pole1" style="width:100%"></td>
	  </tr>
	  <tr>
		<td height="28" style="color:#000; font-size:10px; padding:0 15px 0 13px"><i>Пароль</i></td>
		<td style="padding:0 20px 0 0"><input type="password" name="secret[pass]" class="pole1" style="width:100%"></td>
	  </tr>
	  <tr>
		<td height="30"></td>
		<td valign="top" style="padding-top:5px">
			<table>
			  <tr>
				<td width="20"><input type="checkbox" name="secret[save]"></td>
				<td style="color:#000; font-size:10px;"><i>Запомнить меня</i></td>
			  </tr>
			</table>
		</td>
	  </tr>
	  <tr>
		<td></td>
		<td valign="top"><input type="image" src="/img/btn_enter.png" width="76" height="24"></td>
	  </tr>
	</table>
	</form>
	
	<script>
	$(function(){
		//
		$('#enter_link').click(function(){
			if(!$('#pop_auth').is(':visible'))
			{
				$('#pop_auth').css({
					'left' : absPosition($(this)[0]).x-320+'px',
					'top'  : '0'
				});			
				$('#pop_auth').show("slide", { direction: "right" }, 400);
				
				$('#pop_auth_exit').bind('click',function(){
					$('#pop_auth').hide("slide", { direction: "right" }, 300);
					$(this).unbind('click');
				});
			}				
			return false;
		});
	})
	</script>
	<?
	return pop_win('pop_auth','pop_auth_exit',ob_get_clean());
}


function show_order_pop()
{
	ob_start();
	?>
  <style>
	#inner_pop_order td { font-size:14px; color:#00437f; font-style:italic; }
	#inner_pop_order span { color:#000; }
	#id_order { font-weight:bold; font-size:16px; font-family:"Times New Roman", Times, serif; }
	</style>
	<table id="inner_pop_order" width="100%" height="100%">
      <tr>
        <td style="padding:25px 15px 0 15px" valign="top">
        	<div style="margin-bottom:20px">Спасибо за Ваш заказ!</div>
            <div style="margin-bottom:20px">В ближайшее время с Вами свяжется наш менеджер.</div>
            <span>Вашему заказу присвоен номер <span id="id_order">19283</span></span>
        </td>
      </tr>
    </table>
	<?
	return pop_win('pop_order','pop_order_exit',ob_get_clean());
}
function show_zapros($good)
{
	ob_start();
?>
  <form id="frm_zapros" action="/show_goods.php?action=zapros" method="post" target="ajax">
  	<input type="hidden" name="id" value="<?=$good['id']?>">
		<div class="ar">
    	<div class="kol">
      	<div class="h">Количество</div>
        <div class="chkol">
          <div class="str"><a href="" class="more"><div title="+1"></div></a><a href="" class="less"><div title="-1"></div></a></div>
          <div class="field"><input type="text" name="kol" maxval="99" value="1"></div>
        </div>
      </div>
      <div class="notes">
      	<div class="h" style="padding-bottom:5px;">Комментарий <span>(необязательно)</span></div>
      	<textarea name="notes"></textarea>
    	</div>
      <div align="right"><input type="image" src="/img/btn_send.png" width="110" height="24"></div>
    </div>
  </form>
	<?
	return pop_win('pop_zapros','pop_zapros_exit',ob_get_clean());
}



function show_zapros_shassi()
{
	ob_start();
?>
	<form action="/inc/action.php?action=zapros_shassi" method="post" target="ajax" style="width:100%; height:100%">
	<table width="100%" height="100%">
	  <tr>
		<td colspan="2" style="padding:10px 20px 0 13px" valign="top">
		   Запрос детали по номеру шасси
		</td>
	  </tr>
	  <tr>
		<td height="28" style="color:#000; font-size:10px; padding:0 15px 0 13px"><i>Имя</i></td>
		<td width="100%" style="padding:0 20px 0 0"><input type="text" name="info[Имя]" value="<?=$_SESSION['user']['name']?>" class="pole1" style="width:100%"></td>
	  </tr>
	  <tr>
		<td height="28" style="color:#000; font-size:10px; padding:0 15px 0 13px"><i>Телефон для связи</i></td>
		<td width="100%" style="padding:0 20px 0 0"><input type="text" name="info[Телефон для связи]" value="<?=$_SESSION['user']['phone']?>" class="pole1" style="width:100%"></td>
	  </tr>
	  <tr>
		<td height="28" style="color:#000; font-size:10px; padding:0 15px 0 13px"><i>Марка, год  автомобиля</i></td>
		<td width="100%" style="padding:0 20px 0 0"><input type="text" name="info[Марка, год  автомобиля]" class="pole1" style="width:100%"></td>
	  </tr>
	  <tr>
		<td height="28" style="color:#000; font-size:10px; padding:0 15px 0 13px"><i>Номер шасси</i></td>
		<td width="100%" style="padding:0 20px 0 0"><input type="text" name="info[Номер шасси]" class="pole1" style="width:100%"></td>
	  </tr>
	  <tr>
		<td height="28" style="color:#000; font-size:10px; padding:0 15px 0 13px"><i>Список необходимых деталей</i></td>
		<td width="100%" style="padding:0 20px 0 0"><input type="text" name="info[Список необходимых деталей]" class="pole1" style="width:100%"></td>
	  </tr>
	  <tr>
		<td></td>
		<td valign="top"><input type="image" src="/img/btn_send.png" width="110" height="24"></td>
	  </tr>
	</table>
	</form>
<?
	return pop_win('pop_zapros_shassi','pop_zapros_shassi_exit',ob_get_clean());
}

function show_zapros_catalog()
{
	ob_start();
?>
	<form action="/inc/action.php?action=zapros_catalog" method="post" target="ajax" style="width:100%; height:100%">
	<table width="100%" height="100%">
	  <tr>
		<td colspan="2" style="padding:10px 20px 0 13px" valign="top">
		   Запрос каталога
		</td>
	  </tr>
	  <tr>
		<td height="28" style="color:#000; font-size:10px; padding:0 15px 0 13px"><i>Имя</i></td>
		<td width="100%" style="padding:0 20px 0 0"><input type="text" name="info[Имя]" value="<?=$_SESSION['user']['name']?>" class="pole1" style="width:100%"></td>
	  </tr>
	  <tr>
		<td height="28" style="color:#000; font-size:10px; padding:0 15px 0 13px"><i>Телефон</i></td>
		<td width="100%" style="padding:0 20px 0 0"><input type="text" name="info[Телефон для связи]" value="<?=$_SESSION['user']['phone']?>" class="pole1" style="width:100%"></td>
	  </tr>
	  <tr>
		<td height="28" style="color:#000; font-size:10px; padding:0 15px 0 13px"><i>E-mail</i></td>
		<td width="100%" style="padding:0 20px 0 0"><input type="text" name="info[E-mail]" value="<?=$_SESSION['user']['mail']?>" class="pole1" style="width:100%"></td>
	  </tr>
	  <tr>
		<td height="28" style="color:#000; font-size:10px; padding:0 15px 0 13px"><i>Марка, модель коробки</i></td>
		<td width="100%" style="padding:0 20px 0 0"><input type="text" name="info[Марка, модель коробки]" value="пример: КАМАZ, 16S181" title="пример: КАМАZ, 16S181" onFocus="if(this.value==this.title) this.value=''" onBlur="if(this.value=='') this.value=this.title" class="pole1" style="width:100%"></td>
	  </tr>
	  <tr>
		<td height="28" style="color:#000; font-size:10px; padding:0 15px 0 13px"><i>Номер коробки</i></td>
		<td width="100%" style="padding:0 20px 0 0"><input type="text" name="info[Номер коробки]" value="пример: 1304.030.026" title="пример: 1304.030.026" onFocus="if(this.value==this.title) this.value=''" onBlur="if(this.value=='') this.value=this.title" class="pole1" style="width:100%"></td>
	  </tr>
	  <tr>
		<td></td>
		<td valign="top"><input type="image" src="/img/btn_send.png" width="110" height="24"></td>
	  </tr>
	</table>
	</form>
<?
	return pop_win('pop_zapros_catalog','pop_zapros_catalog_exit',ob_get_clean());
}


function pop_win($id,$id_exit,$content)
{
	ob_start();
	?>
    <div class="pop_win_block" id="<?=$id?>">
    <div class="pop_exit" id="<?=$id_exit?>"></div>
    <table class="pop_win_tab">
      <tr>
        <td class="pop_win_l" rowspan="3"></td>
        <td class="pop_win_u"></td>
        <td class="pop_win_r" rowspan="3"></td>
      </tr>
      <tr>
        <td class="pop_win_c"><?=$content?></td>
      </tr>
      <tr>
        <td class="pop_win_b"></td>
      </tr>
    </table>
    </div>
    <?
	return ob_get_clean();
}
// КОРЗИНА
function show_cart()
{
	global $prx;
	
	ob_start();
	
	$display = !isset($_SESSION["cart"]) ? '' : 'display:none;';
	?><div id="cart_b1" style="margin-top:6px;<?=$display?>"><a href="<?=isset($_SESSION['user'])?'/cart/':'/auth.php?show=reg'?>" class="linkv">Личный кабинет</a></div><?
	
	$count_goods = 0;
	if(@$_SESSION['cart']['ost'])
		foreach($_SESSION['cart']['ost'] as $id=>$count)
			$count_goods += $count;
	
	$display = !isset($_SESSION["cart"]) ? 'display:none;' : '';
	?>
	<div id="cart_b2" style="margin-top:6px;<?=$display?>">
	В корзине <a id="cart_count" href="/cart/" class="linkv"><?=$count_goods?></a>
	<span id="cart_goods"><?=get_cart_goods($count_goods)?></span> на сумму <a id="cart_itogo" href="/cart/" class="linkv"><?=$_SESSION["cart"]["itogo"]?></a> руб.
	</div>
	<?
	
	return ob_get_clean();
}
function get_cart_goods($count_goods)
{
	$str = strrev($count_goods);
	$str = (int)substr($str,0,1);
	
	if($str==1) 
		return 'товар';
	elseif($str==2 ||$str==3 || $str==4) 
		return 'товара';
	else 
		return 'товаров';
}
function get_cart_ost($count)
{
	$str = strrev($count);
	$str = (int)substr($str,0,1);
	
	if($str==1) 
		return "находится {$count}";
	elseif($str==2 || $str==3 || $str==4) 
		return "находятся {$count}";
	else 
		return "находятся {$count}";
}
function show_banners($top=false)
{
	global $prx;
	
	$tbl = 'banners';
	
	$res = sql("SELECT * FROM {$prx}{$tbl} WHERE ".($top?'`top`':'`left`')."=1 ORDER BY sort,id");
	if(@mysql_num_rows($res))
	{
		ob_start();
		while($arr = mysql_fetch_assoc($res))
		{
			$id = $arr['id'];
			if($fe = getFileFormat($_SERVER['DOCUMENT_ROOT']."/uploads/{$tbl}/{$id}.*"))
			{
				if($fe=='swf')
				{
					require($_SERVER['DOCUMENT_ROOT'].'/inc/swfheader.php');
					$swf = new swfheader();
					$swf->loadswf($_SERVER['DOCUMENT_ROOT']."/uploads/{$tbl}/{$id}.{$fe}");
					$size = array($swf->width,$swf->height);
					?><div><?=flash("/{$tbl}/{$id}.swf",'width="'.$size[0].'" height="'.$size[1].'"')?></div><?
				}
				else
				{
					$size = getimagesize($_SERVER['DOCUMENT_ROOT']."/uploads/{$tbl}/{$id}.{$fe}");
					$target = mb_strpos($arr['link'],'http://')!==false ? ' target="_blank"' : '';
					?><div><a href="<?=$arr['link']?>"<?=$target?>><img src="/<?=$tbl?>/<?=$id?>.<?=$fe?>" width="100%"></a></div><?
				}
			}
		}
		$data = ob_get_clean();
	}
	
	if($data)
	{
		?><div id="banners_<?=$top?'top':'left'?>" align="center"><?=$data?></div><?
	}
}
// ПОИСК
function show_search()
{
	ob_start();
	
	$search1 = clean(@$_GET['search1']);
	$search2 = clean(@$_GET['search2']);
	
	?>
  <form action="/search.php" id="search_frm" method="get">
	  <table width="450" height="25" align="right">
		 <tr>
			<td width="100%" nowrap><input onKeyPress="$(this).next().val('Название')" type="text" id="st1" name="search1" value="<?=$search1?$search1:'Артикул'?>" style="width:40%;" /><input onKeyPress="$(this).prev().val('Артикул')" type="text" id="st2" name="search2" value="<?=$search2?$search2:'Название'?>"  style="width:60%;" /></td>
			<td align="left" nowrap>
				<a href=""><input type="button" style="height:25px; width:77px; background-image:url(/img/btn_search.png); border:none; cursor:pointer;" onMouseDown="this.style.backgroundImage='url(/img/btn_search_.png)';" onMouseUp="this.style.backgroundImage='url(/img/btn_search.png)';" value="" /></a>
			</td>
		 </tr>
	  </table>
		<input type="submit" style="display:none;">
  </form>
  <script>
	$(function(){
		$frm = $('#search_frm');
		$st1 = $frm.find('#st1');
		$st2 = $frm.find('#st2');
		$btn = $frm.find('a');
		
		//if($.browser.msie)
			//$st.css('padding-top','3px');
		
		$st1.input_fb({
			text : 'Артикул',
			color_focus : "#00437f",
			color_blur : "#00437f"
		});
		$st2.input_fb({
			text : 'Название',
			color_focus : "#00437f",
			color_blur : "#00437f"
		});
		
		$btn.click(function(){ $frm.submit(); return false; });
		
		$frm.submit(function(){
			errors = 0;
			var text = $st1.val()=='Артикул' ? $st2.val() : $st1.val();
			if(text=='Артикул' || text=='Название')
			{
				top.$(document).jAlert('show','alert','<center>Введите пожалуйста запрос</center>');
				errors++;
			}
			else if(text.length<3)
			{
				top.$(document).jAlert('show','alert','<center>Слишком короткий запрос (меньше 3-x символов)</center>');
				errors++;
			}
			if(errors)
				return false;
		});
	});
	</script>
    <?	
	return ob_get_clean();
}
// СПИСОК ПРОИЗВОДИТЕЛЕЙ
function show_makers_list()
{	
	global $prx;
	
	ob_start();
	
	?>
    <h1 style="padding:0">Производители</h1>
	<table id="ml">
    <tr valign='top'>
    <?    
	$count_columns = 4;
	$c = getField("SELECT COUNT(*) FROM {$prx}makers");
	$c = ceil($c/$count_columns);
	for($i; $i<$count_columns+1; $i++)
	{    
		?><td width="<?=round(100/$count_columns)?>%"><?
		$res = sql("SELECT * FROM {$prx}makers ORDER BY name LIMIT ".$i*$c.", {$c}");
		while($row = mysql_fetch_array($res))
		{
			?><a href="/makers/<?=$row['link']?>/"><?=$row["name"]?></a><br><? 
		} 
		?></td><?
	}    
	?>
    </tr>
	</table>
    <?
	
	return around_makers_list(ob_get_clean());
}
function around_makers_list($content)
{
	ob_start();
	?>
    <table class="aml">
      <tr>
        <td class="aml_lu"></td>
        <td class="aml_u"></td>
        <td class="aml_ru"></td>
      </tr>
      <tr>
        <td class="aml_l"></td>
        <td class="aml_c"><?=$content?></td>
        <td class="aml_r"></td>
      </tr>
      <tr>
        <td class="aml_lb"></td>
        <td class="aml_b"></td>
        <td class="aml_rb"></td>
      </tr>
    </table>
	<?
	return ob_get_clean();
}

// НОВОСТИ НА ГЛАВНОЙ
function show_index_news()
{
	global $prx;
	ob_start();
	
	$res = mysql_query("SELECT * FROM {$prx}news WHERE status=1 ORDER BY `date` DESC LIMIT 2");
	if(@mysql_num_rows($res))
	{
		?>
        <h1 style="margin-top:30px; padding-left:20px;">Новости</h1>
        <table width="100%">
		<?
		$i=0;
		while($row = mysql_fetch_assoc($res))
		{
			if(++$i%2==1) echo "<tr>";
			?>
            <td width="50%" valign="top" style="padding:<?=$i%2==1?'0 13px 0 0':'0 0 0 13px'?>">
            	<?=nh($row['date'],$row['avtor'])?>
                <div style="padding:0 5px">
                <div style="margin-bottom:5px;"><a href="/news/<?=$row['id']?>.htm"><?=$row['name']?></a></div>
                <?=$row['preview']?>
                </div>
            </td>
            <?
		}
		?></table><?
	}
	
	return ob_get_clean();
}

function show_left_news()
{
	global $prx;
	ob_start();
	
	$res = mysql_query("SELECT * FROM {$prx}news WHERE status=1 ORDER BY `date` DESC LIMIT 2");
	if(@mysql_num_rows($res))
	{
		?>
        <h1 style="margin-top:30px;">Новости</h1>
        <table width="100%">
		<?
		$i=0;
		while($row = mysql_fetch_assoc($res))
		{
			echo "<tr>";
			?>
            <td valign="top" style="padding-bottom:10px;">
            	<?=nh($row['date'],$row['avtor'])?>
                <div style="padding:0 5px">
                <div style="margin-bottom:5px;"><a href="/news/<?=$row['id']?>.htm"><?=$row['name']?></a></div>
                <?=$row['preview']?>
                </div>
            </td>
            <?
		}
		?></table><?
	}
	
	return ob_get_clean();
}

function nh($date,$avtor,$count_comment='')
{
	?>
    <table class="nh">
      <tr>
        <th><?=date('d.m.Y',strtotime($date))?> Автор: <?=$avtor?></th>
        <?
		if($count_comment!='')
		{
			?><td>Комментариев: <?=$count_comment?></td><?
		}
		?>
      </tr>
    </table>
    <?
}

function get_tr($side,$num)
{
	ob_start();
	?>
    <table class="tab_<?=$side?>">
      <tr><td class="td1<?=$num?>_<?=$side?>"></td></tr>
      <tr><td class="td2<?=$num?>_<?=$side?>"></td></tr>
      <tr><td class="td3<?=$num?>_<?=$side?>"></td></tr>
    </table>
    <?
	return ob_get_clean();
}

function head_list_tr1($rowspan)
{
	ob_start();
	?>
    <tr class="tr_head">
      <td class="td1_left" height="32"><?=get_tr('left','1')?></td>
      <td style="background-color:#fff; padding:0 5px;"><span>Артикул</span></td>
      <th rowspan="<?=$rowspan?>"></th>
      <td width="100%" style="background-color:#fff;"><span>Наименование товара</span></td>
      <th rowspan="<?=$rowspan?>"></th>
      <td nowrap style="background-color:#fff; padding:0 5px;"><span>Цена, руб.</span></td>
      <th rowspan="<?=$rowspan?>"></th>
      <td nowrap style="background-color:#fff; padding:0 5px;"><span>Кол-во, шт.</span></td>
      <th rowspan="<?=$rowspan?>"></th>
      <td style="background-color:#fff; padding:0 5px;"><span>Стоимость</span></td>
      <th rowspan="<?=$rowspan?>"></th>
      <td style="background-color:#fff; padding:0 5px;"><span>Сообщения</span></td>
      <th rowspan="<?=$rowspan?>"></th>
      <td style="background-color:#fff; padding:0 5px;"><span>Статус</span></td>
      <td class="td1_right"><?=get_tr('right','1')?></td>
    </tr>
    <tr><td colspan="11" class="sep_head"></td></tr>
    <?
	return ob_get_clean();
}
function head_list_tr2($rowspan)
{
	ob_start();
	?>
    <tr class="tr_head">
      <td class="td1_left" height="32"><?=get_tr('left','1')?></td>
      <td style="background-color:#fff; padding:0 5px;"><span>Артикул</span></td>
      <th rowspan="<?=$rowspan?>"></th>
      <td width="100%" style="background-color:#fff;"><span>Наименование товара</span></td>
      <th rowspan="<?=$rowspan?>"></th>
      <td nowrap style="background-color:#fff; padding:0 5px;"><span>Цена, руб.</span></td>
      <th rowspan="<?=$rowspan?>"></th>
      <td nowrap style="background-color:#fff; padding:0 5px;"><span>Кол-во, шт.</span></td>
      <th rowspan="<?=$rowspan?>"></th>
      <td style="background-color:#fff; padding:0 5px;"><span>Стоимость</span></td>
      <th rowspan="<?=$rowspan?>"></th>
      <td style="background-color:#fff; padding:0 5px;"><span>Сообщения</span></td>
      <td class="td1_right"><?=get_tr('right','1')?></td>
    </tr>
    <tr><td colspan="11" class="sep_head"></td></tr>
    <?
	return ob_get_clean();
}
function head_list_tr3($rowspan)
{
	ob_start();
	?>
    <tr class="tr_head">
      <td class="td1_left" height="32"><?=get_tr('left','1')?></td>
      <td style="background-color:#fff; padding:0 5px;"><span>Артикул</span></td>
      <th rowspan="<?=$rowspan?>"></th>
      <td width="100%" style="background-color:#fff;"><span>Наименование товара</span></td>
      <th rowspan="<?=$rowspan?>"></th>
      <td nowrap style="background-color:#fff; padding:0 5px;"><span>Цена, руб.</span></td>
      <th rowspan="<?=$rowspan?>"></th>
      <td nowrap style="background-color:#fff; padding:0 5px;"><span>Кол-во, шт.</span></td>
      <th rowspan="<?=$rowspan?>"></th>
      <td style="background-color:#fff; padding:0 5px;"><span>Стоимость</span></td>
      <th rowspan="<?=$rowspan?>"></th>
      <td style="background-color:#fff; padding:0 5px;"><span>Заказ</span></td>
      <th rowspan="<?=$rowspan?>"></th>
      <td style="background-color:#fff; padding:0 5px;"><span>Сообщения</span></td>
      <th rowspan="<?=$rowspan?>"></th>
      <td style="background-color:#fff; padding:0 5px;"><span>Статус</span></td>
      <td class="td1_right"><?=get_tr('right','1')?></td>
    </tr>
    <tr><td colspan="11" class="sep_head"></td></tr>
    <?
	return ob_get_clean();
}
// СТРОКА НАВИГАЦИИ
function show_navigate()
{
	global $navigate;
	
	ob_start();
	if($navigate)
	{
		?>
        <div id="navigate">
        <table><tr><td><a href="/">Главная</a><?=$navigate?></td></tr></table>
        </div>
		<?
	}
	return ob_get_clean();
}
function get_navigate($id,$flag=false)
{
	$str_id = '';
	
	if($flag)
		$str_id = ' &raquo; <a href="/catalog/'.$id.'/">'.get_tab_value('cat',$id,'name').'</a>'.$str_id;
	else
		$str_id = ' &raquo; <span>'.get_tab_value('cat',$id,'name').'</span>'.$str_id;
		
	construct_navigate($id);
	
	return $str_id;
}
function construct_navigate($id)
{
	global $prx, $str_id;
	
	$id_parent = getField("select id_parent from {$prx}cat where id={$id}");
	if($id_parent)
	{
		$result = mysql_query("select id,name from {$prx}cat where id={$id_parent}");
		if (mysql_num_rows($result) > 0) 
		{
			while ($row = mysql_fetch_array($result) ) 
			{
				$str_id = ' &raquo; <a href="/catalog/'.$row['id'].'/">'.$row['name'].'</a>'.$str_id;
				construct_navigate($row['id']);
			}
		}
	}
}

function get_good_price($good)
{
	global $prx;
	
	// выбираем остаток с минимальной ценой
	if($ost = getRow("SELECT * FROM {$prx}ost WHERE articul='{$good['articul']}' ORDER BY price LIMIT 1"))
	{
		$price = get_ost_price($ost);
	}
	// если нет остатков
	else
	{
		if($_SESSION['user'])
		{
			$p1 = $_SESSION['user']['p1'];
			$p2 = $_SESSION['user']['p2'];
			
			if($good['price'.$p2])
				$price = $good['price'.$p2];
			else
				$price = $good['price'.$p1] ? $good['price'.$p1] : $good['price'];
		}
		else
			$price = $good['price5'] ? $good['price5'] : $good['price'];
	}
	
	return round($price);
}

function get_ost_price($ost, $max=false)
{
	global $prx;

	
	$price = $ost['price'];
	
	if($ost['spec'])
		return $price;
	
	$good = getRow("SELECT * FROM {$prx}goods WHERE articul='{$ost['articul']}'");
	
	if($_SESSION['user'] && !$max)
	{
		$p1 = $_SESSION['user']['p1'];
		$p2 = $_SESSION['user']['p2'];
		
		// дилер
		if(!$price) // ----------- УБРАЛ $p2 && -----------
		{
			if($good['price'.$p2])
				$price = $good['price'.$p2];
			else
				$price = $good['price'.$p1];
		}
		// пользователь
		else
		{
			if($good['price'.$p1])
				$price = $good['price'.$p1];
			else
			{
				$koef = set('kz'.$p1);
				$price = $koef ? ($price*$koef) : $price;
			}
		}
	}
	else
	{
		if($good['price5'])
			$price = $good['price5'];
		else
		{
			$koef = set('kz5');
			$price = $koef ? ($price*$koef) : $price;
		}
	}
	
	return round($price);
}

function get_good_kol($good)
{
	if($good['kol'])
		return $good['kol'];
	else
	{
		if($good['da'])
			return date('d.m.Y',strtotime($good['da']));
		else
			return '-';
	}
}

function getItogo()
{
	global $prx;
	
	$itogo = 0;
	if($_SESSION['cart']['ost'])
	{
		foreach($_SESSION['cart']['ost'] as $id_ost=>$kol)
		{
			$ost = gtv('ost','*',$id_ost);
			$price = get_ost_price($ost);
			
			$itogo += $kol * $price;
		}
	}
	
	return $itogo;
}

function get_good_status($row)
{
	/*
	изначально смотрим в колонку "кол-во"...
	если > 0 тогда "в наличии"
	если = 0 тогда смотрим на дату:
	если там есть дата - тогда статус "ожидается"
	если нет даты - под заказ
	*/
	$res = '';
	
	if($row['kol'])
		$res = 'в наличии';
	else
	{
		if($row['da'])
			$res = 'ожидается';
		else
			$res = 'под заказ';
	}
	
	return $res;
}

function get_good_analogues($id)
{
	global $prx, $glres;
	
	$good = getRow("SELECT articul,analogues FROM {$prx}goods WHERE id={$id}");
	
	// есть ли аналоги у данного товара
	if($good['analogues'])
	{
		$good['analogues'] = str_replace('/', ';', $good['analogues']);
		$mas_analogues = explode(';',$good['analogues']);
		foreach($mas_analogues as $analog)
		{			
			// 1) ищем товары у которых в аналогах стоит тот же номер
			$res = mysql_query("SELECT id FROM {$prx}goods WHERE analogues RLIKE '{$analog}'");
			while($arr = @mysql_fetch_assoc($res))
			{
				if(!in_array($arr['id'],(array)$glres))
				{
					$glres[] = $arr['id'];
					//get_good_analogues($arr['id']);
				}
			}
			
			// 2) ищем товары у которых в артикул равен номеру аналога текущего товара
			$id = getField("SELECT id FROM {$prx}goods WHERE articul='{$analog}'");
			if($id && !in_array($id,(array)$glres))
			{
				$glres[] = $id;
				get_good_analogues($id);
			}		
		}
	}
	
	// 3) ищем товары для которых данный товар является аналогом
	if($good['articul'])
	{
		$res = mysql_query("SELECT id FROM {$prx}goods WHERE analogues LIKE '%{$good['articul']}%'");
		while($arr = @mysql_fetch_assoc($res))
		{
			if(!in_array($arr['id'],(array)$glres))
			{
				$glres[] = $arr['id'];
				get_good_analogues($arr['id']);
			}
		}
	}
	
	return (array)$glres;
}

// Страницы навигации
// show_navigate_pages(количество страниц,текущая,'ссылка = ?topic=news&page=')
function show_navigate_pages($x,$p,$link, $all=false)
{
	if($x<2) return '';
	
	?>
    
    <table class="str_page">
  	<tr>
    <td>
	<?
	if($x<4 || $all)
	{
		for($i=1;$i<=$x;$i++)
		{
			if($i==$p)
				echo "[".$i."]&nbsp;";
			else
				echo get_href($link,$i)."&nbsp;";
		}
	}
	else
	{
		if($x==4)
		{
			if($p==1) // 1
				echo "[".$p."]&nbsp;".get_href($link,$p+1)."&nbsp;...&nbsp;".get_href($link,$x);
			if($p==2) // 2
				echo get_href($link,1)."&nbsp;[".$p."]&nbsp;".get_href($link,$p+1)."&nbsp;...&nbsp;".get_href($link,$x);
			if(($p-1)==2) // 3
				echo get_href($link,1)."&nbsp;...&nbsp;".get_href($link,$p-1)."&nbsp;[".$p."]&nbsp;".get_href($link,$x);
			if($p==$x) // 4
				echo get_href($link,1)."&nbsp;...&nbsp;".get_href($link,$x-1)."&nbsp;[".$p."]";
		}
		if($x>4)
		{
			if($p==1) // 1
				echo "[1]&nbsp;".get_href($link,$p+1)."&nbsp;...&nbsp;".get_href($link,$x);
			elseif($p==2) // 2
				echo get_href($link,1)."&nbsp;[".$p."]&nbsp;".get_href($link,$p+1)."&nbsp;...&nbsp;".get_href($link,$x);
			elseif(($p-1)==2) // 3
				echo get_href($link,1)."&nbsp;...&nbsp;".get_href($link,$p-1)."&nbsp;[".$p."]&nbsp;".get_href($link,$p+1)."&nbsp;...&nbsp;".get_href($link,$x);
			elseif(($x-$p)==1) // 4
				echo get_href($link,1)."&nbsp;...&nbsp;".get_href($link,$p-1)."&nbsp;[".$p."]&nbsp;".get_href($link,$x);
			elseif($p==$x) // 5
				echo get_href($link,1)."&nbsp;...&nbsp;".get_href($link,$x-1)."&nbsp;[".$p."]";
			else
				echo get_href($link,1)."&nbsp;...&nbsp;".get_href($link,$p-1)."&nbsp;[".$p."]&nbsp;".get_href($link,$p+1)."&nbsp;...&nbsp;".get_href($link,$x);
		}
	}
	?>
    </td>
    </tr>
	</table>
    <?
}
function get_href($link,$page)
{
	ob_start();
	?>
    <a href="<?=$link?>&page=<?=$page?>"><?=$page?></a>
    <?
	return ob_get_clean();
}

function show_message_frm()
{
	ob_start();
	
	?>
    <h1 style="margin:20px 0">Форма обратной связи</h1>
    <form id="message_frm" action="/messages.php" target="ajax" method="post">
    <table width="100%">
      <tr>
        <td valign="top" style="padding-right:10px;" width="50%">
        	<h3>ФИО:</h3>
            <input type="text" class="pole" name="name" style="margin-bottom:10px; width:100%;">
            <h3>E-mail:</h3>
            <input type="text" class="pole" name="mail" style="margin-bottom:10px; width:100%;">
			<h3>Телефон:</h3>
			<input type="text" class="pole" name="phone" style="margin-bottom:10px; width:100%;">
            <h3>Тема сообщения:</h3>
            <input type="text" class="pole" name="tema" style="width:100%;">
        </td>
        <td valign="top" style="padding-left:10px;">
        	<h3>Текст сообщения:</h3>
        	<textarea name="text" style="width:100%;"></textarea>
        </td>
      </tr>
    </table>
    <div align="right" style="margin-top:20px">
    	<input type="image" src="/img/btn_ok.png" width="50" height="24" />   	
    </div>
    </form>
    
    <script>
	$(function(){
		//
		if($.browser.msie)
			$('#message_frm textarea').height(141);
		else
			$('#message_frm textarea').height(136);
		//
		/*$("#kod1").keypress(function(e){
			if(e.which!=8 && e.which!=0 && $(this).attr('value').length>2)
				return false;
			if(e.which!=8 && e.which!=0 && (e.which<48 || e.which>57))
				return false;    
		});*/	
	});
	</script>
	<?
	
	return ob_get_clean();
}

function show_footer()
{
	global $prx;
	
	?>
    <table id="footer">
      <tr>
        <th id="footer_left"></th>
        <td id="footer_center">
        	<table>
              <tr>
                <td width="250" align="left"><?=break_to_str(set('contacts'))?></td>
                <td align="center">
					<?	
                    $res = mysql_query("SELECT html FROM {$prx}counters WHERE status=1 ORDER BY sort");
                    if(@mysql_num_rows($res))
                    {
                        while($row = @mysql_fetch_assoc($res))
                            echo "&nbsp;{$row['html']}&nbsp;";	
                    }
                    else
                        echo "&nbsp;";
                    ?>
                </td>
                <td width="250" align="right">
                	сделано в <a href="http://mxweb.ru" title="MXWeb.studio" class="lsmall">MXWeb.studio</a><br>совместно с<br>
                  <a href="http://ok-arts.ru/" class="lsmall" target="_blank" title="OK ARTs design team">OK ARTs design team</a>
                </td>
              </tr>
            </table>
        </td>
        <th id="footer_right"><div id="bus"></div></th>
      </tr>
    </table>
    <?
}

// ВЫВОД ALERT ОБ ОШИБКЕ (и прерывание выполнения)
function errorAlertClient($method,$type,$text,$exit=true)
{
	?><script>top.$(document).jAlert('<?=$method?>','<?=$type?>','<?=$text?>')</script><?
	if($exit)
	  exit;
}
?>