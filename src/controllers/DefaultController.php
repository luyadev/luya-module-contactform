<?php

namespace luya\contactform\controllers;

use Yii;
use yii\base\InvalidConfigException;
use yii\base\Model;
use luya\base\DynamicModel;
use luya\TagParser;
use luya\web\filters\RobotsFilter;

/**
 * Contact Form Default Controller.
 *
 * @author Basil Suter <basil@nadar.io>
 */
class DefaultController extends \luya\web\Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        
        $behaviors['robotsFilter'] = [
            'class' => RobotsFilter::class,
            'delay' => $this->module->spamDetectionDelay,
        ];
        
        return $behaviors;
    }
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
                $attributes = $rule[0];
                $validator = $rule[1];
                unset($rule[0], $rule[1]);
                $model->addRule($attributes, $validator, $rule);
            } else {
                throw new InvalidConfigException('Invalid validation rule: a rule must specify both attribute names and validator type.');
            }
        }
        
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            
            $mail = Yii::$app->mail->compose($this->module->mailTitle, $this->generateMailMessage($model));
            
            foreach ($this->module->recipients as $recipientMail) {
                $mail->address($recipientMail);
            }
            
            if ($this->module->replyToAttribute) {
                $replyToAttribute = $this->module->replyToAttribute;
                $mail->addReplyTo($model->$replyToAttribute);
            }
            
            if ($mail->send()) {
                if ($this->module->sendToUserEmail) {
                    $sendToUserMail = $this->module->sendToUserEmail;
                    $mailer = Yii::$app->mail;
                    $mailer->altBody = $this->generateMailAltBody($model);
                    $mailer->subject($this->module->mailTitle);
                    $mailer->body($this->generateMailMessage($model));
                    $mailer->address($model->$sendToUserMail);
                    $mailer->send();
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
        }

        if (Yii::$app->request->isAjax) {
            return $this->renderAjax('index', [
                'model' => $model
            ]);
        }
        return $this->render('index', [
            'model' => $model,
        ]);
    }
    
    /**
     * Generate E-Mail Message
     * @param \yii\base\Model $model
     * @return string The rendered Html content.
     */
    public function generateMailMessage(Model $model)
    {
        return $this->renderFile('@'.$this->module->id.'/views/_mail.php', [
            'model' => $model,
            'detailViewAttributes' => $this->module->detailViewAttributes,
            'title' => $this->module->mailTitle,
            'text' => TagParser::convertWithMarkdown($this->module->mailText),
            'template' => $this->module->mailTemplate,
            'footerText' => TagParser::convertWithMarkdown($this->module->mailFooterText),
        ]);
    }
    
    /**
     * Generate E-Mail Alt Body without html data.
     * 
     * @param Model $model
     * @return string|NULL|string
     */
    public function generateMailAltBody(Model $model)
    {
    	return $this->renderFile('@'.$this->module->id.'/views/_altmail.php', [
    		'model' => $model,
    		'detailViewAttributes' => $this->module->detailViewAttributes,
    		'title' => $this->module->mailTitle,
    		'text' => strip_tags($this->module->mailText),
    	]);
    }
}
