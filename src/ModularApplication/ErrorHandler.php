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
use Psr\Http\Server\RequestHandlerInterface;
use RuntimeException;
use Throwable;
use Piko\ModularApplication;

/**
 * Error handler class.
 * Forwards exception to application's error route (if defined).
 *
 * @author Sylvain PHILIP <contact@sphilip.com>
 */
final class ErrorHandler implements RequestHandlerInterface
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
     * @see \Psr\Http\Server\RequestHandlerInterface::handle()
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $exception = $request->getAttribute('exception');

        if (!$exception instanceof Throwable) {
            throw new RuntimeException('Exception must be instance of Throwable');
        }

        $errorRoute = $this->application->errorRoute;

        if ($errorRoute === '') {
            throw $exception;
        }

        list($moduleId, $controllerId, $actionId) = ModularApplication::parseRoute($errorRoute);

        $request = $request->withAttribute('route_params', ['exception' => $exception])
                           ->withAttribute('module', $moduleId)
                           ->withAttribute('controller', $controllerId)
                           ->withAttribute('action', $actionId);

        $module = $this->application->getModule((string) $moduleId);

        $response = $module->handle($request);

        $code = $exception->getCode();

        if (is_string($code) && is_numeric($code)) {
            // Some exceptions, such as PDOException, may return the code as a string.
            // Refer to the discussion on this issue: https://github.com/php/php-src/issues/9529
            $code = (int) $code; // @codeCoverageIgnore
        } elseif (is_string($code) && !is_numeric($code)) {
            $code = 500; // @codeCoverageIgnore
        }

        if ($code < 100 || $code > 599) {
            $code = 500; // Internal server error
        }

        return $response->withStatus($code, $exception->getMessage());
    }
}
