<?
require('inc/common.php');

$id = (int)@$_GET['id'];
$tbl = 'avto';

if($id)
{
	$row = getRow("SELECT * FROM {$prx}{$tbl} WHERE id={$id}");
	if(!$row)
	{
		header("location: /{$tbl}/");
		exit;
	}
	
	foreach(array('title','keywords','description') as $val)
		if($row[$val]) $$val = $row[$val];
	
	$features1 = unserialize($row['features1']);
	$features2 = unserialize($row['features2']);
	$row['name'] = $features1['Модель'];
	
	$title = "Тягачи &raquo; ".$row['name'];
	$navigate = ' &raquo; <a href="/'.$tbl.'/">Тягачи</a> &raquo; '.$row['name'].'';
	
	ob_start();
	
	?>
	<h1>Ждут своего хозяина:</h1>
	<div style="height:20px;"></div>
	<table width="100%">
		<tr valign="top">
			<td>
				<div style="width:180px;">
					<a href="/uploads/avto/<?=$id?>_1.jpg" class="highslide" onclick="return hs.expand(this)">
					  <img src="/avto/160x120/<?=$id?>_1.jpg" />
				  </a>
				 </div>
			</td>
			<td width="100%">
				<table class="features">
			  <?	foreach($features1 as $key=>$val)
						if($val)
						{	?>
							<tr>
								<th><?=$key?></th>
								<td><?=$val?></td>
							</tr>
					<?	}	?>		  
				</table>
			</td>
			<td><div style="width:230px;"></div></td>
		</tr>
		<tr>
			<td colspan="2" style="padding:10px 0;">
			<?	for($i=2; $i<9; $i++)
					if(file_exists($_SERVER['DOCUMENT_ROOT']."/uploads/avto/{$id}_{$i}.jpg"))
					{ ?>
						<a href="/uploads/avto/<?=$id?>_<?=$i?>.jpg" class="highslide" onclick="return hs.expand(this)">
						  <img src="/avto/0x60/<?=$id?>_<?=$i?>.jpg" />
					  </a>
				<?	}	?>				
			</td>
		</tr>
		<tr>
			<td></td>
			<td>
				<table class="features">
			  <?	foreach($features2 as $key=>$val)
						if($val)
						{	?>
							<tr>
								<th><?=$key?></th>
								<td><?=$val?></td>
							</tr>
					<?	}	?>		  
				</table>
			</td>
		</tr>	
	</table>
	<br><br>
    <div class="back"><a href="">&laquo; назад</a></div>
    <?
	
	$content = ob_get_clean();
}
else
{
	$title = "Тягачи";
	$navigate = " &raquo; Тягачи";
	
	$cur_page = @$_GET['page'] ? (int)$_GET['page'] : 1;
		
	// кол-во объектов в базе
	$count_obj = getField("SELECT count(*) from {$prx}{$tbl}");
	$count_obj_on_page = 5; // кол-во объектов на странице
	$kol_str = ceil($count_obj/$count_obj_on_page); // количество страниц

	ob_start();
	
	$query = "SELECT * FROM {$prx}{$tbl} ORDER BY sort limit ".($count_obj_on_page*$cur_page-$count_obj_on_page).",".$count_obj_on_page;
	$res = mysql_query($query);
	$count = @mysql_num_rows($res);
	if($count)
	{
		?>
        <h1>Ждут своего хозяина:</h1>
		 <?	while($row = mysql_fetch_assoc($res)) 
		 		{
					$id = $row['id'];
					$features1 = unserialize($row['features1']); ?>
				  <div style="height:20px;"></div>
				  <table width="100%">
					<tr valign="top">
						<td>
							<div style="width:180px;">
								<a href="/avto/<?=$id?>.htm">
								  <img src="/avto/160x120/<?=$id?>_1.jpg" />
							  </a>
							 </div>
						</td>
						<td width="100%">
							<table class="features">
						  <?	foreach($features1 as $key=>$val)
									if($val)
									{	?>
										<tr>
											<th><?=$key?></th>
											<td><?=$val?></td>
										</tr>
								<?	}	?>		  
							</table>
						</td>
						<td><div style="width:230px;"></div></td>
					</tr>
				</table>
				<div style="height:10px;"></div>
				<?
			}
        		
		echo show_navigate_pages($kol_str,$cur_page,"/{$tbl}/");
	}
		
	$content = ob_get_clean();
}

require("tpl/template.php");
?>