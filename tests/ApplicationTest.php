<?php
use PHPUnit\Framework\TestCase;

use piko\Piko;
use piko\Application;
use piko\HttpException;
use piko\Router;
use piko\View;

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

    public function testAppAlias()
    {
        new Application(self::CONFIG);

        $this->assertEquals(__DIR__, Piko::getAlias('@app'));
    }

    public function testRunWithEmptyConfiguration()
    {
        $_SERVER['REQUEST_URI'] = '/';

        $app = new Application([]);

        $this->assertInstanceOf(Router::class, $app->getRouter());
        $this->assertInstanceOf(View::class, $app->getView());

        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('Route not defined');
        $this->expectExceptionCode(500);

        $app->run();
    }

    public function testRunWithWrongModuleType()
    {
        $_SERVER['REQUEST_URI'] = '/';

        $config = self::CONFIG;
        $config['modules']['test'] = 'tests\modules\test\models\ContactForm';

        $app = new Application($config);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('module test must be instance of Module');

        $app->run();
    }

    public function testDefaultRun()
    {
        $this->expectOutputString('TestModule::TestController::indexAction');
        $_SERVER['REQUEST_URI'] = '/';
        (new Application(self::CONFIG))->run();

        // Check if TestModule::bootstrap() has been called
        $this->assertTrue(Piko::get('TestModule::bootstrap'));
    }

    public function testRunWithUriParameter()
    {
        $this->expectOutputString('55');
        $_SERVER['REQUEST_URI'] = '/user/55';
        (new Application(self::CONFIG))->run();
    }

    public function testRunWithUriParameters()
    {
        $this->expectOutputString('5.33/60.54');
        $_SERVER['REQUEST_URI'] = '/location/5.33/60.54/1';
        (new Application(self::CONFIG))->run();
    }

    public function testRunWithSubModule()
    {
        $this->expectOutputString('TestModule::SubModule::TestController::indexAction');
        $_SERVER['REQUEST_URI'] = '/test/sub/test/index';
        (new Application(self::CONFIG))->run();
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testErrorRoute()
    {
        $this->expectOutputString('Route not defined');
        $_SERVER['REQUEST_URI'] = '/forum';
        (new Application(self::CONFIG))->run();
        $this->assertContains('HTTP/1.1 500 Route not defined', Application::$instance->headers);
    }

    public function testDefaultLayout()
    {
        $this->expectOutputRegex('~<!DOCTYPE html>~');
        $this->expectOutputRegex('~TestModule::TestController::index2Action~');

        $_SERVER['REQUEST_URI'] = 'test/test/index2';
        (new Application(self::CONFIG))->run();
    }

    public function testUndeclaredModule()
    {
        $this->expectOutputString('Configuration not found for module blog.');
        $_SERVER['REQUEST_URI'] = 'blog/index/index';
        (new Application(self::CONFIG))->run();
    }

    public function testIncompleteRoutes()
    {
        $app = new Application(self::CONFIG);

        // Internal route should be test/test/index
        $this->assertEquals('TestModule::TestController::indexAction', $app->dispatch('test/test'));

        // Internal route should be test/index/index
        $this->assertEquals('TestModule::IndexController::indexAction', $app->dispatch('test'));
    }

    public function testSubmoduleRoutes()
    {
        $app = new Application(self::CONFIG);

        $this->assertEquals(
            'TestModule::SubModule::TestController::indexAction',
            $app->dispatch('test/sub/test/index')
        );

        $this->assertEquals(
            'TestModule::SubModule::SubtilModule::TestController::indexAction',
            $app->dispatch('test/sub/til/test/index')
        );
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testHeaders()
    {
        $_SERVER['REQUEST_URI'] = '/';

        $app = new Application(self::CONFIG);
        $app->setHeader('Location', '/test');
        $app->setHeader(' Content-Type :application/json ');

        ob_start();
        $app->run();
        ob_end_clean();

        $headers = xdebug_get_headers();

        $this->assertContains('Location: /test', $headers);
        $this->assertContains('Content-Type: application/json', $headers);
    }
}
