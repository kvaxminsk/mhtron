<?
require('inc/common.php');

$rubric = 'Заказы';
$tbl = 'orders';

// ------------------- СОХРАНЕНИЕ ------------------------
if(isset($_GET["action"]))
{
	$id = @$_GET['id'] ? (int)@$_GET['id'] : 0;
	
	switch(@$_GET['action'])
	{
		// ----------------- сохранение заказа
		case 'save':
			
			$id_order = $id;
			$id_user = (int)$_POST['id_user'];
			$status = (int)$_POST['status'];
			$order_cost = 0;
			$notes = clean($_POST['notes']);
			
			$ids = array();
			foreach((array)$_POST['gid'] as $num=>$gid)
			{
				$good = gtv('goods','articul,name',$gid);
				$gprice = (int)$_POST['gprice'][$num];
				$gkol = (int)$_POST['gkol'][$num];
				$gs = $_POST['gs'][$num];
				
				$ds = '';
				if($gs==2) $ds = ',ds2=NOW()';
				elseif($gs==3) $ds = ',ds3=NOW()';

				// если данный товар уже есть в заказе
				$id_og = getField("SELECT id FROM {$prx}order_goods WHERE id_order='{$id}' AND id_good={$gid}");
				$set = "id_order='{$id_order}',
								id_good='{$gid}',
								articul='".clean($good['articul'])."',
								name='".clean($good['name'])."',
								price='{$gprice}',
								kol='{$gkol}',
								status='{$gs}'{$ds}";
				if(update('order_goods',$set,$id_og))
					$ids[] = $gid;
				$order_cost += $gprice*$gkol;
			}
			
			if(!sizeof($ids))
				errorAlert('В заказе должен быть хотя бы один товар.');
			
			sql("DELETE FROM {$prx}order_goods WHERE id_good NOT IN (".implode(',',$ids).") and id_order={$id_order}");
			
			if($status==2)
			{
				// проверяем все ли товары получены клиентом
				if(getField("SELECT count(*) FROM {$prx}order_goods WHERE id_order={$id} and status<>3"))
					$status = 1;
			}
			
			$set = "cost='{$order_cost}',notes='{$notes}',status='{$status}'";
			
			if($id_order = update($tbl,$set,$id_order))
			{
				// отправляем клиенту уведомление
				$order = getRow("SELECT id_user,`date` FROM {$prx}orders WHERE id={$id_order}");
				if($user  = getRow("SELECT name,surname,mail FROM {$prx}users WHERE id={$order['id_user']}"))
				{
					$tema = 'Статусы заказов';
					ob_start();
					?>Добрый день <?=$user['name']?> <?=$user['surname']?>.<br>
          Уведомляем Вас о статусах заказа.<br>
					Номер заказа <b><?=$id_order?></b> от <b><?=date('d.m.Y',strtotime($order['date']))?></b><br><br>
					<table cellpadding="5" cellspacing="0" border="1">
          	<tr>
            	<th width="20"></th>
              <th>Артикул</th>
              <th>Наименование</th>
              <th>Кол-во (шт.)</th>
              <th>Цена (руб.)</th>
              <th>Статус</th>
            </tr>
					<?
					$r = mysql_query("SELECT * FROM {$prx}order_goods WHERE id_order={$id_order}");
					$n=1;
					while($good = @mysql_fetch_assoc($r))
					{
						if($good['status']==1) $status = 'ожидает обработки';
						if($good['status']==2) $status = 'готов к выдаче';
						if($good['status']==3) $status = 'отправлен клиенту';
						?>
            <tr>
              <td><?=$n++?></td>
              <td align="center"><?=$good['articul']?></td>
              <td align="left"><a href="http://<?=$_SERVER['SERVER_NAME']?>/goods/<?=$good['id_good']?>.htm"><?=$good['name']?></a></td>
              <td align="center"><?=$good['kol']?></td>
              <td align="right"><?=$good['price']?></td>
              <td align="center"><?=$status?></td>
            </tr>
            <?
					}
					?>
            <tr>
              <td colspan="4" align="right"><b>Итого:</b></td>
              <td align="right"><?=$order_cost?></td>
              <td></td>
            </tr>
          </table>
					<?
					$text = ob_get_clean();
					mailTo($user['mail'],$tema,$text,set('title')); // клиенту
				}
				
				// проверяем все ли товары закрыты (отправлены клиенту)
				?><script><?
				if(!getField("SELECT count(*) FROM {$prx}order_goods WHERE id_order={$id_order} and status<3"))
				{
					update($tbl,'status=2',$id_order);
					?>alert('Все товары текщего заказа доставлены клиенту!\nСтатус заказа "завершенный".');<?
				}
				?>
        top.location.href = "<?=$script?>?id=<?=$id?>";
        </script>
				<?
			}
			else
				errorAlert('Во время сохранения данных произошла ошибка.');
			break;
		// ----------------- обновление статуса
		case "status":
			$status = (int)@$_GET['status'];
			// если пытаемся установить статус "завершенный"
			if($status==2)
			{
				// проверяем все ли товары получены клиентом
				if(getField("SELECT count(*) FROM {$prx}order_goods WHERE id_order={$id} and status<>3"))
				{
					?>
          <script>
					alert('действие отменено!\nв заказе присутствуют товары,\nкоторые ещё не доставлены клиенту');
					top.$('select[name="status\[<?=$id?>\]"] option').eq(0).attr('selected',true);
					</script>
          <?
				}
				else
				{
					update($tbl,"status=2",$id);
					?><script>top.location.href = "<?=$script?>?id=<?=$id?>";</script><?
				}
			}
			// если пытаемся установить статус "действующий"
			else
			{
				?>
				<script>
				alert('действие отменено!\nзавершенный заказ невозможно перевести в статус "действующий"');
				top.$('select[name="status\[<?=$id?>\]"] option').eq(1).attr('selected',true);
				</script>
        <?
			}
			break;
		// ----------------- удаление одной записи
		case "del":
			update($tbl,'',$id);
			sql("DELETE FROM {$prx}order_goods WHERE id_order={$id}");
			?><script>top.location.href = "<?=$script?>";</script><?
			break;
		// ----------------- удаление нескольких записей
		case "multidel":
			foreach($_POST['check_del_'] as $k=>$v)
			{
				update($tbl,'',$k);
				sql("DELETE FROM {$prx}order_goods WHERE id_order={$k}");
			}
			?><script>top.location.href = "<?=$script?>";</script><?
			break;
		// ----------------- добавление товара к заказу
		case 'add_good':
			if(!$id_order = (int)$_GET['id_order']) exit;
			if(!$id_user = gtv('orders','id_user',$id_order)) exit;
			if(!$good = gtv('goods','*',$id)) exit;
			$nprice = gtv('users','nprice',$id_user);
				$nprice = $nprice ? $nprice : 5;
			?>
      <script>
			$tab = top.$('#tbl_order');
			var i = $tab.find('span.num').size()+1;

			data  = '<tr>';
			data += '<td style="width:20px"><input type="hidden" name="gid[]" value="<?=$good['id']?>" /><span class="num">'+i+'</span></td>';
			data += '<td><?=$good['articul']?></td>';
			data += '<td style="width:100%; text-align:left"><a href="http://<?=$_SERVER['HTTP_HOST']?>/goods/<?=$good['id']?>.htm"><?=$good['name']?></a></td>';
			data += '<td></td>';
      data += '<td><input type="text" name="gprice[]" style="width:80px; text-align:center;" value="<?=$good['price'.$nprice]?>"></td>'
      data += '<td><input type="text" name="gkol[]" style="width:40px; text-align:center;" value="1"></td>'
      data += '<td>';
			data += '<select name="gs[]"">';
			data += '		<option value="1" selected>ожидает обработки / в пути</option>';
      data += '		<option value="2">готов к выдаче</option>';
      data += '		<option value="3">отправлен клиенту</option>';
      data += '</select>';
      data += '</td>';
      data += '<td><a class="del_good" href="" title="удалить товар из заказа"><img src="img/del.png" width="16" height="16" align="absmiddle" /></a></td>';
			data += '</tr>';
			
			$tab.append(data);
			</script>
      <?
			
			break;
		// ----------------- добавление сообщения
		case "add_message":
			$id_order = (int)@$_GET['id_order'];
			$text = clean($_POST['message'],true);
			if(!$text)
				errorAlert('Напишите сообщение!');
				
			update("order_messages","id_order={$id_order},`from`='manager',text='{$text}',`date`=NOW()");
			
			// отправляем клиенту уведомление
			$order = getRow("SELECT id_user,`date` FROM {$prx}orders WHERE id={$id_order}");
			$user  = getRow("SELECT name,surname,mail FROM {$prx}users WHERE id={$order['id_user']}");
			
			$tema = "Уведомление о новом сообщении на сайте ".$_SERVER['SERVER_NAME'];
			ob_start();
			?>Добрый день <?=$user['name']?> <?=$user['surname']?>.<br><br>
      Уведомляем Вас о том, что в заказе №<?=$id_order?> от <?=date('d.m.Y',strtotime($order['date']))?> появилось новое сообщение от Вашего менеджера.<br>
      <a href="http://<?=$_SERVER['HTTP_HOST']?>/messages/">http://<?=$_SERVER['HTTP_HOST']?>/messages/</a><?
			$text = ob_get_clean();
			$admin_mail = set("admin_email");
			mailTo($user['mail'],$tema,$text,set('title')); // клиенту
			
			?><script>top.location.href = "<?=$script?>?red=<?=$id_order?>";</script><?
			break;
		// ----------------- удаление сообщения
		case "del_message":
			$id_order = (int)@$_GET['id_order'];
			update("order_messages","",$id);
			?><script>top.location.href = "<?=$script?>?red=<?=$id_order?>";</script><?
			break;
	}
	exit();
}
// ------------------ РЕДАКТИРОВАНИЕ --------------------
elseif(isset($_GET["red"]))
{
	$id_order = (int)@$_GET['red'];	
	$row = getRow("SELECT * FROM {$prx}{$tbl} WHERE id={$id_order}");
	if(!$row)
	{
		header("Location: {$script}");
		exit();
	}
	
	$rubric .= " &raquo; Редактирование";
	$page_title .= " :: ".$rubric;
	
	ob_start();
	
	// если заказ действующий
	if($row['status']==1)
	{
		?>
		<script src="js/orders.js" type="text/javascript"></script>
		<input type="hidden" id="id_order" value="<?=$id_order?>" />
    <input type="hidden" name="id_user" value="<?=$row['id_user']?>" />
		<form action="?action=save&id=<?=$id_order?>" method="post" target="ajax">
    <?
	}
	?>
  <table width="100%" border="0" cellspacing="0" cellpadding="5" class="tab_red">
    <tr>
    	<th class="tab_red_th"></th>
      <th>Номер заказа</th>
      <td><b><?=$id_order?></b></td>
    </tr>
    <tr>
    	<th class="tab_red_th"></th>
      <th>Дата</th>
      <td><?=date("d.m.Y H:i:s",strtotime($row['date']))?></td>
    </tr>
    <tr>
    	<th class="tab_red_th"></th>
      <th>Товары</th>
      <td>
        <?
				$res = sql("SELECT * FROM {$prx}order_goods WHERE id_order={$id_order}");
				if(@mysql_num_rows($res))
				{
					?>
        	<table class="orders" id="tbl_order" style="width:100%">
				  	<tr>
              <th style="width:20px">N</th>
              <th>Артикул</th>
              <th>Наименование</th>
				  <th>Поставщик</th>
              <th nowrap>Цена (руб.)</th>
              <th nowrap>Кол-во (шт.)</th>
              <th>Статус</th>
              <?
							if($row['status']==1)
							{
								?>
                <th style="padding:0 15px">
                	<a id="add_goods" href="" title="добавить товар"><img src="img/add.png" /></a>
                </th>
								<?
							}
							?>
            </tr>
						<?
            $i=1;
            while($arr = mysql_fetch_assoc($res))
            {
              ?>
              <tr>
                <td style="width:20px"><input type="hidden" name="gid[]" value="<?=$arr['id_good']?>" /><span class="num"><?=$i++?></span></td>
                <td><?=$arr['articul']?></td>
                <td style="width:100%; text-align:left"><a href='http://<?=$_SERVER['HTTP_HOST']?>/goods/<?=$arr['id_good']?>.htm'><?=$arr['name']?></a></td>
                <td><?=$arr['maker']?></td>
                <td><input type="text" name="gprice[]" style="width:80px; text-align:center;" value="<?=$arr['price']?>"></td>
                <td><input type="text" name="gkol[]" style="width:40px; text-align:center;" value="<?=$arr['kol']?>"></td>
                <?
								if($row['status']==1)
								{
					  			?>
                  <td>
                    <select name="gs[]">
                      <option value="1"<?=$arr['status']==1?' selected':''?>>ожидает обработки / в пути</option>
                      <option value="2"<?=$arr['status']==2?' selected':''?>>готов к выдаче</option>
                      <option value="3"<?=$arr['status']==3?' selected':''?>>отправлен клиенту</option>
                    </select>
                  </td>
                  <td><a class="del_good" href="" title="удалить товар из заказа"><img src="img/del.png" width="16" height="16" align="absmiddle" /></a></td>
                  <?
								}
								else
									echo "<td>отправлен клиенту</td>";
								?>
              </tr>
             	<?
						}
						?>
          </table>
				<?
				}
				?>
      </td>
    </tr>
    <tr>
    	<th class="tab_red_th"></th>
      <th>Стоимость</th>
      <td><b><?=$row['cost']?> руб.</b></td>
    </tr>
	<?	if($row['bonus']) { ?>
			<tr>
				<th class="tab_red_th"></th>
				<th>Бонус</th>
				<td style="color:red;"><b><?=$row['bonus']?> руб.</b></td>
			</tr>
	<?	}	?>
    <tr>
    	<th class="tab_red_th"></th>
      <th>Статус</th>
      <td>
				<?
        if($row['status']==1)
        {
          ?>
          <select name="status">
            <option value="1"<?=$row['status']==1?' selected':''?>>действующий</option>
            <option value="2"<?=$row['status']==2?' selected':''?>>завершенный</option>
          </select>
          <?
				}
				else
					echo 'завершенный';
				?>
      </td>
    </tr>
    <tr>
    	<th class="tab_red_th"></th>
      <th>Сведения о<br>клиенте</th>
      <td>
				<?
        $arr = getRow("SELECT * FROM {$prx}users WHERE id={$row['id_user']}");
        if($arr)
        {
          ?>
          <table class="orders" id="tbl_order">
				  	<tr>
              <th>Организация</th>
              <td><?=$arr['org']?></td>
            </tr>
            <tr>
              <th>ФИО</th>
              <td><a href="users.php?red=<?=$arr['id']?>"><?=$arr['family']?> <?=$arr['name']?> <?=$arr['surname']?></a></td>
            </tr>
            <tr>
              <th>E-mail</th>
              <td><a href="mailto:<?=$arr['mail']?>"><?=$arr['mail']?></a></td>
            </tr>
            <tr>
              <th>Контактный телефон</th>
              <td><?=$arr['phone']?></td>
            </tr>
					</table>
					<?
				}
				?>
        <div style="margin-top:10px">
        <a href="" id="show_user_info" style="color:#090">в момент заказа</a>
        <div style="margin-top:10px; display:none">
        <?=$row['user_info']?>
        </div>
        </div>
      </td>
    </tr>
    <tr>
    	<th class="tab_red_th"></th>
      <th>Примечание</th>
      <td><?=show_pole('textarea','notes',$row['notes'])?></td>
    </tr>
    <tr>
    	<th class="tab_red_th"></th>
      <th></th>
      <td align="center">
      	<?
        if($row['status']==1)
        {
					?><input type="submit" value="Сохранить" class="but1" onclick="loader(true)" />&nbsp;<?
				}
				?>
        <input type="button" value="Отмена" class="but1" onclick="top.location.href='<?=$script?>'" />
      </td>
    </tr>
  </table>
  </form>
  <?
	
	// ---------------------- ПЕРЕПИСКА МЕНЕДЖЕРА С КЛИЕНТОМ ---------------------------
	?><h1 style="margin:10px 0;">Переписка с клиентом</h1><?
	
	$cur_page = $_SESSION['page'] ? $_SESSION['page'] : 1;
	
	$count_obj = getField("SELECT count(*) FROM {$prx}order_messages WHERE id_order={$id_order}"); // кол-во объектов в базе
	$count_obj_on_page = set("count_order_messages_admin"); // кол-во объектов на странице
	$kol_str = ceil($count_obj/$count_obj_on_page); // количество страниц
	
	$query  = "SELECT * FROM {$prx}order_messages WHERE id_order={$id_order} ";
	if($_SESSION['sort']) 
	{
		$sort = explode(":",$_SESSION['sort']);
		$cur_pole = $sort[0];
		$cur_sort = $sort[1];

		$query .= "ORDER BY {$cur_pole} ";
		if($cur_sort=="up")
			$query .= "DESC ";
		else
			$query .= "ASC ";
	}
	else
		$query .= "ORDER BY date ";
	$query .= "limit ".($count_obj_on_page*$cur_page-$count_obj_on_page).",".$count_obj_on_page;
	
	show_filters($script."?red={$id_order}");
    show_navigate_pages($kol_str,$cur_page,$script."?red={$id_order}");
	
	?>
	<form action="?action=add_message&id_order=<?=$id_order?>" method="post" target="ajax">
  <input type="hidden" id="cur_id" value="<?=@$_GET['id']?(int)$_GET['id']:''?>" />
	<table width="100%" border="1" cellspacing="0" cellpadding="0" class="tab1">
	  <tr>
		<th style="padding:0 20px"></th>
		<th><?=ShowSortPole($script."?red={$id_order}",$cur_pole,$cur_sort,'Дата','date')?></th>
		<th width="100%"><?=ShowSortPole($script."?red={$id_order}",$cur_pole,$cur_sort,'Сообщение','text')?></th>
		<th style="padding:0 30px;"></th>
	  </tr>
  <?
	$res = mysql_query($query);
	if(@mysql_num_rows($res))
	{
		while($arr = mysql_fetch_assoc($res))
		{
			?>
      <tr id="<?=$arr['id']?>">
        <th style="font-weight:normal"><?=$arr['from']=='user'?'Клиент':'Менеджер'?></th>
        <td nowrap><?=date("d.m.Y H:i:s",strtotime($arr['date']))?></td>
        <td><?=$arr['text']?></th>
        <td nowrap align="center">
          <img src="img/del.png" width="16" height="16" alt="удалить" title="удалить сообщение" style="cursor:pointer;" onclick="if(confirm('Уверены?')) toajax('?action=del_message&id_order=<?=$id_order?>&id=<?=$arr['id']?>')" />
        </td>
      </tr>
      <?
		}
	}
	else
	{
		?><tr><td colspan="4" align="center">переписка отсутствует</td></tr><?
	}
	?>
  </table>
    
  <fieldset style="margin:10px 0 0 0">
  <legend>Написать сообщение</legend>
  <textarea style="width:100%; height:100px; margin:10px 0" name="message"></textarea>
  <center><input type="submit" value="отправить" class="but1" /></center>
  </fieldset>

  </form>
  
  <?=popup_modul()?>
  
  <?
		
	$content = ob_get_clean();
}
// -----------------ПРОСМОТР-------------------
else
{
	//mailTo('epihovad@mail.ru','тема','текст сообщения',set('title'));
	//echo set('title');
	
	$cur_page = $_SESSION['page'] ? $_SESSION['page'] : 1;
	
	$page_title .= " :: ".$rubric; 
	$rubric .= " &raquo; Общий список";
	
	$where = '';
	if(isset($_SESSION['cur_user']))
	{
		$rubric .= " &raquo; ".getField("SELECT org FROM {$prx}users WHERE id=".$_SESSION['cur_user']);
		$where = " and id_user=".$_SESSION['cur_user'];
	}
	
	$razdel = array("Удалить"=>"javascript:multidel(document.red_frm,'check_del_','');");
	$subcontent = show_subcontent($razdel);
	
	$query = "SELECT * FROM {$prx}{$tbl}";
	
	$count_obj = getField(str_replace('*','COUNT(*)',$query)); // кол-во объектов в базе
	$count_obj_on_page = set("count_{$tbl}_admin"); // кол-во объектов на странице
	$kol_str = ceil($count_obj/$count_obj_on_page); // количество страниц

	ob_start();
	// проверяем текущую сортировку
	// и формируем соответствующий запрос
	if($_SESSION['sort']) 
	{
		$sort = explode(":",$_SESSION['sort']);
		$cur_pole = $sort[0];
		$cur_sort = $sort[1];

		$query .= " ORDER BY {$cur_pole}";
		$query .= $cur_sort=='up' ? ' DESC' : ' ASC';
	}
	else
		$query .= " ORDER BY `date` DESC";
	$query .= " LIMIT ".($count_obj_on_page*$cur_page-$count_obj_on_page).",".$count_obj_on_page;
	//-----------------------------
	//echo $query;
	
	show_filters($script);
	show_navigate_pages($kol_str,$cur_page,$script);
	
	?>
  <form action="?action=multidel" name="red_frm" method="post" target="ajax">
  <input type="hidden" id="cur_id" value="<?=@$_GET['id']?@(int)$_GET['id']:""?>" />
  <table width="100%" border="1" cellspacing="0" cellpadding="0" class="tab1">
    <tr>
      <th><input type="checkbox" name="check_del" id="check_del" /></th>
      <th>№</th>
      <th><?=ShowSortPole($script,$cur_pole,$cur_sort,'Дата','date')?></th>
      <?
			if(!isset($_SESSION['cur_user']))
			{
				?><th>Организация</th><?
			}
			?>
      <th width="100%">Заказ</th>
      <th nowrap><?=ShowSortPole($script,$cur_pole,$cur_sort,'Стоимость заказа','cost')?></th>
      <th><?=ShowSortPole($script,$cur_pole,$cur_sort,'Статус','status')?></th>
      <th style="padding:0 30px;"></th>
    </tr>
  <?
	$res = sql($query);
	if(@mysql_num_rows($res))
	{
		while($row = mysql_fetch_array($res))
		{
			?>
			<tr id="<?=$row['id']?>">
			<th><input type="checkbox" name="check_del_[<?=$row['id']?>]" id="check_del_<?=$row['id']?>" /></th>
			<th nowrap><?=$row['id']?></th>
			<td nowrap align="center"><?=date('d.m.Y',strtotime($row['date']))?></td>
      <?
			if(!isset($_SESSION['cur_user']))
			{
				?><td nowrap><?=getField("SELECT org FROM {$prx}users WHERE id={$row['id_user']}")?></td><?
			}
			?><td><?
			$r = mysql_query("SELECT * FROM {$prx}order_goods WHERE id_order={$row['id']}");
			$n=1;
			while($arr = @mysql_fetch_assoc($r))
			{
				?><a href="/goods/<?=$arr['id']?>.htm" target="_blank">(<?=$arr['articul']?>) <?=$arr['name']?></a>
				(<span style="font-size:11px">кол-во: <?=$arr['kol']?>, цена: <?=$arr['price']?></span>)<br /><?
			}
			?>
      </td>
      <td nowrap align="center" style="color:#000">
			<b><?=$row['cost']?> руб.</b>
		<?	if($row['bonus']) { ?><div style="color:red;">Бонус: <b><?=$row['bonus']?> руб.</b></div><? } ?>
		</td>
			<td nowrap align="center">
				<select name="status[<?=$row['id']?>]" onchange="toajax('?action=status&id=<?=$row['id']?>&status='+this.value)">
        	<option value="1"<?=$row['status']==1?' selected':''?>>действующий</option>
        	<option value="2"<?=$row['status']==2?' selected':''?>>завершенный</option>
        </select>
      </td>
			<td nowrap align="center"><?=btn_edit($row['id'])?></td>
			</tr>
			<?
		}	
	}
	else
	{
		?>
    <tr>
      <td colspan="10" align="center">
      по вашему запросу ничего не найдено.<?=help('нет ни одной записи отвечающей критериям вашего запроса,<br>возможно вы установили неверные фильтры')?>
      </td>
    </tr>
    <?
	}
	?>
  </table>
  </form>
  <?	
	$content = $subcontent.ob_get_clean();
}

require("tpl/tpl.php");
?>