<?
require('inc/common.php');

$link = @$_GET['link'] ? mysql_real_escape_string($_GET['link']) : "/";

$row = getRow("SELECT * FROM {$prx}pages WHERE link='{$link}'");
if(!$row)
{
	header("location: /");
	exit;
}

$pageID = $row['id'];
$title = $row['name'];
$navigate = " &raquo; {$row['name']}";

ob_start();

?>
<h1><?=$row['name']?></h1>
<?
	if($link == 'katalogi')
	{	?>
		<span id="zapros_catalog">Запросить каталог</span>	
		<?=show_zapros_catalog()?>
		<script>
			$(function(){
				$('span#zapros_catalog').click(function(){
					$pop_zapros_catalog = $('#pop_zapros_catalog');
					if($pop_zapros_catalog.size() && !$pop_zapros_catalog.is(':visible'))
					{
						var bs = BodySize();
						$pop_zapros_catalog.css('top', 40);
						setTimeout(function(){
							$pop_zapros_catalog.show().animate({left:'-30px'}, 100);
						},400);
										
						$('#pop_zapros_catalog_exit').bind('click',function(){
							$pop_zapros_catalog.animate({left:-500}, 100, function(){ $(this).hide() });
						});
					}
				});
			});
		</script>		
		<br><br>
<?	}
?>

<?=$row['text']?>
<?
if($pageID=='28')
	echo show_message_frm();

$content = ob_get_clean();

foreach(array('title','keywords','description') as $val)
	if($row[$val]) $$val = $row[$val];

require('tpl/template.php');
?>
