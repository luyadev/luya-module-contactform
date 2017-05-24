<?php

namespace luya\contactform\controllers;

use Yii;
use luya\base\DynamicModel;
use luya\Exception;
use luya\TagParser;
use yii\base\InvalidConfigException;

class DefaultController extends \luya\web\Controller
{   
    /**
     * Index Action
     *
     * @throws InvalidConfigException
     * @return string
     */
    public function actionIndex()
    {
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
            
            $mail = Yii::$app->mail->compose($this->module->mailTitle, $this->generateMailMessage($model));
            
            foreach ($this->module->recipients as $recipientMail) {
                $mail->address($recipientMail);
            }
            
            if ($this->module->replyToAttribute) {
                $replyToAttribute = $this->module->replyToAttribute;
                $mail->mailer->addReplyTo($model->$replyToAttribute);
            }
            
            if ($mail->send()) {
                if ($this->module->sendToUserEmail) {
                	$sendToUserMail = $this->module->sendToUserEmail;
                	Yii::$app->mail->compose($this->module->mailTitle, $this->generateMailMessage($model))->address($model->$sendToUserMail)->send();
                }
                
                // callback eval
                if (is_callable($this->module->callback)) {
                    call_user_func($this->module->callback, $model);
                }
                
                Yii::$app->session->setFlash('contactform_success');
                
                return $this->refresh();
                
            } else {
            	throw new InvalidConfigException('Unable to send contact email, maybe the mail component is not setup properly in your config.');
            }
        } else {
            // as the toolbar maybe try's to re render this part of the controller.
            Yii::$app->session->set('renderTime', time());
        }
        
        return $this->render('index', [
            'model' => $model,
        ]);
    }
    
    public function generateMailMessage($model)
    {
    	return $this->renderFile('@'.$this->module->id.'/views/_mail.php', [
    		'model' => $model,
    	    'detailViewAttributes' => $this->module->detailViewAttributes,
    		'title' => $this->module->mailTitle,
    		'text' => TagParser::convertWithMarkdown($this->module->mailText),
    	]);
    }
}
