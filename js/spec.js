$(function(){
	//
	$('#logo').click(function(){ location.href = '/' });
	//
	$('a[href^="http://"]').each(function(){ $(this).attr('target','_blank') });
	//
	$('.back').click(function(){ history.back(); return false; });
	//
	$.preloadImg('/img/pop_l.png','/img/pop_r.png','/img/pop_u.png','/img/pop_b.png');
	//
	$(document).pngFix();
	//
	$(document).jAlert();
});

function toCart(id,kol)
{
	$.ajax({
		type: "GET",
		url: "/cart.php",
		data: "action=tocart&id="+id+"&kol="+(kol*1<1?1:kol),
		success: function(data){
			data = str_replace('<script>','',data);
			data = str_replace('</script>','',data);
			eval(data);
		}
	});
}