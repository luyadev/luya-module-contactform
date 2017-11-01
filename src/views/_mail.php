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
    'template' => function ($attribute, $index, $widget) {
        $value = is_array($attribute['value']) ? implode(", ", $attribute['value']): $attribute['value'];
        
        return strtr('<tr><th width="150" style="border-bottom:1px solid #F0F0F0">{label}</th><td style="border-bottom:1px solid #F0F0F0">{value}</td></tr>', [
            '{label}' => $attribute['label'],
            '{value}' => $widget->formatter->format($value, $attribute['format']),
        ]);
    }
]); ?>
