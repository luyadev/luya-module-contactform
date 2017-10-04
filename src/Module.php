<?php

namespace luya\contactform;

use Yii;
use luya\Exception;

/**
 * LUYA CONTACT FORM MODULE
 *
 * Example configuration:
 *
 * ```php
 * 'contactform' => [
 *     'class' => 'luya\contactform\Module',
 *     'mailTitle' => 'Contact Form',
 *     'attributes' => ['name', 'email', 'street', 'city', 'tel', 'message'],
 *     'rules' => [
 *         [['name', 'email', 'street', 'city', 'message'], 'required'],
 *         ['email', 'email'],
 *     ],
 *     'recipients' => ['admin@example.com'],
 * ],
 * ```
 *
 * @property string $mailTitle The mail title property.
 * @property string $replyToAttribute Returns the attribute which should be used to set the replyTo adresse. If not found it trys to detected. Otherwise null.
 * If the `$sendToUserEmail` attribute is set, it will take this attribute field to set the reply to.
 *
 * @author nadar
 * @since 1.0.0-beta6
 */
class Module extends \luya\base\Module
{
    /**
     * @var boolean By default this module will lookup the view files in the appliation view folder instead of
     * the module base path views folder.
     */
    public $useAppViewPath = true;
    
    /**
     * @var array An array containing all the attributes for this model
     *
     * ```
     * 'attributes' => ['name', 'email', 'street', 'city', 'tel', 'message'],
     * ```
     */
    public $attributes = null;
    
    /**
     * @var array An array of detail view attributes based to the {{yii\widgets\DetailView::attributes}} in order to
     * customize the mail table which is rendered trough {{yii\widgets\DetailView}}.
     * @since 1.0.2
     */
    public $detailViewAttributes;
    
    /**
     * @var array An array define the attribute labels for an attribute, internal the attribute label values
     * will be wrapped into the `Yii::t()` method.
     *
     * ```
     * 'attributeLabels' => [
     *     'email' => 'E-Mail-Adresse',
     * ],
     * ```
     */
    public $attributeLabels = [];
    
    /**
     * @var array An array define the rules for the corresponding attributes. Example rules:
     *
     * ```php
     * rules' => [
     *     [['name', 'email', 'street', 'city', 'message'], 'required'],
     *     ['email', 'email'],
     * ],
     * ```
     */
    public $rules = [];
    
    /**
     * @var callable You can define a anonmys function which will be trigger on success, the first parameter of the
     * function can be the model which will be assigned [[\luya\base\DynamicModel]]. Example callback
     *
     * ```php
     * $callback = function($model) {
     *     // insert the name of each contact form into `contact_form_requests` table:
     *     Yii::$db->createCommand()->insert('contact_form_requests', ['name' => $model->name])->execute();
     * }
     * ```
     */
    public $callback = null;
    
    /**
     *@var array An array with all recipients the mail should be sent on success, recipients will be assigned via
     * {{\luya\components\Mail::addresses()}} method of the mailer function.
     */
    public $recipients = null;
    
    /**
     * @var int Number in seconds, if the process time is faster then `$spamDetectionDelay`, the mail will threated as spam
     * and throws an exception. As humans requires at least more then 2 seconds to fillup a form we use this as base value.
     */
    public $spamDetectionDelay = 2;
    
    /**
     * @var string If you like to enable that the same email for $recipients is going to be sent to the customer which enters form provide the attribute name
     * for the email adresse from the $model configuration. Assuming you have an attribute 'email' in your configuration attributes you have to provide this name.
     *
     * ```php
     * 'sendToUserEmail' => 'email',
     * ```
     */
    public $sendToUserEmail = false;
    
    /**
     * @var string Markdown enabled text which can be prepand to the e-mail sent body.
     */
    public $mailText = null;
    
    /**
     * {@inheritDoc}
     * @see \luya\base\Module::init()
     */
    public function init()
    {
        parent::init();
        
        if ($this->attributes === null) {
            throw new Exception("The attributes attributed must be defined with an array of available attributes.");
        }
        
        if ($this->recipients === null) {
            throw new Exception("The recipients attributed must be defined with an array of recipients who will recieve an email.");
        }
        
        Yii::$app->i18n->translations['contactform'] = [
            'class' => 'yii\i18n\PhpMessageSource',
            'basePath' => '@'.$this->id.'/messages',
            'fileMap' => ['contactform' => 'contactform.php'],
        ];
    }
    
    private $_mailTitle = null;
    
    /**
     * Getter method for $mailTitle.
     *
     * @return string
     */
    public function getMailTitle()
    {
        if ($this->_mailTitle === null) {
            $this->_mailTitle = '['.Yii::$app->siteTitle.'] ' .  static::t('Contact Request');
        }
         
        return $this->_mailTitle;
    }
    
    /**
     * Setter method fro $mailTitle.
     *
     * @param string $title The mail title text.
     */
    public function setMailTitle($title)
    {
        $this->_mailTitle = $title;
    }
    
    private $_replyToAttribute = null;
    
    /**
     * Getter method for replyToAttribute
     *
     * @return string Returns the auto evaled replyToAttribute or used the value from setter method.
     */
    public function getReplyToAttribute()
    {
        if ($this->_replyToAttribute === null) {
            if ($this->sendToUserEmail) {
                $this->_replyToAttribute = $this->sendToUserEmail;
            } else {
                // try to auto detected email attribute from attributes last
                foreach (['email', 'mail', 'emailaddresse', 'email_address'] as $mail) {
                    if (in_array($mail, $this->attributes)) {
                        $this->_replyToAttribute = $mail;
                        break;
                    }
                }
            }
        }
        
        return $this->_replyToAttribute;
    }
    
    /**
     * Setter method for replyToAttribute.
     *
     * @param string $attributeName The reply to attribute name
     */
    public function setReplyToAttribute($attributeName)
    {
        $this->_replyToAttribute = $attributeName;
    }
    
    /**
     * Translation Method for Contact Form.
     *
     * @param string $message The message to translate.
     * @param array $params Parameters to pass to the translation message.
     * @return string
     */
    public static function t($message, array $params = [])
    {
        return Yii::t('contactform', $message, $params);
    }
}
