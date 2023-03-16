<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var \yii\base\Model $model Contains the model object based on DynamicModel yii class. */
/** @var \luya\web\View $this */
/** @var \yii\widgets\ActiveForm $form */

?>
<?php if (Yii::$app->session->getFlash('contactform_success')): ?>
    <div class="alert alert-success">The form has been submitted successfully.</div>
<?php else: ?>
    <?php $form = ActiveForm::begin(); ?>
    
    <?php foreach ($this->context->module->attributes as $fieldName): ?>
        <?= $form->field($model, $fieldName); ?>
    <?php endforeach; ?>
    
    <?= Html::submitButton('Submit', ['class' => 'btn btn-primary']) ?>
    
    <?php ActiveForm::end(); ?>
<?php endif; ?>
