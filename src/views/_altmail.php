<?php
use yii\widgets\DetailView;

/** @var \yii\base\Model $model The model which holds the migration*/
/** @var string $title The title text */
/** @var string $text The message text */
/** @var array $detailViewAttributes */
?>
<?= $title . PHP_EOL . Yii::$app->formatter->asDatetime(time()) . PHP_EOL . $text; ?>
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
        $value = is_array($attribute['value']) ? implode(", ", $attribute['value']) : $attribute['value'];

        return strtr('{label}: {value}', [
            '{label}' => $attribute['label'],
            '{value}' => $widget->formatter->format($value, $attribute['format']),
        ]);
    }
])); ?>
