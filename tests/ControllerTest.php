<?php
use PHPUnit\Framework\TestCase;

use tests\modules\test\controllers\IndexController;
use Piko\ModularApplication;
use Piko\View;
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
                ])
            ],
            'modules' => [
                'test' =>'tests\modules\test\TestModule'
            ]
        ]);

        $this->controller = new IndexController($this->app->getModule('test'));
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
        $this->expectExceptionMessage('Piko\View is not registered as component');
        $this->controller->handle($request);
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
