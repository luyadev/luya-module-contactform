<?php

namespace luya\contactform\tests\controller;

use Yii;
use luya\testsuite\cases\WebApplicationTestCase;
use luya\contactform\controllers\DefaultController;
use luya\contactform\Module;
use luya\base\DynamicModel;

class DefaultControllerTest extends WebApplicationTestCase
{
    public function getConfigArray()
    {
        return [
            'id' => 'test',
            'basePath' => dirname(__DIR__),
            'modules' => [
                'contactform' => [
                    'class' => 'luya\contactform\Module',
                    'attributes' => ['firstname', 'lastname', 'email'],
                    'recipients' => ['test@luya.io'],
                ],
                'contactformstring' => [
                    'class' => 'luya\contactform\Module',
                    'attributes' => ['firstname', 'lastname', 'email'],
                    'recipients' => 'test@luya.io',
                ],
                'contactcallable' => [
                    'class' => 'luya\contactform\Module',
                    'attributes' => ['firstname', 'lastname', 'email'],
                    'recipients' => 'test@luya.io',
                    'mailText' => function () {
                        return 'callable text';
                    },
                    'mailTitle' => function () {
                        return 'callable title';
                    }
                ],
                'callableform' => [
                    'class' => 'luya\contactform\Module',
                    'attributes' => ['firstname', 'lastname', 'email'],
                    'recipients' => function ($model) {
                        if ($model->firstname == 'barfoo') {
                            return 'barfoo@luya.io';
                        }

                        return 'another@luya.io';
                    }
                ],
            ]
        ];
    }

    public function testStringRecipient()
    {
        $module = \Yii::$app->getModule('contactformstring');
        /** @var \luya\contactform\Module $module */
        
        $this->assertInstanceOf('luya\contactform\Module', $module);
        $this->assertSame('[LUYA Application] Contact Request', $module->mailTitle);
        
        $ctrl = new DefaultController('default', $module);
        $model = new DynamicModel(['firstname', 'lastname']);
        $this->assertSame(['test@luya.io'], $ctrl->ensureRecipients($model));
    }

    public function testClosureTextAndTitle()
    {
        $mod = new Module('contact', null, ['modelClass' => 'app/models/Stuff', 'recipients' => 'none']);
        $mod->mailTitle = function () {
            return 'mailtitle';
        };
        $mod->mailText = function () {
            return 'mailtext';
        };

        $this->assertSame('mailtitle', $mod->mailTitle);
        $this->assertSame('mailtext', $mod->mailText);
    }

    /**
     * @runInSeparateProcess
     */
    public function testCallableRecipients()
    {
        $module = \Yii::$app->getModule('callableform');
        /** @var \luya\contactform\Module $module */
        
        $this->assertInstanceOf('luya\contactform\Module', $module);
        $this->assertSame('[LUYA Application] Contact Request', $module->mailTitle);
        
        $ctrl = new DefaultController('default', $module);

        $model = new DynamicModel(['firstname', 'lastname']);
        $model->addRule(['firstname', 'lastname'], 'string');
        $model->firstname = 'john';
        $model->lastname = 'doe';
        $this->assertSame(['another@luya.io'], $ctrl->ensureRecipients($model));
        $model->firstname = 'barfoo';
        $this->assertSame(['barfoo@luya.io'], $ctrl->ensureRecipients($model));
    }
    
    /**
     * @runInSeparateProcess
     */
    public function testController()
    {
        $module = \Yii::$app->getModule('contactform');
        /** @var \luya\contactform\Module $module */
        
        $this->assertInstanceOf('luya\contactform\Module', $module);
        $this->assertSame('[LUYA Application] Contact Request', $module->mailTitle);
        
        $ctrl = new DefaultController('default', $module);
        $ctrl->layout = false;
        $this->assertSame('form', $ctrl->runAction('index'));
    }
    
    public function testModuleGetterSetter()
    {
        $module = \Yii::$app->getModule('contactform');
        /* @var \luya\contactform\Module $module */
        
        $module->attributes = ['foo'];
        
        $this->assertNull($module->replyToAttribute);
        
        $module->attributes = ['email', 'mail'];
        
        $this->assertSame('email', $module->replyToAttribute);
        
        $module->replyToAttribute = 'foobar';
        
        $this->assertSame('foobar', $module->replyToAttribute);
    }

    public function testTranslation()
    {
        $this->assertSame('foo', Module::t('foo'));
        
        $module = \Yii::$app->getModule('contactform');
        /* @var \luya\contactform\Module $module */
        
        Yii::$app->language = 'de';
        $this->assertSame('Kontakt Anfrage', Module::t('Contact Request'));
        $this->assertSame('[LUYA Application] Kontakt Anfrage', $module->mailTitle);
    }
    
    public function testEmailMessage()
    {
        $model = new DynamicModel(['foo']);
        $model->foo = 'bar';
        
        $mail = <<<EOT
<table id="w0" style="width:100%" cellpadding="5" cellpsacing="2" border="0"><tr><th width="150" style="border-bottom:1px solid #F0F0F0">Foo</th><td style="border-bottom:1px solid #F0F0F0">bar</td></tr></table>
EOT;
        
        $ctrl = new DefaultController('default', Yii::$app->getModule('contactform'));
        $this->assertContains($mail, $ctrl->generateMailMessage($model));
    }
    
    public function testEmailArrayMessage()
    {
        $model = new DynamicModel(['foo']);
        $model->foo = ['bar', 'foo'];
        
        $mail = <<<EOT
<table id="w1" style="width:100%" cellpadding="5" cellpsacing="2" border="0"><tr><th width="150" style="border-bottom:1px solid #F0F0F0">Foo</th><td style="border-bottom:1px solid #F0F0F0">bar, foo</td></tr></table>
EOT;
        
        $ctrl = new DefaultController('default', Yii::$app->getModule('contactform'));
        $this->assertContains($mail, $ctrl->generateMailMessage($model));
    }
    
    public function testGenerateMailAltBody()
    {
        $model = new DynamicModel(['foo', 'labelized']);
        $model->foo = ['bar', 'foo'];
        $model->labelized = 'Content';
        $model->attributeLabels = ['labelized' => 'Label for Field'];
        
        $ctrl = new DefaultController('default', Yii::$app->getModule('contactform'));
        // in order to see the brs in tests we wrap with nl2br.
        $altBody = $ctrl->generateMailAltBody($model);
        $this->assertContains('Foo: bar, foo
Label for Field: Content', $altBody);
    }
    
    public function testGenerateMailAltBodyWithAttributesLAbels()
    {
        $model = new DynamicModel(['foo']);
        $model->foo = false;
         
        $module = Yii::$app->getModule('contactform');
        $module->detailViewAttributes = ['foo:boolean:The Label'];
        
        $ctrl = new DefaultController('default', $module);
        // in order to see the brs in tests we wrap with nl2br.
        $altBody = $ctrl->generateMailAltBody($model);
        $this->assertContains('The Label: No', $altBody);
    }
    
    public function testModuleMailTemplateProperty()
    {
        $model = new DynamicModel(['foo']);
        $model->foo = 'bar';
        
        $module = Yii::$app->getModule('contactform');
        $module->mailTitle = '-';
        $module->mailTemplate = '<p>foo{title}bar</p>';
        $ctrl = new DefaultController('default', $module);
        $this->assertSame('<p>foo-bar</p>', $ctrl->generateMailMessage($model));
    }
    
    /**
     * @see https://github.com/luyadev/luya-module-contactform/issues/17
     */
    public function testGenericEmailTemplateOutput()
    {
        $model = new DynamicModel(['foo']);
        $model->foo = 'bar';
        
        $module = Yii::$app->getModule('contactform');
        $module->mailText = "## Hello\nParagraph\n+ foo\n+ bar";
        $ctrl = new DefaultController('default', $module);
        $this->assertContainsTrimmed('<h2>Hello</h2><p>Paragraph</p><ul><li>foo</li><li>bar</li></ul><table id="w5" style="width:100%" cellpadding="5" cellpsacing="2" border="0"><tr><th width="150" style="border-bottom:1px solid #F0F0F0">Foo</th><td style="border-bottom:1px solid #F0F0F0">bar</td></tr></table>', $ctrl->generateMailMessage($model));
    }

    public function testMailerObjects()
    {
        $module = new Module('contactform', null, [
            'attributes' => ['foo', 'bar', 'email'],
            'recipients' => 'admin@luya.io',
            'replyToAttribute' => 'email',
            'sendToUserEmail' => 'email',
            'rules' => [
                [['foo', 'bar', 'email'], 'string']
            ]
        ]);

        $ctrl = new DefaultController('mailer', $module);

        $model = $module->getModel();
        $model->foo = '1';
        $model->bar = '2';
        $model->email = 'user@luya.io';

        $r = $ctrl->composeAdminEmail($model);

        $this->assertSame([
            'user@luya.io' => ['user@luya.io', 'user@luya.io']
        ], $r->mailer->getReplyToAddresses());
        $this->assertSame([
            ['admin@luya.io', 'admin@luya.io']
        ], $r->mailer->getToAddresses());


        $r = $ctrl->composeUserEmail($model);

        $this->assertSame([], $r->mailer->getReplyToAddresses());
        $this->assertSame([
            ['user@luya.io', 'user@luya.io']
        ], $r->mailer->getToAddresses());
    }

    public function testMailerError()
    {
        $module = new Module('contactform', null, [
            'attributes' => ['foo'],
            'recipients' => 'admin@luya.io',
            'rules' => [
                [['foo'], 'string']
            ]
        ]);

        $ctrl = new DefaultController('mailer', $module);

        Yii::$app->request->setBodyParams([
            'DynamicModel' => ['foo' => 'bar'],
        ]);

        $this->expectException('yii\base\InvalidConfigException', 'Unable to send email, maybe the mail component is not setup properly in the config (SMTP connect() failed. https://github.com/PHPMailer/PHPMailer/wiki/Troubleshooting).');
        $action = $ctrl->actionIndex();

        var_dump($action);
    }
}
