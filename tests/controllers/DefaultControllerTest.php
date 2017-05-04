<?php

namespace luya\contactform\tests\controller;

use Yii;
use luya\testsuite\cases\WebApplicationTestCase;
use luya\contactform\controllers\DefaultController;

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
}