<?
require('inc/common.php');

$rubric = 'Менеджеры';
$tbl = 'managers';

// ------------------- СОХРАНЕНИЕ ------------------------
if(isset($_GET["action"]))
{
	$id = (int)$_GET['id'];
	
	switch($_GET['action'])
	{
		// ----------------- сохранение
		case "save":
		
			foreach($_POST as $key=>$value)
				$$key = clean($value);
			
			if(!$login || !$pass)
				errorAlert('Поля \"Логин\" и \"Пароль\" должны быть заполнены !');
			
			$where = $id ? " and id<>{$id}" : "";	
			if(getField("SELECT login FROM {$prx}managers WHERE login='{$login}'{$where}"))
				errorAlert('Менеджер с таким логином уже существует !');
			if($mail && !check_mail($mail))
				errorAlert('Неверный формат E-mail');
				
			$priv = array();
			foreach($_POST['priv'] as $k=>$v)
				$priv[] = $k;
			
			$id = update($tbl,"name='{$name}',login='{$login}',pass='{$pass}',`show`='{$show}',dolgnost='{$dolgnost}',phone='{$phone}',icq='{$icq}',mail='{$mail}',text='{$text}',priv='".implode(',',$priv)."'",$id);
			
			// загружаем картинку для рубрики
			if($_FILES['img']['name'])
			{
				// проверка ширины флэхи
				$imageinfo = getimagesize($_FILES['img']['tmp_name']);
				/*
				Array
				(
					[0] => 560
					[1] => 161
					[2] => 2
					[3] => width="560" height="161"
					[bits] => 8
					[channels] => 3
					[mime] => image/jpeg
				)
				*/				
				// расширение файла
				$fe = getFileExtension(basename($_FILES['img']['name']));
				// проверка
				if($fe!='jpg' && $fe!='gif' && $fe!='png' || $imageinfo[0]!=70 || $imageinfo[1]!=70)
					errorAlert('Нарушение требований к изображению!\n(см. примечание)');
				// загружаем
				@move_uploaded_file($_FILES['img']['tmp_name'],$_SERVER['DOCUMENT_ROOT']."/uploads/managers/{$id}.jpg");
				@chmod($_SERVER['DOCUMENT_ROOT']."/uploads/managers/{$id}.jpg",0644);
			}
			?><script>top.location.href = "<?=$script?>?id=<?=$id?>";</script><?			
		break;
		// ----------------- удаление одной записи
		case "del":
			// разрываем связь с пользователями
			sql("UPDATE {$prx}users WHERE id_manager={$id}");
			// удаляем манагера
			update($tbl,"",$id);
			?><script>top.location.href = "<?=$script?>";</script><?
		break;
		// ----------------- удаление нескольких записей
		case "multidel":
			foreach($_POST['check_del_'] as $k=>$v)
			{
				// разрываем связь с пользователями
				sql("UPDATE {$prx}users WHERE id_manager={$k}");
				// удаляем манагера
				update($tbl,"",$k);
			}
			?><script>top.location.href = "<?=$script?>";</script><?
		break;
		// ----------------- удаление изображения
		case "pic_del":
			@unlink($_SERVER['DOCUMENT_ROOT']."/uploads/managers/{$id}.jpg");
			?><script>top.location.href = "<?=$script?>?red=<?=$id?>";</script><?
		break;

		// ----------------- обновление статуса
		case "show":
			update_flag($tbl,'`show`',$id);
		break;
		
	}
	exit();
}
// ------------------ РЕДАКТИРОВАНИЕ --------------------
elseif(isset($_GET["red"]))
{
	$id = @$_GET['red'] ? (int)@$_GET['red'] : 0;
	
	$rubric .= " &raquo; ".($id ? "Редактирование" : "Добавление");
	$page_title .= " :: ".$rubric;
	
	$row = getRow("SELECT * FROM {$prx}{$tbl} WHERE id={$id}");
	
	ob_start();
	?>
  <form action="?action=save&id=<?=$id?>" enctype="multipart/form-data" method="post" target="ajax">
  <table width="100%" border="0" cellspacing="0" cellpadding="5" class="tab_red">
    <tr>
      <th class="tab_red_th"></th>
      <th>Имя</th>
      <td><?=show_pole('text','name',htmlspecialchars($row['name']))?></td>
    </tr>
    <tr>
      <th class="tab_red_th"><?=help('для входа в админку')?></th>
      <th>Логин</th>
      <td><?=show_pole('text','login',$row['login'])?></td>
    </tr>
    <tr>
      <th class="tab_red_th"><?=help('для входа в админку')?></th>
      <th>Пароль</th>
      <td><?=show_pole('text','pass',$row['pass'])?></td>
    </tr>
    <tr>
      <th class="tab_red_th"></th>
      <th>Должность</th>
      <td><?=show_pole('text','dolgnost',htmlspecialchars($row['dolgnost']))?></td>
    </tr>
    <tr>
      <th class="tab_red_th"></th>
      <th>Телефон</th>
      <td><?=show_pole('text','phone',htmlspecialchars($row['phone']))?></td>
    </tr>
    <tr>
      <th class="tab_red_th"><?=help('номер icq без пробелов и пр. символов<br />- только цифры')?></th>
      <th>ICQ</th>
      <td><?=show_pole('text','icq',$row['icq'])?></td>
    </tr>
    <tr>
      <th class="tab_red_th"></th>
      <th>E-mail</th>
      <td><?=show_pole('text','mail',$row['mail'])?></td>
    </tr>
    <?=show_tr_img(	'img',
										"/uploads/{$tbl}/",
										"{$id}.jpg",
										$script."?action=pic_del&id={$id}",
										'Изображение',
										'<div style="text-align:left">требования:<br />- ширина: 70 пикселей;<br />- высота: 70 пикселей;<br />- поддерживаемые форматы: jpg, gif, png.</div>')?>
    <tr>
     	<th class="tab_red_th"><?=help('текст на странице переписки с клиентом')?></th>
      <th>Описание</th>
      <td><?=showFck("text",$row['text'],"Basic","100%",150);?></td>
    </tr>
    <tr>
    	<th class="tab_red_th"></th>
      <th>Привилегии</th>
      <td>
				<? 
        $mmp = explode(',',$row['priv']);
        $mas_priv = array(	'Страницы'=>'pages.php',
                            'Производители'=>'makers.php',
                            'Товары'=>'goods.php',
                            'КПП каталог'=>'kpp_cat.php',
                            'КПП Товары'=>'kpp_goods.php',
                            'Заказы'=>'orders.php',
														'Запросы'=>'zapros.php',
                            'Новости'=>'news.php',
                            'Статьи'=>'articles.php',
                            'Вопросы-ответы'=>'faq.php',
                            'Сообщения'=>'messages.php',
                            'Счетчики'=>'counters.php',
                            'Пользователи'=>'users.php',
                            'Импорт/Экспорт'=>'import_export.php',
									 'Показывать поставщиков'=>'showmaker'); 
        ?>
				<style>
        .orders th { text-align:left; }
        </style>
        <table class="orders">
        <?
        foreach($mas_priv as $name=>$sc)
        {
          ?><tr><th><?=$name?></th><td><input type="checkbox" name="priv[<?=$sc?>]"<?=in_array($sc,$mmp)?' checked':''?>></td></tr><?
        }
        ?>
        </table>
      </td>
    </tr>
      <tr>
      	<th class="tab_red_th"></th>
        <th>Показывать на сайте</th>
        <td><?=dll(array('0'=>'нет','1'=>'да'),'name="show"', $row['show'])?></td>
      </tr>
    <tr>
      <th class="tab_red_th"></th>
      <th></th>
      <td align="center">
				<input type="submit" value="<?=($id ? "Сохранить" : "Добавить")?>" class="but1" onclick="loader(true)" />&nbsp;
				<input type="button" value="Отмена" class="but1" onclick="location.href='<?=$script?>'" />
			</td>
    </tr>
  </table>
  </form>
  <?
	$content = ob_get_clean();
}
// -----------------ПРОСМОТР-------------------
else
{
	$cur_page = $_SESSION['page'] ? $_SESSION['page'] : 1;
	
	$page_title .= " :: ".$rubric; 
	$rubric .= " &raquo; Общий список"; 
	
	$razdel = array("Добавить"=>"?red=0","Удалить"=>"javascript:multidel(document.red_frm,'check_del_','');");
	$subcontent = show_subcontent($razdel);

	ob_start();
	// проверяем текущую сортировку
	// и формируем соответствующий запрос
	$query = "SELECT * FROM {$prx}{$tbl} ";
	if($_SESSION['sort']) 
	{
		$sort = explode(":",$_SESSION['sort']);
		$cur_pole = $sort[0];
		$cur_sort = $sort[1];

		$query .= "ORDER BY {$cur_pole} ";
		if($cur_sort=="up")
			$query .= "DESC";
		else
			$query .= "ASC ";
	}
	else
		$query .= "ORDER BY name ";
	//-----------------------------
	//echo $query;
	
	show_filters($script);
	
	?>
  <form action="?action=multidel" name="red_frm" method="post" target="ajax">
  <input type="hidden" id="cur_id" value="<?=@$_GET['id']?@(int)$_GET['id']:""?>" />
  <table width="100%" border="1" cellspacing="0" cellpadding="0" class="tab1">
    <tr>
      <th><input type="checkbox" name="check_del" id="check_del" /></th>
      <th>№</th>
      <th width="100%" nowrap><?=ShowSortPole($script,$cur_pole,$cur_sort,'Имя','name')?></th>
      <th nowrap><?=ShowSortPole($script,$cur_pole,$cur_sort,'Должность','dolgnost')?></th>
      <th nowrap><?=ShowSortPole($script,$cur_pole,$cur_sort,'Телефон','phone')?></th>
      <th nowrap><?=ShowSortPole($script,$cur_pole,$cur_sort,'ICQ','icq')?></th>
      <th nowrap><?=ShowSortPole($script,$cur_pole,$cur_sort,'E-mail','mail')?></th>
      <th nowrap>Пользователи</th>
      <th nowrap><?=ShowSortPole($script,$cur_pole,$cur_sort,'На сайте','show')?></th>
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
			<th nowrap><?=$i++?></th>
			<td><a href="?red=<?=$row['id']?>" class="link1"><?=$row['name']?></a></td>
			<td nowrap><?=$row['dolgnost']?></td>
			<td nowrap><?=$row['phone']?></td>
      <td nowrap align="center" style="color:#697079;">
        <img src="http://wwp.icq.com/scripts/online.dll?icq=<?=$row['icq']?>&img=5" width="18" height="18" align="absmiddle">
        <?=$row['icq']?>
      </td>
      <td nowrap><?=$row['mail']?></td>
			<td nowrap align="center">
      	<?
				if($count_users = getField("SELECT count(*) FROM {$prx}users WHERE id_manager={$row['id']}"))
				{
					?><a href="" target="_blank" onClick="RegSessionSort('users.php','fmanager=<?=$row['id']?>');return false;" title='кол-во пользователей данного менеджера' style='color:#090;'><?=$count_users?></a><?
				}
				else
					echo '0';
				?>

      </td>
         <td nowrap align="center"><?=btn_flag($row['show'],$row['id'],'action=show&id=')?></td>
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
      по вашему запросу ничего не найдено.<?=help('нет ни одной записи отвечающей критериям вашего запроса,<br>возможно вы установили неверные фильтры')?>
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