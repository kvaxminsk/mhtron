<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>Untitled Document</title>
</head>
	
    <link rel="stylesheet" href="css/colorpicker.css" type="text/css" />
    <script type="text/javascript" src="js/jquery.js"></script>
	<script type="text/javascript" src="js/colorpicker.js"></script>
    <script type="text/javascript" src="js/eye.js"></script>
    <script type="text/javascript" src="js/utils.js"></script>
    <script type="text/javascript" src="js/layout.js?ver=1.0.2"></script>
    
<body>

<script type="text/javascript">        
$(document).ready(
  function()
  {
	$('.picker').ColorPicker({
		onShow: function (colpkr) {
			$(colpkr).fadeIn(500);
			return false;
		},
		/*onHide: function (colpkr) {
			$(colpkr).fadeOut(500);
			return false;
		},*/
		onSubmit: function(hsb, hex, rgb, el) {
			$(el).val(hex);
			$(el).ColorPickerHide();
		},
		onBeforeShow: function () {
			$(this).ColorPickerSetColor(this.value);
		}
	})
	.bind('keyup', function(){
		$(this).ColorPickerSetColor(this.value);
	});
  });

</script>

<input type="text" maxlength="6" size="6" class="picker" value="0000ff" />
<input type="text" maxlength="6" size="6" class="picker" value="0000ff" />
<input type="text" maxlength="6" size="6" class="picker" value="0000ff" />

</body>
</html>