<?php
/* @var $model \yii\base\Model The model which holds the migration*/
/* @var $title string The title text */
/* @var $text string The message text */
?>
<h2><?= $title; ?></h2>
<?= $text; ?>
<table border="0" cellpadding="5" cellspacing="2" width="100%">
    <?php foreach ($model->getAttributes() as $key => $value): ?>
    	<tr>
    		<td width="150" style="border-bottom:1px solid #F0F0F0"><?= $model->getAttributeLabel($key); ?>:</td>
    		<td style="border-bottom:1px solid #F0F0F0">
    			<?php if (is_array($value)): ?>
    				<ul>
    					<?php foreach ($value as $item): ?>
    					<li><?= nl2br($item); ?></li>
    					<?php endforeach; ?>
    				</ul>
    			<?php else: ?>
    				<?= nl2br($value); ?>
    			<?php endif; ?>
    		</td>
    <?php endforeach; ?>
</table>
<p><i><?= date("r"); ?></i></p>