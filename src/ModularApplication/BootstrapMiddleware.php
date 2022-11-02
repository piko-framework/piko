<?php

/**
 * This file is part of Piko - Web micro framework
 *
 * @copyright 2019-2022 Sylvain PHILIP
 * @license LGPL-3.0; see LICENSE.txt
 * @link https://github.com/piko-framework/piko
 */

declare(strict_types=1);

namespace Piko\ModularApplication;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Piko\ModularApplication;
use Piko\Module;

/**
 * Bootstrap the application with the modules registered in the
 * bootstrap part of the configuration.
 *
 * @author Sylvain PHILIP <contact@sphilip.com>
 */
final class BootstrapMiddleware implements MiddlewareInterface
{
    /**
     * @var ModularApplication
     */
    private $application;

    public function __construct(ModularApplication $app)
    {
        $this->application = $app;
    }

    /**
     * {@inheritDoc}
     * @see \Psr\Http\Server\MiddlewareInterface::process()
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        foreach ($this->application->bootstrap as $name) {
            $module = $this->application->getModule($name);

            if ($module instanceof Module && method_exists($module, 'bootstrap')) {
                $module->bootstrap();
            }
        }

        return $handler->handle($request);
    }
}
