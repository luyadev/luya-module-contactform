# LUYA CONTACT FORM MODULE

[![LUYA](https://img.shields.io/badge/Powered%20by-LUYA-brightgreen.svg)](https://luya.io)
[![Total Downloads](https://poser.pugx.org/luyadev/luya-module-contactform/downloads)](https://packagist.org/packages/luyadev/luya-module-contactform)
[![Latest Stable Version](https://poser.pugx.org/luyadev/luya-module-contactform/v/stable)](https://packagist.org/packages/luyadev/luya-module-contactform)
[![Join the chat at https://gitter.im/luyadev/luya](https://badges.gitter.im/Join%20Chat.svg)](https://gitter.im/luyadev/luya)

This module provides a very fast and secure way to create customizable contact forms.

## Installation

Require the contact module via composer

```sh
composer require luyadev/luya-module-contactform:~1.0.0
```

add the contact form module to your config:

```php
'modules' => [
    // ...
    'contactform' => [
        'class' => 'luya\contactform\Module',
        'mailTitle' => 'Contact Form',
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

#### Integrate the Module

In order to use the module inside the LUYA CMS, just pick the `Module Block` and drag the block into the page. After droped the block edit the module block and picke the `contactform` module. This will then maybe throw an exception that there is no view file. In order to create the view file follow the next section.

#### View Files

Typically view files are located in `views` folder of your project root, create the view file for the corresponding model data at `views/contactform/default/index.php` with the following content:


```php
<?php

use yii\widgets\ActiveForm;
use yii\helpers\Html;

/* @var object $model Contains the model object based on DynamicModel yii class. */
/* @var $this \luya\web\View */
/* @var $form \yii\widgets\ActiveForm */

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
```


#### Odering fields in email

You can oder the fields in the mail wich will be send to the contact form recipient.

```php
'modules' => [
    // ...
    'contactform' => [
        // ...
        'detailViewAttributes' => [
            [
                'attribute' => 'title',
                'value' => function($model) {
                    return $model->title == 1 ? 'Miss' : 'Mister';
                },
            ],
            ['attribute' => 'first_name'],
            ['attribute' => 'last_name'],
            ['attribute' => 'institution'],     
            ['attribute' => 'street'],
            ['attribute' => 'postalcode'],
            ['attribute' => 'location'],
            ['attribute' => 'country'],
            ['attribute' => 'phone'],
            ['attribute' => 'email'],
            ['attribute' => 'message'],
            [
                'attribute' => 'newsletter',
                'value' => function($model) {
                    return $model->newsletter == 1 ? 'Yes' : 'No';
                },
            ],
        ],
    ],
];
```

#### Advanced configuration

|attribte     |example
|---        |---
|`mailTitle`|The mail title is also known as the mail subject
|`mailText`|This is a message which can be used for the mail body as intro, markdown parsing is enabled by default. 
|`sendToUserEmail`|If enabled the mail will also sent to the user how has submited the mail, configure the property with the mail field from the model.
|`callback`|An anyonymus function find the model argument in order to trigger custom functions.
