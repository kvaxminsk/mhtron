<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
	<title><?=set('title').($title!=''?" &raquo; {$title}":'')?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<meta name="keywords" content="<?=$keywords?>">
	<meta name="description" content="<?=$description?>">
    
	<link type="image/png" rel="shortcut icon" href="/favicon.png"/>
	 
    <meta name='yandex-verification' content='5935f595b6d3c7d0' />
    
    <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon">
    <link rel="icon" href="/favicon.ico" type="image/x-icon">
    
    <link type="text/css" href="/css/style.css" rel="stylesheet">
	
    <!--script type="text/javascript" src="/js/jquery-1.4.4.min.js"></script-->
    <!--script type="text/javascript" src="/js/jquery-1.9.0.min.js"></script-->
    <script type="text/javascript" src="/js/jquery.js"></script>
	 
    <script type="text/javascript" src="/js/jquery-ui-1.8.1.custom.min.js"></script>
    <script type="text/javascript" src="/js/jquery.gradient.js"></script>
    <script type="text/javascript" src="/js/jquery.pngFix.js"></script>
    
    <script type="text/javascript" src="/js/utils.js"></script>
    <script type="text/javascript" src="/js/spec.js"></script>
	 
    <script type="text/javascript" src="/js/jquery.limarquee.js"></script>
	 <link rel="stylesheet" href="/js/limarquee.css">	 
    
    <script type="text/javascript" src="/inc/advanced/highslide/highslide-with-gallery.js"></script>
		<link type="text/css" href="/inc/advanced/highslide/highslide.css" rel="stylesheet"/>
    
    <script type="text/javascript" src="/inc/advanced/jAlert/jquery.jAlert.js"></script>
  	<link rel="stylesheet" href="/inc/advanced/jAlert/jAlert.css" type="text/css" />

    <script type="text/javascript" src="/js/jquery.maphilight.min.js"></script>
	 
</head>
<body style="background:url(/img/vfm<?=rand(1,7)?>a.png) 100% 0 no-repeat #EAE9E9;">

<?=$body?>

<table id="arr_content"><tr><td align="center">
<div id="content">
<table width="100%" height="100%"><tr><td height="443" style="padding:0 29px">

<table width="100%" height="100%" style="background:url(/img/logo.png) 0 0 no-repeat;">
  <tr>
    <td width="241" height="170" align="right" valign="top">
	 	<a href='/' style="display:inline-block; width:241px; height:136px;"></a>
	 </td>
    <td style="padding-left:19px" valign="top">
    	<div style="position:relative;"><div id="loupe"></div></div>
        <div style="position:relative; z-index:2;" align="right">
			<table  style="width:600px;" id="up_tab">
				<tr>
					<td>
						<? //=show_banners(true)?>
					</td>
					<td align="right" style="padding:20px 0;">
						<table><tr><td align="left">
							<div style=" font-style:italic; color:#3ea304; "><?=show_auth()?></div>
						 <?=show_cart()?>
						</td></tr></table>
					</td>
					<td rowspan="3" valign="top">
						<div style="width:200px; padding:20px 0 0 30px;">
							<div style="position:absolute; margin-left:210px;">
							<?	$res = sql("SELECT * FROM {$prx}banners WHERE `right`='1' ORDER BY sort,id");
								while($row = mysql_fetch_assoc($res))
								{
									$fe = getFileFormat($_SERVER['DOCUMENT_ROOT']."/uploads/banners/{$row['id']}.*");	 ?>
									<div style="margin-top:5px;"><a href="<?=$row['link']?>"><img src="/banners/<?=$row['id']?>.<?=$fe?>"></a></div>
							<?	}	?>
							</div>
						<? if($_SESSION['search']){ ?>
						<table style="border-collapse:separate;" cellspacing="1" width="100%">
							<tr><th style="font:10px Arial;" bgcolor="#FFFFFF">история поиска</th></tr>
							<?
							foreach($_SESSION['search'] as $s_search)
							{
								$arr = explode('#',$s_search);
								?><tr><td style="font:10px Arial; cursor:pointer;" onClick="$('#<?=$arr[1]?>').val('<?=$arr[0]?>'); $('#search_frm').submit();" bgcolor="#FFFFFF" align="center"><?=$arr[0]?></td></tr><?
							}
							?>
						</table>
						<? }	?>
						</div>
					</td>
				</tr>
				<tr>
					<td colspan="2">
						<?=show_search()?>
					</td>
				</tr>
				<tr>
					<td style="padding-top:10px;" colspan="2">
						<table width="100%">
							<tr>
								<td>
									<a href="/" title="на главную" style="margin-right:8px"><img src="/img/u1.png" align="absmiddle"></a><?
									?><a href="/map/" title="карта сайта" style="margin-right:8px"><img src="/img/u2.png" align="absmiddle"></a><?
									?><a href="/pages/<?=getField("SELECT link FROM {$prx}pages WHERE id=28")?>" title="написать нам"><img src="/img/u3.png" align="absmiddle"></a>
								</td>
								<td align="right">
									<span id="zapros_shassi">Запрос детали по  номеру шасси</span>
									<div style="margin-top:5px;"><a href="/kpp_cat/" class="kpps">Подобрать деталь по каталогу КПП</a></div>
								</td>
							</tr>
						</table>
					</td>
					</tr>							
			</table>
        </div>
    </td>
  </tr>
  <tr>
    <td valign="top" style="padding:10px 10px 10px 20px" height="600">
			<?=show_menu()?>
      <?=show_manager_info()?>
    </td>
    <td valign="top" id="center">
		<?
		if($_SERVER['REQUEST_URI']=='/')
			echo $content;
		else
		{
			?><div style="position:relative; z-index:3"><?=$content?></div><?
		}
		?>
    </td>
  </tr>
  <tr>
    <td height="107" colspan="2" valign="top"><?=show_footer()?></td>
  </tr>
</table>

</td></tr></table>
</div>
</td></tr></table>

<?
if(!isset($_SESSION['user']))
	echo show_auth_pop();
if(strpos($_SERVER['REQUEST_URI'],'cart/') && isset($_SESSION['cart']))
	echo show_order_pop();
?>
<?=show_zapros_shassi()?>
<script>
	$(function(){
		$('span#zapros_shassi').click(function(){
			$pop_zapros_shassi = $('#pop_zapros_shassi');
			if($pop_zapros_shassi.size() && !$pop_zapros_shassi.is(':visible'))
			{
				var bs = BodySize();
				$pop_zapros_shassi.css('top', 120);
				setTimeout(function(){
					$pop_zapros_shassi.show().animate({left: Math.round((bs.width/2) - ($pop_zapros_shassi.width()/2) + 220) + 'px'}, 100);
				},400);
								
				$('#pop_zapros_shassi_exit').bind('click',function(){
					$pop_zapros_shassi.animate({left:-500}, 100, function(){ $(this).hide() });
				});
			}
		});
	});
</script>

<iframe name="ajax" id="ajax"></iframe>

</body>
</html>