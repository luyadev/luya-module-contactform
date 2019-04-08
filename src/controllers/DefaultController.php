<?php

namespace luya\contactform\controllers;

use Yii;
use yii\base\InvalidConfigException;
use yii\base\Model;
use luya\base\DynamicModel;
use luya\TagParser;
use luya\web\filters\RobotsFilter;
use yii\di\Instance;

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
        if ($this->module->modelClass) {
            // generate the model object from property
            $model = Instance::ensure($this->module->modelClass, 'yii\base\Model');
        } else {
            // use the dynamic model
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
        }
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            
            $mail = Yii::$app->mail->compose($this->module->mailTitle, $this->generateMailMessage($model));

            $recipients = $this->ensureRecipients($model);
            $mail->addresses($recipients);
            
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

                if (Yii::$app->request->isAjax) {
                    return $this->renderAjax("index", [
                        "model" => $model
                    ]);
                }

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
     * Ensure recipients from callable or array/string notation.
     *
     * @param Model $model
     * @return string
     * @since 1.0.10
     */
    public function ensureRecipients(Model $model)
    {
        if (is_callable($this->module->recipients)) {
            return (array) call_user_func($this->module->recipients, $model);
        }

        return (array) $this->module->recipients;
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
