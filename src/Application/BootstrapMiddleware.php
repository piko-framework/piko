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
use piko\Module;
use piko\Piko;

/**
 * Bootstrap the application with the modules registered in the
 * bootstrap part of the configuration.
 *
 * @author Sylvain PHILIP <contact@sphilip.com>
 */
final class BootstrapMiddleware implements MiddlewareInterface
{
    /**
     * {@inheritDoc}
     * @see \Psr\Http\Server\MiddlewareInterface::process()
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $app = Application::getInstance();

        foreach ($app->bootstrap as $name) {
            $module = Piko::createObject($app->modules[$name]);

            if ($module instanceof Module && method_exists($module, 'bootstrap')) {
                $module->bootstrap($app);
            }
        }

        return $handler->handle($request);
    }
}
