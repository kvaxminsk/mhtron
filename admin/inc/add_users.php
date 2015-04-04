<?
require('common.php');

$ids_users = explode(',',$_GET['ids']);
$list = $_GET['list'];

ob_start();

$res = mysql_query("SELECT id,org FROM {$prx}users ORDER BY org");
if(@mysql_num_rows($res))
{
	?>
	<style type="text/css">
	#tab { width:100%; margin:10px 0 0 0; border-collapse:collapse; border-color:#ecf0fb; }
	#tab th, #tab td { padding:2px 5px 2px 5px;	border:1px solid #93acd0; }
	#tab th { background-color:#d4dff2; color:#3e6aaa; font:bold 12px Arial, Helvetica, sans-serif; text-align:center; }
	#tab td { width:100%; color:#697079; font:normal 12px Arial, Helvetica, sans-serif; text-align:left; }
	</style>
	<script>
	function to_list($list)
	{
		$('input:checked').each(function(){
			var id = $(this).attr('id');
			var name = $(this).val();
			
			if(!$list.find('option[value="'+id+'"]').size())
				$list.append('<option value="'+id+'">'+name+'</option>');
		});
		top.hide_popup_window();
	}
	$(function(){
		// полосатая таблица
		$('#tab').zebra();
		$('#btn_save').click(function(){ to_list(top.$('#<?=$list?>')) })
		$('#btn_cancel').click(function(){ top.hide_popup_window() })
	});
	</script>
	<table id="tab">
  	<tr>
      <th></th>
      <th>Организация</th>
    </tr>
	  <?
		while($row = mysql_fetch_assoc($res))
		{
			$checked = in_array($row['id'],$ids_users) ? ' checked' : '';
			?>
			<tr>
				<th><input type="checkbox" id="<?=$row['id']?>" value="<?=htmlspecialchars($row['org'])?>"<?=$checked?>></th>
				<td><?=$row['org']?></td>
			</tr>
			<?
		}
		?>
  </table>
  <div align="center" style="margin-top:10px;">
  <input type="button" id="btn_save" value="сохранить" class="but1">
  <input type="button" id="btn_cancel" value="отмена" class="but1">
  </div>
	<?
}
$content = ob_get_clean();

require('../tpl/tpl_popup.php');
?>