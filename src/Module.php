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
 * @property string|callable $mailTitle The mail title property. See {{setMailTitle()}}.
 * @property string|callable  $mailText  An optional mail text which is displayed above the table with the form values. See {{setMailText()}}.
 * @property string $replyToAttribute Returns the attribute which should be used to set the replyTo adresse. If not found it trys to detected. Otherwise null.
 * If the `$sendToUserEmail` attribute is set, it will take this attribute field to set the reply to.
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
class Module extends \luya\base\Module
{
    /**
     * @var boolean By default this module will lookup the view files in the appliation view folder instead of
     * the module base path views folder.
     */
    public $useAppViewPath = true;
    
    /**
     * @var string|array You can define a model class which is used instead of $attributes, $attributeLabels and $rules
     * is defined those properties has no effect `$attributes`, `$rules` and `$attributeLabels`.
     * 
     * ```php
     * 'modelClass' => 'app\models\MyFormModel',
     * ```
     * The model must be an instance of {{yii\base\Model}}.
     * 
     * @since 1.0.11
     */
    public $modelClass;

    /**
     * @var array An array containing all the attributes for this model
     *
     * ```
     * 'attributes' => ['name', 'email', 'street', 'city', 'tel', 'message'],
     * ```
     */
    public $attributes;
    
    /**
     * @var array An array of detail view attributes based to the {{yii\widgets\DetailView::attributes}} in order to
     * customize the mail table which is rendered trough {{yii\widgets\DetailView}}. If no value is provided,
     * the $attributes property will be take by default in order to generate the DetailView for the Email.
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
    public $callback;
    
    /**
     * @var array|string|callable An array or string with all recipients the mail should be sent on success, recipients will be assigned via
     * {{\luya\components\Mail::addresses()}} method of the mailer function. Since version 1.0.10 its also possible
     * to provide a callable function which must return a string or an array which is then passed to adresses().
     * 
     * ```php
     * 'recipients' => function($model) {
     *     if ($model->xyz) {
     *         return 'thisrecipient@luya.io';
     *     }
     * 
     *     return 'anotherrecipient@luya.io';
     * }
     * ```
     */
    public $recipients;
    
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

    private $_mailText;

    /**
     * An optional mail text which is displayed above the table with the form values. The text will be parsed with markdown and is therfore enclosed with a <p> tag.
     * 
     * An example of how to use markdown and newlines in a string:
     * 
     * ```php
     * 'mailText' => "## Hello\nParagraph\n+ foo\n+ bar",
     * ```
     * 
     * Which would be equals to:
     * 
     * ```php
     * 'mailText' => '
     * ## Hello
     * Paragraph
     * + foo
     * + bar
     * ```
     * 
     * And would renderd in the email as followed:
     * 
     * ```php
     * <h2>Hello</h2>
     * <p>Paragraph</p>
     * <ul>
     *     <li>foo</li>
     *     <li>bar</li>
     * </ul>
     * ```
     * 
     * @param string|callable $mailText Mail text
     * @since 1.0.11
     */
    public function setMailText($mailText)
    {
        $this->_mailText = $mailText;
    }

    /**
     * Getter method of mailtext.
     * 
     * @since 1.0.11
     */
    public function getMailText()
    {
        if (is_callable($this->_mailText)) {
            return call_user_func($this->_mailText);
        }

        return $this->_mailText;
    }
    
    /**
     * @var string An optional text which is displayed as footer in the email message. The text will be parsed with markdown and is therfore enclosed with a <p> tag.
     * @see {{luya\contactform\Module::$mailText}}
     * @since 1.0.8
     */
    public $mailFooterText;
    
    /**
     * @var string The template which is used to render the email. Default template is `<h2>{title}</h2><p><i>{time}</i></p>{text}{table}{footer}` with variables:
     * + title: Value from $mailTitle
     * + time: Contains the timestamp of when the email is sent.
     * + text: Value from $mailText
     * + table: The attributes with the values from the user input.
     * + footer: Value from $mailFooterText
     * 
     * Keep in mind the {text} and {footer} variables will be parsed with {{luya\TagParsers::convertWithMarkdown()}} and is therefore enclosed with a <p> tag.
     * @since 1.0.8
     */
    public $mailTemplate = "<h2>{title}</h2><p><i>{time}</i></p>{text}{table}{footer}";
    
    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        
        if (!$this->modelClass && $this->attributes === null) {
            throw new Exception("The `attributes` property must be defined with an array of available attributes.");
        }
        
        if ($this->recipients === null) {
            throw new Exception("The `recipients` property must be defined with an array of recipients who will recieve an email.");
        }
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

        // if its a callable, evaluate the title when accessing.
        if (is_callable($this->_mailTitle)) {
            $this->_mailTitle = call_user_func($this->_mailTitle);
        }
         
        return $this->_mailTitle;
    }
    
    /**
     * Setter method fro $mailTitle.
     *
     * @param string|callable $title The mail title text.
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
                    if (isset($this->attributes) && in_array($mail, $this->attributes)) {
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
     * @inheritdoc
     */
    public static function onLoad()
    {
        self::registerTranslation('contactform', static::staticBasePath() . '/messages', [
            'contactform' => 'contactform.php',
        ]);
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
        return parent::baseT('contactform', $message, $params);
    }
}
