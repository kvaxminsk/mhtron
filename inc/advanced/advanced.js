$(function(){
	var isIE = $.browser.msie;
	
	/* ---------------------- arrnum ---------------------------- */
	if(isIE)
		$('.arrnum').css("padding","0 3px 2px 3px");
	else
		$('.arrnum').css("padding","0 3px 1px 3px");
		
	$(".arrnum input").keypress(function(e){
		if(e.which!=8 && e.which!=0 && $(this).attr('value').length>2)
			return false;
		if(e.which!=8 && e.which!=0 && (e.which<48 || e.which>57))
			return false; 
			
		disOform();   
	});
			
	$.preloadImg('/img/str_up_hover.gif','/img/str_down_hover.gif');
	$('.str_up').hover(
		function(){
			$(this).css("background","url(/img/str_up_hover.gif) no-repeat");
		},
		function(){
			$(this).css("background","url(/img/str_up.gif) no-repeat");
		}
	);
	$('.str_down').hover(
		function(){
			$(this).css("background","url(/img/str_down_hover.gif) no-repeat");
		},
		function(){
			$(this).css("background","url(/img/str_down.gif) no-repeat");
		}
	);
	
	$('.str_up,.str_down').click(function(){
		var i=0;
		var parentEls = $(this).parents().map(function(){
			if(++i>4) 
				return false;
			else
				return this;
		}).get();
		var tab = parentEls[3];
		var input = $(tab).find('input');
		var cur_val = $(input).val();
		if(!cur_val || isNaN(cur_val))
			$(input).val(1);
		
		cur_val = parseInt($(input).val());
		
		if($(this).hasClass('str_up'))
		{
			if(cur_val<99)
			{
				$(input).val(cur_val+1);
				disOform();
			}
		}
		else
		{
			if(cur_val>1)
			{
				$(input).val(cur_val-1);
				disOform();
			}
		}
	});
	
	/* -------------------------- / arrnum ---------------------------- */
});