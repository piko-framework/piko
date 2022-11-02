<?php

/**
 * This file is part of Piko - Web micro framework
 *
 * @copyright 2019-2022 Sylvain PHILIP
 * @license LGPL-3.0; see LICENSE.txt
 * @link https://github.com/piko-framework/piko
 */

declare(strict_types=1);

namespace Piko\Application;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Piko\Application;
use Piko\Router;

/**
 * Dispatch route to its corresponding module.
 *
 * @author Sylvain PHILIP <contact@sphilip.com>
 */
final class RoutingMiddleware implements MiddlewareInterface
{
    /**
     * @var Application
     */
    private $application;

    /**
     * @var Router
     */
    private $router;

    public function __construct(Application $app)
    {
        $this->application = $app;
        $router = $this->application->getComponent(Router::class);

        if ($router instanceof Router) {
            $this->router = $router;
        }
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
        $route = $match->found ? $match->handler : $path;

        if (is_string($route)) {

            list($moduleId, $controllerId, $actionId) = Application::parseRoute($route);

            if ($controllerId) {
                $request = $request->withAttribute('controller', $controllerId);
            }

            if ($actionId) {
                $request = $request->withAttribute('action', $actionId);
            }

            if ($moduleId) {
                $request = $request->withAttribute('module', $moduleId);
                $module = $this->application->getModule($moduleId);

                return $module->handle($request->withAttribute('route_params', $match->params));
            }
        }

        return $handler->handle($request);
    }
}
