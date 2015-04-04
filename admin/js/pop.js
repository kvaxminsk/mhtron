function to_list(list)
{
	$('input:checked').each(function(){
		var id = $(this).attr('id');
		var name = $(this).val();
		
		var find_flag=0;
		list.find('option').each(function(){
			if($(this).val()==id)
			{
				find_flag++;
				return false;
			}
		});
		
		if(!find_flag)
			list.append('<option value="'+id+'">'+name+'</option>');
	});
	
	top.hide_popup_window();
}

function to_tab(id_order)
{
	$tab = top.$('#tbl_order');
	
	$('input:checked').each(function(){
		var id = this.id;
		var name = $(this).val();
		
		// проверка наличия товара в заказе
		var find_flag=0;
		$tab.find('input[name=gid]').each(function(){
			if($(this).val()==id)
			{
				find_flag++;
				return false;
			}
		});
		
		if(!find_flag)
		{
			$.ajax({
				type: "GET",
				url: "/admin/orders.php",
				data: "action=add_good&id_order="+id_order+"&id="+id,
				success: function(data){
					data = str_replace('<script>','',data);
					data = str_replace('</script>','',data);
					eval(data);
				}
			});
		}
		else
			alert('товар "'+name+'" уже есть в заказе!');
	});
}

$(function(){
	$('#tab').zebra();
	
	$('input[value=сохранить]').click(function(){
		id_order = top.$('input#id_order').val();
		if(id_order)
			to_tab(id_order);
		else
			to_list(top.$('#'+$('#list').val()));
	});
	
	$('input[value=отмена]').click(function(){
		top.hide_popup_window();
	});
});