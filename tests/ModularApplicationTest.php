<?php

use HttpSoft\Message\ServerRequestFactory;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Piko\ModularApplication;
use Piko\HttpException;
use Piko\Router;
use Piko\View;

class ModularApplicationTest extends TestCase
{
    /**
     * @var ModularApplication
     */
    protected $app;

    protected function setUp(): void
    {
        $this->app = new ModularApplication([
            'basePath' => __DIR__,
            'components' => [
                View::class => [],
                Router::class => [
                    'construct' => [
                        [
                            'routes' => [
                                '/' => 'test/test/index',
                                '/user/:id' => 'test/test/index3',
                                '/location/:lat/:lng/:coordinates' => 'test/test/index4',
                                '/test/sub/:controller/:action' => 'test/sub/:controller/:action',
                                '/:module/:controller/:action' => ':module/:controller/:action',
                            ],
                        ]
                    ]
                ]
            ],
            'modules' => [
                'test' => 'tests\modules\test\TestModule',
                'wrong' =>'tests\modules\test\models\ContactForm',
            ],
            'bootstrap' => ['test'],
        ]);
    }

    protected function tearDown(): void
    {
        Piko::reset();
    }

    protected function createRequest($uri, $method = 'GET', $serverParams = []): ServerRequestInterface
    {
        $defaultServerParams = [
            'SCRIPT_NAME' => '',
            'SCRIPT_FILENAME' => '',
            'DOCUMENT_ROOT' => '',
        ];

        $serverParams = array_merge($defaultServerParams, $serverParams);

        return (new ServerRequestFactory())->createServerRequest($method, $uri, $serverParams);
    }

    public function testAppAlias()
    {
        $this->assertEquals(__DIR__, Piko::getAlias('@app'));
    }

    public function testRunWithEmptyConfiguration()
    {
        $app = new ModularApplication([]);
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Piko\Router is not registered as component');
        $app->run();
    }

    public function testRunWithEmptyConfigurationAndRouterComponent()
    {
        $app = new ModularApplication([
            'components' => [
                Router::class => []
            ]
        ]);

        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('Not Found');
        $this->expectExceptionCode(404);
        $app->run();
    }

    public function testRunWithWrongModuleType()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Module wrong must be instance of \Piko\Module');
        $this->app->run($this->createRequest('/wrong'));
    }

    public function testUniqueModuleInstance()
    {
        $module1 = $this->app->getModule('test');
        $module2 = $this->app->getModule('test');
        $this->assertSame(spl_object_hash($module1), spl_object_hash($module2));
    }

    public function testGetApplicationFromModule()
    {
        $module = $this->app->getModule('test');
        $this->assertInstanceOf(ModularApplication::class, $module->getApplication());
    }

    public function testDefaultRun()
    {
        $this->expectOutputString('TestModule::TestController::indexAction');
        $this->app->run($this->createRequest('/'), false);

        // Check if TestModule::bootstrap() has been called
        $this->assertEquals('fr', $this->app->language);
    }

    public function testCustomMiddleware()
    {
        $this->app->pipe(new tests\middleware\TestMiddleware($this->app));
        $this->expectOutputString('Test middleware response');
        $this->app->run($this->createRequest('/testmiddleware'), false);
    }

    public function testErrorHandlerUsingWrongException()
    {
        $this->app->pipe(new tests\middleware\TestMiddleware($this->app));
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Exception must be instance of Throwable');
        $this->app->run($this->createRequest('/test-error-handler-wrong-exception'), false);
    }

    public function testErrorHandlerWithErrorRouteUsingWrongException()
    {
        $this->app->errorRoute = 'test/default/error';
        $this->app->pipe(new tests\middleware\TestMiddleware($this->app));
        $this->expectOutputString('Exception must be instance of Throwable');
        $this->app->run($this->createRequest('/test-error-handler-wrong-exception'), false);
    }

    public function testRunWithUriParameter()
    {
        $this->expectOutputString('55');
        $this->app->run($this->createRequest('/user/55'), false);
    }

    public function testRunWithUriParameters()
    {
        $this->expectOutputString('5.33/60.54');
        $this->app->run($this->createRequest('/location/5.33/60.54/1'), false);
    }

    public function testRunWithSubModule()
    {
        $this->expectOutputString('TestModule::SubModule::TestController::indexAction');
        $this->app->run($this->createRequest('/test/sub/test/index'), false);
    }

    public function testDefaultLayout()
    {
        $this->expectOutputRegex('~<!DOCTYPE html>~');
        $this->expectOutputRegex('~TestModule::TestController::index2Action~');
        $this->app->run($this->createRequest('/test/test/index2'), false);
    }

    public function testUndeclaredModule()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Configuration not found for module blog.');
        $this->app->run($this->createRequest('/blog'), false);
    }

    public function testIncompleteRoute1()
    {
        $this->expectOutputString('TestModule::TestController::indexAction');
        $this->app->run($this->createRequest('/test/test'), false);
    }

    public function testIncompleteRoute2()
    {
        $this->expectOutputString('TestModule::DefaultController::indexAction');
        $this->app->run($this->createRequest('/test'), false);
    }

    public function testSubmoduleRoutes1()
    {
        $this->expectOutputString('TestModule::SubModule::TestController::indexAction');
        $this->app->run($this->createRequest('/test/sub/test/index'), false);
    }

    public function testSubmoduleRoutes2()
    {
        $this->expectOutputString('TestModule::SubModule::SubtilModule::TestController::indexAction');
        $this->app->run($this->createRequest('/test/sub/til/test/index'), false);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testHeaders()
    {
        ob_start();
        $this->app->run($this->createRequest('/test/index/json-response'));
        ob_end_clean();

        $headers = xdebug_get_headers();
        $this->assertContains('Content-Type: application/json', $headers);
    }
}
