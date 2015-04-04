$(function(){
	// выделение сохраненного объекта
	mark_change_tr();
	// полосатая таблица
	$('.tab1').zebra();
	//
	$('#check_del').click(function(){
		$("input[id^='check_del_']").not(this).attr('checked',$(this).attr('checked'));
	});
	$('.check_all').click(function(){
		var checked = $(this).attr('checked');
		var index_column = $(this).parents('td:first').index();
		if(!index_column)
			index_column = $(this).parents('th:first').index();
		var $tab = $(this).parents('table:first');
		$tab.find('tr').slice(1).each(function(){
			$(this).find('th,td').eq(index_column).find('input[type=checkbox]').attr('checked',checked);
		});
	});
	// календарь
	if($('.datepicker').size())
	{
		$.datepicker.setDefaults($.datepicker.regional['ru']);
		$('.datepicker').css({'width':'80px','text-align':'center'});
		$('.datepicker').datepicker();
	}
	// флажки
	$.preloadImg('img/loader.gif');
	$('.flag').click(function(){
		loader(true);
		
		var $obj = $(this);
		var new_src,new_alt,new_title;
		
		if(strpos($obj.attr('src'),'red'))
		{
			new_src = 'img/green-flag.png';
			new_alt = 'активно';
			new_title = 'заблокировать';
		}
		else
		{
			new_src = 'img/red-flag.png';
			new_alt = 'заблокировано';
			new_title = 'активировать';
		}
		
		var $script = $obj.next('input');
			var _script = $script.val();
		var _link = $script.next('input').val();
		
		$.ajax({
			type: "GET",
			url: _script,
			data: _link,
			success: function(data){
				if(data)
				{
					if(data=='reload')
						top.topReload();
					else
					alert(data);
				}
				else
				{					
					$obj.attr({'src':new_src,'alt':new_alt,'title':new_title});
					$obj.parents('tr:first').find('td').effect("highlight", {}, 1000);
				}
				loader(false);		
			}
		});
	});
});