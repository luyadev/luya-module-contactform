# LUYA CONTACT FORM MODULE

This module provides a very fast and secure way to create customizable contact forms.

Installation
----

Require the contact module via composer

```sh
composer require luyadev/luya-module-contactform:^1.0@dev
```

add the contact form module to your config:

```php
'modules' => [
    // ...
    'contactform' => [
        'class' => 'luya\contactform\Module',
        'attributes' => [
            'name', 'email', 'street', 'city', 'tel', 'message',
        ],
        'rules' => [
            [['name', 'email', 'street', 'city', 'message'], 'required'],
            ['email', 'email'],
        ],
        'recipients' => [
            'admin@example.com',
        ],
    ],  
    // ...
],
```

To defined the attribute labels you can configure the module as followed:

```php
'attributeLabels' => [
    'email' => 'E-Mail',
],
```

By default LUYA will wrap the value into the `Yii::t('app', $value)` functions so you are able to translate the attributes labels. The above exmaple would look like this `Yii::t('app', 'E-Mail')`.

#### View Files

Typically view files are located in `views` folder of your project root, create the view file for the corresponding model data at `views/contactform/default/index.php` with the following content:


```php
<?php

use yii\widgets\ActiveForm;
use yii\helpers\Html;

/**
 * @var object $model Contains the model object based on DynamicModel yii class.
 * @var boolean $success Return true when successfull sent mail and validated
 */
?>

<? if (Yii::$app->session->getFlash('contactform_success')): ?>
    <div class="alert alert-success">The form has been submited successfull.</div>
<? else: ?>
    <? $form = ActiveForm::begin(); ?>
    
    <?= $form->field($model, 'name'); ?>
    <?= $form->field($model, 'email'); ?>
    <?= $form->field($model, 'street'); ?>
    <?= $form->field($model, 'city'); ?>
    <?= $form->field($model, 'tel'); ?>
    <?= $form->field($model, 'message')->textarea(); ?>
    
    <?= Html::submitButton('Submit', ['class' => 'btn btn-primary']) ?>
    
    <? ActiveForm::end(); ?>
<? endif; ?>
```

when the form validation success the variable `$success` will be true, in addition a Yii2 flash mesage `Yii::$app->session->setFlash('contactform_success')` with the key-name `contactform_success` will be set.

Tip: In order to style required fields with asterisks, you can use the following CSS:

```css
div.required label.control-label:after {
   content: " *";
   color: red;
}
```

#### Trigger after success

You can define a anonmys function which will be trigger **after success**, the first parameter of the function can be the model which will be assigne [[\luya\base\DynamicModel]]. Example callback:

```php
'modules' => [
    // ...
    'contactform' => [
        // ...
        'callback' => function($model) {
            // insert the name of each contact form into `contact_form_requests` table:
            Yii::$db->createCommand()->insert('contact_form_requests', ['name' => $model->name])->execute();
        }
    ],
];
$callback = 
```
