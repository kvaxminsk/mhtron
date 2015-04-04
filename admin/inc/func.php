<?
function remove_good($id,$just_image=false,$fname='')
{
	global $prx,$tbl;
	
	// мочим конкретную картинку
	if($just_image)	
	{
		if($fname)
		{
			// мочим большую картинку
			@unlink($_SERVER['DOCUMENT_ROOT']."/uploads/{$tbl}/{$fname}");
			// мочим уменьшенные копии
			$mas_dir = get_dir_list($_SERVER['DOCUMENT_ROOT']."/uploads/{$tbl}/");
			if(sizeof($mas_dir))
				foreach($mas_dir as $dir)
					@unlink($dir.$fname);
		}
		
		return;
	}
	// мочим все картинки
	else
	{
		$articul = gtv($tbl,'articul',$id);

		$fname = "{$articul}.jpg";
		
		// мочим большую картинку
		@unlink($_SERVER['DOCUMENT_ROOT']."/uploads/{$tbl}/{$fname}");
		// мочим уменьшенные копии
		$mas_dir = get_dir_list($_SERVER['DOCUMENT_ROOT']."/uploads/{$tbl}/");
		if(sizeof($mas_dir))
			foreach($mas_dir as $dir)
				@unlink($dir.$fname);	
	}
		
	// удаление товара
	update($tbl,'',$id);
}


function remove_kpp_good($id,$just_image=false,$fname='')
{
	global $prx,$tbl;
	
	// мочим конкретную картинку
	if($just_image)	
	{
		if($fname)
		{
			// мочим большую картинку
			@unlink($_SERVER['DOCUMENT_ROOT']."/uploads/{$tbl}/{$fname}");
			// мочим уменьшенные копии
			$mas_dir = get_dir_list($_SERVER['DOCUMENT_ROOT']."/uploads/{$tbl}/");
			if(sizeof($mas_dir))
				foreach($mas_dir as $dir)
					@unlink($dir.$fname);
		}
		
		return;
	}
	// мочим все картинки
	else
	{
		$fname = "{$id}.jpg";
		
		// мочим большую картинку
		@unlink($_SERVER['DOCUMENT_ROOT']."/uploads/{$tbl}/{$fname}");
		// мочим уменьшенные копии
		$mas_dir = get_dir_list($_SERVER['DOCUMENT_ROOT']."/uploads/{$tbl}/");
		if(sizeof($mas_dir))
			foreach($mas_dir as $dir)
				@unlink($dir.$fname);	
	}
		
	// удаление товара
	update($tbl,'',$id);
}

?>