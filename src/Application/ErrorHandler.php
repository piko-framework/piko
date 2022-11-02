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

use HttpSoft\Message\Response;
use Piko\Application;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use RuntimeException;
use Throwable;

/**
 * Error handler class.
 * Forwards exception to application's error route (if defined).
 *
 * @author Sylvain PHILIP <contact@sphilip.com>
 */
final class ErrorHandler implements RequestHandlerInterface
{
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

        $errorRoute = Application::getInstance()->errorRoute;

        if ($errorRoute === '') {
            throw $exception;
        }

        return (new Response())->withStatus($exception->getCode(), $exception->getMessage());
    }
}
