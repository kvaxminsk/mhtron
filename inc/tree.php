<?
// ПОЛУЧЕНИЕ ДЕРЕВА
// $query = "SELECT * FROM {$prx}{$tbl} WHERE id_parent='%s'", ветка с которой начинаем стоить дерево, "глубина" дерева, текущая глубина - не задается
function getTree($query, $id_parent=0, $depth=0, $level=0)
{
	if(!$depth || $depth>$level)
	{
		$sql = sprintf($query,$id_parent);
		$res = sql($sql);
		while($row = mysql_fetch_assoc($res)) 
		{
			$tree[] = array("level" => $level, "row" =>  $row);
			$tree = array_merge($tree, (array)getTree($query, $row['id'], $depth, $level+1));
		}
	}
	return (array)@$tree;
}
// префикс относительно уровня вложенности дерева
function getPrefix($level=0, $prefix="&raquo;&nbsp;") 
{
	$prefix = str_repeat("&mdash;&nbsp;",$level).$prefix;
	return $prefix;
}
// ВЫПАДАЮЩИЙ СПИСОК ДЛЯ ДЕРЕВА
// $sql = "SELECT * FROM {$prx}{$tbl} WHERE id_parent='%s'", 
// св-ва списка, 
// значение, 
// "пустое" значение(может быть массивом),  
// значение скрываемой рубрики (и ее подрубрик), 
// id начала веток, 
// глубина дерева, 
// свой префикс
function dllTree($sql, $properties, $value="", $default=NULL, $hidevalue="", $id_parent=0, $depth=0, $prefix=NULL)
{ 
	ob_start();
	?>
	<select <?=$properties?>>
	<?	
	if(!is_null($default))
	{
		if(is_array($default)) 
		{	
			?><option value="<?=$default[0]?>"><?=$default[1]?></option><?	
		} 
		else 
		{ 
			?><option value=""><?=$default?></option><?
        }
	}
	if($tree = getTree($sql, $id_parent, $depth))
	{
		foreach ($tree as $vetka) 
		{
			$row =  $vetka["row"];
			$level = $vetka["level"];
				
			// не выводим скрываемую рубрику и ее подрубрики
			if($row['id'] == $hidevalue)
			{
				$hide_pages_level = $level;
				continue;
			}
			if(isset($hide_pages_level) && $hide_pages_level < $level)
				continue;
			else
				unset($hide_pages_level);
			
			$prx = $prefix===NULL ? getPrefix($level) : str_repeat($prefix, $level);
			
			?><option value="<?=$row['id']?>"<?=($row['id']==$value ? " selected" : "")?>><?=$prx.$row["name"]?></option><?
        }
	}
	?>				
	</select>
	<? 	
	return ob_get_clean();
}
// КОЛИЧЕСТВО ВХОДЯЩИХ В РУБРИКУ ТОВАРОВ
function get_count_parent_goods($cat_id) 
{
	global $prx, $count_parent_goods;
	
	$count_parent_goods += (int)getField("SELECT COUNT(*) FROM {$prx}goods WHERE id_cat={$cat_id}");
	
	$res = mysql_query("SELECT id FROM {$prx}cat WHERE id_parent={$cat_id}");

	while($row = @mysql_fetch_array($res))
		get_count_parent_goods($row['id']);
}
// массив родительских рубрик
function getArrParents($sql, $id, $parent_fill="id_parent") // $sql = "SELECT id,id_parent FROM {$prx}{$tbl} WHERE id='%s'"
{
    do
    {
        $row = getRow(sprintf($sql, $id));
        $tree[] = $row['id'];
        $id = $row[$parent_fill];
    }
    while($id);

    return (array)array_reverse($tree);
}
// массив подчинённых рубрик
function get_chaild_ids($tab,$id_parent)
{
	global $prx,$mas_chaild;
	
	$res = mysql_query("SELECT id FROM {$prx}{$tab} WHERE id_parent={$id_parent} ORDER BY sort");
	while($row = @mysql_fetch_array($res)) 
	{
		$id = $row['id'];
		$mas_chaild[] = $id;
		get_chaild_ids($tab,$id);
	}
	
	return (array)$mas_chaild;
}
// КОЛ-ВО ПОДЧИНЕННЫХ ЭЛЕМЕНТОВ В ДЕРЕВЕ
function find_chaild($id) 
{
	global $prx;
	
	return (int)getField("SELECT count(*) as kol FROM {$prx}cat WHERE id_parent={$id}");
}
// МАКС УРОВЕНЬ ВЛОЖЕННОСТИ
function get_max_level_tree() 
{
	global $prx;
	
	$max_level = 1;
	$mas = getTree("SELECT id,id_parent,name FROM {$prx}cat WHERE id_parent='%s' ORDER BY sort");
	$mas_size = sizeof($mas);
	if($mas_size>0)
	{
		foreach($mas as $vetka)
		{
			if($vetka['level']>$max_level)
				$max_level = $vetka['level'];			
		}
	}
	
	return $max_level;	
}
?>