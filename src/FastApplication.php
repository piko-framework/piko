<?php

/**
 * This file is part of Piko - Web micro framework
 *
 * @copyright 2019-2022 Sylvain PHILIP
 * @license LGPL-3.0; see LICENSE.txt
 * @link https://github.com/piko-framework/piko
 */

declare(strict_types=1);

namespace Piko;

use HttpSoft\Message\Response;
use HttpSoft\Message\StreamFactory;
use Psr\Http\Message\ServerRequestInterface;
use Piko\FastApplication\RoutingMiddleware;
use Piko\FastApplication\RequestHandler;

/**
 * This class implements a fast and simple route handlers application
 *
 * @author Sylvain PHILIP <contact@sphilip.com>
 */
class FastApplication extends Application
{
    /**
     * @var Router
     */
    private $router;

    /**
     * {@inheritDoc}
     */
    public function run(ServerRequestInterface $request = null, bool $emitHeaders = true)
    {
        $this->pipe(new RoutingMiddleware($this));
        parent::run($request, $emitHeaders);
    }

    /**
     * Register a route handler
     *
     * @param string|array<string> $requestMethod The allowed request(s) method(s)
     * @param string $path The route path
     * @param callable $handler A callable handler, which have the following signature:
     * ```
     * function(Psr\Http\Message\ServerRequestInterface $request): string|Psr\Http\Message\ResponseInterface
     * ```
     * @return void
     */
    public function listen($requestMethod, string $path, callable $handler): void
    {
        $requestHandler = new RequestHandler($requestMethod, $handler);
        $this->getRouter()->addRoute($path, $requestHandler);
    }

    /**
     * Return a router instance
     *
     * @return Router
     */
    public function getRouter(): Router
    {
        if ($this->router === null) {
            $router = isset($this->components[Router::class]) ? $this->getComponent(Router::class) : null;
            $this->router = $router instanceof Router ? $router : new Router();
        }

        return $this->router;
    }

    /**
     * Helper to create a response
     *
     * @param string $body The response body
     * @return Response
     */
    public static function createResponse(string $body = ''): Response
    {
        return (new Response())->withBody((new StreamFactory())->createStream((string) $body));
    }
}
