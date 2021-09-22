<?php
use PHPUnit\Framework\TestCase;

use piko\Piko;
use piko\Application;

class ApplicationTest extends TestCase
{
    protected function tearDown(): void
    {
        Piko::reset();
    }

    public function testRoutes()
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
            ]
        ];

        $_SERVER['SCRIPT_NAME'] = '';
        $_SERVER['SCRIPT_FILENAME'] = '';
        $_SERVER['DOCUMENT_ROOT'] = '';

        Piko::$app = new Application($config);

        $_SERVER['REQUEST_URI'] = '/';

        ob_start();
        Piko::$app->run();
        $output = ob_get_contents();
        ob_end_clean();

        $this->assertEquals('index Action', $output);

        $_SERVER['REQUEST_URI'] = '/test/test/index2';

        ob_start();
        Piko::$app->run();
        $output = ob_get_contents();
        ob_end_clean();

        $this->assertEquals('index2 Action', $output);

        $_SERVER['REQUEST_URI'] = '/user/55';

        ob_start();
        Piko::$app->run();
        $output = ob_get_contents();
        ob_end_clean();

        $this->assertEquals('55', $output);

        $_SERVER['REQUEST_URI'] = '/test/sub/test/index';

        ob_start();
        Piko::$app->run();
        $output = ob_get_contents();
        ob_end_clean();

        $this->assertEquals('TestModule::SubModule::TestController::indexAction', $output);

        $_SERVER['REQUEST_URI'] = '/test/sub/til/test/index';

        ob_start();
        Piko::$app->run();
        $output = ob_get_contents();
        ob_end_clean();

        $this->assertEquals('TestModule::SubModule::SubtilModule::TestController::indexAction', $output);
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

    public function testHeaders()
    {
        $app = new Application([]);
        $app->setHeader('Location: /test');
        $app->setHeader(' Content-Type :appllication/json ');

        $this->assertEquals('/test', $app->headers['Location']);
        $this->assertEquals('appllication/json', $app->headers['Content-Type']);
    }
}
