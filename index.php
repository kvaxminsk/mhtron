<?
require('inc/common.php');

ob_start();

?>
<?=show_makers_list()?>
<?
$res = sql("SELECT * FROM {$prx}banners WHERE `top`='1' ORDER BY sort,id");
if(mysql_num_rows($res))
{	?>
	<div align="center" style="white-space:nowrap; margin-top:5px; width:980px; overflow:hidden;" class="str_wrap">
	<?	while($row = mysql_fetch_assoc($res))
		{
			$fe = getFileFormat($_SERVER['DOCUMENT_ROOT']."/uploads/banners/{$row['id']}.*");
			?><a href="<?=$row['link']?>"><img src="/banners/<?=$row['id']?>.<?=$fe?>" style="margin:0 1px;"></a><?
		}
		unset($row);	?>
	</div>
	<script>
	  $(window).load(function(){
		 $('.str_wrap').liMarquee();
	  });
	</script>
<?
}
?>
<? //=show_index_news()?>
<br>
<?=getField("SELECT text FROM {$prx}pages WHERE link='/'")?>
<?
$content = ob_get_clean();

//foreach(array('title','keywords','description') as $val)
	//if($row[$val]) $$val = $row[$val];

require("tpl/template.php");
?>