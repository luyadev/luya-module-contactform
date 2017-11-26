<?php
use yii\widgets\DetailView;

/* @var $model \yii\base\Model The model which holds the migration*/
/* @var $title string The title text */
/* @var $text string The message text */
?>
<?= $title . PHP_EOL . strftime('%c') . PHP_EOL . $text; ?>
<?= strip_tags(DetailView::widget([
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
        
        return strtr('{label}: {value}', [
            '{label}' => $attribute['label'],
            '{value}' => $widget->formatter->format($value, $attribute['format']),
        ]);
    }
])); ?>
