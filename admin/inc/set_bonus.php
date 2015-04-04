<?
require('common.php');
$tbl = 'bonus';

$id_users = (int)@$_GET['id_users'];

// ------------------- СОХРАНЕНИЕ ------------------------
if(isset($_GET["action"]))
{
	switch($_GET['action'])
	{
		// ----------------- сохранение
		case "save":
			print_r($_POST['id']);
			foreach($_POST['id'] as $id=>$v)
			{
				$date = formatDateTime($_POST['date'][$id]);
				$pay = str_replace(',','.',$_POST['pay'][$id]);
				$note = clean($_POST['note'][$id]);
				update($tbl, $pay ? "date='{$date}', pay='{$pay}', note='{$note}', id_users='{$id_users}'" : '',$id);
			}
			
			?>
			<script>
				window.opener.document.getElementById('bonus').innerHTML = '<?=getField("SELECT SUM(pay) AS s FROM {$prx}bonus WHERE id_users='{$id_users}'")?>';
				window.close();
         </script>
			<?
			
			break;
	}
	exit;
}

ob_start();
?>
<style type="text/css">
#tab
{
	width:100%;
	margin:0 0 0 0;
	border-collapse:collapse;
	border-color:#ecf0fb;
}
#tab th, #tab td 
{ 
	padding:3px 5px;
	border:1px solid #93acd0;
	text-align:center;
}
#tab th
{
	background-color:#d4dff2;
	color:#3e6aaa;
	font:bold 12px Arial, Helvetica, sans-serif;	
}
#tab td
{
	color:#697079;
	font:normal 12px Arial, Helvetica, sans-serif;
}
#tab input { padding:0; margin:0; }
</style>
	<div align="center" style="padding:10px;">Внимание! Для списания бонуса указывать сумму со знаком минус</div>
	<form action="?action=save&id_users=<?=$id_users?>" method="post" style="margin:0;">
	<table id="tab">
    <tr>
    	<th>№</th>
        <th>Дата</th>
        <th>Сумма</th>
        <th width="100%">Комментарий</th>
    </tr>
	<?
	$res = mysql_query("SELECT * FROM {$prx}bonus WHERE id_users='{$id_users}' ORDER BY date");
	while($row = mysql_fetch_assoc($res))
	{	?>
		<tr>
        	<th style="font-weight:bold;"><input type="hidden" name="id[<?=$row['id']?>]"><?=(++$i)?></th>
        	<td><input name="date[<?=$row['id']?>]" value="<?=date('d.m.Y H:i', strtotime($row['date']))?>" style="text-align:center; width:110px;"></td>
        	<td><input name="pay[<?=$row['id']?>]" value="<?=$row['pay']?>" style="text-align:right; width:55px;"></td>
        	<td><input name="note[<?=$row['id']?>]" value="<?=$row['note']?>" style="width:100%;"></td>
        </tr>
		<?
	}
	?>
		<tr>
        	<th style="font-weight:bold;"><input type="hidden" name="id[0]">добавить</th>
        	<td><input name="date[0]" value="<?=date('d.m.Y H:i')?>" style="text-align:center; width:110px;"></td>
        	<td><input name="pay[0]" value="" style="text-align:right; width:55px;"></td>
        	<td><input name="note[0]" value="" style="width:100%;"></td>
        </tr>
    </table>
    <div align="center" style="margin-top:10px;"><input type="submit" value="сохранить" class="but1"></div>
	</form>    
    <?

$content = ob_get_clean();

require("../tpl/tpl_popup.php");
?>