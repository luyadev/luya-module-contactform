<?php

namespace luya\contactform\controllers;

use Yii;
use yii\base\InvalidConfigException;
use yii\base\Model;
use luya\TagParser;
use luya\web\filters\RobotsFilter;

/**
 * Contact Form Default Controller.
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
class DefaultController extends \luya\web\Controller
{
    const CONTACTFORM_SUCCESS_FLASH = 'contactform_success';

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
        $model = $this->module->getModel();

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            
            $mail = Yii::$app->mail->compose($this->module->mailTitle, $this->generateMailMessage($model));
            $mail->altBody = $this->generateMailAltBody($model);
            $mail->addresses($this->ensureRecipients($model));
            
            if ($this->module->replyToAttribute) {
                $replyToAttribute = $this->module->replyToAttribute;
                $mail->addReplyTo($model->{$replyToAttribute});
            }
            
            if ($mail->send()) {
                // evulate the callback
                if (is_callable($this->module->callback)) {
                    call_user_func($this->module->callback, $model);
                }
                // evulate whether a mail needs to be send to the user or not
                if ($this->module->sendToUserEmail) {
                    $sendToUserMail = $this->module->sendToUserEmail;
                    // composer new mailer object
                    $mailer = Yii::$app->mail->compose($this->module->mailTitle, $this->generateMailMessage($model));
                    $mailer->altBody = $this->generateMailAltBody($model);
                    $mailer->address($model->{$sendToUserMail});
                    $mailer->send();
                }
                
                Yii::$app->session->setFlash(self::CONTACTFORM_SUCCESS_FLASH);

                if (Yii::$app->request->isAjax) {
                    return $this->renderAjax("index", [
                        "model" => $model
                    ]);
                }

                return $this->refresh();
            }

            throw new InvalidConfigException('Unable to send contact email, maybe the mail component is not setup properly in your config.');
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
     * @return array
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
     * @return string The ald body content
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
