<?php
use PHPUnit\Framework\TestCase;

use tests\modules\test\controllers\IndexController;
use Piko\Piko;
use Piko\Application;

use HttpSoft\Message\ServerRequestFactory;
use Psr\Http\Message\ServerRequestInterface;

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
                    '/' => 'test/test/index',
                    '/user/:id' => 'test/test/index3',
                    '/test/sub/til/:controller/:action' => 'test/sub/til/:controller/:action',
                    '/test/sub/:controller/:action' => 'test/sub/:controller/:action',
                    '/:module/:controller/:action' => ':module/:controller/:action',
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

        $_SERVER['REQUEST_SCHEME'] = 'http';
        $_SERVER['HTTP_HOST'] = 'localhost';

        $this->controller = new IndexController([
            'id' => 'index',
            'layout' => false,
            'module' => Application::createModule('test'),
        ]);
    }

    protected function createRequest($uri, $method = 'GET', $serverParams = []): ServerRequestInterface
    {
        $factory = new ServerRequestFactory();
        $request = $factory->createServerRequest($method, $uri, $serverParams);

        return $request;
    }

    public function testUnknownAction()
    {
        $request = $this->createRequest('/')->withAttribute('action', 'goodBye');
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Method "goodByeAction" not found in ' . IndexController::class);
        $this->controller->handle($request);
    }

    public function testRenderViewWithoutLayout()
    {
        $request = $this->createRequest('/')
                        ->withAttribute('action', 'say-hello')
                        ->withAttribute('route_params', ['name' => 'Toto']);

        $response = $this->controller->handle($request);

        $body = (string) $response->getBody();

        $this->assertFalse(strpos($body, '<!DOCTYPE html>'));
        $this->assertMatchesRegularExpression('~<p>Hello Toto</p>~', $body);
    }

    public function testRenderViewWithLayout()
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
        $response = $this->controller->handle($request);
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

    /*
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
    */

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

    /*
    public function testRawInput()
    {
        $this->assertEquals('', $this->controller->runAction('rawInput'));
    }
    */

}
