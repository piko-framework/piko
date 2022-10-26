<?php

use HttpSoft\Message\ServerRequestFactory;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Piko\Application;
use Piko\HttpException;
use Piko\Piko;
use Piko\Router;
use Piko\View;

class ApplicationTest extends TestCase
{
    const CONFIG = [
        'basePath' => __DIR__,
        'errorRoute' => 'test/test/error',
        'components' => [
            'router' => [
                'class' => 'piko\Router',
                'routes' => [
                    '/' => 'test/test/index',
                    '/user/:id' => 'test/test/index3',
                    '/location/:lat/:lng/:coordinates' => 'test/test/index4',
                    '/test/sub/:controller/:action' => 'test/sub/:controller/:action',
                    '/:module/:controller/:action' => ':module/:controller/:action',
                ],
            ]
        ],
        'modules' => [
            'test' => 'tests\modules\test\TestModule',
        ],
        'bootstrap' => ['test'],
    ];

    protected function setUp(): void
    {
        $_SERVER['SCRIPT_NAME'] = '';
        $_SERVER['SCRIPT_FILENAME'] = '';
        $_SERVER['DOCUMENT_ROOT'] = '';
    }

    protected function tearDown(): void
    {
        Piko::reset();
    }

    protected function createRequest($uri, $method = 'GET', $serverParams = []): ServerRequestInterface
    {
        $factory = new ServerRequestFactory();

        $defaultServerParams = [
            'SCRIPT_NAME' => '',
            'SCRIPT_FILENAME' => '',
            'DOCUMENT_ROOT' => '',
        ];

        $serverParams = array_merge($defaultServerParams, $serverParams);

        $request = $factory->createServerRequest($method, $uri, $serverParams);

        return $request;
    }

    public function testAppAlias()
    {
        new Application(self::CONFIG);

        $this->assertEquals(__DIR__, Piko::getAlias('@app'));
    }

    public function testRunWithEmptyConfiguration()
    {
        $app = new Application([]);

        $this->assertInstanceOf(Router::class, $app->getRouter());
        $this->assertInstanceOf(View::class, $app->getView());

        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('Not Found');
        $this->expectExceptionCode(404);

        $app->run();
    }

    public function testRunWithWrongModuleType()
    {
        $config = self::CONFIG;
        $config['modules']['test'] = 'tests\modules\test\models\ContactForm';

        $app = new Application($config);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('module test must be instance of piko\Module');

        $app->run($this->createRequest('/'));
    }

    public function testDefaultRun()
    {
        $this->expectOutputString('TestModule::TestController::indexAction');
        (new Application(self::CONFIG))->run($this->createRequest('/'), false);

        // Check if TestModule::bootstrap() has been called
        $this->assertTrue(Piko::get('TestModule::bootstrap'));
    }

    public function testMiddleware()
    {
        $app = new Application(self::CONFIG);
        $app->pipe(new tests\middleware\TestMiddleware());

        $this->expectOutputString('Test middleware response');
        $app->run($this->createRequest('/testmiddleware'), false);
    }

    public function testErrorHandlerUsingWrongException()
    {
        $app = new Application(self::CONFIG);
        $app->pipe(new tests\middleware\TestMiddleware());
        $this->expectOutputString('Exception must be instance of Throwable');
        $app->run($this->createRequest('/testwrongexception'), false);
    }

    public function testRunWithUriParameter()
    {
        $this->expectOutputString('55');
        (new Application(self::CONFIG))->run($this->createRequest('/user/55'), false);
    }

    public function testRunWithUriParameters()
    {
        $this->expectOutputString('5.33/60.54');
        (new Application(self::CONFIG))->run($this->createRequest('/location/5.33/60.54/1'), false);
    }

    public function testRunWithSubModule()
    {
        $this->expectOutputString('TestModule::SubModule::TestController::indexAction');
        (new Application(self::CONFIG))->run($this->createRequest('/test/sub/test/index'), false);
    }

    public function testDefaultLayout()
    {
        $this->expectOutputRegex('~<!DOCTYPE html>~');
        $this->expectOutputRegex('~TestModule::TestController::index2Action~');
        (new Application(self::CONFIG))->run($this->createRequest('/test/test/index2'), false);
    }

    public function testUndeclaredModule()
    {
        $this->expectOutputString('Configuration not found for module blog.');
        (new Application(self::CONFIG))->run($this->createRequest('/blog'), false);
    }

    public function testIncompleteRoute1()
    {
        $this->expectOutputString('TestModule::TestController::indexAction');
        (new Application(self::CONFIG))->run($this->createRequest('/test/test'), false);
    }

    public function testIncompleteRoute2()
    {
        $this->expectOutputString('TestModule::DefaultController::indexAction');
        (new Application(self::CONFIG))->run($this->createRequest('/test'), false);
    }

    public function testSubmoduleRoutes1()
    {
        $this->expectOutputString('TestModule::SubModule::TestController::indexAction');
        (new Application(self::CONFIG))->run($this->createRequest('/test/sub/test/index'), false);
    }

    public function testSubmoduleRoutes2()
    {
        $this->expectOutputString('TestModule::SubModule::SubtilModule::TestController::indexAction');
        (new Application(self::CONFIG))->run($this->createRequest('/test/sub/til/test/index'), false);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testHeaders()
    {
        ob_start();
        (new Application(self::CONFIG))->run($this->createRequest('/test/index/json-response'));
        ob_end_clean();

        $headers = xdebug_get_headers();
        $this->assertContains('Content-Type: application/json', $headers);
    }
}
