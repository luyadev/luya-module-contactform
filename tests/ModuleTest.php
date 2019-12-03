<?php

namespace luya\contactform\tests;

use luya\contactform\Module;
use luya\contactform\tests\data\ModelClassModel;
use luya\testsuite\cases\WebApplicationTestCase;
use yii\base\InvalidConfigException;

class ModuleTest extends WebApplicationTestCase
{
    public function getConfigArray()
    {
        return [
            'id' => 'module',
            'basePath' => dirname(__DIR__),
        ];
    }

    public function testInvalidConfig()
    {
        $this->expectException(InvalidConfigException::class);
        new Module('1');
    }

    public function testInvalidRecipients()
    {
        $this->expectException(InvalidConfigException::class);
        new Module('1', null, ['modelClass' => 'path\to\class']);
    }

    public function testGetModel()
    {
        $module = new Module('1', null, [
            'attributes' => ['foo', 'bar'],
            'recipients' => 'john@doe.com',
        ]);

        $this->assertSame(['foo', 'bar'], $module->model->attributes());
    }

    public function testModelClass()
    {
        $module = new Module('1', null, [
            'modelClass' => ModelClassModel::class,
            'recipients' => 'john@luya.io',
        ]);

        $this->assertSame('mail', $module->getReplyToAttribute());
    }

    public function testModelRules()
    {
        $module = new Module('1', null, [
            'attributes' => ['foo', 'bar'],
            'recipients' => 'john@doe.com',
            'rules' => [
                ['foo', 'string']
            ]
        ]);

        $this->assertNotEmpty($module->model->getValidators());

        $module = new Module('1', null, [
            'attributes' => ['foo', 'bar'],
            'recipients' => 'john@doe.com',
            'rules' => [
                ['foo']
            ]
        ]);

        $this->expectException(InvalidConfigException::class);
        $module->model->getValidators();
    }
}
