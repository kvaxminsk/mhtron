<?
require('common.php');

// ------------------- СОХРАНЕНИЕ ------------------------
if(isset($_GET["action"]))
{
	switch($_GET['action'])
	{
		// ----------------- сохранение
		case "save":
			
			$location = clean($_POST['location']);
			
			foreach($_POST['id'] as $id=>$v)
			{
				$show_flag = isset($_POST['show'][$id]) ? 1 : 0;
				
				update("criteria","show_flag={$show_flag}",$id);
			}
			
			?>
			<script>
			top.hide_popup_window();
			top.location.href = '../<?=$location?>';
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
	width:100%;
	color:#697079;
	font:normal 12px Arial, Helvetica, sans-serif;
}
#tab input { padding:0; margin:0; }
</style>
<script>
$(function(){
	$('#tab').zebra();
	$('#check_all').click(function(){
		$('input[type=checkbox]').not(this).attr('checked',$(this).attr('checked'));
	});
});
</script>
<?
$tbl = clean($_GET['tab']);
$location = clean($_GET['location']);

$res = mysql_query("SELECT * FROM {$prx}criteria WHERE tab_name='{$tbl}' ORDER BY id");
if(@mysql_num_rows($res))
{
	?>
	<form action="?action=save" method="post" target="ajax" style="margin:0;">
    <input type="hidden" name="location" value="<?=$location?>">
	<table id="tab">
    <tr>
    	<th width="20">№</th>
        <th>имя поля</th>
        <th width="70">отображать<br><input type="checkbox" id="check_all" /></th>
    </tr>
	<?
	$i=0;
	while($row = mysql_fetch_assoc($res))
	{
		$comment = $row['comment'] ? $row['comment'] : getStructureTable($tbl,$row['field_name']);
		?>
		<tr>
        	<th style="font-weight:bold;"><input type="hidden" name="id[<?=$row['id']?>]" value="1"><?=(++$i)?></th>
        	<td style="text-align:left"><?=$comment?></td>
            <td style="padding:0;"><input type="checkbox" name="show[<?=$row['id']?>]"<?=$row['show_flag']?' checked':''?>></td>
        </tr>
		<?
	}
	?>
    </table>
    <div align="center" style="margin-top:10px;"><input type="submit" value="сохранить" class="but1"></div>
	</form>    
    <?
}

$content = ob_get_clean();

require("../tpl/tpl_popup.php");
?>