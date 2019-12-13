<p align="center">
  <img src="https://raw.githubusercontent.com/luyadev/luya/master/docs/logo/luya-logo-0.2x.png" alt="LUYA Logo"/>
</p>

# Contactform Module

[![LUYA](https://img.shields.io/badge/Powered%20by-LUYA-brightgreen.svg)](https://luya.io)
[![Build Status](https://travis-ci.org/luyadev/luya-module-contactform.svg?branch=master)](https://travis-ci.org/luyadev/luya-module-contactform)
[![Test Coverage](https://api.codeclimate.com/v1/badges/01672f2d0b93a17a156b/test_coverage)](https://codeclimate.com/github/luyadev/luya-module-contactform/test_coverage)
[![Maintainability](https://api.codeclimate.com/v1/badges/01672f2d0b93a17a156b/maintainability)](https://codeclimate.com/github/luyadev/luya-module-contactform/maintainability)
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
        'useAppViewPath' => true, // When enabled the views will be looked up in the @app/views folder, otherwise the views shipped with the module will be used.
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

/** @var \yii\base\Model $model Contains the model object based on DynamicModel yii class. */
/** @var \luya\web\View $this The current View object */
/** @var ActiveForm $form The ActiveForm Object */
?>
<?php if (Yii::$app->session->getFlash($this->context::CONTACTFORM_SUCCESS_FLASH)): ?>
    <div class="alert alert-success">The form has been submitted successfully.</div>
<?php else: ?>
    <?php $form = ActiveForm::begin(); ?>
    <?= $form->field($model, 'name'); ?>
    <?= $form->field($model, 'email'); ?>
    <?= $form->field($model, 'street'); ?>
    <?= $form->field($model, 'city'); ?>
    <?= $form->field($model, 'tel'); ?>
    <?= $form->field($model, 'message')->textarea(); ?>
    <?= Html::submitButton('Submit', ['class' => 'btn btn-primary']) ?>
    <?php ActiveForm::end(); ?>
<?php endif; ?>
```

when the form validation success the variable `$success` will be true, in addition a Yii2 flash mesage `Yii::$app->session->setFlash('contactform_success')` with the key-name `contactform_success` will be set.

In order to ensure a form is only submited once use the LUYA Core SubmitButtonWidget.

```php
SubmitButtonWidget::widget(['label' => 'Send', 'pushed' => 'Sending...', 'activeForm' => $form, 'options' => ['class' => 'btn btn-primary']]);
```

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
            Yii::$app->db->createCommand()->insert('contact_form_requests', ['name' => $model->name])->execute();
        }
    ],
];
```


#### Ordering fields in email

You can oder the fields in the mail wich will be send to the contact form recipient. This could be put before the rules in the contact form config. 

For short and small forms this notation could be used as well:

```php
'modules' => [
    // ...
    'contactform' => [
        // ...
        'detailViewAttributes' => ['name', 'newsletter:bool', 'city', 'message:text:Label for Message'],    
       // ...
    ],
];
```

For long and complex form we would recommend this notation:

```php
'modules' => [
    // ...
    'contactform' => [
        // ...
        'detailViewAttributes' => [
            ['attribute' => 'name'],
            ['attribute' => 'email'],
            ['attribute' => 'newsletter:bool'],
            ['attribute' => 'city:integer'],
            ['attribute' => 'message'],
        ],       
       // ...
    ],
];
```

#### Advanced configuration

|attribte     |example
|---        |---
|`mailTitle`|The mail title is also known as the mail subject
|`mailText`|This is a message which can be used as intro for the mail body. Markdown parsing is enabled by default. 
|`sendToUserEmail`|If enabled, the mail will also be sent to the user who has submitted the form, configure the property with the mail field from the model.
|`callback`|An anyonymus function find the model argument in order to trigger custom functions.
