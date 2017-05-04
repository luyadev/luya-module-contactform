<?php
use yii\widgets\DetailView;
/* @var $model \yii\base\Model The model which holds the migration*/
/* @var $title string The title text */
/* @var $text string The message text */
?>
<h2><?= $title; ?></h2>
<p><i><?= strftime('%c')?></i></p>
<?= $text; ?>
<?= DetailView::widget([
    'model' => $model,
    'attributes' => $detailViewAttributes,
    'options' => [
        'class' => null,
        'style' => 'width:100%',
        'cellpadding' => 5,
        'cellpsacing' => 2,
        'border' => 0,
    ],
    'template' => '<tr><th width="150" style="border-bottom:1px solid #F0F0F0" {captionOptions}>{label}</th><td style="border-bottom:1px solid #F0F0F0" {contentOptions}>{value}</td></tr>'
]); ?>
