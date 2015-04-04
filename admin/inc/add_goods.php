<?
require('common.php');

$list = clean($_GET['list']);
$ids = clean($_GET['ids']);
$tbl = 'goods';	

$cur_page = @$_GET['p'] ? @(int)$_GET['p'] : 1;
	
$f_maker = (int)@$_GET['maker'];
$f_context = htmlspecialchars(stripslashes($_GET['context']));
	
$where = '';
if($f_maker)		$where .= " and id_maker={$f_maker}";
if($f_context)	$where .= " and ( articul LIKE '%".clean($f_context)."%' or 
																	name LIKE '%".clean($f_context)."%' )";

$query = "SELECT * FROM {$prx}{$tbl} WHERE 1{$where}";

$count_obj = getField(str_replace('*','COUNT(*)',$query)); // кол-во объектов в базе
$count_obj_on_page = set("count_{$tbl}_admin"); // кол-во объектов на странице
$kol_str = ceil($count_obj/$count_obj_on_page); // количество страниц

$query .= " ORDER BY name LIMIT ".($count_obj_on_page*$cur_page-$count_obj_on_page).",".$count_obj_on_page;
//echo $query;

ob_start();

?>
<link rel="stylesheet" href="../css/pop.css" type="text/css">
<script src="../js/pop.js" type="text/javascript"></script>

<form id="frm" style="margin-bottom:5px;">
<input type="hidden" id="list" name="list" value="<?=$list?>" />
<input type="hidden" name="ids" value="<?=$ids?>" />
<table id="filters">
  <tr>
   <td align="left">Производитель</td>
    <td colspan="2"><?=dll("SELECT id,name FROM {$prx}makers ORDER BY name",'name="maker" style="width:100%" onChange="this.form.submit();return false;"',$f_maker,array('','-- все --'))?></td>
  </tr>
  <tr>
    <td>Контекстный поиск</td>
    <td><input type="text" name="context" value="<?=$f_context?>" style="width:200px;"></td>
    <td><a href="javascript:$('#frm').submit()" class="link">найти</a></td>
  </tr>
</table>
</form>
 
<?
$script = "?p=%s&list={$list}&ids={$ids}&maker={$f_maker}&search={$search}";
$session = false;
show_navigate_pages($kol_str,$cur_page,$script);

$ids = explode(',',$ids);
?>  

<table id="tab">
  <tr>
    <th width="20"></th>
    <th>Артикул</th>
    <th>Наименование</th>
  </tr>
	<?
  $res = mysql_query($query);
  if(@mysql_num_rows($res))
  {
    while($row = mysql_fetch_assoc($res))
    {
      ?>
      <tr>
        <th><input type="checkbox" id="<?=$row['id']?>" value="<?=htmlspecialchars($row['name'])?>"<?=in_array($row['id'],$ids)?' checked':''?>></th>
        <td><?=$row['articul']?></td>
        <td><?=$row['name']?></td>
      </tr>
      <?
    }
  }
  else
  {
    ?><tr><td colspan="10" align="center">товары не найдены</td></tr><?
  }
  ?>
</table>
  
<div align="center" style="margin-top:10px;">
<input type="button" value="сохранить" class="but1">
<input type="button" value="отмена" class="but1">
</div>
<?

$content = ob_get_clean();

require("../tpl/tpl_popup.php");
?>