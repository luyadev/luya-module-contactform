<?php
use yii\widgets\DetailView;

/* @var $model \yii\base\Model The model which holds the migration*/
/* @var $title string The title text */
/* @var $text string The message text */
/* @var $template string */
/* @var $footerText string */

// since 1.0.8 generate mail message from template

$table = DetailView::widget([
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
        // generate value from attribute
        $value = is_array($attribute['value']) ? implode(", ", $attribute['value']): $attribute['value'];
        // replace label and value from template
        return strtr('<tr><th width="150" style="border-bottom:1px solid #F0F0F0">{label}</th><td style="border-bottom:1px solid #F0F0F0">{value}</td></tr>', [
            '{label}' => $attribute['label'],
            '{value}' => $widget->formatter->format($value, $attribute['format']),
        ]);
    }
]);

// replace template and return, default template:
// <h2>{title}</h2><p><i>{time}</i></p>{text}\n{table}\n{footer}
echo strtr($template, [
    '{title}' => $title,
    '{time}' => strftime('%c'),
    '{text}' => $text,
    '{table}' => $table,
    '{footer}' => $footerText
]);
