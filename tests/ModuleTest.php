<?php
use PHPUnit\Framework\TestCase;

use Piko\Tests\modules\test\TestModule;
use Piko\Tests\modules\test\sub\SubModule;
use Piko\Tests\modules\test\sub\til\SubtilModule;

use HttpSoft\Message\ServerRequestFactory;
use Piko\ModularApplication;

class ModuleTest extends TestCase
{
    public function testGetModule()
    {
        $module = new TestModule();
        $subModule = $module->getModule('sub');
        $this->assertInstanceOf(SubModule::class, $subModule);
        $this->assertSame($subModule, $module->getModule('sub'));

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

    public function testRunWithWrongController()
    {
        $factory = new ServerRequestFactory();
        $request = $factory->createServerRequest('GET', '/')
                           ->withAttribute('controller', 'wrong');

        $module = new TestModule();
        $exceptionMsg = 'Piko\Tests\modules\test\controllers\WrongController is not instance of Piko\Controller';

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage($exceptionMsg);
        $module->handle($request);
    }

    public function testRunWithCustomControllerMap()
    {
        $factory = new ServerRequestFactory();
        $request = $factory->createServerRequest('GET', '/')
                           ->withAttribute('controller', 'blog')
                           ->withAttribute('action', 'index');

        $module = new TestModule([
            'controllerMap' => [
                'blog' => 'Piko\Tests\modules\test\controllers\TestController'
            ]
        ]);
        $module->setApplication(new ModularApplication([
            'components' => [
                PDO::class => [
                    'construct' => [
                        'sqlite::memory:'
                    ]
                ],
            ],
        ]));

        $response = $module->handle($request);

        $this->assertEquals(
            'TestModule::TestController::indexAction',
            (string) $response->getBody()
        );
    }

    public function testRunWithNonExistentController()
    {
        $factory = new ServerRequestFactory();
        $request = $factory->createServerRequest('GET', '/')
                           ->withAttribute('controller', 'blog')
                           ->withAttribute('action', 'index');

        $module = new TestModule([
            'controllerMap' => [
                'blog' => 'Piko\Tests\modules\test\controllers\TestNonExistentController'
            ]
        ]);
        $exceptionMsg = 'Controller class \'Piko\Tests\modules\test\controllers\TestNonExistentController\' does not exist.';

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage($exceptionMsg);
        $module->handle($request);
    }
}
