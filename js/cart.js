$(function(){
	//
	chkol_func = function(){
		if(!$('#btn_calc').is(':visible'))
			$('#btn_order').fadeOut("fast",function(){ $('#btn_calc').fadeIn("fast") });
	}
	$('.chkol').each(function(){
		mval = $(this).find('input').attr('maxval');
		$(this).Chkol({maxlen:mval.length,maxval:mval,func:chkol_func,color:true});
	});
	$('.pole2').numer();
	$('.pole2').keypress(function(){
		if(!$('#btn_calc').is(':visible'))
			$('#btn_order').fadeOut("fast",function(){ $('#btn_calc').fadeIn("fast") });
	});
	//
	$('.del').click(function(){
		toajax('/cart.php?action=del&id='+$(this).next('input').val());
	});
	//
	$('#btn_calc').click(function(){
		$('#order_frm').attr('action','/cart.php?action=calc');
		$('#order_frm').submit();
	});
	$('#btn_order').click(function(){ 
		$(this).fadeOut("fast");
		$('#order_frm').attr('action','/cart.php?action=send');
		$('#order_frm').submit();
	});
})