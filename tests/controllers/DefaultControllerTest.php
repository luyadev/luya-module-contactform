<?php

namespace luya\contactform\tests\controller;

use Yii;
use luya\testsuite\cases\WebApplicationTestCase;
use luya\contactform\controllers\DefaultController;
use luya\contactform\Module;

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
            ]
        ];
    }
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
}
