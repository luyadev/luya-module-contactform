<?php

namespace luya\contactform\controllers;

use Yii;
use luya\base\DynamicModel;
use yii\base\InvalidConfigException;
use luya\Exception;

class DefaultController extends \luya\web\Controller
{
    /**
     * @var null|bool if null no status information has been assigned, if false a global error happend (could not send mail), if true
     * the form has been sent successfull.
     */
    public $success = null;
    
    /**
     * Index Action
     *
     * @throws InvalidConfigException
     * @return string
     */
    public function actionIndex()
    {
        $mailTitle = $this->module->mailTitle;
        
        // create dynamic model
        $model = new DynamicModel($this->module->attributes);
        $model->attributeLabels = $this->module->attributeLabels;
        
        foreach ($this->module->rules as $rule) {
            if (is_array($rule) && isset($rule[0], $rule[1])) {
                $model->addRule($rule[0], $rule[1], isset($rule[2]) ? $rule[2] : []);
            } else {
                throw new InvalidConfigException('Invalid validation rule: a rule must specify both attribute names and validator type.');
            }
        }
        
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ((time() - (int) Yii::$app->session->get('renderTime', 0)) < $this->module->spamDetectionDelay) {
                throw new Exception("We haved catched a spam contact form with the values: " . print_r($model->attributes, true));
            }
            
            $mail = Yii::$app->mail->compose('['.Yii::$app->siteTitle.'] ' . $mailTitle, $this->renderFile('@'.$this->module->id.'/views/_mail.php', [
                'model' => $model,
                'mailTitle' => $mailTitle,
            ]));
            
            $mail->adresses($this->module->recipients);
            if ($mail->send()) {
                $this->success = true;
                Yii::$app->session->setFlash('contactform_success');
                
                // callback
                $cb = $this->module->callback;
                if (is_callable($cb)) {
                    $cb($model);
                }
            } else {
                $this->success = false;
            }
        } else {
            // as the toolbar maybe try's to re render this part of the controller.
            Yii::$app->session->set('renderTime', time());
        }
        
        return $this->render('index', [
            'model' => $model,
            'success' => $this->success,
        ]);
    }
}
