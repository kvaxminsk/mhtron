/*
jAlert - собственный плагин (v1.0).
Создан с целью замены стандартных диалоговых окон alert и confirm
(в последствии возможно и promt).
*/

(function($){
	
	var $jA_wind, $jA_wind_shadow, $jA_blackout, $jA_btn_ok, $jA_btn_yes, $jA_btn_no;
	
	var jA_methods = {
		/* ---------------- ИНИЦИАЛИЗАЦИЯ -------------------- */
		init : function(prm){
			// настройки
			prm = $.extend({
				ws : true, // с применением тени
				z : 50 // z-index окна
			}, prm);
			
			$jA_wind = $('<div id="jAlert"></div>'); // окно
			$jA_wind_shadow = $('<div id="jAlert_shadow"></div>'); // тень от окна (только для IE)
			$jA_blackout; // тень (если она есть)
			$jA_btn_ok = $('<a href="" class="ok"><div></div></a>'); // кнопка OK
			$jA_btn_yes = $('<a href="" class="yes"><div></div></a>'); // кнопка YES
			$jA_btn_no = $('<a href="" class="no"><div></div></a>'); // кнопка NO
			
			$jA_wind.css({'z-index':prm.z});
			
			$jA_wind.prepend('<div class="info_place"></div><div class="btn_place" align="center"></div>');

			$('body').prepend($jA_wind);
			if(prm.ws)
			{
				$(document).blackout({opacity:50});
				$jA_blackout = $('#blackout');
			}
			if($.browser.msie)
			{
				$jA_wind_shadow.css({'z-index':prm.z-1});
				$('body').prepend($jA_wind_shadow);
			}
				
			$(window).resize(function(){ reposjAlert() });
			
			function reposjAlert()
			{
				var jAlertLeft = Math.round( ($('body').width()/2) - ($jA_wind.width()/2) + $(window).scrollLeft() );
				var jAlertTop = Math.round( ($('body').height()/2) - ($jA_wind.height()/2) + $(window).scrollTop() );
				
				// окно
				$jA_wind.css({
					'left' : jAlertLeft,
					'top' : jAlertTop
				});
				// тень от окна
				$jA_wind_shadow.css({
					'left' : jAlertLeft-4,
					'top' : jAlertTop-4
				});
			}
			
			/*this.data('jAlert', { wind : $jA_wind,
														wind_shadow : $jA_wind_shadow,
														blackout : $jA_blackout,
														btn_ok : $jA_btn_ok,
														btn_yes : $jA_btn_yes,
														btn_no : $jA_btn_no
													});*/
		},
		
		show : function(type,text,func){

			/*var $jA_wind = this.data('jAlert').wind;
			var $jA_wind_shadow = this.data('jAlert').wind_shadow;
			var $jA_blackout = this.data('jAlert').blackout;
			var $jA_btn_ok = this.data('jAlert').btn_ok;
			var $jA_btn_yes = this.data('jAlert').btn_yes;
			var $jA_btn_no = this.data('jAlert').btn_no;*/
			
			if(!$jA_wind.size()) return false;
			
			var windW, windH, windL, windT;
			
			$jA_wind.find('.info_place').html(text);
			$jA_wind.find('.btn_place').html(' ');
			
			// добавляем кнопки
			if(type=='alert')
			{
				$jA_wind.find('.btn_place').prepend($jA_btn_ok);
				$jA_btn_ok.click(function(){ if(func) func.call(); jA_methods.hide.call(); return false; });
			}
			else if (type=='confirm')
			{
				$jA_wind.find('.btn_place').prepend($jA_btn_no).prepend($jA_btn_yes);
				$jA_btn_yes.click(function(){ if(func) func.call(); jA_methods.hide.call(); return false; });
				$jA_btn_no.click(function(){ jA_methods.hide.call(); return false; });
			}			
			
			windW = $jA_wind.width();
			windH = $jA_wind.height();
			windL = Math.round( ($('body').width()/2) - (windW/2) + $(window).scrollLeft() );
			windT = Math.round( ($('body').height()/2) - (windH/2) + $(window).scrollTop() );
			
			$jA_wind.css({
				'left' : windL,
				'top' : windT
			});
			
			if($jA_blackout.size())
				$jA_blackout.show();
			$jA_wind.show();
			if($jA_wind_shadow.size())
			{
				$jA_wind_shadow.css({
					'width' : windW,
					'height' : windH,
					'left' : windL-4,
					'top' : windT-4
				}).show();
			}
		},
		
		hide : function(){
			
			$jA_wind.add($jA_wind_shadow).add($jA_blackout).hide();
			
		}
	};
	
	/* ---------------- ПЛАГИН jAlert -------------------- */
	$.fn.jAlert = function(method){
		
		// логика вызова метода
		if(jA_methods[method]) {
			return jA_methods[method].apply(this,Array.prototype.slice.call(arguments,1));
		} else if (typeof method === 'object' || !method) {
			return jA_methods.init.apply(this,arguments);
		} else {
			$.error('Метод'+method+' в jQuery.jAlert не существует');
		}
		
	};
	
})(jQuery);