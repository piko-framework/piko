<?php
namespace tests\middleware;

use HttpSoft\Message\Response;
use HttpSoft\Message\StreamFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

use piko\Application\ErrorHandler;

final class TestMiddleware implements MiddlewareInterface
{
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

        if ($path === '/testwrongexception') {
            $error = new ErrorHandler();
            return $error->handle($request->withAttribute('exception', new \DateTime()));
        }

        return $handler->handle($request);
    }
}
