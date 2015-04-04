<?
require('common.php');

$num = (int)$_GET['num'];

ob_start();

$mas = getTree("SELECT id,id_parent,code,name FROM {$prx}cat WHERE id_parent='%s' ORDER BY sort");
$mas_size = sizeof($mas);

if($mas_size>0)
{
	?>
    <style>
	td { font: normal 12px Arial, Helvetica, sans-serif; }
	</style>
    <script>
	var num = '<?=$num?>';
	
    function add_good(obj)
	{
		var gid = obj.id;
		mas = gid.split('_');
		var id = mas[1];
		var name = $('#gname_'+id).val();
		
		var _find=0;
		top.$('#coord_tab td:eq(0) input:hidden').each(function(){
			if($(this).val()==id)
			{
				alert('данный товар уже есть!');
				_find++;
			}
		});
		
		if(!_find)
		{
			top.$('#cell_dot'+num+' td:eq(0) input:hidden').val(id);
			top.$('#cell_dot'+num+' .add_good').html(name);
			top.hide_popup_window();
		}
	}
    </script>
     
	<div align="center" style="padding:10px 0 10px 0px;">
	<a href="" class="cat_open" style="color:#697079;">Развернуть каталог</a>&nbsp;|&nbsp;<a href="" class="cat_close" style="color:#697079;">Свернуть каталог</a>
	</div>
	
	<?
	$mas_opened = getArrParents("SELECT id,id_parent FROM {$prx}cat WHERE id='%s'",$cur_id);
		
	$old_lvl = 0;
	foreach($mas as $vetka)
	{
		$lvl = $vetka['level'];
		$cat_id = $vetka['row']['id'];
		$cat_id_parent = $vetka['row']['id_parent'];
		$cat_name = $vetka['row']['name'];
		
		$otstup = $lvl>0 ? "padding-left:".($lvl*20)."px;" : "";
		ins_div($lvl,$old_lvl,$cat_id_parent,$cat_id,$cur_id);
		$get_chaild = find_chaild($cat_id);
		
		$count_parent_goods = 0;
		get_count_parent_goods($cat_id);
		
		$find_goods = getField("SELECT count(*) FROM {$prx}goods WHERE id_cat={$cat_id}") ? true : false;
		
		$style = $cat_id==$cur_id ? " style='background-color:#ff9;'": "";
		?>
		<table width="100%" border="0" cellspacing="0" cellpadding="0" style="margin:3px 0 3px 0;">
		  <tr>
			<td width="20" align="center" style="<?=$otstup?>">
				<?
				if($get_chaild)
				{
					?><span id="znak_<?=$cat_id?>" class="rubric_znak"><img src="../img/cat_closed.gif" align="absmiddle" /></span><?
				}
				elseif($find_goods)
				{
					?><span id="znak_<?=$cat_id?>" class="rubric_znak"><img src="../img/cat_closed.gif" align="absmiddle" /></span><?
				}
				?>
			</td>			
			<td width="20" align="center">
				<span id="folder_<?=$cat_id?>"class="rubric_znak"><img src="../img/cat_folder_close1.gif" align="absmiddle" /></span>
			</td>
			<td align="left" style="padding-left:5px;">
				<a href='?red=<?=$cat_id?>' title='редактировать текущий раздел' class='link1'<?=$style?>><?=$cat_name?></a>
				<?
				if($count_parent_goods)
				{
					?><sup title="кол-во товаров в рубрике"><?=$count_parent_goods?></sup><?
				}
				?>
			</td>
		  </tr>
		</table>
        <?
		if($find_goods)
		{
			?>
			<div id="goods_<?=$cat_id?>" style="display:none;">
			<table width="100%" border="0" cellspacing="0" cellpadding="0" style="margin:3px 0 3px 0;">
			  <tr>
				<td width="20" style="<?=$otstup?>"></td>
				<td width="20"></td>
				<td style="padding-left:5px;">
					<?
					$res = mysql_query("SELECT id,articul,name FROM {$prx}goods WHERE id_cat={$cat_id} and chief=1");
					while($arr = mysql_fetch_assoc($res))
					{
						$articul = $arr['articul'];
						$name = $arr['name'];
						
						$str_articul = $articul ? "<span style='color:#090;'>".$articul."</span> - " : "";
						$str_name = $name;
						
						?>
						<table width="100%" border="0" cellspacing="0" cellpadding="0">
						  <tr>
							<td width="100%">
							<input type="hidden" id="gname_<?=$arr['id']?>" value="<?=$articul?> - <?=$name?>">
							<?=$str_articul?><a href="" target="_blank" id="gid_<?=$arr['id']?>" style="color:#697079" onclick="add_good(this);return false;"><?=$str_name?></a>
                            </td>
						  </tr>
						</table>
						<?
					}
					?>
				</td>
			  </tr>
			</table>
			</div>
			<?
		}
		
		$old_lvl = $lvl;
	}
	ins_div(0,$old_lvl,$cat_id_parent,$cat_id,$cur_id);
	?>
	
	<div align="center" style="padding:10px 0 0 0;">
	<a href="" class="cat_open" style="color:#697079;">Развернуть каталог</a>&nbsp;|&nbsp;<a href="" class="cat_close" style="color:#697079;">Свернуть каталог</a>
	</div>
    
	<script>
	$.preloadImg('../img/cat_folder_open1.gif','../img/cat_opened.gif','../img/cat_folder_close1.gif','../img/cat_closed.gif');
	
	$(function(){
		<?
		foreach($mas_opened as $id)
		{
			?>
			$('#cat_<?=$id?>').show();
			$('#znak_<?=$id?> img').attr('src','../img/cat_opened.gif');
			$('#folder_<?=$id?> img').attr('src','../img/cat_folder_open1.gif');
			<?
		}
		?>
		$("span[id^='znak_']").click(function(){
			var mas = this.id.split('_');
			var id = mas[1];
			
			var block = $('#cat_'+id);
			
			if(!block.size())
				block = $('#goods_'+id);
			
			if(!block.is(':visible'))
			{
				block.show();
				$(this).find('img').attr('src','../img/cat_opened.gif');
				$('#folder_'+id).find('img').attr('src','../img/cat_folder_open1.gif');
			}
			else
			{
				block.hide();
				$(this).find('img').attr('src','../img/cat_closed.gif');
				$('#folder_'+id).find('img').attr('src','../img/cat_folder_close1.gif');
			}
		});
		
		$(".cat_open").click(function(){
			$("div[id^='cat_']").show();
			$("span[id^='znak_']").find('img').attr('src','../img/cat_opened.gif');
			$("span[id^='folder_']").find('img').attr('src','../img/cat_folder_open1.gif');
			
			$("div[id^='goods_']").show();
			return false;
		});
		$(".cat_close").click(function(){
			$("div[id^='cat_']").hide();
			$("span[id^='znak_']").find('img').attr('src','../img/cat_closed.gif');
			$("span[id^='folder_']").find('img').attr('src','../img/cat_folder_close1.gif');
			
			$("div[id^='goods_']").hide();
			return false;
		});
		
	});
	</script>
	<?
}

$content = ob_get_clean();

require("../tpl/tpl_popup.php");
?>