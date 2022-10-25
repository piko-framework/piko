<?php
use PHPUnit\Framework\TestCase;

use tests\modules\test\TestModule;
use tests\modules\test\sub\SubModule;
use tests\modules\test\sub\til\SubtilModule;

use HttpSoft\Message\ServerRequestFactory;

class ModuleTest extends TestCase
{
    public function testGetModule()
    {
        $module = new TestModule();
        $subModule = $module->getModule('sub');
        $this->assertInstanceOf(SubModule::class, $subModule);
        $subtilModule = $subModule->getModule('til');
        $this->assertInstanceOf(SubtilModule::class, $subtilModule);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Configuration not found for sub module tal.');
        $module->getModule('tal');
    }

    public function testGetWrongModule()
    {
        $module = new TestModule();
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('module form must be instance of Module');
        $module->getModule('form');
    }

    public function testRunWithCustomControllerMap()
    {
        $factory = new ServerRequestFactory();
        $request = $factory->createServerRequest('GET', '/')
                           ->withAttribute('controller', 'blog')
                           ->withAttribute('action', 'index');

        $module = new TestModule([
            'controllerMap' => [
                'blog' => 'tests\modules\test\controllers\TestController'
            ]
        ]);

        $response = $module->handle($request);

        $this->assertEquals(
            'TestModule::TestController::indexAction',
            (string) $response->getBody()
        );
    }
}
