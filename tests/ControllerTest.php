<?php
use PHPUnit\Framework\TestCase;

use tests\modules\test\controllers\IndexController;
use piko\Piko;
use piko\Application;

class ControllerTest extends TestCase
{
    /**
     * @var IndexController
     */
    protected $controller;

    const APP_CONFIG = [
        'basePath' => __DIR__,
        'components' => [
            'router' => [
                'class' => 'piko\Router',
                'routes' => [
                    '^/$' => 'test/test/index',
                    '^/user/(\d+)' => 'test/test/index3|id=$1',
                    '^/test/sub/til/(\w+)/(\w+)' => 'test/sub/til/$1/$2',
                    '^/test/sub/(\w+)/(\w+)' => 'test/sub/$1/$2',
                    '^/(\w+)/(\w+)/(\w+)$' => '$1/$2/$3',
                ],
            ]
        ],
        'modules' => [
            'test' => 'tests\modules\test\TestModule',
        ],
    ];

    protected function setUp(): void
    {
        Piko::reset();
        new Application(self::APP_CONFIG);
        $this->controller = new IndexController([
            'id' => 'index',
            'layout' => false,
            'module' => Piko::$app->getModule('test'),
        ]);
    }

    public function testUnknownAction()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Method "goodByeAction" not found in ' . IndexController::class);
        $this->controller->runAction('goodBye');
    }

    public function testRenderView()
    {
        $_GET['name'] = 'Toto';

        $output = $this->controller->runAction('sayHello');

        $this->assertMatchesRegularExpression('~<p>Hello Toto</p>~', $output);
    }

    public function testRedirectUsingGetUrl()
    {
        $this->controller->runAction('goHome');
        $this->assertContains('Location: /', Piko::$app->headers);
    }

    public function testForward()
    {
        $this->assertEquals('TestModule::TestController::indexAction', $this->controller->runAction('homeTest'));
    }

    public function testJsonResponse()
    {
        $response = $this->controller->runAction('testJson');
        $this->assertFalse($this->controller->layout);
        $this->assertContains('Content-Type: application/json', Piko::$app->headers);
        $data = json_decode($response, true);
        $this->assertArrayHasKey('status', $data);
        $this->assertEquals('ok', $data['status']);
    }

    public function testGetMethod()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $this->assertEquals('is get', $this->controller->runAction('testGet'));
    }

    public function testPostMethod()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $this->assertEquals('is post', $this->controller->runAction('testPost'));
    }

    public function testPutMethod()
    {
        $_SERVER['REQUEST_METHOD'] = 'PUT';
        $this->assertEquals('is put', $this->controller->runAction('testPut'));
    }

    public function testDeleteMethod()
    {
        $_SERVER['REQUEST_METHOD'] = 'DELETE';
        $this->assertEquals('is delete', $this->controller->runAction('testDelete'));
    }

    public function testAjaxRequest()
    {
        $this->assertEquals('is not ajax', $this->controller->runAction('testAjax'));
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'xmlhttprequest';
        $this->assertEquals('is ajax', $this->controller->runAction('testAjax'));
    }

    public function testRawInput()
    {
        $this->assertEquals('', $this->controller->runAction('rawInput'));
    }
}
