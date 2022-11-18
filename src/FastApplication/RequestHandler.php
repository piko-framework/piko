<?php

/**
 * This file is part of Piko - Web micro framework
 *
 * @copyright 2019-2022 Sylvain PHILIP
 * @license LGPL-3.0; see LICENSE.txt
 * @link https://github.com/piko-framework/piko
 */

namespace Piko\FastApplication;

use HttpSoft\Message\Response;
use HttpSoft\Message\StreamFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Dispatch route to its corresponding module.
 *
 * @author Sylvain PHILIP <contact@sphilip.com>
 */
class RequestHandler implements RequestHandlerInterface
{
    /**
     * Allowed request method(s)
     *
     * @var string|array<string>
     */
    public $method;

    /**
     * Request handler
     *
     * @var callable|null
     */
    private $handler;

    /**
     * Request parameters
     *
     * @var array<string, string|int|float>
     */
    private $params = [];

    /**
     * Constructor
     *
     * @param string|array<string> $method The allowed request method(s)
     * @param callable|null $handler A callable handler, which have the following signature:
     * ```
     * function(Psr\Http\Message\ServerRequestInterface $request): string|Psr\Http\Message\ResponseInterface
     * ```
     */
    public function __construct($method, $handler = null)
    {
        $this->method = $method;
        $this->handler = $handler;
    }

    /**
     * Set request parameters
     *
     * @param array<string, string|int|float> $params The request parameters
     * @return void
     */
    public function setParams(array $params = []): void
    {
        $this->params = $params;
    }

    /**
     * Check if the request can be handled, looking-up on allowed request method
     *
     * @param ServerRequestInterface $request
     * @return boolean
     */
    public function canHandle(ServerRequestInterface $request): bool
    {
        if (is_string($this->method) && strtoupper($this->method) === $request->getMethod()) {
            return true;
        } elseif (is_array($this->method)) {
            foreach ($this->method as $method) {
                if (strtoupper($method) === $request->getMethod()) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * {@inheritDoc}
     * @see \Psr\Http\Server\RequestHandlerInterface::handle()
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        foreach ($this->params as $name => $value) {
            $request = $request->withAttribute($name, $value);
        }

        assert(is_callable($this->handler));

        $response = call_user_func_array($this->handler, [$request, $this->params]);

        if (is_string($response) || is_numeric($response)) {
            $response = (new Response())->withBody((new StreamFactory())->createStream((string) $response));
        }

        assert($response instanceof ResponseInterface);

        return $response;
    }
}
