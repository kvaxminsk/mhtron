<?
require('inc/common.php');

$id = (int)@$_GET['id'];
$tbl = 'news';
$pageID = 4;

if($id)
{
	$row = getRow("SELECT * FROM {$prx}{$tbl} WHERE id={$id} and status=1");
	if(!$row)
	{
		header("location: /{$tbl}/");
		exit;
	}
	
	$title = "Новости &raquo; ".$row['name'];
	$navigate = ' &raquo; <a href="/'.$tbl.'/">Новости</a> &raquo; '.date('d.m.Y',strtotime($row['date'])).'';
	
	ob_start();
	?>
	<h1>Новости</h1>
	<?=nh($row['date'],$row['avtor'])?>
    <h2 style="margin-top:10px"><?=$row['name']?></h2>
    <div style="font-size:16px"><?=$row['text']?></div>
    <div class="back"><a href="">&laquo; назад</a></div>
	<?
	$content = ob_get_clean();
	
	foreach(array('title','keywords','description') as $val)
		if($row[$val]) $$val = $row[$val];
}
else
{
	$title = "Новости";
	$navigate = " &raquo; Новости";
	
	$cur_page = @$_GET['page'] ? (int)$_GET['page'] : 1;
		
	// кол-во объектов в базе
	$count_obj = getField("SELECT count(*) from {$prx}{$tbl} WHERE status=1");
	$count_obj_on_page = set("count_{$tbl}_client"); // кол-во объектов на странице
	$kol_str = ceil($count_obj/$count_obj_on_page); // количество страниц

	ob_start();
	
	$query = "SELECT * FROM {$prx}{$tbl} WHERE status=1 ORDER BY `date` DESC limit ".($count_obj_on_page*$cur_page-$count_obj_on_page).",".$count_obj_on_page;
	$res = mysql_query($query);
	$count = @mysql_num_rows($res);
	if($count)
	{
		?>
        <h1>Новости</h1>
        <table width="100%">
		<?
		$i=0;
		while($row = mysql_fetch_assoc($res))
		{
			if(++$i%2==1)
			{
				echo "<tr>";
				$padding = $count==1 ? '0 0 20px 0' : '0 13px 20px 0';
			}
			else
				$padding = $count==1 ? '0 0 20px 0' : '0 0 20px 13px';
			?>
            <td width="50%" valign="top" style="padding:<?=$padding?>">
            	<?=nh($row['date'],$row['avtor'])?>
                <div style="padding:0 5px">
                <div style="margin-bottom:5px;"><a href="/<?=$tbl?>/<?=$row['id']?>.htm"><?=$row['name']?></a></div>
                <?=$row['preview']?>
                </div>
            </td>
            <?
		}
		?></table><?
        		
		echo show_navigate_pages($kol_str,$cur_page,"/{$tbl}/");
	}
		
	$content = ob_get_clean();
}

require("tpl/template.php");
?>