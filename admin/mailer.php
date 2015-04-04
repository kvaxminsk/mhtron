<?
require('inc/common.php');

$rubric = 'Рассылка';
$tbl = 'mailer';

// ------------------- СОХРАНЕНИЕ ------------------------
if(isset($_GET["action"]))
{
	$id = @$_GET['id'] ? (int)@$_GET['id'] : 0;
	
	switch(@$_GET['action'])
	{
		// ----------------- сохранение
		case "save":
		
			foreach($_POST as $key=>$value)
				$$key = clean($value);
			
			if(!$tema) errorAlert('необходимо указать тему !');
			if(!$text) errorAlert('необходимо ввести текст сообщения !');
			
			$ids_managers = implode(',',$_POST['ids_managers']);
			$ids_users = implode(',',$_POST['ids_users']);
			
			$set = "`date`=NOW(),
							tema='{$tema}',
							text='{$text}',
							send_managers='{$send_managers}',
							ids_managers=".($ids_managers?"'{$ids_managers}'":"NULL").",
							send_users='{$send_users}',
							ids_users=".($ids_users?"'{$ids_users}'":"NULL");
			if($id = update($tbl,$set,$id))
			{
				// закачиваем файлы
				if($_FILES['files']['name'])
				{
					for($i=0; $i<count($_FILES['files']['name']); $i++)
					{
						if(!$fname = basename($_FILES['files']['name'][$i])) continue;
						
						$fe = getFileExtension($fname); // расширение файла
						$fname = mb_substr($fname,0,mb_strlen($fname)-mb_strlen($fe)-1);
						$fname = makeUrl($fname).'.'.$fe;
						
						if($id_file = update('mailer_files',"id_mailer={$id},name='{$fname}'"))
						{
							$fname = $id_file.'_'.$fname;
							$path = $_SERVER['DOCUMENT_ROOT']."/uploads/mailer/{$fname}";
							@move_uploaded_file($_FILES['files']['tmp_name'][$i],$path);
							@chmod($path,0644);
						}
					}
				}
				
				// проверка
				//mailTo('epihovad@mail.ru','Тема','Текст','busarin@autopartrus.ru');
				
				$mails = array();
				// ящики менеджеров
				if($send_managers && $ids_managers)
				{
					$where = $ids_managers ? " WHERE id IN ({$ids_managers})" : '';
					$arr = getArr("SELECT mail FROM {$prx}managers{$where}");
					$mails = array_merge($mails,$arr);
				}
				// ящики пользователей
				if($send_users && $ids_users)
				{
					$where = $ids_users ? " and id IN ({$ids_users})" : '';
					$arr = getArr("SELECT mail FROM {$prx}users WHERE status=1{$where}");
					$mails = array_merge($mails,$arr);
				}
				
				// оправляем письмо
				$admin_mail = set('admin_email');
				
				$files = array();
				$r = mysql_query("SELECT id,name FROM {$prx}mailer_files WHERE id_mailer='{$id}'");
				while($arr = @mysql_fetch_assoc($r))
				{
					$path = $_SERVER['DOCUMENT_ROOT']."/uploads/mailer/{$arr['id']}_{$arr['name']}";
					if(file_exists($path))
						$files[] = $path;
				}
				
				foreach($mails as $mail)
				{
					if($mail)
						mailToFiles($mail,$tema, stripslashes($text),$admin_mail,$files);
				}
				?>
				<script>
				alert('Сообщение успешно разослано!');
				top.location.href = '<?=$script?>';
        </script>
				<?
			}	
			else
				errorAlert('Во время сохранения данных произошла ошибка');	
		break;
		// ----------------- удаление одной записи
		case "del":
			// мочим письмо
			update($tbl,'',$id);
			// мочим файлы
			$r = sql("SELECT id,name FROM {$prx}mailer_files WHERE id_mailer='{$id}'");
			while($arr = @mysql_fetch_assoc($r))
			{
				@unlink($_SERVER['DOCUMENT_ROOT'].'/uploads/mailer/'.$arr['id'].'_'.$arr['name']);
				update('mailer_files','',$arr['id']);
			}
			?><script>top.location.href = "<?=$script?>";</script><?
		break;
		// ----------------- удаление нескольких записей
		case 'multidel':
			foreach($_POST['check_del_'] as $k=>$v)
			{
				// мочим письмо
				update($tbl,'',$k);
				// мочим файлы
				$r = sql("SELECT id,name FROM {$prx}mailer_files WHERE id_mailer='{$k}'");
				while($arr = @mysql_fetch_assoc($r))
				{
					@unlink($_SERVER['DOCUMENT_ROOT'].'/uploads/mailer/'.$arr['id'].'_'.$arr['name']);
					update('mailer_files','',$arr['id']);
				}
			}
			?><script>top.location.href = "<?=$script?>";</script><?
		break;
		// ----------------- удаление одной записи
		case 'fdel':
			$fname = $_GET['fname'];
			$id_mailer = (int)$_GET['id_mailer'];
			update('mailer_files','',$id);
			@unlink($_SERVER['DOCUMENT_ROOT']."/uploads/mailer/{$fname}");
			?><script>top.location.href = "<?=$script?>?red=<?=$id_mailer?>";</script><?
		break;
	}
	exit;
}
// ------------------ РЕДАКТИРОВАНИЕ --------------------
elseif(isset($_GET['red']))
{
	$id = (int)$_GET['red'];
	
	$rubric .= ' &raquo; '.($id ? 'Редактирование' : 'Добавление');
	$page_title .= ' :: '.$rubric;
	
	$row = gtv($tbl,'*',$id);
	
	ob_start();
	?>
  <style>
	#rgroup { margin-bottom:10px; }
	#rgroup .rb { float:left; margin:-3px 5px 0 -5px; }
	#rgroup .label { margin:0 20px 0 0; }
	</style>
  <script type="text/javascript" src="js/mailer.js"></script>
	<form id="frm" action="?action=save&id=<?=$id?>" method="post" target="ajax" enctype="multipart/form-data">
	<table width="100%" border="0" cellspacing="0" cellpadding="5" class="tab_red">
		<tr>
			<th class="tab_red_th"></th>
			<th>Тема</th>
			<td><?=show_pole('text','tema',htmlspecialchars($row['tema']))?></td>
		</tr>
		<tr>
			<th class="tab_red_th"></th>
			<th>Текст</th>
			<td><?=showFck("text",$row['text'],'Medium','100%',300);?></td>
		</tr>
    <tr>
			<th class="tab_red_th"></th>
			<th>Файлы</th>
			<td>
      	<div class="fblock">
          <div class="add">
            <div class="i1"><input type="file" name="files[]"></div>
            <div class="i2"><a href="" title="добавить">ещё</a></div>
          </div>
          <?
					$r = sql("SELECT id,name FROM {$prx}mailer_files WHERE id_mailer='{$row['id']}'");
					if(@mysql_num_rows($r))
          {
            $i=1;
            while($arr = mysql_fetch_assoc($r))
            {
              ?>
              <div class="file">
                <div class="i1"><?=$i++?>.</div>
                <div class="i2"><a href="/uploads/mailer/<?=$arr['id']?>_<?=$arr['name']?>" target="_blank"><?=$arr['id']?>_<?=$arr['name']?></a></div>
                <div class="i3"><a href="?action=fdel&id=<?=$arr['id']?>&fname=<?=$arr['id']?>_<?=$arr['name']?>&id_mailer=<?=$row['id']?>" title="удалить" target="ajax"><img src="img/del.png"></a></div>
              </div>
              <?
            }
          }
          ?>
        </div>
      </td>
		</tr>
    <tr>
      <th class="tab_red_th"></th>
      <th>Рассылать менеджерам</th>
      <td><?=dll(array('0'=>'нет','1'=>'да'),' name="send_managers"',isset($row['send_managers'])?$row['send_managers']:1)?></td>
    </tr>
		<tr>
			<th class="tab_red_th"><?=help('по-умолчанию рассылка осуществляется всем менеджерам,<br>после добавления менеджеров в список,<br>письмо отправляется только указанным в списке объектам.')?></th>
			<th>Менеджеры</th>
			<td>
        <input id="add_managers" type="button" class="but1" value="добавить" style="margin:5px 10px 10px 0;">
        <input type="button" class="but1" value="удалить" style="margin:5px 0 10px 0;" onClick="del_options($('#ids_managers'));">
        <select id="ids_managers" name="ids_managers[]" size="10" style="width:100%" multiple>
        <?
        if($row['ids_managers'])
        {
          $query = "SELECT id,name FROM {$prx}managers WHERE id IN ({$row['ids_managers']}) ORDER BY name";
          $res = mysql_query($query);
          while($arr = @mysql_fetch_assoc($res))
          {
            ?><option value="<?=$arr['id']?>"><?=$arr['name']?></option><?
          }
        }
        ?>
        </select>
      </td>
		</tr>
    <tr>
      <th class="tab_red_th"></th>
      <th>Рассылать пользователям</th>
      <td><?=dll(array('0'=>'нет','1'=>'да'),' name="send_users"',isset($row['send_users'])?$row['send_users']:1)?></td>
    </tr>
    <tr>
			<th class="tab_red_th"><?=help('по-умолчанию рассылка осуществляется всем пользователям,<br>после добавления пользователей в список,<br>письмо отправляется только указанным в списке объектам.')?></th>
			<th>Пользователи</th>
			<td>
        <input id="add_users" type="button" class="but1" value="добавить" style="margin:5px 10px 10px 0;">
        <input type="button" class="but1" value="удалить" style="margin:5px 0 10px 0;" onClick="del_options($('#ids_users'));">
        <select id="ids_users" name="ids_users[]" size="10" style="width:100%" multiple>
        <?
        if($row['ids_users'])
        {
          $query = "SELECT id,org FROM {$prx}users WHERE id IN ({$row['ids_users']}) ORDER BY org";
          $res = mysql_query($query);
          while($arr = @mysql_fetch_assoc($res))
          {
            ?><option value="<?=$arr['id']?>"><?=$arr['org']?></option><?
          }
        }
        ?>
        </select>
      </td>
		</tr>
		<tr>
			<th class="tab_red_th"></th>
			<th></th>
			<td align="center">
				<input type="submit" value="Отправить" class="but1" onclick="loader(true)" />&nbsp;
				<input type="button" value="Отмена" class="but1" onclick="location.href='<?=$script?>'" />
			</td>
		</tr>
	</table>
	</form>
  <?=popup_modul()?>
	<?
	$content = ob_get_clean();
}
// -----------------ПРОСМОТР-------------------
else
{
	$cur_page = @$_SESSION['page'] ? @(int)$_SESSION['page'] : 1;
	
	$f_context = htmlspecialchars(stripslashes($_SESSION['context']));

	$where = '';
	if($f_context) $where .= " and (tema LIKE '%".clean($f_context)."%' OR text LIKE '%".clean($f_context)."%')";
	
	$page_title .= " :: ".$rubric; 
	$rubric .= " &raquo; Общий список"; 
	
	$razdel = array("Добавить"=>"?red=0","Удалить"=>"javascript:multidel(document.red_frm,'check_del_','');");
	$subcontent = show_subcontent($razdel);
	
	$query = "SELECT * FROM {$prx}{$tbl} WHERE 1{$where}";

	$count_obj = getField(str_replace('*','COUNT(*)',$query)); // кол-во объектов в базе
	$count_obj_on_page = 20; // кол-во объектов на странице
	$kol_str = ceil($count_obj/$count_obj_on_page); // количество страниц

	ob_start();
	// проверяем текущую сортировку
	// и формируем соответствующий запрос
	if($_SESSION['sort']) 
	{
		$sort = explode(":",$_SESSION['sort']);
		$cur_pole = $sort[0];
		$cur_sort = $sort[1];

		$query .= " ORDER BY {$cur_pole}";
		$query .= $cur_sort=="up" ? " DESC" : " ASC";
	}
	else
		$query .= " ORDER BY id DESC";
	$query .= " LIMIT ".($count_obj_on_page*$cur_page-$count_obj_on_page).",".$count_obj_on_page;
	//-----------------------------
	//echo $query;
	
	show_filters($script);
	show_navigate_pages($kol_str,$cur_page,$script);
	
	?>
	<table class="filter_tab" style="margin:5px 0 0 0;">
		<tr>
			<td>контекстный поиск</td>
			<td><input type="text" id="search_txt" value="<?=$f_context?>" style="width:200px;"></td>
			<td><a href="" target="ajax" class="link" onClick="RegSessionSort('<?=$script?>','context='+$('#search_txt').val());return false;">найти</a></td>
		</tr>
	</table>
	
	<form action="?action=multidel" name="red_frm" method="post" target="ajax">
	<input type="hidden" id="cur_id" value="<?=@$_GET['id']?@(int)$_GET['id']:""?>" />
	<table width="100%" border="1" cellspacing="0" cellpadding="0" class="tab1">
		<tr>
			<th><input type="checkbox" name="check_del" id="check_del" /></th>
			<th>№</th>
      <th><?=ShowSortPole($script,$cur_pole,$cur_sort,'Дата','date');?></th>
      <th width="30%"><?=ShowSortPole($script,$cur_pole,$cur_sort,'Тема','tema');?></th>
			<th width="60%">Текст</th>
			<th style="padding:0 30px;"></th>
		</tr>
	<?
	$res = mysql_query($query);
	if(@mysql_num_rows($res))
	{
		$i=1;
		while($row = mysql_fetch_array($res))
		{
			?>
			<tr id="<?=$row['id']?>">
			<th><input type="checkbox" name="check_del_[<?=$row['id']?>]" id="check_del_<?=$row['id']?>" /></th>
			<th><?=$i++?></th>
      <td><?=date('d.m.Y',strtotime($row['date']))?></td>
      <td><a href="?red=<?=$row['id']?>" class="link1"><?=$row['tema']?></a></td>
			<td><?=nl2br($row['text'])?></td>
			<td nowrap align="center"><?=btn_edit($row['id'])?></td>
			</tr>
			<?
		}		
	}
	else
	{
		?>
		<tr>
			<td colspan="10" align="center">
			по вашему запросу ничего не найдено. <?=help('нет ни одной записи отвечающей критериям вашего запроса,<br>возможно вы установили неверные фильтры')?>
			</td>
		</tr>
		<?
	}
	?>
	</table>
	</form>
	<?	
	$content = $subcontent.ob_get_clean();
}

require("tpl/tpl.php");
?>