<?php

/**
 * This file is part of Piko - Web micro framework
 *
 * @copyright 2019-2021 Sylvain PHILIP
 * @license LGPL-3.0; see LICENSE.txt
 * @link https://github.com/piko-framework/piko
 */

declare(strict_types=1);

namespace Piko;

use HttpSoft\ServerRequest\ServerRequestCreator;
use Piko\Application\ErrorHandler;
use Piko\Application\Event\InitEvent;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use RuntimeException;
use SplQueue;
use Throwable;

/**
 * The main application class
 *
 * @author Sylvain PHILIP <contact@sphilip.com>
 */
class Application implements RequestHandlerInterface
{
    use BehaviorTrait;
    use EventHandlerTrait;

    /**
     * The absolute base path of the application.
     *
     * @var string
     */
    public $basePath = '';

    /**
     * The default layout name without file extension.
     *
     * @var string
     */
    public $defaultLayout = 'main';

    /**
     * The default layout path. An alias could be used.
     *
     * @var string
     */
    public $defaultLayoutPath = '@app/layouts';

    /**
     * The Error route to display exceptions in a friendly way.
     *
     * If not set, Exceptions catched will be thrown and stop the script execution.
     *
     * @var string
     */
    public $errorRoute = '';

    /**
     * The language that is meant to be used for end users.
     *
     * @var string
     */
    public $language = 'en';

    /**
     * The components container
     *
     * @var array<object|callable>
     */
    public $components = [];

    /**
     * @var RequestHandlerInterface
     */
    protected $errorHandler;

    /**
     * The aliases container.
     *
     * @var array<string, string>
     */
    protected $aliases = [];

    /**
     * @var SplQueue<MiddlewareInterface>
     */
    protected $pipeline = null;

    /**
     * Application Instance
     *
     * @var Application
     */
    public static $instance;

    /**
     * Constructor
     *
     * @param array<string, mixed> $config The application configuration.
     * @return void
     */
    public function __construct(array $config = [])
    {
        \Piko::configureObject($this, $config);

        foreach ($this->components as $type => $definition) {
            if (is_array($definition)) {
                $this->components[$type] = fn() => \Piko::createObject($type, $definition);
            }
        }

        \Piko::setAlias('@app', $this->basePath);
        \Piko::setAlias('@web', $config['baseUrl'] ?? ''); // @phpstan-ignore-line
        \Piko::setAlias('@webroot', $config['webroot'] ?? $this->basePath . '/web'); // @phpstan-ignore-line
        $this->pipeline = new SplQueue();
        static::$instance = $this;
        $this->trigger(new InitEvent($this));
    }

    /**
     * Get the application instance
     *
     * @return Application
     */
    public static function getInstance(): Application
    {
        return static::$instance;
    }

    /**
     * Add a middleware in the application pipeline queue
     *
     * @param MiddlewareInterface $middleware
     *
     * @see \Psr\Http\Server\MiddlewareInterface
     */
    public function pipe(MiddlewareInterface $middleware): void
    {
        $this->pipeline->enqueue($middleware);
    }

    /**
     * Run the application.
     *
     * @param ServerRequestInterface|null $request
     * @param bool $emitHeaders Controls whether headers will be emmited (header() function called)
     *
     * @return void
     */
    public function run(ServerRequestInterface $request = null, bool $emitHeaders = true)
    {
        if (!$request) {
            $request = ServerRequestCreator::create();
        }

        try {
            $response = $this->handle($request);
        } catch (Throwable $e) {
            if (!$this->errorHandler instanceof RequestHandlerInterface) {
                $this->errorHandler = new ErrorHandler();
            }

            $response = $this->errorHandler->handle($request->withAttribute('exception', $e));
        }

        if ($emitHeaders) {
            $statusCode = $response->getStatusCode();
            $reasonPhrase = $response->getReasonPhrase();
            $protocolVersion = $response->getProtocolVersion();
            $status = $statusCode . (!$reasonPhrase ? '' : ' ' . $reasonPhrase);

            header('HTTP/' . $protocolVersion . ' ' . $status, true, $statusCode);

            foreach ($response->getHeaders() as $header => $values) {
                $header = trim($header);

                foreach ($values as $value) {
                    header($header . ': ' . trim($value));
                }
            }
        }

        echo $response->getBody();
    }

    /**
     * {@inheritDoc}
     * @see \Psr\Http\Server\RequestHandlerInterface::handle()
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if ($this->pipeline->count() === 0) {
            throw new HttpException(404, 'Not Found');
        }

        $middleware = $this->pipeline->dequeue();

        return $middleware->process($request, $this);
    }

    /**
     * Retrieve a unique instance of a registered component
     *
     * @param string $type The component class
     * @throws RuntimeException If the component is not found
     * @return object
     */
    public function getComponent(string $type): object
    {
        if (!isset($this->components[$type])) {
            throw new RuntimeException(sprintf('%s is not registered as component', $type));
        }

        $component = $this->components[$type];

        if (is_object($component) && is_a($component, $type)) {
            return $component;
        }

        if (is_callable($this->components[$type])) {
            $this->components[$type] = $this->components[$type]();
        }

        return $this->components[$type];
    }
}
