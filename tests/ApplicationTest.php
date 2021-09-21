<?php
use PHPUnit\Framework\TestCase;

use piko\Piko;
use piko\Application;

class TestModule extends \piko\Module
{

}

class TestController extends \piko\Controller
{
    public $layout = false;

    public function indexAction()
    {
        return 'index Action';
    }

    public function index2Action()
    {
        return 'index2 Action';
    }

    public function index3Action()
    {
        return $_GET['id'];
    }
}

class ApplicationTest extends TestCase
{
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
                        '^/(\w+)/(\w+)/(\w+)' => '$1/$2/$3'
                    ],
                ]
            ],
            'modules' => [
                'test' => [
                    'class' => 'TestModule',
                    'controllerMap' => [
                        'test' => 'TestController'
                    ]
                 ]
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
