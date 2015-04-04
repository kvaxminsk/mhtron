<?
require('inc/common.php');

$id = (int)@$_GET['id'];
$tbl = 'articles';
$pageID = 26;

if($id)
{
	$row = getRow("SELECT * FROM {$prx}{$tbl} WHERE id={$id} and status=1");
	if(!$row)
	{
		header("location: /{$tbl}/");
		exit;
	}
	
	foreach(array('title','keywords','description') as $val)
		if($row[$val]) $$val = $row[$val];
	
	$title = "Статьи &raquo; ".$row['name'];
	$navigate = ' &raquo; <a href="/'.$tbl.'/">Статьи</a> &raquo; '.date('d.m.Y',strtotime($row['date'])).'';
	
	ob_start();
	
	$count_comments = getField("SELECT count(*) FROM {$prx}art_comments WHERE id_art={$id}");
	
	?>
	<h1>Статьи</h1>
	<?=nh($row['date'],$row['avtor'],$count_comments)?>
    <h2 style="margin-top:10px"><?=$row['name']?></h2>
    <div style="font-size:16px"><?=$row['text']?></div>
    <div class="back"><a href="">&laquo; назад</a></div>
	<?
		
	// --------------- КОММЕНТАРИИ -------------------
	?>
    <table width="100%" style="margin:20px 0 0 0">
      <tr>
        <td><h2 style="margin:0 0 0 20px; font-size:14px">Комментарии</h2></td>
        <td align="right">
        	<?
            if(isset($_SESSION['user']))
			{
				?><a id="link_add_com" href="" class="link">Добавить</a>&nbsp;/&nbsp;<?
			}
			?><a id="link_watch_com" href="" class="link">Смотреть</a>
        </td>
      </tr>
    </table>
    
    <?
	// если комментарии отсутствуют, но пользователь авторизован - none, иначе - block
	$display = !$count_comments && isset($_SESSION['user']) ? 'none' : 'block';
	?>
    <div id="com_watch" style="display:<?=$display?>">
    <?
    if($count_comments)
	{
		?><table id="com_tab"><?
		$query  = "SELECT A.*, B.name, B.surname FROM {$prx}art_comments A ";
		$query .= "INNER JOIN {$prx}users B ON A.id_user=B.id ";
		$query .= "WHERE A.id_art={$id} ";
		$query .= "ORDER BY A.`date`";
		$res = sql($query);
		$i=0;
		while($arr = @mysql_fetch_assoc($res))
		{
			$class = ++$i%2 ? 'com_tr1' : 'com_tr2';
			?>
            <tr class="<?=$class?>">
            	<th><?=$arr['name']?> <?=$arr['surname']?><br><span><?=date('d.m.Y',strtotime($arr['date']))?></span></th>
                <td><?=break_to_str($arr['text'])?></td>
            </tr>
            <?
		}
		?></table><?
	}
	else
	{
		?>
        <center>комментарии отсутствуют</center>
        <?
	}
	?>
    </div>
    
    <?
	if(isset($_SESSION['user']))
	{
		// если комментарии отсутствуют, но пользователь авторизован - none, иначе - block
		$display = !$count_comments && isset($_SESSION['user']) ? 'block' : 'none';
		?>
		<div id="com_add" style="display:<?=$display?>">
		<form id="com_frm" action="/comments.php" method="post" target="ajax">
        <input type="hidden" name="id_art" value="<?=$id?>" />
		<table>
		  <tr>
			<td><textarea name="text"></textarea></td>
		  </tr>
		</table>
        </form>
        <div align="right" style="margin-top:10px">
        	<input id="btn_clear" type="image" src="/img/btn_clear.png" width="93" height="24" style="margin-right:10px" />
            <input id="btn_add" type="image" src="/img/btn_add.png" width="93" height="24" />
        </div>
		</div>
		<?
	}
	?>
    <script>
	$(function(){
		// добавить
		$('#link_add_com').click(function(){
			if($('#com_add').is(':visible'))
				return false;
			
			$('#com_watch').hide();
			$('#com_add').show();
			/*if($.browser.msie)
				$('#com_watch').animate({ height: 0 }, 400, "linear", function(){
					$('#com_watch').hide(); 
					$('#com_add').slideDown(400);
				});
			else
				$('#com_watch').slideUp(400,function(){ $('#com_add').slideDown(400) });
			*/
			return false;
		});
		// смотреть
		$('#link_watch_com').click(function(){
			if($('#com_watch').is(':visible'))
				return false;
			
			$('#com_add').hide();
			$('#com_watch').show();
			/*if($.browser.msie)
				$('#com_add').animate({ height: 0 }, 400, "linear", function(){
					$('#com_add').hide(); 
					$('#com_watch').slideDown(400);
				});
			else
				$('#com_add').slideUp(400,function(){ $('#com_watch').slideDown(400) });
			*/
			return false;
		});
		// очистить
		$('#btn_clear').click(function(){ $('#com_frm textarea').val(''); $('#com_frm textarea').focus(); });
		// добавить
		$('#btn_add').click(function(){ $('#com_frm').submit() });		
	})
	</script>
    <?
	
	$content = ob_get_clean();
}
else
{
	$title = "Статьи";
	$navigate = " &raquo; Статьи";
	
	$cur_page = @$_GET['page'] ? (int)$_GET['page'] : 1;
		
	// кол-во объектов в базе
	$count_obj = getField("SELECT count(*) from {$prx}{$tbl} WHERE status=1");
	$count_obj_on_page = set("count_{$tbl}_client"); // кол-во объектов на странице
	$kol_str = ceil($count_obj/$count_obj_on_page); // количество страниц

	ob_start();
	
	$query = "SELECT * FROM {$prx}{$tbl} WHERE status=1 ORDER BY sort limit ".($count_obj_on_page*$cur_page-$count_obj_on_page).",".$count_obj_on_page;
	$res = mysql_query($query);
	$count = @mysql_num_rows($res);
	if($count)
	{
		?>
        <h1>Статьи</h1>
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
            	<?=nh($row['date'],$row['avtor'],getField("SELECT count(*) FROM {$prx}art_comments WHERE id_art={$row['id']}"))?>
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