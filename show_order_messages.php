<?
require('inc/common.php');

if(!isset($_SESSION['user']))
{
	header("Location: /");
	exit;
}

if(isset($_GET["action"]))
{
	switch($_GET["action"])
	{
		// ----------- добавление комментария
		case "add_comment":
			$id_order = (int)@$_POST['id_order'];
			if(!$id_order)
				errorAlertClient('show','alert','<center>Выберите номер заказа</center>');
			$text = clean($_POST['text'],true);
			if(!$text)
				errorAlertClient('show','alert','<center>Напишите сообщение</center>');
				
			if($id = update("order_messages","id_order={$id_order},`from`='user',text='{$text}',`date`=NOW()"))
			{
				// -------- фиксируем в журнале
				update("log","`date`=NOW(),text='новое сообщение в заказе',link='orders.php?red={$id_order}&id={$id}'");
				?>
				<script>
				top.$(document).jAlert('show','alert','<center>Сообщение успешно сохранено</center>',function(){
					top.location.href='/messages/';
				});
        </script>
				<?
			}
			else
			{
				?>
				<script>
				top.$(document).jAlert('show','alert','Во время сохранения Вашего сообщения произошла ошибка!<br>Приносим свои извинения.<br>В ближайшее время проблема будет устранена.',function(){
					top.location.href='/messages/';
				});
        </script>
				<?
			}
			break;
	}	
	exit;
}

$title = 'Профиль &raquo; Сообщения';

ob_start();

?>
<h1>Профиль</h1>
<?=set('profile_text')?>
<div id="prof_str">
<a href="/profile/" class="link">Изменить данные</a> / <a href="/cart/" class="link">Заказы</a> / <span>Сообщения</span>
<? if($_SESSION['user']['manager']) { ?> / <a href="/organizations/" class="link">Организации</a><? } ?>
</div>
<h3><b>Ваш персональный менеджер</b></h3>
<?
$manager = getRow("SELECT id,name,text FROM {$prx}managers WHERE id={$_SESSION['user']['id_manager']}");
?>
<table width="100%" style="margin:0 0 20px 0">
  <tr>
  	<?
	if(file_exists($_SERVER['DOCUMENT_ROOT']."/uploads/managers/{$manager['id']}.jpg"))
	{
		?><td valign="top" width="100"><img src="/uploads/managers/<?=$manager['id']?>.jpg" width="70" height="70" /></td><?
	}
	?>
    <td valign="top"><?=$manager['text']?></td>
  </tr>
</table>

<?
$res = mysql_query("SELECT id,`date` FROM {$prx}orders WHERE id_user=".$_SESSION['user']['id']." ORDER BY `date` DESC");
if(@mysql_num_rows($res))
{
	$ids_orders = array();
	while($row = mysql_fetch_assoc($res))
	{
		$ids_orders[] = $row['id'];
		?>
        <h2 style="padding-left:20px; font-size:14px; margin:5px 0;">
        	Заказ № <span class="numer"><?=$row['id']?></span> от <span class="numer"><?=date('d.m.Y',strtotime($row['date']))?></span>
        </h2>
		<?
		$r = mysql_query("SELECT * FROM {$prx}order_messages WHERE id_order={$row['id']} ORDER BY `date`");
		if(@mysql_num_rows($r))
		{
			?><table id="com_tab"><?
			$i=0;
			while($arr = @mysql_fetch_assoc($r))
			{
				$class = ++$i%2 ? 'com_tr1' : 'com_tr2';
				$avtor = $arr['from']=='user' ? 'Вы' : getField("SELECT name FROM {$prx}managers WHERE id=".$_SESSION['user']['id_manager']);
				?>
				<tr class="<?=$class?>">
					<th><?=$avtor?><br><span><?=date('d.m.Y',strtotime($arr['date']))?></span></th>
					<td><?=break_to_str($arr['text'])?></td>
				</tr>
				<?
			}
			?></table><?
		}
		else
		{
			?><div style="padding-left:40px"><i>сообщения отсутствуют</i></div><?
		}
	}
	?>
    <div id="com_add" style="margin-top:40px">
    <form id="com_frm" action="?action=add_comment" method="post" target="ajax">
    <table>
      <tr>
        <td>
        	<div style="margin-bottom:10px">
            <select name="id_order" class="select">
            <option value="">-- выберите номер заказа --</option>
            <?
			foreach($ids_orders as $nomer)
			{
				?><option value="<?=$nomer?>">заказ № <?=$nomer?></option><?
			}
			?>
            </select>
            </div>
        	<textarea name="text"></textarea>
        </td>
      </tr>
    </table>
    </form>
    <div align="right" style="margin-top:10px">
        <input id="btn_clear" type="image" src="/img/btn_clear.png" width="93" height="24" style="margin-right:10px" />
        <input id="btn_add" type="image" src="/img/btn_add.png" width="93" height="24" />
    </div>
    </div>
    <script>
	$(function(){
		// очистить
		$('#btn_clear').click(function(){ $('#com_frm textarea').val(''); $('#com_frm textarea').focus(); });
		// добавить
		$('#btn_add').click(function(){ $('#com_frm').submit() });		
	})
	</script>
    <?
}

$content = ob_get_clean();

require('tpl/template.php');
?>
