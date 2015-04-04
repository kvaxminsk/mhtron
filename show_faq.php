<?
require('inc/common.php');

$pageID = 27;
$title = "Вопросы / Ответы";
$navigate = " &raquo; Вопросы / Ответы";

ob_start();

?>
<h1>Вопросы / Ответы</h1>
<?=set('faq_text')?>
<?

$mas = getTree("SELECT * FROM {$prx}faq WHERE id_parent='%s' AND status=1 ORDER BY sort");
if(sizeof($mas))
{
	?><table id="faqtab"><?
	foreach($mas as $vetka)
	{
		$level = $vetka["level"];
		$row = $vetka['row'];
		
		$lsize = $level>0 ? '14' : '16';
		$otstup = $level>0 ? ' style="padding-left:'.($level*35).'px;"' : '';
		$display = $level>0 ? ' style="display:none"' : '';
		
		?>
		<tr id="<?=$row['id']?>-<?=$row['id_parent']?>"<?=$display?>>
			<td<?=$otstup?>>
            	<a href="<?=$link?>" class="link" style="font-size:<?=$lsize?>px"><?=$row['name']?></a>
            </td>
		</tr>
		<?
		
		if($row['text'])
		{
			?>
            <tr id="text_<?=$row['id']?>"<?=$display?>>
                <td<?=$otstup?>>
                	<table class="faqtab_tab">
                      <tr>
                        <td><?=$row['text']?></td>
                      </tr>
                    </table>
                </td>
            </tr>
            <?
		}
	}
	?>
    </table>
	
    <script>
	$(function(){
		$('#faqtab a').click(function(){
			if($(this).attr('class')=='link')
			{
				cur_id_ = $(this).parents('tr:first').attr('id');
				cur_id_ = cur_id_.split('-');
				cur_id  = cur_id_[0];
				
				//$(this).css('color','#080c43');
				
				// открываем подчиненные рубрики
				$('#faqtab tr').each(function(){
					id_ = $(this).attr('id');
					id_ = id_.split('-');
					id  = id_[0];
					id_parent = id_[1];
					
					if(id_parent==cur_id)
					{
						if(!$(this).is(':visible'))
							$(this).show();
						else
						{
							// скрываем тексты
							text = $('#text_'+id);
							if(text.size())
								text.hide();
								
							$(this).hide();
						}
					}
					
					// открываем текст
					text = $('#text_'+cur_id);
					if(text.size())
					{
						if(!text.is(':visible'))
							text.show();
						else
							text.hide();
					}
				});
				
				return false;
			}
		});
	})
	</script>
	<?
}

$content = ob_get_clean();

require("tpl/template.php");
?>