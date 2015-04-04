$(function(){
	$('#show_user_info').click(function(){
		block = $(this).next('div');
		if(!block.is(':visible'))
			block.slideDown(400);
		else
		{
			if($.browser.msie)
				block.animate({ height: 0 }, 200, "linear", function(){ block.hide() });
			else
				block.slideUp(200);
		}
		
		return false;
	});
	
	$tab = $('#tbl_order');
	
	$tab.find('a.del_good').live('click',function(){
		$(this).parents('tr:first').remove();
		var i=1;
		$tab.find('.num').each(function(){ $(this).html(i++) });
		return false;
	});
	
	$tab.find('a#add_goods').click(function(){
		show_popup_window('Добавление товаров к заказу','inc/add_goods.php');
		return false;
	});
});