<?php
use Piko\View;
use Piko\HttpException;
use Piko\FastApplication;
use PHPUnit\Framework\TestCase;
use HttpSoft\Message\ServerRequestFactory;
use Psr\Http\Message\ServerRequestInterface;

class FastApplicationTest extends TestCase
{
    /**
     * @var MonolithicApplication
     */
    protected $app;

    protected function setUp(): void
    {
        $this->app = new FastApplication([
            'basePath' => __DIR__,
            'components' => [
                View::class => [],
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

    public function testListenToGetWithParam()
    {
        $this->app->listen('GET', '/user/:name', function(ServerRequestInterface $request) {
            $name = $request->getAttribute('name');
            return "Hello {$name}";
        });

        $this->expectOutputString('Hello john');
        $this->app->run($this->createRequest('/user/john'), false);
    }

    public function testListenToGetPost()
    {
        $this->app->listen(['GET', 'POST'], '/edit', function(ServerRequestInterface $request) {

            if ($request->getMethod() === 'POST') {
                return FastApplication::createResponse('POST');
            }

            return FastApplication::createResponse('GET');
        });

        ob_start();
        $this->app->run($this->createRequest('/edit')->withMethod('GET'), false);
        $output = ob_get_contents();
        ob_end_clean();

        $this->assertEquals('GET', $output);

        ob_start();
        $this->app->run($this->createRequest('/edit')->withMethod('POST'), false);
        $output = ob_get_contents();
        ob_end_clean();

        $this->assertEquals('POST', $output);
    }

    public function testNotFound()
    {
        $this->app->listen('GET', '/edit', function(ServerRequestInterface $request) {
            return FastApplication::createResponse('GET');
        });

        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('Not Found');
        $this->expectExceptionCode(404);

        $this->app->run($this->createRequest('/edit')->withMethod('POST'), false);
    }
}
