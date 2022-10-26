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
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use RuntimeException;
use SplQueue;
use Throwable;
use Piko\Application\BootstrapMiddleware;
use Piko\Application\ErrorHandler;
use Piko\Application\RoutingMiddleware;

/**
 * The main application class
 *
 * @author Sylvain PHILIP <contact@sphilip.com>
 */
class Application extends Component implements RequestHandlerInterface
{
    /**
     * The absolute base path of the application.
     *
     * @var string
     */
    public $basePath = '';

    /**
     * List of modules configurations.
     *
     * Should be either :
     *
     * ```php
     * [
     *   'moduleId' => 'moduleClassName'
     * ]
     * ```
     *
     * Or :
     *
     * ```php
     * [
     *   'moduleId' => [
     *     'class' => 'moduleClassName',
     *     'layoutPath' => '/some/path'
     *     // ...
     *   ]
     * ]
     * ```
     *
     * @var array<mixed>
     * @see Module To have more informations on module attributes
     */
    public $modules = [];

    /**
     * List of module IDs that should be run during the application bootstrapping process.
     *
     * Each module may be specified with a module ID as specified via [[modules]].
     *
     * During the bootstrapping process, each module will be instantiated. If the module class
     * implements the bootstrap() method, this method will be also be called.
     *
     * @var array<string>
     */
    public $bootstrap = [];

    /**
     * The configuration loaded on application instantiation.
     *
     * @var array<mixed>
     */
    public $config = [];

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
     * @param array<mixed> $config The application configuration.
     * @return void
     */
    public function __construct(array $config)
    {
        parent::__construct($config);

        if (!isset($config['components']['view'])) {
            $config['components']['view'] = 'Piko\View';
        }

        if (!isset($config['components']['router'])) {
            $config['components']['router'] = 'piko\Router';
        }

        if (isset($config['components'])) {
            foreach ($config['components'] as $name => $definition) {
                // Lasy-loading of component instances
                Piko::set($name, function () use ($definition) {
                    return Piko::createObject($definition);
                });
            }
        }

        Piko::setAlias('@app', $this->basePath);
        Piko::setAlias('@web', $config['baseUrl'] ?? '');
        Piko::setAlias('@webroot', $config['webroot'] ?? $this->basePath . '/web');

        $this->config = $config;

        $this->pipeline = new SplQueue();

        static::$instance = $this;

        $this->trigger('init');
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

        $this->pipeline->enqueue(new BootstrapMiddleware());
        $this->pipeline->enqueue(new RoutingMiddleware());

        try {
            $response = $this->handle($request);
        } catch (Throwable $e) {
            $errorHandler = new ErrorHandler();
            $response = $errorHandler->handle($request->withAttribute('exception', $e));
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
            throw  new HttpException(404, 'Not Found');
        }

        $middleware = $this->pipeline->dequeue();

        return $middleware->process($request, $this);
    }

    /**
     * Get the application router instance
     *
     * @return Router instance
     */
    public function getRouter(): Router
    {
        return Piko::get('router');
    }

    /**
     * Get the application view instance
     *
     * @return View instance
     */
    public function getView(): View
    {
        return Piko::get('view');
    }

    /**
     * Parse a route and return an array containing the module's id, the controller's id and the action's id.
     *
     * @param string $route The route to parse. The route format is one of the following :
     *
     * ```
     * '{moduleId}/{subModuleId}/.../{controllerId}/{actionId}'
     * '{moduleId}/{controllerId}/{actionId}'
     * '{moduleId}/{controllerId}'
     * '{moduleId}'
     * ```
     * @return array<string|null>
     */
    public static function parseRoute(string $route): array
    {
        $parts = explode('/', trim($route, '/'));

        $moduleId = array_shift($parts);
        $actionId = array_pop($parts);
        $controllerId = array_pop($parts);

        if ($controllerId === null) {
            $controllerId = $actionId;
            $actionId = null;
        }

        if (count($parts)) {
            $moduleId .= '/' . implode('/', $parts);
        }

        return [$moduleId, $controllerId, $actionId];
    }

    /**
     * Create a module instance based on its definition in the configuration
     *
     * @param string $moduleId The module identifier
     * @throws RuntimeException
     *
     * @return Module instance
     */
    public static function createModule(string $moduleId): Module
    {
        $app = static::getInstance();

        $parts = [];

        if (strpos($moduleId, '/') !== false) {
            $parts = explode('/', trim($moduleId, '/'));
            $moduleId = array_shift($parts);
        }

        if (!isset($app->modules[$moduleId])) {
            throw new RuntimeException("Configuration not found for module {$moduleId}.");
        }

        $module = Piko::createObject($app->modules[$moduleId]);

        if ($module instanceof Module) {

            // In case of sub module
            while ($parts) {
                $moduleId = array_shift($parts);
                $module = $module->getModule($moduleId);
            }

            return $module;
        }

        throw new RuntimeException("module $moduleId must be instance of piko\Module");
    }
}
