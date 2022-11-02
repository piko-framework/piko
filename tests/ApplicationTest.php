<?php

use HttpSoft\Message\ServerRequestFactory;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Piko\Application;
use Piko\HttpException;

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
                'Piko\View' => [],
                'Piko\Router' => [
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

    public function testNonRegisteredComponent()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('DateTime is not registered as component');
        $this->app->getComponent(DateTime::class);
    }

    public function testUniqueComponentInstance()
    {
        $view1 = $this->app->getComponent('Piko\View');
        $view2 = $this->app->getComponent('Piko\View');
        $this->assertSame(spl_object_hash($view1), spl_object_hash($view2));
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
}
