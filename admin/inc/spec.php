<?
// горячая статистика - на самом верху
function show_hot_statistic()
{
	global $prx;
	
	// пользователи
	$str_res = "пользователей онлайн: ".users_online();
	
	$count = getField("select count(*) from {$prx}orders where status='новый'");
	if($count)
	{
		if($str_res) $str_res .= "&nbsp;|&nbsp;";
		ob_start();
		?><a href="" target="ajax" onclick="RegSessionSort('orders.php','filter=remove');return false;"><?=$count?></a>&nbsp;<?
		$str_res .= "новых заказов: ".ob_get_clean();
	}
	
	return $str_res;
}
// главное меню - навигация
function ShowNavigate()
{
	global $prx,$script;
	
	?>
  <div class="nav">
  <table width="100%" border="0" cellspacing="0" cellpadding="3">
  <?
	$mas = getTree("SELECT * FROM {$prx}am WHERE id_parent='%s' ORDER BY sort,id");
	if(sizeof($mas))
	{
		if(isset($_SESSION['manager']))
			$mmp = explode(',',$_SESSION['manager']['priv']);
			
		foreach($mas as $vetka)
		{
			$row = $vetka['row'];
			$level = (string)$vetka["level"];
			
			if($mmp)
				if(!in_array($row['link'],array('log.php','statistics.php','visit.php')))
					if( in_array($row['link'],array('managers.php','settings.php')) || !in_array($row['link'],$mmp) )
						continue;
			
			if($level=='0')
			{
				?>
				<tr>
					<td width="20" align="left"><img src="img/navigate/<?=$row['pic']?>" width="25" height="22"/></td>
					<td><a href="" target="_blank" onclick="RegSessionSort('<?=$row['link']?>','filter=remove');return false;" class="<?=$script==$row['link']?"nav_link2":"nav_link1"?>"><?=$row['name']?></a></td>
				</tr>
				<?
			}
			else
			{
				?>
				<tr>
					<td width="20" align="left"></td>
					<td><a href="" target="_blank" onclick="RegSessionSort('<?=$row['link']?>','filter=remove');return false;" class="<?=$script==$row['link']?"nav_link2":"nav_link1"?>"><?=$row['name']?></a></td>
				</tr>
				<?
			}
		}
	}
	?>
	</table>
	</div>	
	<?
}
// выводим субконтент раздела меню (редактировать, удалить, добавить...)
function show_subcontent($razdel)
{
	ob_start();
	?>
    <table width="100%" cellpadding="5" cellspacing="0" style="background-color:#f4f4f4;"><tr><td valign="middle" align="center">
    	<?
		$str = '';
		foreach($razdel as $k=>$v)
			$str .= ($str?"&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;":"")."<a href=\"{$v}\">{$k}</a>";
		echo $str;
		?>
    </td></tr></table>
    <?
	
	return ob_get_clean();
}
// Страницы навигации
// show_navigate_pages(количество страниц,текущая,'ссылка = ?topic=news&page=')
function show_navigate_pages($x,$p,$link,$all=false)
{
	global $session;
	
	$session = isset($session) ? $session : true;
	
	if($x<2)
		return '';
	
	?>
	<table width="100%" border="0" cellspacing="0" cellpadding="0" class="str_page">
  	<tr>
    <td align="left" valign="middle">&nbsp;
		<?
    if($x<4 || $all)
    {
      for($i=1;$i<=$x;$i++)
      {
        if($i==$p)
          echo "[".$i."]&nbsp;";
        else
          echo get_href($link,$i)."&nbsp;";
      }
    }
	 else
	 {
		 if($x==4)
		 {
			if($p==1) // 1
			  echo "[".$p."]&nbsp;".get_href($link,$p+1)."&nbsp;...&nbsp;".get_href($link,$x);
			if($p==2) // 2
			  echo get_href($link,1)."&nbsp;[".$p."]&nbsp;".get_href($link,$p+1)."&nbsp;...&nbsp;".get_href($link,$x);
			if(($p-1)==2) // 3
			  echo get_href($link,1)."&nbsp;...&nbsp;".get_href($link,$p-1)."&nbsp;[".$p."]&nbsp;".get_href($link,$x);
			if($p==$x) // 4
			  echo get_href($link,1)."&nbsp;...&nbsp;".get_href($link,$x-1)."&nbsp;[".$p."]";
		 }
		 if($x>4)
		 {
			if($p==1) // 1
			  echo "[1]&nbsp;".get_href($link,$p+1)."&nbsp;...&nbsp;".get_href($link,$x);
			elseif($p==2) // 2
			  echo get_href($link,1)."&nbsp;[".$p."]&nbsp;".get_href($link,$p+1)."&nbsp;...&nbsp;".get_href($link,$x);
			elseif(($p-1)==2) // 3
			  echo get_href($link,1)."&nbsp;...&nbsp;".get_href($link,$p-1)."&nbsp;[".$p."]&nbsp;".get_href($link,$p+1)."&nbsp;...&nbsp;".get_href($link,$x);
			elseif(($x-$p)==1) // 4
			  echo get_href($link,1)."&nbsp;...&nbsp;".get_href($link,$p-1)."&nbsp;[".$p."]&nbsp;".get_href($link,$x);
			elseif($p==$x) // 5
			  echo get_href($link,1)."&nbsp;...&nbsp;".get_href($link,$x-1)."&nbsp;[".$p."]";
			else
			  echo get_href($link,1)."&nbsp;...&nbsp;".get_href($link,$p-1)."&nbsp;[".$p."]&nbsp;".get_href($link,$p+1)."&nbsp;...&nbsp;".get_href($link,$x);
		 }
	 }
    ?>
    </td>
    <td align="right" valign="middle">
    <span>перейти к странице&nbsp;</span>
    <?
		if($session)
			$onchange = "RegSessionSort('{$link}','page='+this.value);";
		else
			$onchange = "location.href='".(sprintf($link,"'+this.value+'"))."'";
		?>
    <select onchange="<?=$onchange?>">
    <?
		for($i=1;$i<=$x;$i++)
		{
				?><option value="<?=$i?>" <?=($p==$i?"selected":"")?>><?=$i?></option><?
		}
		?>
    </select>&nbsp;
    </td>
    </tr>
	</table>
  </div>
  <?	
}
function get_href($link,$name)
{	
	global $session;
	
	$session = isset($session) ? $session : true;
	
	ob_start();
	if($session)
	{
		?>
    <a href="" target="_blank" onclick="RegSessionSort('<?=$link?>','page=<?=$name?>');return false;"><?=$name?></a>
		<?
	}
	else
	{
		$link = sprintf($link,$name);
		?>
    <a href="<?=$link?>"><?=$name?></a>
		<?
	}
	
	return ob_get_clean();
}

function btn_flag($flag,$id,$link,$locked=0)
{
	global $script;
	
	if($locked) return;
	
	if($flag)
	{
		?><img class="flag" src="img/green-flag.png" alt="активно" title="заблокировать" width="16" height="16"><?
		?><input type="hidden" value="<?=$script?>" /><?
		?><input type="hidden" value="<?=$link.$id?>" /><?
	}
	else
	{
		?><img class="flag" src="img/red-flag.png" alt="заблокировано" title="активировать" width="16" height="16"><?
		?><input type="hidden" value="<?=$script?>" /><?
		?><input type="hidden" value="<?=$link.$id?>" /><?
	}
}

function btn_edit($id,$locked=0)
{
	ob_start();
	?>
  <a href="?red=<?=$id?>"><img src="img/edit.png" width="16" height="16" alt="редактировать" title="редактировать" /></a>
	<?
	if(!$locked)
	{
		?><a href="javascript:if(confirm('Уверены?')) location.href='?action=del&id=<?=$id?>'" target="ajax"><?
		?><img src="img/del.png" width="16" height="16" alt="удалить" title="удалить" /><?
    ?></a><?
	}
	return ob_get_clean();
}

function btn_sort($id,$param='')
{
	ob_start();
	?>
    <a href="" target="ajax" onclick="toajax('?action=moveup&id=<?=$id?><?=$param?>');return false;"><img src="img/up.png" width="16" height="16" class="alpha_png" alt="вверх" title="вверх" border="0" /></a>
    <a href="" target="ajax" onclick="toajax('?action=movedown&id=<?=$id?><?=$param?>');return false;"><img src="img/down.png" width="16" height="16" class="alpha_png" alt="вниз" title="вниз" border="0" /></a>
    <?
	return ob_get_clean();
}

function update_flag($tab,$pole,$id)
{
	global $prx;
	
	$res = getField("SELECT {$pole} FROM {$prx}{$tab} WHERE id={$id}");
	sql("UPDATE {$prx}{$tab} SET {$pole}=".($res?"0":"1")." WHERE id='{$id}'");
}

function get_criteria($tab)
{
	global $prx;
	
	$mas = array();
	
	$res = mysql_query("SELECT * FROM {$prx}criteria WHERE tab_name='{$tab}'");
	while($row = @mysql_fetch_assoc($res))
		if($row['show_flag'])
			$mas[] = $row['field_name'];
	
	return $mas;
}

function edit_criteria($tab)
{
	global $prx;
	
	$mas = array();
	
	$res = mysql_query("SELECT * FROM {$prx}criteria WHERE tab_name='{$tab}'");
	while($row = @mysql_fetch_assoc($res))
		if($row['show_flag'] && $row['edit_flag'])
			$mas[] = $row['field_name'];
	
	return $mas;
}

function show_tr_img($input_name,$path,$fname,$href,$name='Изображение',$help='',$tr='')
{
	ob_start();
	?>
	<?=$tr?$tr:'<tr>'?>
    <th class="tab_red_th"><?=$help?help($help):''?></th>
    <th><?=$name?></th>
    <td align="left">
      <table border="0" cellspacing="0" cellpadding="0">
        <tr>
          <td style="border:none;"><input type="file" size="30" name="<?=$input_name?>" /></td>
          <td align="left" style="border:none;">
          <?
          if(file_exists($_SERVER['DOCUMENT_ROOT'].$path.$fname))
          {
            ?>
            <table border="0" cellspacing="0" cellpadding="0">
              <tr>
              <td style="padding:0 5px 0 20px; border:none;">
                <a href="<?=$path.$fname?>" class="highslide" onclick="return hs.expand(this)">
                <img src="img/image20x20.png" width="20" height="20" title="показать изображение" />
                </a>
              </td>
              <td style="padding:0 0 0 5px; border:none;">
                <a href="<?=$href?>" target="ajax" style="border:none;">
                <img src="img/del_pic.png" width="20" height="20" title="удалить изображение" />
                </a>
              </td>
              </tr>
            </table>
            <?
          }
          ?>
          </td>
        </tr>
      </table>
    </td>
  </tr>
  <?
	return ob_get_clean();
}

// ------------------------ ФУНКЦИИ СОРТИРОВКИ -----------------------------
// ПЕРЕСОРТИРОВКА
function resort($tab,$where='')
{
	global $prx;
	
	$where = $where ? " WHERE {$where}" : "";
	
	$res = mysql_query("SELECT id FROM {$prx}{$tab} {$where} ORDER BY sort");
	$i=0;
	while($row = @mysql_fetch_assoc($res))
		update($tab,"sort=".(++$i),$row['id']);
}
// В САМЫЙ ВЕРХ
function sort_movetop($tab,$id,$where='')
{
	global $prx;
	
	if(!getField("SELECT id FROM {$prx}{$tab} WHERE id='{$id}'"))
		exit;
	
	// пересортировка
	resort($tab,$where);
	
	$where = $where ? " and {$where}" : "";
	
	// определим новое значение поля sort
	$res = getRow("SELECT id,sort FROM {$prx}{$tab} WHERE 1=1{$where} ORDER BY sort LIMIT 1");
	if($res['id']==$id)
		errorAlert('Выше некуда!');
	else
	{
		$sort = (int)$res['sort']-1;
		update($tab,"sort={$sort}",$id);
	}
}
// В САМЫЙ НИЗ
function sort_movebottom($tab,$id,$where='')
{
	global $prx;
	
	if(!getField("SELECT id FROM {$prx}{$tab} WHERE id='{$id}'"))
		exit;
	
	// пересортировка
	resort($tab,$where);
	
	$where = $where ? " and {$where}" : "";
	
	// определим новое значение поля sort
	$res = getRow("SELECT id,sort FROM {$prx}{$tab} WHERE 1=1{$where} ORDER BY sort DESC LIMIT 1");
	if($res['id']==$id)
		errorAlert('И так уже внизу!');
	else
	{
		$sort = (int)$res['sort']+1;
		update($tab,"sort={$sort}",$id);
	}
}
// НА ОДНУ ПОЗИЦИЮ ВЕРХ
function sort_moveup($tab,$id,$where='')
{
	global $prx;
	
	if(!getField("SELECT id FROM {$prx}{$tab} WHERE id='{$id}'"))
		exit;
	
	// пересортировка
	resort($tab,$where);
	
	$where = $where ? " and {$where}" : "";
	
	// текущая позиция
	$cur_sort = getField("SELECT sort FROM {$prx}{$tab} WHERE id='{$id}'");
	// верхняя позиция
	$res = getRow("SELECT id,sort FROM {$prx}{$tab} WHERE sort<{$cur_sort}{$where} ORDER BY sort DESC LIMIT 1");
	if($res)
	{
		$pre_id = $res['id'];
		$pre_sort = $res['sort'];				
	}
	if($pre_id)
	{
		// меняем позицию предыдущей записи
		update($tab,"sort={$cur_sort}",$pre_id);
		// меняем позицию текущей записи
		update($tab,"sort={$pre_sort}",$id);
	}
	else
		errorAlert('Выше некуда!');
}
// НА ОДНУ ПОЗИЦИЮ ВНИЗ
function sort_movedown($tab,$id,$where='')
{
	global $prx;
	
	if(!getField("SELECT id FROM {$prx}{$tab} WHERE id='{$id}'"))
		exit;
	
	// пересортировка
	resort($tab,$where);
	
	$where = $where ? " and {$where}" : "";
	
	// текущая позиция
	$cur_sort = getField("SELECT sort FROM {$prx}{$tab} WHERE id='{$id}'");
	// нижняя позиция
	$res = getRow("SELECT id,sort FROM {$prx}{$tab} WHERE sort>{$cur_sort}{$where} ORDER BY sort LIMIT 1");
	if($res)
	{	
		$sled_id = $res['id'];
		$sled_sort = $res['sort'];
	}
	if($sled_id)
	{
		// меняем позицию предыдущей записи
		update($tab,"sort={$cur_sort}",$sled_id);
		// меняем позицию текущей записи
		update($tab,"sort={$sled_sort}",$id);
	}
	else
		errorAlert('И так уже внизу!');
}
// ShowSortPole('страница = news.php','Имя столбца = Название','текущая сортировка = up/down/0','имя поля в БД = name');
function ShowSortPole($page,$cur_pole,$cur_sort,$name,$pole)
{
	ob_start();
	
	if(!$cur_pole) // если сессии нет
	{
		?><a href="" target="_blank" onclick="RegSessionSort('<?=$page?>','sort=<?=$pole?>:down');return false;"><?=$name?></a><?
	}
	else
	{
		if($pole==$cur_pole)
		{
			?>
			<a href="" target="_blank" onclick="RegSessionSort('<?=$page?>','sort=<?=$pole?>:<?=($cur_sort=="up"?"down":"up")?>');return false;"><?=$name?></a>
			<img src='img/sort_<?=$cur_sort?>.gif' border='0' width='9' height='9' title='сортировка <?=($cur_sort=="up"?"по убыванию (Я-А)":"по возрастанию (А-Я)")?>' align="absmiddle" />
			<?
		}
		else
		{
			?><a href="" target="_blank" onclick="RegSessionSort('<?=$page?>','sort=<?=$pole?>:down');return false;"><?=$name?></a><?
		}
	}
		
	return ob_get_clean();
}

function ins_div($tek_lvl,$old_lvl,$id_parent)
{
	// текущий уровень больше предыдущего
	if($tek_lvl>$old_lvl)
	{
		?><div id="cat_<?=$id_parent?>" class="ar_block" style="display:none;"><?
	}
	// текущий уровень меньше предыдущего
	if($tek_lvl<$old_lvl)
	{
		$delta = ($old_lvl-$tek_lvl);
		while($delta>0)
		{
			echo "</div>";
			$delta--;
		}
	}
}

function show_pole($type,$name,$value='',$locked=0)
{
	ob_start();
	switch($type)
	{
		case 'text':
			?><input type="<?=$type?>" name="<?=$name?>" value="<?=$value?>" style="width:100%;"<?=($locked?" readonly":"")?> /><?
			break;
		case 'textarea':
			?><textarea name="<?=$name?>" style="width:100%;" rows="3"<?=($locked?" readonly":"")?>><?=$value?></textarea><?
			break;
		case "checkbox":	
			?>
			<input type="hidden" name="<?=$name?>" id="ch_<?=$name?>"  value="<?=$value?>">
			<input type="checkbox" <?=($value=="true" ? "checked" : "")?> onClick="$('#ch_<?=$name?>').val(this.checked);" style="width:auto;"<?=($locked?" readonly":"")?>>
			<?
      break;		
		case "datetime":
		case "date":
			echo aInput($type, "name='{$name}'", $value);	
			break;
		case "color":
			echo aInput("color", "name='{$name}'", $value);	
			break;		
		case "file":
			?>
      <table class="tab_no_borders" border="0" cellspacing="0" cellpadding="0">
      	<tr>
          <td><input type="file" name="<?=$name?>"></td>
          <?
          if(file_exists($_SERVER['DOCUMENT_ROOT']."/uploads/settings/{$name}.jpg"))
          {
						?>
						<td style="padding:0 10px 0 10px;">
              <a href="/uploads/settings/<?=$name?>.jpg" class="highslide" onclick="return hs.expand(this)">
              <img src="/img_spec/20x20/<?=$name?>.jpg" width="20" height="20" style="border:1px solid #333;" />
              </a>
						</td>
						<td>
              <a href="?action=pic_del&id=<?=$name?>" target="ajax">
              <img src="img/del_pic.png" title="удалить текущую картинку" width="20" height="20" style="border:1px solid #333;" class="alpha_png" />
              </a>
						</td>
						<?
          }
          ?>
     		</tr>
      </table>
      <?			
      break;
	}
	return ob_get_clean();
}
function help($text)
{
	ob_start();
	?><a class="help" title="<?=htmlspecialchars($text)?>" href="" onClick="return false"><img src="img/help.png" width="16" height="16" align="absmiddle" /></a><?
	return ob_get_clean();
}
//
function show_letter_navigate($link,$tab,$pole,$where='')
{
	global $prx;
	
	$mas_en = array("A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z");
	$mas_ru = array("А","Б","В","Г","Д","Е","Ё","Ж","З","И","Й","К","Л","М","Н","О","П","Р","С","Т","У","Ф","Х","Ц","Ч","Ш","Щ","Ъ","Ы","Ь","Э","Ю","Я");
	$mas_num = array("0","1","2","3","4","5","6","7","8","9");
	
	$mas_en_in_base = array();
	$mas_ru_in_base = array();
	$mas_num_in_base = array();
	
	$res = mysql_query("SELECT DISTINCT SUBSTRING({$pole},1,1) as symbol from {$prx}{$tab} WHERE 1=1 {$where} ORDER BY {$pole}");
	while($row = @mysql_fetch_assoc($res))
	{
		//$symbol = strto($row['symbol'],'upper');
		$symbol = mb_strtoupper($row['symbol']);
		
		if(in_array($symbol, $mas_en))
			$mas_en_in_base[] = $symbol;
		elseif(in_array($symbol, $mas_ru))
			$mas_ru_in_base[] = $symbol;
		elseif(in_array($symbol, $mas_num))
			$mas_num_in_base[] = $symbol;
	}	
	
	?>
    <table width="100%" align="center" border="0" cellspacing="0" cellpadding="0" style="margin:5px 0 5px 0;">
    
    <tr>
    <td align="center" style="padding:0;"><?
	$i=1;
	$size = sizeof($mas_en);
	foreach($mas_en as $k=>$v)
	{
		if(in_array($v, $mas_en_in_base))
		{
			?><a href="" target="ajax" class="green_link" onclick="RegSessionSort('<?=$link?>','letter=<?=$v?>');return false;"<?=($_SESSION['letter']==$v?" style='color:#ff3300;'":"")?> title="вывести объекты на букву '<?=$v?>'"><?=$v?></a><?=($i!=$size?"&nbsp;":"")?><?
		}
		else
		{
			?><span style="color:#CCC;"><?=$v?></span><?=($i!=$size?"&nbsp;":"")?><?
		}
		$i++;
	}
	?>
    </td>
    </tr>
    
    <tr>
    <td align="center" style="padding:3px 0 3px 0;"><?
	$i=1;
	$size = sizeof($mas_ru);
	foreach($mas_ru as $k=>$v)
	{
		if(in_array($v, $mas_ru_in_base))
		{
			?><a href="" target="ajax" class="green_link" onclick="RegSessionSort('<?=$link?>','letter=<?=$v?>');return false;"<?=($_SESSION['letter']==$v?" style='color:#ff3300;'":"")?> title="вывести объекты на букву '<?=$v?>'"><?=$v?></a><?=($i!=$size?"&nbsp;":"")?><?
		}
		else
		{
			?><span style="color:#CCC;"><?=$v?></span><?=($i!=$size?"&nbsp;":"")?><?
		}
		$i++;
	}
	?>
    </td>
    </tr>
    
    <tr>
    <td align="center" style="padding:0 0 3px 0;"><?
	$i=1;
	$size = sizeof($mas_num);
	foreach($mas_num as $k=>$v)
	{
		if(in_array($v, $mas_num_in_base))
		{
			?><a href="" target="ajax" class="green_link" onclick="RegSessionSort('<?=$link?>','letter=<?=$v?>');return false;"<?=($_SESSION['letter']==$v?" style='color:#ff3300;'":"")?> title="вывести объекты, назване которых начинаются с цифры '<?=$v?>'"><?=$v?></a><?=($i!=$size?"&nbsp;":"")?><?
		}
		else
		{
			?><span style="color:#CCC;"><?=$v?></span><?=($i!=$size?"&nbsp;":"")?><?
		}
		$i++;
	}
	?>
    </td>
    </tr>
    
    </table>
	<?
}
function show_filters($link)
{
	$mas = array();
	
	if($_SESSION['sort'])
		$mas['sort'] = 'сбросить фильтр "сортировка по колонкам"';
	if($_SESSION['cat'])
		$mas['cat'] = 'сбросить фильтр "выбор товаров по рубрике"';
	if($_SESSION['maker'])
		$mas['maker'] = 'сбросить фильтр "выбор товаров по производителю"';
	if($_SESSION['fmanager'])
		$mas['fmanager'] = 'сбросить фильтр "выбор пользователей по менеджеру"';
	if(isset($_SESSION['letter']))
		$mas['letter'] = 'сбросить фильтр "выбор объектов по букве"';
	if(isset($_SESSION['context']))
		$mas['context'] = 'сбросить фильтр "выбор объектов по контекстному поиску"';
	if(isset($_SESSION['cur_user']))
		$mas['cur_user'] = 'сбросить фильтр "выбор заказов по пользователю"';
	
	$size = sizeof($mas);
	if($size>0)
	{
		?>
    <table width="100%" border="0" cellspacing="0" cellpadding="0" style="margin:3px 0 3px 0;">
			<tr>
        <td>
          <?=help('здесь вы можете сбросить фильтры,<br>примененные ранее к текущему списку объектов')?>
        </td>
      </tr>
			<?
  		foreach($mas as $k=>$v)
			{
				?>
        <tr>
          <td align="left"><a href="" onclick="RegSessionSort('<?=$link?>','<?=$k?>=remove');return false;" style="color:#697079;"><?=$v?></a></td>
        </tr>
        <?
			}		
			?>
			<tr>
				<td align="left"><a href="" onclick="RegSessionSort('<?=$link?>','filter=remove');return false;" style="color:#090;">сбросить все фильтры</a></td>
			</tr>
		</table>
		<?
	}
}

function change_user_info($info,$user)
{
	preg_match_all("|ФИО</b>: ([^<]*)|i",$info,$mas);
	if($mas)
	{
		$fio = $mas[1][0];
		ob_start();
		?><a href="users.php?red=<?=$user?>" target="_blank"><?=$fio?></a><?
		$new_fio = ob_get_clean();
		$info = str_replace($fio, $new_fio, $info);
	}
	
	return $info;
}

function get_pic_name($prefix)
{
	global $tbl;
		
	if(!file_exists($_SERVER['DOCUMENT_ROOT']."/uploads/{$tbl}/{$prefix}.jpg"))
		return "{$prefix}.jpg"; 
	
	$num = array();
	$images = getFileFormat($_SERVER['DOCUMENT_ROOT']."/uploads/{$tbl}/{$prefix}_*",true);
	if($images)
	{
		foreach($images as $fname)
		{
			// $fname имеет формат
			// C:/www/sites/s-dom.local/uploads/goods/049489381774_2.jpg
			// нужна лишь 049489381774_2.jpg
			$fname = end(explode('/',$fname));
			preg_match("/^".$prefix."_([0-9]+).jpg$/isU",$fname,$mas);
			if($mas[1])
				$num[] = $mas[1];
		}
	}
	
	$new_fname = '';
	
	$size = sizeof($num);
	if($size)
	{
		asort($num);
		for($i=0; $i<$size; $i++)
		{
			if($num[$i]!=$i+1)
			{
				$new_fname = "{$prefix}_".($i+1).".jpg";
				break;
			}
		}
		
		if(!$new_fname)
		{		
			$n = end($num)+1;
			$new_fname = "{$prefix}_{$n}.jpg";
		}
	}
	else
		$new_fname = "{$prefix}_1.jpg";
	
	return $new_fname;
}

function popup_modul()
{
	ob_start();
	?>    
  <div id="popup_window" style="display:none;">
  <table width="100%" height="100%" border="0" cellspacing="0" cellpadding="0">
    <tr>
      <td height="16" width="100%" style="background-color:#d4dff2; border-bottom:1px solid #FFF;">
          <table width="100%" height="100%" border="0" cellspacing="0" cellpadding="0">
            <tr>
              <td align="center"><span id="popup_window_title" class="window_title"></span></td>
            </tr>
          </table>
      </td>
      <td width="16" style="padding:3px; background-color:#d4dff2; border-bottom:1px solid #FFF;">
          <img src="img/exit.png" border="0" align="absmiddle" style="cursor:pointer;" onclick="hide_popup_window()" />
      </td>
    </tr>
    <tr>
      <td colspan="2" bgcolor="#FFFFFF">
      <div id="popup_loader"><img src="img/loader.gif"></div>
      <iframe id="popup_frame"></iframe>
      </td>
    </tr>
  </table>
  </div>
  <script>$(function(){$.preloadImg("img/popup_loader.gif")})</script>
  <?	
	return ob_get_clean();
}

function stat_around($block)
{
	ob_start();
	?>
    <table width="100%" border="0" cellspacing="0" cellpadding="0" class="tab_subcontent">
      <tr>
        <td width="50" height="50"><img src="img/stat_left_up.jpg" border="0" alt="" /></td>
        <td style="background-image:url(img/stat_up.jpg); background-repeat:repeat-x;"></td>
        <td width="50"><img src="img/stat_right_up.jpg" border="0" alt="" /></td>
      </tr>
      <tr>
        <td style="background-image:url(img/stat_left.jpg); background-repeat:repeat-y;"></td>
        <th style="background-color:#ecf0fb;" valign="top"><?=$block?></th>
        <td style="background-image:url(img/stat_right.jpg); background-repeat:repeat-y;"></td>
      </tr>
      <tr>
        <td height="50"><img src="img/stat_left_down.jpg" border="0" alt="" /></td>
        <td style="background-image:url(img/stat_down.jpg); background-repeat:repeat-x;"></td>
        <td><img src="img/stat_right_down.jpg" border="0" alt="" /></td>
      </tr>
    </table>
    <?
	return ob_get_clean();
}

function show_stat_visit()
{
	global $prx;
	
	// за сегодня
	$date = date("Y-m-d");
	$day = getField("SELECT count(*) FROM {$prx}users_visit WHERE date='{$date}'");
	
	// за вчера
	$date = date("Y-m-d", mktime(0,0,0,date("m"),date("d")-1,date("Y")));
	$yesterday = getField("SELECT count(*) FROM {$prx}users_visit WHERE date='{$date}'");
	
	// за неделю
	$date = date("Y-m-d", mktime(0,0,0,date("m"),date("d")-7,date("Y")));
	$week = getField("SELECT count(*) FROM {$prx}users_visit WHERE date>='{$date}'");
	
	// за месяц
	$date = date("Y-m-d", mktime(0,0,0,date("m")-1,date("d"),date("Y")));
	$month = getField("SELECT count(*) FROM {$prx}users_visit WHERE date>='{$date}'");
	
	// за год
	$date = date("Y-m-d", mktime(0,0,0,date("m"),date("d"),date("Y")-1));
	$year = getField("SELECT count(*) FROM {$prx}users_visit WHERE date>='{$date}'");
	
	// всего
	$all = getField("SELECT count(*) FROM {$prx}users_visit");
	
	ob_start();
	?>
    <table width="100%" border="0" height="100%">
      <tr>
        <th style="color:#697079;font:normal 14px Tahoma, Geneva, sans-serif;" align="left">
          <? $date_start = getField("SELECT MIN(date) FROM {$prx}users_visit"); ?>
          Статистика посещений сайта c <?=date("d.m.Y", $date_start ? strtotime($date_start) : time())?>
        </th>
      </tr>
      <tr>
        <td colspan="2" valign="middle">
        
          <table class="tab_stat" cellpadding="5" style="margin:10px 0 10px 0;">
            <tr>
                <th>Сегодня</th>
                <th>Вчера</th>
                <th>Неделя</th>
                <th>Месяц</th>
                <th>Год</th>
                <th>Всего</th>
            </tr>
            <tr>
                <td style="color:#3e6aaa;"><b><?=(int)$day?></b></td>
                <td><?=(int)$yesterday?></td>
                <td><?=(int)$week?></td>
                <td><?=(int)$month?></td>
                <td><?=(int)$year?></td>
                <td style="color:#3e6aaa;"><b><?=(int)$all?></b></td>
            </tr>
          </table>

        </td>
      </tr>
      <tr>
      	<th style="text-align:right; font-weight:normal;">
        	<a href="" target="ajax" onclick="if(confirm('Вы действительно хотите удалить всю статистику?')) location.href='?action=del';return false;">удалить статистику</a>
        </th>
      </tr>
  	</table>
    <?
	return ob_get_clean();
}

function show_stat_order()
{
	global $prx;
	
	// за сегодня
	$date = date("Y-m-d");
	$day = getField("SELECT count(*) FROM {$prx}orders WHERE DATE_FORMAT(date,'%Y-%m-%d')='{$date}'");
	
	// за вчера
	$date = date("Y-m-d", mktime(0,0,0,date("m"),date("d")-1,date("Y")));
	$yesterday = getField("SELECT count(*) FROM {$prx}orders WHERE DATE_FORMAT(date,'%Y-%m-%d')='{$date}'");
	
	// за неделю
	$date = date("Y-m-d", mktime(0,0,0,date("m"),date("d")-7,date("Y")));
	$week = getField("SELECT count(*) FROM {$prx}orders WHERE DATE_FORMAT(date,'%Y-%m-%d')>='{$date}'");
	
	// за месяц
	$date = date("Y-m-d", mktime(0,0,0,date("m")-1,date("d"),date("Y")));
	$month = getField("SELECT count(*) FROM {$prx}orders WHERE DATE_FORMAT(date,'%Y-%m-%d')>='{$date}'");
	
	// за год
	$date = date("Y-m-d", mktime(0,0,0,date("m"),date("d"),date("Y")-1));
	$year = getField("SELECT count(*) FROM {$prx}orders WHERE DATE_FORMAT(date,'%Y-%m-%d')>='{$date}'");
	
	// всего
	$all = getField("SELECT count(*) FROM {$prx}orders");
	
	ob_start();
	?>
    <table width="100%" border="0">
      <tr>
        <th align="left">
          <? $date_start = getField("SELECT MIN(date) FROM {$prx}orders"); ?>
          <span class="rubric">Статистика заказов c <?=date("d.m.Y", $date_start ? strtotime($date_start) : time())?></span>
        </th>
      </tr>
      <tr>
        <td valign="middle">
        
          <table width="100%" class="tab_stat" cellpadding="5" style="margin:10px 0 0 0;">
            <tr>
                <th>Сегодня</th>
                <th>Вчера</th>
                <th>Неделя</th>
                <th>Месяц</th>
                <th>Год</th>
                <th>Всего</th>
            </tr>
            <tr>
                <td style="color:#3e6aaa;"><b><?=(int)$day?></b></td>
                <td><?=(int)$yesterday?></td>
                <td><?=(int)$week?></td>
                <td><?=(int)$month?></td>
                <td><?=(int)$year?></td>
                <td style="color:#3e6aaa;"><b><?=(int)$all?></b></td>
            </tr>
          </table>

        </td>
      </tr>
  	</table>
    <?
	return ob_get_clean();
}

function show_stat_count()
{
	global $prx;
	
	ob_start();
	?>
    <style type="text/css">
	.stat_count td
	{
		color:#15428b;
		font:normal 12px Tahoma, Geneva, sans-serif;
		padding:5px 0 0 0;
	}
	.comment
	{
		font:normal 12px Tahoma, Geneva, sans-serif;
		color:#697079;
	}
	.stat_count b
	{
		color:#069;
	}
    </style>    
    <table width="100%" border="0" cellspacing="0" cellpadding="0" class="stat_count">
   	  <tr><th align="left" style="padding-bottom:15px;"><span class="rubric">Общая статистика</span></th></tr>
      <tr><td>Кол-во страниц: <b><?=(int)getField("select count(*) from {$prx}pages");?></b> (<span class="comment">заблокированных:</span> <b><?=(int)getField("select count(*) from {$prx}pages where status=0")?></b>)</td></tr>
      <tr><td>Кол-во производителей: <b><?=(int)getField("select count(*) from {$prx}makers");?></b></td></tr>
      <tr><td>Кол-во товаров: <b><?=(int)getField("select count(*) from {$prx}goods");?></b> (<span class="comment">заблокированных:</span> <b><?=(int)getField("select count(*) from {$prx}goods where status=0")?></b>)</td></tr>
      <tr><td>Кол-во заказов: <b><?=(int)getField("select count(*) from {$prx}orders");?></b> (<span class="comment">действующих:</span> <b><?=(int)getField("select count(*) from {$prx}orders where status=1")?></b>; <span class="comment">завершенных:</span> <b><?=(int)getField("select count(*) from {$prx}orders where status=2")?></b>)</td></tr>
      <tr><td>Кол-во новостей: <b><?=(int)getField("select count(*) from {$prx}news");?></b> (<span class="comment">заблокированных:</span> <b><?=(int)getField("select count(*) from {$prx}news where status=0")?></b>)</td></tr>
      <tr><td>Кол-во статей: <b><?=(int)getField("select count(*) from {$prx}articles");?></b> (<span class="comment">заблокированных:</span> <b><?=(int)getField("select count(*) from {$prx}articles where status=0")?></b>)</td></tr>
      <tr><td>Кол-во сообщений: <b><?=(int)getField("select count(*) from {$prx}messages");?></b></td></tr>
      <tr><td>Кол-во менеджеров: <b><?=(int)getField("select count(*) from {$prx}managers");?></b></td></tr>
      <tr><td>Кол-во пользователей: <b><?=(int)getField("select count(*) from {$prx}users");?></b> (<span class="comment">заблокированных:</span> <b><?=(int)getField("select count(*) from {$prx}users where status=0")?></b>)</td></tr>
    </table>
	<?
	return ob_get_clean();
}
?>