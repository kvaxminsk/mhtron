jQuery(function(){
	$block = $('.fblock');
	$block.find('.add .i2 a').live('click',function(){
		
		n = $block.find('.add').size();
		
		// убираем у предыдущего поля "ещё"
		$last = $block.find('.add').eq(n-1);
		$last.find('.i2').remove();
		
		data  = '<div class="add">';
		data += '	<div class="i1"><input type="file" name="files[]"></div>';
		if(n<4)
			data += '	<div class="i2"><a href="" title="добавить">ещё</a></div>';
		data += '</div>';
		$last.after(data);

		return false;
	});
	
	$frm = $('#frm');
	$ids_managers = $('#ids_managers');
	$ids_users = $('#ids_users');
	
	$('#add_managers').click(function(){
		var ids = '';
		$ids_managers.find('option').each(function(){
			ids += (ids?',':'')+$(this).val();
		});
		show_popup_window('Выбор менеджеров','inc/add_managers.php?list=ids_managers&ids='+ids);
	});
	$('#add_users').click(function(){
		var ids = '';
		$ids_users.find('option').each(function(){
			ids += (ids?',':'')+$(this).val();
		});
		show_popup_window('Выбор пользователей','inc/add_users.php?list=ids_users&ids='+ids);
	});
	$frm.submit(function(){
		$ids_managers.add($ids_users).find('option').attr('selected',true);
	});
});