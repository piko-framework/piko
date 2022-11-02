<?php

use HttpSoft\Message\ServerRequestFactory;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Piko\Application;
use Piko\Application\BootstrapMiddleware;
use Piko\Application\RoutingMiddleware;
use Piko\HttpException;
use Piko\Router;
use Piko\View;

class ApplicationTest extends TestCase
{
    /**
     * @var Application
     */
    protected $app;

    protected function setUp(): void
    {
        $this->app = new Application([
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
        $app = new Application([]);

        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('Not Found');
        $this->expectExceptionCode(404);

        $app->run();
    }

    public function testRunWithEmptyConfigurationAndRoutingMiddleware()
    {
        $app = new Application([
            'components' => [
                Router::class => new Router([])
            ]
        ]);

        $app->pipe(new RoutingMiddleware($app));
        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('Not Found');
        $this->expectExceptionCode(404);
        $app->run();
    }

    public function testRunWithWrongModuleType()
    {
        $this->app->pipe(new RoutingMiddleware($this->app));
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Module wrong must be instance of \Piko\Module');
        $this->app->run($this->createRequest('/wrong'));
    }

    public function testGetApplicationFromModule()
    {
        $module = $this->app->getModule('test');
        $this->assertInstanceOf(Application::class, $module->getApplication());
    }

    public function testNonRegisterdComponent()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('DateTime is not registered as component');
        $this->app->getComponent(DateTime::class);
    }

    public function testDefaultRun()
    {
        $this->app->pipe(new BootstrapMiddleware($this->app));
        $this->app->pipe(new RoutingMiddleware($this->app));
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
        $this->app->run($this->createRequest('/testwrongexception'), false);
    }

    public function testErrorHandlerWithErrorRouteUsingWrongException()
    {
        $this->app->errorRoute = 'test/default/error';
        $this->app->pipe(new tests\middleware\TestMiddleware($this->app));
        $this->expectOutputString('Exception must be instance of Throwable');
        $this->app->run($this->createRequest('/testwrongexception'), false);
    }

    public function testRunWithUriParameter()
    {
        $this->app->pipe(new RoutingMiddleware($this->app));
        $this->expectOutputString('55');
        $this->app->run($this->createRequest('/user/55'), false);
    }

    public function testRunWithUriParameters()
    {
        $this->app->pipe(new RoutingMiddleware($this->app));
        $this->expectOutputString('5.33/60.54');
        $this->app->run($this->createRequest('/location/5.33/60.54/1'), false);
    }

    public function testRunWithSubModule()
    {
        $this->app->pipe(new RoutingMiddleware($this->app));
        $this->expectOutputString('TestModule::SubModule::TestController::indexAction');
        $this->app->run($this->createRequest('/test/sub/test/index'), false);
    }

    public function testDefaultLayout()
    {
        $this->app->pipe(new RoutingMiddleware($this->app));
        $this->expectOutputRegex('~<!DOCTYPE html>~');
        $this->expectOutputRegex('~TestModule::TestController::index2Action~');
        $this->app->run($this->createRequest('/test/test/index2'), false);
    }

    public function testUndeclaredModule()
    {
        $this->app->pipe(new RoutingMiddleware($this->app));
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Configuration not found for module blog.');
        $this->app->run($this->createRequest('/blog'), false);
    }

    public function testIncompleteRoute1()
    {
        $this->app->pipe(new RoutingMiddleware($this->app));
        $this->expectOutputString('TestModule::TestController::indexAction');
        $this->app->run($this->createRequest('/test/test'), false);
    }

    public function testIncompleteRoute2()
    {
        $this->app->pipe(new RoutingMiddleware($this->app));
        $this->expectOutputString('TestModule::DefaultController::indexAction');
        $this->app->run($this->createRequest('/test'), false);
    }

    public function testSubmoduleRoutes1()
    {
        $this->app->pipe(new RoutingMiddleware($this->app));
        $this->expectOutputString('TestModule::SubModule::TestController::indexAction');
        $this->app->run($this->createRequest('/test/sub/test/index'), false);
    }

    public function testSubmoduleRoutes2()
    {
        $this->app->pipe(new RoutingMiddleware($this->app));
        $this->expectOutputString('TestModule::SubModule::SubtilModule::TestController::indexAction');
        $this->app->run($this->createRequest('/test/sub/til/test/index'), false);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testHeaders()
    {
        $this->app->pipe(new RoutingMiddleware($this->app));

        ob_start();
        $this->app->run($this->createRequest('/test/index/json-response'));
        ob_end_clean();

        $headers = xdebug_get_headers();
        $this->assertContains('Content-Type: application/json', $headers);
    }
}
