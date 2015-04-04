<?
require('inc/common.php');

$rubric = 'Пользователи';
$tbl = 'users';

$mas_field = array(	'org','director','director_type','family','name','surname','phone','fax','mail','inn','kpp','bik','okpo','bank','rs','ks',
					'address_ur','address_fact','dostavka','note'); //'index_ur','city_ur','index_fact','city_fact',

// ------------------- СОХРАНЕНИЕ ------------------------
if(isset($_GET["action"]))
{
	$id = (int)$_GET['id'];
	
	switch($_GET['action'])
	{
		// ----------------- сохранение
		case 'save':
			$row = getRow("SELECT * FROM {$prx}{$tbl} WHERE id={$id}");
			if($row)
			{
				$login = clean($_POST['login']);
				$pass = clean($_POST['pass']);
				
				if(!$login)
					errorAlert('Введите логин!');
				if(getField("SELECT id FROM {$prx}{$tbl} WHERE login='{$login}' and id<>'{$id}'"))
					errorAlert('Введенный логин уже занят!');
				if(!$pass)
					errorAlert('Введите пароль!');
				
				$p1 = (int)$_POST['p1'];
				$p2 = (int)$_POST['p2'];
				$status = (int)$_POST['status'];
				$showmaker = (int)$_POST['showmaker'];
				$manager = (int)$_POST['manager'];
					
				$query = '';			
				foreach($_POST as $key=>$value)
					$query .= ($query?',':'')."{$key}='".clean($value)."'";

				$query .= ",pass='{$pass}',p1='{$p1}',p2='{$p2}',status='{$status}',showmaker='{$showmaker}',manager='{$manager}'";

				if($id = update($tbl,$query,$id))
				{
					?><script>top.location.href = "<?=$script?>?id=<?=$id?>";</script><?
				}
				else
					errorAlert('Во время сохранения данных произошла ошибка.');
			}
		break;
		// ----------------- обновление статуса
		case "status":
			update_flag($tbl,'status',$id);
		break;
		// ----------------- удаление одной записи
		case "del":
			update($tbl,'',$id);
			?><script>top.location.href = "<?=$script?>";</script><?
		break;
		// ----------------- удаление нескольких записей
		case "multidel":
			foreach($_POST['check_del_'] as $k=>$v)
				update($tbl,'',$k);
			?><script>top.location.href = "<?=$script?>";</script><?
		break;
		// ---------------- передать пользователей --------------
		case "to_manager":
			$id_manager = (int)@$_GET['id_manager'];
			foreach($_POST['check_del_'] as $k=>$v)
				sql("UPDATE {$prx}{$tbl} SET id_manager={$id_manager} WHERE id={$k}");
			?><script>top.location.href = "<?=$script?>";</script><?
			break;
		// ---------------- настройка критериев --------------
		case "set_criteria":
			?>
			<script>
			top.show_popup_window('Настройка отображения полей','inc/set_criteria.php?tab=<?=$tbl?>&location=<?=$script?>');
			</script>
			<?
			break;
			
	}
	exit();
}
// ------------------ РЕДАКТИРОВАНИЕ --------------------
elseif(isset($_GET['red']))
{
	$id = (int)$_GET['red'];
	
	$row = gtv($tbl,'*',$id);
	if(!$row) { header("Location: {$script}"); exit(); }
	
	$rubric .= ' &raquo; Редактирование';
	$page_title .= ' :: '.$rubric;
	
	ob_start();
	?>
  <form action="?action=save&id=<?=$id?>" method="post" target="ajax">
  <table width="100%" border="0" cellspacing="0" cellpadding="5" class="tab_red">
    <tr>
      <th class="tab_red_th"></th>
      <th>Менеджер</th>
      <td><?=dll("SELECT id,name FROM {$prx}managers ORDER BY name",'name="id_manager" style="width:100%"',$row['id_manager'])?></td>
    </tr>
    <tr>
      <th class="tab_red_th"><?=help('не должен быть пустым')?></th>
      <th style="color:#090">Логин</th>
      <td><?=show_pole('text','login',htmlspecialchars($row['login']))?></td>
    </tr>
    <tr>
      <th class="tab_red_th"><?=help('не должен быть пустым')?></th>
      <th style="color:#090">Пароль</th>
      <td><?=show_pole('text','pass',htmlspecialchars($row['pass']))?></td>
    </tr>
    <?
	  foreach($mas_field as $field)
	  {
		  ?>
      <tr>
      	<th class="tab_red_th"></th>
        <th><?=getStructureTable($tbl,$field)?></th>
        <td>
					<?
          if($field=='note')
            echo show_pole('textarea','note',$row[$field]);
          else
            echo show_pole('text',$field,htmlspecialchars($row[$field]));
          ?>
        </td>
      </tr>
      <?
	  }
	  ?>
    <tr>
			<th class="tab_red_th"></th>
			<th>Цена</th>
			<td>
      	<table class="subtab">
        	<tr>
          	<th style="padding:3px 5px">пользовательская</th>
            <th style="padding:3px 5px">дилерская</th>
          </tr>
          <tr>
          	<th style="padding:3px 5px">
            	<select name="p1">
								<?
                for($n=1; $n<6; $n++)
                {
                  ?><option value="<?=$n?>"<?=$row['p1']==$n?' selected':''?>>Цена <?=$n?></option><?
                }
                ?>
              </select>
            </th>
            <th style="padding:3px 5px">
            	<select name="p2">
								<option value="0"<?=!$row['p2']?' selected':''?>>-</option>
								<?
                for($n=6; $n<11; $n++)
                {
                  ?><option value="<?=$n?>"<?=$row['p2']==$n?' selected':''?>>Цена <?=$n?></option><?
                }
                ?>
              </select>
            </th>
          </tr>
        </table>
      </td>
		</tr>
    <tr>
			<th class="tab_red_th"><?=help('Будет показывать поставщиков в остатках на странице товара')?></th>
			<th>Показывать поставщиков</th>
			<td><?=dll(array('0'=>'нет','1'=>'да'),'name="showmaker"',isset($row['showmaker'])?$row['showmaker']:1)?></td>
		</tr>
    <tr>
			<th class="tab_red_th"><?=help('Возможность формирования заказа со своими ценами')?></th>
			<th>Барыга</th>
			<td>
				<?=dll(array('0'=>'нет','1'=>'да'),'name="manager"',isset($row['manager'])?$row['manager']:1)?> &nbsp; &nbsp;
			<?	if($id) { 	?>
					Бонус: <span id='bonus'><?=getField("SELECT SUM(pay) AS s FROM {$prx}bonus WHERE id_users='{$id}'")?></span>  &nbsp; &nbsp; &nbsp; 
					<a href="inc/set_bonus.php?id_users=<?=$id?>" target="my" onClick="openWindow(640,480)">история бонусов</a>
			<?	}	?>
			</td>
		</tr>
    <tr>
			<th class="tab_red_th"></th>
			<th>Статус</th>
			<td><?=dll(array('0'=>'заблокировано','1'=>'активно'),'name="status"',isset($row['status'])?$row['status']:1)?></td>
		</tr>
    <tr>
			<th class="tab_red_th"></th>
			<th></th>
			<td align="center">
				<input type="submit" value="<?=($id ? "Сохранить" : "Добавить")?>" class="but1" onclick="loader(true)" />&nbsp;
				<input type="button" value="Отмена" class="but1" onclick="location.href='<?=$script?>'" />
			</td>
		</tr>
  </table>
	</form>
	<?
	$content = ob_get_clean();
}
// -----------------ПРОСМОТР-------------------
else
{
	$cur_page = $_SESSION['page'] ? $_SESSION['page'] : 1;
	
	$f_manager = 	(int)@$_SESSION['fmanager'];
	$f_context = htmlspecialchars(stripslashes($_SESSION['context']));
		
	$where = '';
	if($f_manager) 	$where .= "and id_manager={$f_manager} ";
	if($f_context)
	{
		$where .= "and ( ";
		$count = sizeof($mas_field);
		$i=0;
		foreach($mas_field as $field)
			$where .= "{$field} LIKE '%".clean($f_context)."%' ".(++$i<$count?'or ':'');
		$where .= " ) ";
	}
			
	$page_title .= " :: ".$rubric;
	$rubric .= " &raquo; Общий список";
	
	$razdel = array("Удалить"=>"javascript:multidel(document.red_frm,'check_del_');",
					"Передать"=>"javascript:document.red_frm.action='?action=to_manager&id_manager='+$('#to_manager').val();document.red_frm.submit();",
					"Настройка"=>"javascript:toajax('?action=set_criteria');"
					);
	$subcontent = show_subcontent($razdel);
	
	$count_obj = getField("SELECT count(*) FROM {$prx}{$tbl} WHERE 1=1 {$where}"); // кол-во объектов в базе
	$count_obj_on_page = set("count_{$tbl}_admin"); // кол-во объектов на странице
	$kol_str = ceil($count_obj/$count_obj_on_page); // количество страниц

	ob_start();
	// проверяем текущую сортировку
	// и формируем соответствующий запрос
	$query = "SELECT * FROM {$prx}{$tbl} WHERE 1=1 {$where} ";
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
		$query .= "ORDER BY id ";
	
	$query .= "limit ".($count_obj_on_page*$cur_page-$count_obj_on_page).",".$count_obj_on_page;
	//-----------------------------
	//echo $query;
	
	show_filters('users.php');
	show_navigate_pages($kol_str,$cur_page,'users.php');
	
	?>
  <table cellspacing="0" cellpadding="0" style="margin:10px 0">
    <tr>
      <td style="padding:0">
        <table class="filter_tab" style="margin:5px 0 0 0;">
          <tr>
            <td align="left">Менеджер</td>
            <td colspan="2"><?=dll("SELECT id,name FROM {$prx}managers ORDER BY name",'onChange="RegSessionSort(\''.$script.'\',\'fmanager=\'+this.value);return false;"',$f_manager,array('remove','-- все --'))?></td>
          </tr>
          <tr>
            <td align="left">контекстный поиск <?=help('поиск осуществляется по всем полям,<br>в том числе скрытым')?></td>
            <td align="left"><input type="text" id="search_txt" value="<?=$f_context?>" style="width:100%"></td>
            <td align="center"><a href="" target="ajax" class="link" onClick="RegSessionSort('<?=$script?>','context='+$('#search_txt').val());return false;">найти</a></td>
          </tr>
        </table>
      </td>
      <td style="padding:0 0 0 20px">
        <fieldset style="height:30px">
          <legend>Передать менеджеру<?=help('для того, чтобы назначить пользователям другого менеджера<br>отметьте в списке нужных пользователей<br>и нажмите "Передать"')?></legend>
          <?=dll("SELECT id,name FROM {$prx}managers ORDER BY name",'id="to_manager"')?>
          </fieldset>
      </td>
    </tr>
  </table>    
  <?
	$criteria = get_criteria($tbl);
	?>
  <form action="?action=multidel" name="red_frm" method="post" target="ajax">
  <input type="hidden" id="cur_id" value="<?=@$_GET['id']?(int)$_GET['id']:""?>" />
  <table width="100%" border="1" cellspacing="0" cellpadding="0" class="tab1">
    <tr>
      <th width="20" style="padding:0px;"><input type="checkbox" name="check_del" id="check_del" /></th>
      <th width="20" style="padding:0px;">№</th>
      <?
			foreach($mas_field as $field)
			{
				if(in_array($field,$criteria)) 
				{ 
					?><th><?=ShowSortPole($script,$cur_pole,$cur_sort,str_replace(' ','<br>',getStructureTable($tbl,$field)),$field)?></th><?
				}
			}
			if(in_array('count_orders',$criteria)) 
			{ 
				?><th><?=str_replace(' ','<br>',getStructureTable($tbl,'count_orders'))?></th><?
			}
			if(in_array('p1',$criteria))
			{
				?><th>ЦП <?=help('цена пользовательская')?></th><?
			}
			if(in_array('p2',$criteria))
			{
				?><th>ЦД <?=help('цена дилерская')?></th><?
			}
			if(in_array('status',$criteria))
			{
				?><th><?=ShowSortPole($script,$cur_pole,$cur_sort,'Статус','status')?><?=help('заблокирован данный товар или нет')?></th><?
			}
			if(in_array('id',$criteria))
			{
				?><th><?=ShowSortPole($script,$cur_pole,$cur_sort,'ID','id')?></th><?
			}
			?>
      <th style="padding:0 30px;"></th>
  	</tr>
  <?
	$res = mysql_query($query);
	if(@mysql_num_rows($res))
	{
		$i=1;
		while($row = mysql_fetch_array($res))
		{
			?>
			<tr id="<?=$row['id']?>">
			<th><input type="checkbox" name="check_del_[<?=$row['id']?>]" id="check_del_<?=$row['id']?>" /></th>
			<th nowrap><?=$i++?></th>
			<?
      foreach($mas_field as $field)
      {
				if(in_array($field,$criteria)) 
				{
					?><td><?=$row[$field]?></td><?
				}
      }
      if(in_array('count_orders',$criteria)) 
      {
				?>
        <td align="center">
					<?
          if($row['count_orders'])
          {
            ?><a href="" target="_blank" onClick="RegSessionSort('orders.php','cur_user=<?=$row['id']?>');return false;" title="кол-во заказов данного пользователя" style="color:#090"><?=$row['count_orders']?></a><?
          }
          else
            echo '0';
          ?>
        </td>
				<?
			}
			if(in_array('p1',$criteria))
			{
				?><td align="center"><?=$row['p1']?></td><?
			}
			if(in_array('p2',$criteria))
			{
				?><td align="center"><?=$row['p2']?></td><?
			}
			if(in_array('status',$criteria))
			{
					?><td align="center"><?=btn_flag($row['status'],$row['id'],'?action=status&id=')?></td><?
			}
			if(in_array('id',$criteria))
			{
					?><th><?=$row['id']?></th><?
			}
			?>
			<td nowrap align="center"><?=btn_edit($row['id'])?></td>
			</tr>
			<?
		}
	}
	else
	{
		?>
    <tr>
      <td colspan="100" align="center">
      по вашему запросу ничего не найдено.<?=help('нет ни одной записи отвечающей критериям вашего запроса,<br>возможно вы установили неверные фильтры')?>
      </td>
    </tr>
    <?
	}
	?>
  </table>
  </form>
  
  <?=popup_modul()?>
  
  <?	
	$content = $subcontent.ob_get_clean();
}

require("tpl/tpl.php");
?>