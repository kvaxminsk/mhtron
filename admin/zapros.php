<?
require('inc/common.php');

$rubric = 'Запросы';
$tbl = 'zapros';

// ------------------- СОХРАНЕНИЕ ------------------------
if(isset($_GET["action"]))
{
	$id = (int)$_GET['id'];
	
	switch($_GET['action'])
	{
		// ----------------- сохранение
		case 'status':
			$status = (int)$_GET['status'];
			if($id = update($tbl,"status='{$status}'",$id))
				errorAlert('Статус успешно изменён!');
			else
				errorAlert('Во время сохранения данных произошла ошибка.');
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
	}
	exit;
}

$cur_page = $_SESSION['page'] ? $_SESSION['page'] : 1;

$f_manager = (int)@$_SESSION['fmanager'];

$where = '';
if($f_manager)
{
	$ids_user = getArr("SELECT id FROM {$prx}users WHERE id_manager={$f_manager}");
	$where .= " and id_user IN (".implode(',',$ids_user).")";
}
	
$page_title .= " :: ".$rubric; 

$razdel = array("Удалить"=>"javascript:multidel(document.red_frm,'check_del_','?action=multidel');");
$subcontent = show_subcontent($razdel);

$query = "SELECT * FROM {$prx}{$tbl} WHERE 1{$where}";
	
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
	$query .= " ORDER BY id DESC";
$query .= " LIMIT ".($count_obj_on_page*$cur_page-$count_obj_on_page).",".$count_obj_on_page;
//-----------------------------
//echo $query;

show_filters($script);
show_navigate_pages($kol_str,$cur_page,$script);

?>
<table class="filter_tab" style="margin:5px 0 0 0;">
  <tr>
    <td align="left">Менеджер</td>
    <td colspan="2"><?=dll("SELECT id,name FROM {$prx}managers ORDER BY name",'onChange="RegSessionSort(\''.$script.'\',\'fmanager=\'+this.value);return false;"',$f_manager,array('remove','-- все --'))?></td>
  </tr>
</table>

<form action="?action=multidel" name="red_frm" method="post" target="ajax">
<input type="hidden" id="cur_id" value="<?=@$_GET['id']?@(int)$_GET['id']:""?>" />
<table width="100%" border="1" cellspacing="0" cellpadding="0" class="tab1">
  <tr>
    <th><input type="checkbox" name="check_del" id="check_del" /></th>
    <th>№</th>       
    <th>Пользователь</th>
    <th>Товар</th>
    <th nowrap><?=ShowSortPole($script,$cur_pole,$cur_sort,'Количество','kol');?></th>            
    <th width="100%">Комментарий</th>
    <th nowrap><?=ShowSortPole($script,$cur_pole,$cur_sort,'Дата','date');?></th>
    <th nowrap><?=ShowSortPole($script,$cur_pole,$cur_sort,'Статус','status');?></th>
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
      <th><?=$row['id']?></th>
      <td nowrap>
				<?
        if($user = gtv('users','family,name,surname',$row['id_user']))
				{
					?><a href="users.php?red=<?=$row['id_user']?>" style="color:#090"><?=$user['family']?> <?=$user['name']?> <?=$user['surname']?></a><?
				}
				else
					echo 'не найден'
				?>
      </td>
      <td nowrap>
				<?
        if($good = gtv('goods','articul,name',$row['id_good']))
				{
					?><a href="/goods/<?=$row['id_good']?>.htm" target="_blank"><?=$good['name']?> (арт. <?=$good['articul']?>)</a><?
				}
				else
					echo 'не найден';
				?>
      </td>
      <td align="center"><?=$row['kol']?></td>
      <td><?=nl2br($row['notes'])?></td>
      <td align="center" nowrap><?=date('d.m.Y H:i:s',strtotime($row['date']))?></td>
      <td><?=dll(array('0'=>'открыт','1'=>'выполнен'),' name="status" onChange="toajax(\''.$script.'?action=status&id='.$row['id'].'&status=\'+this.value)"',isset($row['status'])?$row['status']:0)?></td>
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

require("tpl/tpl.php");
?>