<?
require('inc/common.php');

if(isset($_GET['action']))
{
	switch($_GET['action'])
	{
		// -------------------- ИМПОРТ ТОВАРОВ -----------------------
		case 'import':
		
			if(!$_FILES['userfile']['name'])
				errorAlert('выберите файл импорта');
			
			require('../inc/excel.php');
				
			@move_uploaded_file($_FILES['userfile']['tmp_name'],$_SERVER['DOCUMENT_ROOT'].'/tmp/tmp.xls');
			$sheets=@excel_make_sheets($_SERVER['DOCUMENT_ROOT'].'/tmp/tmp.xls');
			
			// создаем файл логов
			if(!$_SESSION['upload_report']) $_SESSION['upload_report'] = 'upload_'.mktime().'.txt';
			$log = fopen($_SERVER['DOCUMENT_ROOT'].'/tmp/'.$_SESSION['upload_report'],'w');
			
			$find_errors = 0;
			$flag = 0;
			$info = array('update'=>0,'insert'=>0,'ignore'=>0);
			
			foreach($sheets as $nom=>$row)
			{
				$cur_errors = 0;
				
				// пропускаем шапку таблицы
				if(!$nom) continue;
				
				$val = array();
				for($k=0; $k<=20; $k++)
					$val[$k] = clean(win2utf($row[$k]),true);
				$id_maker = (int)getField("SELECT id FROM {$prx}makers WHERE code='{$val[0]}' and code<>''");		
				$name = $val[1];	
				$articul = $val[2];
				$analogues = str_replace(' ','',$val[3]);
				
				for($j=1; $j<=10; $j++)
				{
					// 1&#0039;786.00 руб.
					${"price{$j}"} = str_replace(',','.',$val[$j+3]);
					${"price{$j}"} = str_replace(' ','',${"price{$j}"});
					${"price{$j}"} = str_replace("'",'',${"price{$j}"});
					${"price{$j}"} = str_replace('&#0039;','',${"price{$j}"});
										
					${"price{$j}"} = (int)${"price{$j}"};
				}
				$da = $val[14] ? date('Y-m-d',strtotime($val[14])) : '';
				$status = (string)$val[15];
					$status = $status=='0' ? '0' : '1';
						$status = !$price1 ? '0' : $status;
				$title = $val[16];
				$keywords = $val[17];
				$description = $val[18];
				$soput = $val[19];
				
				// проверка обязательных полей
				if(!$id_maker)
				{
					fwrite($log,"сторка ".($nom+1).": не найден производитель в базе, либо код в файле отсутствует\n");
					$find_errors++;
					$cur_errors++;
				}
				if(!$articul)
				{
					fwrite($log,"сторка ".($nom+1).": артикул товара отсутствует\n");
					$find_errors++;
					$cur_errors++;
				}
				if(!$name)
				{
					fwrite($log,"сторка ".($nom+1).": наименование товара отсутствует\n");
					$find_errors++;
					$cur_errors++;
				}
				
				if($cur_errors)
				{
					unset($val);
					unset($good);
					continue;
				}
				
				// ищем товар в базе
				$find_id_good = getField("SELECT id FROM {$prx}goods WHERE articul='{$articul}'");
				
				// обновляем или добавляем
				$query = "id_maker='{$id_maker}',
									name='{$name}',
									soput='{$soput}',
									articul='{$articul}',
									analogues=".($analogues?"'{$analogues}'":"NULL");
				for($j=1; $j<=10; $j++)
				{
					if($j>2 && $j<6)
					{
						if(!${"price{$j}"})
						{
							$koef = set("kz{$j}");
							${"price{$j}"} = $price1*$koef;
						}
					}
					$query .= ",price{$j}='".${"price{$j}"}."'";
				}
				$query .= ",da=".($da?"'{$da}'":"NULL").",
										status='{$status}',
										title=".($title?"'{$title}'":"NULL").",
										keywords=".($keywords?"'{$keywords}'":"NULL").",
										description=".($description?"'{$description}'":"NULL");
				
				$updateLink = false;
				if(!$find_id_good)
				{
					$link = makeUrl($articul.'-'.$name);
					if(getField("SELECT id FROM {$prx}goods WHERE link='{$link}'"))
						$updateLink = true;
					else
						$query .= ",link='{$link}'";
				}
				
				$id_good = update('goods',$query,$find_id_good);
				
				if($id_good)
				{
					if($updateLink)
						update('goods',"link='".($link.'_'.$id_good)."'",$id_good);
					
					if($id_good==$find_id_good)
						$info['update'] = $info['update'] + 1;
					else
						$info['insert'] = $info['insert'] + 1;
					$flag++;
				}
				else
				{
					fwrite($log,"сторка ".($nom+1).": ".mysql_error()."\n");
					$find_errors++;
				}
					
				unset($val);
				unset($good);
			}
			
			fclose($log);
			
			if($flag)
			{
				?>    
				<script>
				alert('Загрузка успешно завершена.\nОбработано строк: <?=$flag?>\nДобавлено товаров: <?=$info['insert']?>\nОбновлено товаров: <?=$info['update']?>\nПропущено товаров: <?=$info['ignore']?>');
				<?
				if($find_errors)
				{
					?>alert('В процессе загрузки данных возникли ошибки.\nПроверьте протокол загрузки.')<?
				}
				?>
				</script>
				<?
			}
			else
			{
				if($find_errors)
				{
					?><script>alert('В процессе загрузки данных возникли ошибки.\nПроверьте протокол загрузки.')</script><?
				}
				else
				{
					?><script>alert('Ни одной строки не обработано.\nВозможно записи в загружаемом файле отсутствуют.')</script><?
				}
			}
			
			if(!$find_errors)
				@unlink($_SERVER['DOCUMENT_ROOT'].'/tmp/'.$_SESSION['upload_report']);
			
			?><script>top.topReload();</script><?
			break;
		
		// -------------------- ИМПОРТ ОСТАТКОВ -----------------------	
		case 'import_ost':
		
			if(!$_FILES['userfile']['name'])
				errorAlert('выберите файл импорта');
				
			// создаем файл логов
			if(!$_SESSION['upload_report_ost']) $_SESSION['upload_report_ost'] = 'upload_'.mktime().'.txt';
			$log = fopen($_SERVER['DOCUMENT_ROOT'].'/tmp/'.$_SESSION['upload_report_ost'],'w');

			// чистим таблицу остатков
			mysql_query("TRUNCATE TABLE {$prx}ost");

			$find_errors = 0;
			$flag = 0;
			
			file_put_contents($_SERVER['DOCUMENT_ROOT'].'/tmp/tmp.xls', win2utf(file_get_contents($_FILES['userfile']['tmp_name'])));
			
			$file = fopen($_SERVER['DOCUMENT_ROOT'].'/tmp/tmp.xls', 'r');
			$start = 1; 
			$i = 0;
			while($row = fgetcsv($file, 1000, ';')) 
				if($i++ >= $start)
				{
					$cur_errors = 0;
					//print_r($row);
					$val = array();
					for($k=0; $k<5; $k++)
						$val[$k] = clean($row[$k],true);
					
					$articul = $val[0];
					$price = str_replace(',','.',$val[1]);
					$price = str_replace(' ','',$price);
					$price = str_replace("'",'',$price);
					$price = str_replace('&#0039;','',$price);
					$price = (int)$price;
					$kol = $val[2];
						$kol = mb_strpos($kol,'+')!==false ? 1 : $kol;
							$kol = (int)$kol;
					$spec = (int)$val[3];
					$maker = $val[4];
									
					if(!$articul) { fwrite($log,"сторка ".($nom+1).": артикул товара отсутствует\n"); $find_errors++; $cur_errors++; }
					if(!$price) { fwrite($log,"сторка ".($nom+1).": цена товара отсутствует\n"); $find_errors++; $cur_errors++; }
					if(!$kol) { fwrite($log,"сторка ".($nom+1).": количество товара отсутствует\n"); $find_errors++; $cur_errors++; }
					
					if($cur_errors)
					{
						unset($val);
						unset($good);
						continue;
					}
					
					// добавляем
					if((int)$articul == $articul && !getField("SELECT id FROM {$prx}goods WHERE articul='{$articul}'"))
					{
						$tmp = getField("SELECT articul FROM {$prx}goods WHERE articul*1='{$articul}'");
						if($tmp)
							$articul = $tmp;
						else
							$articul = '0'.$articul;
					}
					$id = update('ost',"articul='{$articul}',price='{$price}',kol='{$kol}',spec='{$spec}',maker='{$maker}'");
					
					if($id)
						$flag++;
					else
					{
						fwrite($log,"сторка ".($nom+1).": ".mysql_error()."\n");
						$find_errors++;
					}
						
					unset($val);
					unset($good);
				}
			
			fclose($log);
			fclose($file);
			
			if($flag)
			{
				?>    
				<script>
				alert('Загрузка успешно завершена.\nОбработано строк: <?=$flag?>');
				<?
				if($find_errors)
				{
					?>alert('В процессе загрузки данных возникли ошибки.\nПроверьте протокол загрузки.')<?
				}
				?>
				</script>
				<?
			}
			else
			{
				if($find_errors)
				{
					?><script>alert('В процессе загрузки данных возникли ошибки.\nПроверьте протокол загрузки.')</script><?
				}
				else
				{
					?><script>alert('Ни одной строки не обработано.\nВозможно записи в загружаемом файле отсутствуют.')</script><?
				}
			}
			
			if(!$find_errors)
				@unlink($_SERVER['DOCUMENT_ROOT'].'/tmp/'.$_SESSION['upload_report_ost']);
			
			?><script>top.topReload();</script><?
			break;
			
		// -------------------- ЭКСПОРТ -----------------------
		case 'export':
			
			require_once("../inc/Spreadsheet/Excel/Writer.php");
			
			$id_maker = (int)@$_POST['id_maker'];
			
			$maker = $id_maker ? getField("SELECT name FROM {$prx}makers WHERE id={$id_maker}") : 'все';
						
			$x = $y = 0;
			
			// Create workbook
			$xls =& new Spreadsheet_Excel_Writer();
			// Create worksheet
			$sheet =& $xls->addWorksheet(utf2win('Автопарт'));
			
			// ------------- стили
			$infoFormat =& $xls->addFormat();
			$infoFormat->setFontFamily('Calibri');
			$infoFormat->setBold();
			$infoFormat->setSize('12');
			$infoFormat->setColor(30);
			$infoFormat->setAlign('left');
				$headFormat =& $xls->addFormat();
				$headFormat->setFontFamily('Arial');
				$headFormat->setBold();
				$headFormat->setSize('10');
				$headFormat->setColor(9);
				$headFormat->setAlign('center');
				$headFormat->setFgColor(30);
			$textFormat =& $xls->addFormat();
			$textFormat->setFontFamily('Arial');
			$textFormat->setBold();
			$textFormat->setSize('10');
			$textFormat->setColor(8);
			$textFormat->setAlign('left');
			$textFormat->setNumFormat('@');
			// ------------- //стили
			
			// ------------ информация
			$info = utf2win("Экспорт товаров c сайта ".$_SERVER['SERVER_NAME']);
				$sheet->write($y,$x,$info,$infoFormat);
					$y = $y+2;
			$info = utf2win("Производитель: {$maker}");
				$sheet->write($y,$x,$info,$infoFormat);
					$y = $y+2;
			// ------------ // информация
			
			// ------------ шапка таблицы
			$sheet->write($y,$x,utf2win("производитель"),$headFormat); $x++;
			$sheet->write($y,$x,utf2win("наименование"),$headFormat); $x++;
			$sheet->write($y,$x,utf2win("артикул"),$headFormat); $x++;
			$sheet->write($y,$x,utf2win("аналоги"),$headFormat); $x++;
			for($j=1; $j<=11; $j++)
			{
				$sheet->write($y,$x,utf2win("цена {$j}"),$headFormat); $x++;
			}
			$sheet->write($y,$x,utf2win("статус"),$headFormat); $x++;
			$sheet->write($y,$x,utf2win("title"),$headFormat); $x++;
			$sheet->write($y,$x,utf2win("keywords"),$headFormat); $x++;
			$sheet->write($y,$x,utf2win("description"),$headFormat); $x++;
			$y++;
			$x=0;
			// ------------ // шапка таблицы
			
			// ------------ товары
			$query = "SELECT * FROM {$prx}goods ";
			if($id_maker)	$query .= "WHERE id_maker={$id_maker} ";
			$query .= "ORDER BY name";
			
			$res = mysql_query($query);
			while($arr = @mysql_fetch_assoc($res))
			{
				$sheet->write($y,$x,utf2win($arr['id_maker']),$textFormat); $x++;
				$sheet->write($y,$x,utf2win($arr['name']),$textFormat); $x++;
				$sheet->write($y,$x,utf2win($arr['articul']),$textFormat); $x++;
				$sheet->write($y,$x,utf2win($arr['analogues']),$textFormat); $x++;
				for($j=1; $j<=11; $j++)
				{
					${"price{$j}"} = number_format($arr["price{$j}"],2,',',' ');
					$sheet->write($y,$x,utf2win(${"price{$j}"}),$textFormat); $x++;
				}
				$sheet->write($y,$x,utf2win($arr['status']),$textFormat); $x++;
				$sheet->write($y,$x,utf2win($arr['title']),$textFormat); $x++;
				$sheet->write($y,$x,utf2win($arr['keywords']),$textFormat); $x++;
				$sheet->write($y,$x,utf2win($arr['description']),$textFormat); $x++;
				
				$y++;
				$x=0;
			}
			// ------------ //товары
			
			$xls->send("export_".date('d.m.Y').".xls");
			$xls->close();
			
			break;
	}
	
	exit;
}

$show = isset($_GET['show']) ? $_GET['show'] : 'import';

ob_start();

$razdel = array('Импорт товаров'=>'?show=import','Импорт остатков'=>'?show=import_ost','Экспорт'=>'?show=export');
$subcontent = show_subcontent($razdel);

switch($show)
{
	// -------------------- ИМПОРТ ТОВАРОВ -----------------------
	case "import":
		
		$rubric = 'Импорт товаров';
		$page_title .= " :: ".$rubric;
		
		// удаляем предыдущий файл импорта
		@unlink($_SERVER['DOCUMENT_ROOT'].'/tmp/tmp.xls');
		
		/*$price = "1'297.00 руб.";
		$price = str_replace(',','.',$price);
		$price = str_replace(' ','',$price);
		$price = str_replace("'",'',$price);					
		echo number_format($price,2,'.','');
		*/
		
		?>
		<style>
		#import_frm td { font: normal 12px Tahoma; color:#666; padding:5px; }
		#comment { font: normal 12px Tahoma; color:#666; padding:20px 0 0 0; }
		#comment span { color:#090; }
		</style>
    <form id="import_frm" action="?action=import" method="post" enctype="multipart/form-data" target="ajax">
    <table>
      <tr>
        <td><input type="file" name="userfile" size="20"></td>
        <td><input type="submit" value="импорт" class="but1" /></td>
        <td>
					<?
          if($_SESSION['upload_report'] && file_exists($_SERVER['DOCUMENT_ROOT'].'/tmp/'.$_SESSION['upload_report']))
          {
						?><a href="/tmp/<?=$_SESSION['upload_report']?>" target="_blank">протокол</a><?
          }
          ?>
        </td>
      </tr>
    </table>
    </form>
    <div id="comment">
    <span><i>Инструкция:</i></span><br><br>
    а) загрузка/обновление базы товаров осуществляется посредством <span>xls файла</span>, который имеет <span>строгий формат</span> (см. <a href="inc/goods.xls">образец</a>);<br><br>
    б) <span>ключевым</span> параметром при импорте товаров является <span>АРТИКУЛ</span> (по артикулу проверяется есть ли товар в базе или нет);<br><br>
    в) если при импорте у товара имеется <span>СТАРЫЙ АРТИКУЛ</span>, поиск соответствующего товара в базе осуществляется именно по этому артикулу;<br><br>
    г) если товар, находящийся в файле, в базе <span>ОТСУТСТВУЕТ</span> &mdash; создаётся новый товар с новыми параметрами, взятыми из файла;<br><br>
    </div>
		<?		
		break;
	
	// -------------------- ИМПОРТ ОСТАТКОВ -----------------------
	case "import_ost":
		
		$rubric = 'Импорт остатков';
		$page_title .= " :: ".$rubric;
		
		// удаляем предыдущий файл импорта
		@unlink($_SERVER['DOCUMENT_ROOT'].'/tmp/tmp.xls');
				
		?>
		<style>
		#import_frm td { font: normal 12px Tahoma; color:#666; padding:5px; }
		#comment { font: normal 12px Tahoma; color:#666; padding:20px 0 0 0; }
		#comment span { color:#090; }
    </style>
		<form id="import_frm" action="?action=import_ost" method="post" enctype="multipart/form-data" target="ajax">
		<table>
			<tr>
				<td><input type="file" name="userfile" size="20"></td>
				<td><input type="submit" value="импорт" class="but1" /></td>
				<td>
					<?
          if($_SESSION['upload_report_ost'] && file_exists($_SERVER['DOCUMENT_ROOT'].'/tmp/'.$_SESSION['upload_report_ost']))
          {
						?><a href="/tmp/<?=$_SESSION['upload_report_ost']?>" target="_blank">протокол</a><?
          }
          ?>
				</td>
			</tr>
		</table>
		</form>
		<div id="comment">
		<span><i>Инструкция:</i></span><br><br>
		а) обновление остатков осуществляется посредством <span>csv файла</span>, который имеет <span>строгий формат</span> (см. <a href="inc/remains.csv">образец</a>);<br><br>
		б) <span>ключевым</span> параметром при импорте товаров является <span>АРТИКУЛ</span> (по артикулу проверяется есть ли товар в базе или нет);<br><br>
		в) если при импорте у товара имеется <span>СТАРЫЙ АРТИКУЛ</span>, поиск соответствующего товара в базе осуществляется именно по этому артикулу;<br><br>
		г) если товар, находящийся в файле, в базе <span>ОТСУТСТВУЕТ</span> &mdash; этот товар игнорируется;<br><br>
		</div>
		<?		
		break;
		
	// -------------------- ЭКСПОРТ -----------------------
	case "export":
		
		$rubric = "Экспорт";
		$page_title .= " :: ".$rubric;
		
		?>
        <form action="?action=export" method="post" enctype="multipart/form-data" target="ajax">
        <fieldset style="margin:10px 0 0 0">
        <legend>Параметры</legend>
        <table class="filter_tab" style="margin:5px 0 0 0;">
          <tr>
            <td align="left">Производитель</td>
            <td><?=dll("SELECT id,name FROM {$prx}makers ORDER BY name",'name="id_maker"','',array('','-- все --'))?></td>
          </tr>
          <tr>
            <td colspan="2" align="right">
            	<input type="submit" value="экспорт" class="but1" />
            </td>
          </tr>
        </table>            
        </fieldset>
        </form>
        <?
		
		break;
}

$content = $subcontent.ob_get_clean();

require("tpl/tpl.php");
?>