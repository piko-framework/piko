<?php

/**
 * This file is part of Piko - Web micro framework
 *
 * @copyright 2019-2022 Sylvain PHILIP
 * @license LGPL-3.0; see LICENSE.txt
 * @link https://github.com/piko-framework/piko
 */

declare(strict_types=1);

namespace Piko\FastApplication;

use Piko\Router;
use Piko\FastApplication;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Dispatch route to its corresponding request handler.
 *
 * @author Sylvain PHILIP <contact@sphilip.com>
 */
final class RoutingMiddleware implements MiddlewareInterface
{
    /**
     * @var FastApplication
     */
    private $application;

    /**
     * @var Router
     */
    private $router;

    /**
     * Constructor
     *
     * @param FastApplication $app
     */
    public function __construct(FastApplication $app)
    {
        $this->application = $app;
        $this->router = $this->application->getRouter();
    }

    /**
     * {@inheritDoc}
     * @see \Psr\Http\Server\MiddlewareInterface::process()
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (!$this->router->baseUri) {
            $this->router->baseUri = (string) \Piko::getAlias('@web');
        }

        $path = $request->getUri()->getPath();
        $match = $this->router->resolve($path);

        if ($match->found && $match->handler instanceof RequestHandler && $match->handler->canHandle($request)) {

            $match->handler->setParams($match->params); // @phpstan-ignore-line

            return $match->handler->handle($request);
        }

        return $handler->handle($request);
    }
}
