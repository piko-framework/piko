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
use piko\Application;
use piko\Piko;

/**
 * Dispatch route to its corresponding module.
 *
 * @author Sylvain PHILIP <contact@sphilip.com>
 */
final class RoutingMiddleware implements MiddlewareInterface
{
    /**
     * {@inheritDoc}
     * @see \Psr\Http\Server\MiddlewareInterface::process()
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $router = Piko::get('router');
        $router->baseUri = (string) Piko::getAlias('@web');
        $match = $router->resolve($request->getUri()->getPath());
        $route = $match->found ? $match->handler : $request->getUri()->getPath();
        list($moduleId, $controllerId, $actionId) = Application::parseRoute($route);

        if ($controllerId) {
            $request = $request->withAttribute('controller', $controllerId);
        }

        if ($actionId) {
            $request = $request->withAttribute('action', $actionId);
        }

        if ($moduleId) {
            $request = $request->withAttribute('module', $moduleId);
            $module = Application::createModule($moduleId);

            return $module->handle($request->withAttribute('route_params', $match->params));
        }

        return $handler->handle($request);
    }
}
