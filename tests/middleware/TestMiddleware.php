<?php
namespace Piko\Tests\middleware;

use HttpSoft\Message\Response;
use HttpSoft\Message\StreamFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Piko\Application;
use Piko\ModularApplication\ErrorHandler;

final class TestMiddleware implements MiddlewareInterface
{
    /**
     * @var Application
     */
    private $application;

    public function __construct(Application $app)
    {
        $this->application = $app;
    }

    /**
     * {@inheritDoc}
     * @see \Psr\Http\Server\MiddlewareInterface::process()
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $path = $request->getUri()->getPath();

        if ($path === '/testmiddleware') {
            $response = new Response();
            $body = (new StreamFactory())->createStream('Test middleware response');

            return $response->withBody($body);
        }

        if ($path === '/test-error-handler-wrong-exception') {
            $error = new ErrorHandler($this->application);
            return $error->handle($request->withAttribute('exception', new \DateTime()));
        }

        return $handler->handle($request);
    }
}
