<?php
use PHPUnit\Framework\TestCase;

use piko\Piko;
use piko\Application;
use piko\HttpException;
use piko\Router;
use piko\View;

class ApplicationTest extends TestCase
{
    protected function tearDown(): void
    {
        Piko::reset();
    }

    public function testRunWithEmptyConfiguration()
    {
        $_SERVER['REQUEST_URI'] = '/';

        $app = new Application([]);

        $this->assertInstanceOf(Router::class, $app->getRouter());
        $this->assertInstanceOf(View::class, $app->getView());
        $this->assertNull($app->getUser());

        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('Route not defined');
        $this->expectExceptionCode(500);

        $app->run();
    }

    public function testRun()
    {
        $config = [
            'basePath' => __DIR__,
            'components' => [
                'router' => [
                    'class' => 'piko\Router',
                    'routes' => [
                        '^/$' => 'test/test/index',
                        '^/user/(\d+)' => 'test/test/index3|id=$1',
                        '^/test/sub/til/(\w+)/(\w+)' => 'test/sub/til/$1/$2',
                        '^/test/sub/(\w+)/(\w+)' => 'test/sub/$1/$2',
                        '^/(\w+)/(\w+)/(\w+)' => '$1/$2/$3'
                    ],
                ]
            ],
            'modules' => [
                'test' => 'tests\modules\test\TestModule',
            ],
            'bootstrap' => ['test'],
        ];

        $_SERVER['SCRIPT_NAME'] = '';
        $_SERVER['SCRIPT_FILENAME'] = '';
        $_SERVER['DOCUMENT_ROOT'] = '';

        new Application($config);

        $_SERVER['REQUEST_URI'] = '/';

        ob_start();
        Piko::$app->run();
        $this->assertEquals('index Action', ob_get_clean());

        // Test if TestModule::bootstrap() has been called
        $this->assertEquals(Piko::get('TestModule::bootstrap'), true);

        $_SERVER['REQUEST_URI'] = '/user/55';

        ob_start();
        Piko::$app->run();
        $this->assertEquals('55', ob_get_clean());

        $_SERVER['REQUEST_URI'] = '/test/sub/test/index';

        ob_start();
        Piko::$app->run();
        $this->assertEquals('TestModule::SubModule::TestController::indexAction', ob_get_clean());

        $_SERVER['REQUEST_URI'] = '/test/sub/til/test/index';

        ob_start();
        Piko::$app->run();
        $this->assertEquals('TestModule::SubModule::SubtilModule::TestController::indexAction',  ob_get_clean());
    }

    public function testErrorRoute()
    {
        $config = [
            'errorRoute' => 'test/test/error',
            'modules' => [
                'test' => 'tests\modules\test\TestModule'
            ]
        ];

        $_SERVER['REQUEST_URI'] = '/';

        $app = new Application($config);

        ob_start();
        $app->run();
        $output = ob_get_clean();

        $this->assertEquals('Route not defined', $output);
    }

    public function testDefaultLayout()
    {
        $config = [
            'basePath' => __DIR__,
            'components' => [
                'router' => [
                    'class' => 'piko\Router',
                    'routes' => [
                        '^/$' => 'test/test/index2',
                    ],
                ]
            ],
            'modules' => [
                'test' => 'tests\modules\test\TestModule'
            ]
        ];

        $_SERVER['REQUEST_URI'] = '/';

        $app = new Application($config);

        ob_start();
        $app->run();
        $output = ob_get_clean();

        $this->assertMatchesRegularExpression('~<!DOCTYPE html>~', $output);
        $this->assertMatchesRegularExpression('~index2 Action~', $output);
    }

    public function testUndeclaredModule()
    {
        $config = [
            'components' => [
                'router' => [
                    'class' => 'piko\Router',
                    'routes' => [
                        '^/$' => 'blog/index/index',
                    ],
                ]
            ],
        ];

        $_SERVER['REQUEST_URI'] = '/';

        $app = new Application($config);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Configuration not found for module blog.');

        $app->run();
    }

    public function testIncompleteRoutes()
    {
        $config = [
            'modules' => [
                'test' => [
                    'class' => 'tests\modules\test\TestModule',
                    'controllerMap' => [
                        'index' => 'tests\modules\test\controllers\TestController'
                    ]
                ]
            ]
        ];

        $app = new Application($config);
        $output = $app->dispatch('test/index'); // Internal route should be test/index/index
        $this->assertEquals('index Action', $output);

        $output = $app->dispatch('test'); // Internal route should be test/index/index
        $this->assertEquals('index Action', $output);
    }

    public function testSubmoduleRoute()
    {
        $config = [
            'modules' => [
                'test' => 'tests\modules\test\TestModule'
            ]
        ];

        $app = new Application($config);
        $output = $app->dispatch('test/sub/test/index');
        $this->assertEquals('TestModule::SubModule::TestController::indexAction', $output);

        $output = $app->dispatch('test/sub/til/test/index');
        $this->assertEquals('TestModule::SubModule::SubtilModule::TestController::indexAction', $output);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testHeaders()
    {
        $config = [
            'components' => [
                'router' => [
                    'class' => 'piko\Router',
                    'routes' => [
                        '^/$' => 'test/test/index',
                    ],
                ]
            ],
            'modules' => [
                'test' => 'tests\modules\test\TestModule'
            ]
        ];

        $_SERVER['REQUEST_URI'] = '/';

        $app = new Application($config);
        $app->setHeader('Location: /test');
        $app->setHeader(' Content-Type :application/json ');

        $app->run();

        $this->assertContains('Location:/test', xdebug_get_headers());
        $this->assertContains('Content-Type:application/json', xdebug_get_headers());
    }
}
