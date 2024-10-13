<?php
use PHPUnit\Framework\TestCase;

use Piko\Tests\modules\test\controllers\IndexController;
use Piko\Tests\lib\CustomView;
use Piko\ModularApplication;
use Piko\View;
use Piko\View\ViewInterface;
use Piko\Router;
use HttpSoft\Message\ServerRequestFactory;
use Psr\Http\Message\ServerRequestInterface;

class ControllerTest extends TestCase
{
    /**
     * @var IndexController
     */
    protected $controller;

    /**
     * @var ModularApplication
     */
    protected $app;

    protected function setUp(): void
    {
        $this->app = new ModularApplication([
            'basePath' => __DIR__,
            'components' => [
                View::class => new View(),
                Router::class => new Router([
                    'routes' => [
                        '/' => 'test/test/index',
                    ],
                ]),
                PDO::class => [
                    'construct' => [
                        'sqlite::memory:'
                    ]
                ],
            ],

            'modules' => [
                'test' =>'Piko\Tests\modules\test\TestModule'
            ]
        ]);

        $this->controller = new IndexController();
        $this->controller->module = $this->app->getModule('test');
        $this->controller->id = 'index';
        $this->controller->layout = false;
    }

    protected function createRequest($uri, $method = 'GET', $serverParams = []): ServerRequestInterface
    {
        return (new ServerRequestFactory())->createServerRequest($method, $uri, $serverParams);
    }

    public function testUnknownAction()
    {
        $request = $this->createRequest('/')->withAttribute('action', 'goodBye');
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Method "goodByeAction" not found in ' . IndexController::class);
        $this->controller->handle($request);
    }

    public function testRenderWithoutViewComponent()
    {
        $request = $this->createRequest('/')
                        ->withAttribute('action', 'say-hello')
                        ->withAttribute('route_params', ['name' => 'Toto']);

        unset($this->app->components[View::class]);
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('View must be instance of Piko\View\ViewInterface');
        $this->controller->handle($request);
    }

    public function testRenderWithCustomViewComponent()
    {
        $app = new ModularApplication([
            'basePath' => __DIR__,
            'components' => [
                ViewInterface::class => [
                    'class' => CustomView::class,
                    'viewPath' => __DIR__ . '/modules/test/views/index'
                ],
            ],
            'modules' => [
                'test' =>'Piko\Tests\modules\test\TestModule'
            ]
        ]);

        $controller = new IndexController();
        $controller->module = $app->getModule('test');
        $controller->id = 'index';

        $request = $this->createRequest('/')
                        ->withAttribute('action', 'say-hello')
                        ->withAttribute('route_params', ['name' => 'Toto']);

        $response = $controller->handle($request);
        $body = (string) $response->getBody();
        $this->assertMatchesRegularExpression('~<p>Hello Toto</p>~', $body);
    }

    public function testRenderWithoutLayout()
    {
        $request = $this->createRequest('/')
                        ->withAttribute('action', 'say-hello')
                        ->withAttribute('route_params', ['name' => 'Toto']);

        $response = $this->controller->handle($request);

        $body = (string) $response->getBody();

        $this->assertFalse(strpos($body, '<!DOCTYPE html>'));
        $this->assertMatchesRegularExpression('~<p>Hello Toto</p>~', $body);
    }

    public function testRenderWithLayout()
    {
        $request = $this->createRequest('/')
                        ->withAttribute('action', 'say-hello')
                        ->withAttribute('route_params', ['name' => 'Toto', 'layout' => 'main']);

        $response = $this->controller->handle($request);

        $body = (string) $response->getBody();

        $this->assertMatchesRegularExpression('~<!DOCTYPE html>~', $body);
        $this->assertMatchesRegularExpression('~<p>Hello Toto</p>~', $body);
    }

    public function testRedirectUsingGetUrl()
    {
        $request = $this->createRequest('/')->withAttribute('action', 'goHome');
        $response =$this->controller->handle($request);
        $values = $response->getHeader('Location');
        $this->assertContains('/', $values);
    }

    public function testRedirectUsingGetUrlWithoutRouter()
    {
        unset($this->app->components[Router::class]);
        $request = $this->createRequest('/')->withAttribute('action', 'goHome');
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Piko\Router is not registered as component');
        $controller = new IndexController();
        $controller->module = $this->app->getModule('test');
        $controller->handle($request);
    }

    public function testRedirectUsingGetUrlWithWrongRouter()
    {
        $this->app->components[Router::class] = new DateTime();
        $request = $this->createRequest('/')->withAttribute('action', 'goHome');
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('getUrl method needs that Piko\Router is registered as application component');
        $controller = new IndexController();
        $controller->module = $this->app->getModule('test');
        $controller->handle($request);
    }

    public function testForward()
    {
        $this->controller->setRequest($this->createRequest('/'));
        $response = $this->controller->testForward('test/test/index');
        $this->assertEquals('TestModule::TestController::indexAction',  $response);
    }

    public function testForwardWithEmptyRoute()
    {
        $this->controller->setRequest($this->createRequest('/'));
        $response = $this->controller->testForward();
        $this->assertEquals('',  $response);
    }

    public function testJsonResponse()
    {
        $request = $this->createRequest('/')->withAttribute('action', 'testJson');
        $response = $this->controller->handle($request);
        $this->assertFalse($this->controller->layout);
        $values = $response->getHeader('Content-Type');
        $this->assertContains('application/json', $values);
        $data = json_decode((string) $response->getBody(), true);
        $this->assertArrayHasKey('status', $data);
        $this->assertEquals('ok', $data['status']);
    }

    public function testJsonResponseWithWrongArgument()
    {
        $request = $this->createRequest('/')->withAttribute('action', 'testJson');
        $response = $this->controller->handle($request);
        $this->assertFalse($this->controller->layout);
        $values = $response->getHeader('Content-Type');
        $this->assertContains('application/json', $values);
        $data = json_decode((string) $response->getBody(), true);
        $this->assertArrayHasKey('status', $data);
        $this->assertEquals('ok', $data['status']);
    }

    public function testAjaxRequest()
    {
        $request = $this->createRequest('/', 'POST')->withAttribute('action', 'testAjax');
        $response = $this->controller->handle($request);
        $this->assertEquals('is not ajax', (string) $response->getBody());

        $request = $this->createRequest('/', 'POST', [
            'HTTP_X_REQUESTED_WITH' => 'xmlhttprequest'
        ])->withAttribute('action', 'testAjax');
        $response = $this->controller->handle($request);

        $this->assertEquals('is ajax', (string) $response->getBody());
    }
}
