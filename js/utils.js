var nav = userNavigator();

// ПЕРЕДАЕМ URL ВО ФРЕЙМ
function toajax(url)
{
	$('#ajax').attr('src',url);
}

function loader(show)
{
	var $loader = $('#loader');
	if($loader.size())
	{
		if(show)
		{
			var bs = BodySize();
			$loader.css({
				'top'	: Math.round( (bs.height/2)-($loader.height()/2)+$('body').scrollTop() ),
				'left': Math.round( (bs.width/2)-($loader.width()/2) )
			});
			$loader.show();
		}
		else
			$loader.hide();
	}
}

// ПЕРЕЗАГРУЗИТЬ СТРАНИЦУ ПОСЛЕ РАБОТЫ ФРЕЙМА
function topReload()
{
	//top.location.href = top.location.href;	return;
	if($.browser.webkit || $.browser.opera)
		history.go(0);
	else if($.browser.mozilla)	{
		history.back();
		setTimeout("top.location.reload(true)",500);
	} else {
		history.back();
		history.go(0);
	}
}
// ВЫЗОВ ФУНКЦИИ history.back() ПОСЛЕ РАБОТЫ ФРЕЙМА
function topBack(post) // post - страница дергалась формой (иначе - ссылкой)
{
	showLoad(false);
	switch(userNavigator())
	{
		case "isChrome":
			if(post)
				history.back();
			break;
		
		default:
			history.back();
			break;
	}
}

// ОПРЕДЕЛЕНИЕ ТИПА БРАУЗЕРА
function userNavigator()
{
	// Получим userAgent браузера и переведем его в нижний регистр 
	var ua = navigator.userAgent.toLowerCase(); 
	// Определим Internet Explorer 
	if( (ua.indexOf("msie") != -1 && ua.indexOf("opera") == -1 && ua.indexOf("webtv") == -1) )
		return "isIE";
	// Opera 
	if( (ua.indexOf("opera") != -1) )
		return "isOpera";
	// Chrome
	if( (ua.indexOf("chrome") != -1) ) 
		return "isChrome";
	// Gecko = Mozilla + Firefox + Netscape 
	if( (ua.indexOf("gecko") != -1) ) 
		return "isGecko";
	// Safari, используется в MAC OS 
	if( (ua.indexOf("safari") != -1) ) 
		return "isSafari";
	// Konqueror, используется в UNIX-системах 
	if( (ua.indexOf("konqueror") != -1) ) 
		return "isKonqueror";

	return false;
}

// ПРЕДВАРИТЕЛЬНАЯ ЗАГРУЗКА КАРТИНОК
// в аргументы передаются пути к картинкам
$.preloadImg = function(){
	for(var i=0; i<arguments.length; i++)
		$("<img>").attr("src", arguments[i]);
};

// ФОРМАТИРУЕТ ВЫВОД ЧИСЛА, АНАЛОГ number_format() В PHP
function number_format(number, decimals, dec_point, thousands_sep) 
{
	var n = number, prec = decimals, dec = dec_point, sep = thousands_sep;
	n = !isFinite(+n) ? 0 : +n;
	prec = !isFinite(+prec) ? 0 : Math.abs(prec);
	sep = sep == undefined ? ',' : sep;
	
	var s = n.toFixed(prec), abs = Math.abs(n).toFixed(prec), _, i;
	if (abs > 1000) {
		_ = abs.split(/\D/);
		i = _[0].length % 3 || 3;
		_[0] = s.slice(0,i + (n < 0)) + _[0].slice(i).replace(/(\d{3})/g, sep+'$1');
		s = _.join(dec || '.');
	}
	return s;
}

function end(array) 
{
	var last_elm, key;

	if (array.constructor === Array){
		last_elm = array[(array.length-1)];
	} else {
		for (key in array){
			last_elm = array[key];
		}
	}
	return last_elm;
}

// strpos('Kevin van Zonneveld', 'e', 5); -> 14
function strpos(haystack,needle,offset)
{
	var i = haystack.indexOf(needle,offset); // returns -1
	return i >= 0 ? i : false;
}
function strrev(string)
{
	var ret = '', i = 0;
	for(i=string.length-1; i>=0; i--)
	{
	   ret += string.charAt(i);
	}
	return ret;
}
function str_replace ( search, replace, subject )
{
	if(!(replace instanceof Array)){
		replace=new Array(replace);
		if(search instanceof Array){
			while(search.length>replace.length){
				replace[replace.length]=replace[0];
			}
		}
	}

	if(!(search instanceof Array))search=new Array(search);
	while(search.length>replace.length){
		replace[replace.length]='';
	}

	if(subject instanceof Array){
		for(k in subject){
			subject[k]=str_replace(search,replace,subject[k]);
		}
		return subject;
	}

	for(var k=0; k<search.length; k++){
		var i = subject.indexOf(search[k]);
		while(i>-1){
			subject = subject.replace(search[k], replace[k]);
			i = subject.indexOf(search[k],i);
		}
	}

	return subject;
}

// Размеры клиентской части окна браузера
function screenSize() 
{
	var w = (window.innerWidth ? window.innerWidth : (document.documentElement.clientWidth ? document.documentElement.clientWidth : document.body.offsetWidth));
	var h = (window.innerHeight ? window.innerHeight : (document.documentElement.clientHeight ? document.documentElement.clientHeight : document.body.offsetHeight));
	return {w:w, h:h};
}

function mousePageXY(e)
{
	var x = 0, y = 0;
	if (!e) e = window.event;
	if (e.pageX || e.pageY)
	{
		x = e.pageX;
		y = e.pageY;
	}
	else if (e.clientX || e.clientY)
	{
		x = e.clientX + (document.documentElement.scrollLeft || document.body.scrollLeft) - document.documentElement.clientLeft;
		y = e.clientY + (document.documentElement.scrollTop || document.body.scrollTop) - document.documentElement.clientTop;
	}
	
	return {"x":x, "y":y};
}

// ОПРЕДЕЛЕНИЕ КООРДИНАТ ЭЛЕМЕНТА
function absPosition(obj) 
{ 
	var x = y = 0;
	while(obj) 
	{
		x += obj.offsetLeft;
		y += obj.offsetTop;
		obj = obj.offsetParent;
	}
	return {x:x, y:y};
	// Пример:
	// "x = " + absPosition(obj).x;
	// "y = " + absPosition(obj).y;
}

function getElementPosition(obj)
{
    var w = obj.offsetWidth;
    var h = obj.offsetHeight;
	
    var l = 0;
    var t = 0;
	
    while (obj)
    {
        l += obj.offsetLeft;
        t += obj.offsetTop;
        obj = obj.offsetParent;
    }

    return {"left":l, "top":t, "width": w, "height":h};
}

function BodySize()
{
	return {"width":$('body').width(), "height":$('body').height()};
}

function add_photo()
{
	var sch = $('#sch_photo').val()*1 + 1;
	var tab = $('#tab_add_photo').get(0);
	
	var _tr = tab.insertRow(-1);
	_tr.id = 'tr_photo'+sch;
	
    var cell_1 = top.document.createElement('TD');
	var cell_2 = top.document.createElement('TH');
	
	// убираем + - у предыдущей строки
	var pred_tr = $('#tr_photo'+(sch-1)).get(0);
	if(pred_tr)
	{
		_th = pred_tr.getElementsByTagName('TH');
		_th[0].innerHTML = '';
	}	
	
	// поле файл 
	_tr.appendChild(cell_1);
    cell_1.innerHTML = '<input type="file" name="user_photo['+sch+']" />';
	// + -	
	_tr.appendChild(cell_2);
	cell_2.innerHTML = '<a href="" target="ajax" class="link2" onclick="add_photo();return false;">ещё</a>';
	
	// увеличиваем счетчик
	$('#sch_photo').val(sch);
	
	// убираем + у 5-й строки
	if(sch+1==4) 
	{
		var cur_tr = $('#tr_photo'+sch).get(0);
		if(cur_tr)
		{
			_th = cur_tr.getElementsByTagName('TH');
			_th[0].innerHTML = '';
		}
	}
}

function clear_select(obj,flag)
{
	while(obj.options.length) 
		obj.options[0] = null;
	
	if(flag)
		obj.options[0] = new Option('', '');
}

function rgb2hex(r, g, b) 
{
	return (((r & 255) << 16) + ((g & 255) << 8) + b).toString(16);
}

function hex2rgb(hex) 
{
	return (function (v) {
		return [v >> 16 & 255, v >> 8 & 255, v & 255];
	})(parseInt(hex, 16));
}

jQuery.fn.input_fb = function(settings){
	
	// Settings
	settings = $.extend({
		text : 'Ваш текст',
		color_focus : '#007fff',
		color_blur : '#ff0000'
	}, settings);
	
	$(this).focus(function(){
		if($(this).val()==settings.text)
		{
			$(this).val('');
			$(this).css('color',settings.color_focus);
		}
	});
	$(this).blur(function(){
		if($(this).val()=='')
		{
			$(this).css('color',settings.color_blur);
			$(this).val(settings.text);
		}
	});
	
	return jQuery;
}

jQuery.fn.numer = function(settings){
	
	// Settings
	settings = $.extend({
		nul : true
	}, settings);
	
	$(this).keypress(function(e){
		if(e.which!=8 && e.which!=0 && (e.which<48 || e.which>57))
			return false;    
	});
	
	$(this).change(function(){
		var value = $(this).val();
		if(!settings.nul)
		{
			if(value*1<1)
				$(this).val('1');
			$(this).val(value.replace(/^[0]+/,''));					
		}
		else
		{
			if(value=='') $(this).val('0');
		}
	});
	
	return jQuery;
}

jQuery.fn.Chkol = function(prm){
	
	// Settings
	prm = $.extend({
		maxlen : 2,
		maxval : 99,
		zero: false,
		func : false,
		color : false,
		color_error : '#ff6666',
		color_default : '#fff'
	}, prm);
	//alert(prm.maxlen);
	//prm.maxval = parseInt(new Array(++prm.maxlen).join('9'));
	
	return this.each(function() {
		
		var $block = $(this);
		var $input = $block.find('input');
		var fp = false;
		
		function paint(flag)
		{
			if(prm.color)
			{
				fp = flag;
				if(flag)
					$input.add($input.parents('.field:first')).css('background-color',prm.color_error);
				else
					$input.add($input.parents('.field:first')).css('background-color',prm.color_default);
			}
		}
	
		$input.keypress(function(e){
			// допустимые символы
			if(e.which!=8 && e.which!=0 && (e.which<48 || e.which>57))
				return false;
			if(!prm.zero && e.which==48 && $input.val().length==0)
				return false;
			// больше/меньше
			var n = parseInt(String.fromCharCode(e.which));
			if(!isNaN(n))
			{
				if(parseInt($(this).val()+n)>prm.maxval)
					return false;
			}
			if(fp) paint();
			if(prm.func) prm.func.call();
		});
		
		$input.blur(function(){
			var cur_val = $input.val();
			if(!cur_val || isNaN(cur_val))
				$input.val(1);
		});
		
		$block.find('.more,.less').click(function(){
			var cur_val = $input.val();
			if(!cur_val || isNaN(cur_val))
				$input.val(1);
			
			cur_val = parseInt($input.val());

			if($(this).hasClass('more'))
			{
				if(cur_val<prm.maxval)
				{
					$input.val(cur_val+1);
					paint();
				}
				else
					paint(1);
			}
			else
			{
				if(cur_val>1)
				{
					$input.val(cur_val-1);
					paint();
				}
				else
					paint(1);
			}
			
			if(prm.func) prm.func.call();
			
			return false;
		});
	});
};

jQuery.fn.blackout = function(settings){
	
	// Settings
	settings = $.extend({
		color : '#000',
		opacity : 80,
		z : 50
	}, settings);
	
	settings.opacity = parseInt(settings.opacity)*0.01;

	var $blackout = $('<div id="blackout"></div>');
	$blackout.css({
		'width' : document.body.clientWidth + 'px', 
		'height' : document.body.scrollHeight + 'px',
		'position' : 'absolute',
		'left' : '0',
		'top' : '0',
		'background-color' : settings.color,
		'padding' : '0',
		'z-index' : settings.z,
		'display' : 'none'
	});
	$blackout.animate({opacity:settings.opacity},0);
	
	$(window).resize(function(){
		$blackout.css({
			'width' : document.body.clientWidth + 'px', 
			'height' :  '31231px'
		});
	});
	
	$('body').prepend($blackout);
}
// ОТКРЫВАЕТ СТРАНИЦУ В ОТДЕЛЬНОМ ОКНЕ
function openWindow(width,height,url,target)
{
	/*
	width	размер в пикселах	ширина нового окна
	height	размер в пикселах	высота нового окна
	left	размер в пикселах	абсцисса левого верхнего угла нового окна
	top	размер в пикселах	ордината левого верхнего угла нового окна
	toolbar	1 / 0 / yes / no	вывод панели инструменов
	location	1 / 0 / yes / no	вывод адресной строки
	directories	1 / 0 / yes / no	вывод панели ссылок
	menubar	1 / 0 / yes / no	вывод строки меню
	scrollbars	1 / 0 / yes / no	вывод полос прокрутки
	resizable	1 / 0 / yes / no	возможность изменения размеров окна
	status	1 / 0 / yes / no	вывод строки статуса
	fullscreen	1 / 0 / yes / no	вывод на полный экран
	*/ 
	if(!target) target = 'my';
	var left = Math.round((screen.width-width)/2);
	var top = Math.round((screen.height-height)/2)-40;
	var win = window.open(url, target, 'resizable=yes,width='+width+',height='+height+',scrollbars=1,top='+top+',left='+left);
	win.focus();
	// Пример:
	// <a href="page.htm" target="my" onClick="openWindow(570,700)">открыть</a>
}