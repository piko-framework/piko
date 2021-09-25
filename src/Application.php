<?php

/**
 * This file is part of Piko - Web micro framework
 *
 * @copyright 2019-2021 Sylvain PHILIP
 * @license LGPL-3.0; see LICENSE.txt
 * @link https://github.com/piko-framework/piko
 */

declare(strict_types=1);

namespace piko;

use RuntimeException;
use Throwable;

/**
 * The Web application class
 *
 * @author Sylvain PHILIP <contact@sphilip.com>
 */
class Application extends Component
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
     * The charset encoding used in the application.
     *
     * @var string
     */
    public $charset = 'UTF-8';

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
     * The response headers.
     *
     * @var array<string>
     */
    public $headers = [];

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
            $config['components']['view'] = [
                'class' => 'piko\View',
                'charset' => $this->charset
            ];
        }

        if (!isset($config['components']['router'])) {
            $config['components']['router'] = [
                'class' => 'piko\Router',
                'routes' => [
                    '^/(\w+)/(\w+)/(\w+)' => '$1/$2/$3',
                ],
            ];
        }

        if (isset($config['components'])) {
            foreach ($config['components'] as $name => $definition) {
                // Lasy-loading of component instances
                Piko::set($name, function () use ($definition) {
                    return Piko::createObject($definition);
                });
            }
        }

        $baseUrl = isset($config['baseUrl']) ? $config['baseUrl'] : rtrim(dirname($_SERVER['SCRIPT_NAME']), '\\/');

        Piko::setAlias('@web', $baseUrl);
        Piko::setAlias('@webroot', dirname($_SERVER['SCRIPT_FILENAME']));
        Piko::setAlias('@app', $this->basePath);

        $this->config = $config;

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
     * Run the application.
     *
     * @return void
     */
    public function run()
    {
        foreach ($this->bootstrap as $name) {
            $module = Piko::createObject($this->modules[$name]);

            if ($module instanceof Module && method_exists($module, 'bootstrap')) {
                $module->bootstrap();
            }
        }

        $this->trigger('beforeRoute');
        $route = $this->getRouter()->resolve();
        $this->trigger('afterRoute', [&$route]);

        try {

            echo $this->dispatch($route);

        } catch (Throwable $e) {

            if ($this->errorRoute === '') {
                throw $e;
            }

            Piko::set('exception', $e);
            echo $this->dispatch($this->errorRoute);
        }
    }

    /**
     * Dispatch a route and return the output result.
     *
     * @param string $route The route to dispatch. The route format is one of the following :
     * ```
     * '{moduleId}/{subModuleId}/.../{controllerId}/{actionId}'
     * '{moduleId}/{controllerId}/{actionId}'
     * '{moduleId}/{controllerId}'
     * '{moduleId}'
     * ```
     * @throws RuntimeException
     * @return string The output result.
     */
    public function dispatch($route)
    {
        if ($route === '') {
            throw new HttpException('Route not defined', 500);
        }

        $parts = explode('/', trim($route, '/'));

        $moduleId = array_shift($parts);
        $actionId = array_pop($parts) ?? 'index';
        $controllerId = array_pop($parts);

        if ($controllerId === null) {
            $controllerId = $actionId;
            $actionId = 'index';
        }

        $module = $this->getModule($moduleId);

        // In case of sub module
        while ($parts) {
            $moduleId = array_shift($parts);
            $module = $module->getModule($moduleId);
        }

        $module->id = $moduleId;
        $this->trigger('beforeRender', [&$module, $controllerId, $actionId]);
        $output = $module->run($controllerId, $actionId);

        if ($module->layout !== false) {
            $layout = $module->layout == null ? $this->defaultLayout : $module->layout;
            $view = $this->getView();
            $path = empty($module->layoutPath) ? $this->defaultLayoutPath : $module->layoutPath;
            $view->paths[] = $path;
            $output = $view->render($layout, ['content' => $output]);
        }

        $this->trigger('afterRender', [&$module, &$output, &$this->headers]);

        foreach ($this->headers as $header) {
            header($header);
        }

        return $output;
    }

    /**
     * Set Response header
     *
     * @param string $header The complete header (key:value) or just the header key
     * @param string $value  (optional) The header value
     */
    public function setHeader(string $header, string $value = ''): void
    {
        if (($pos = strpos($header, ':')) !== false) {
            $value = substr($header, $pos + 1);
            $header = substr($header, 0, $pos);
        }

        $this->headers[] = trim($header) . ': ' . trim($value);
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
     * Get a module instance
     *
     * @param string $moduleId The module identifier
     * @throws RuntimeException
     *
     * @return Module instance
     */
    public function getModule($moduleId)
    {
        if (!isset($this->modules[$moduleId])) {
            throw new RuntimeException("Configuration not found for module {$moduleId}.");
        }

        $module = Piko::createObject($this->modules[$moduleId]);

        if ($module instanceof Module) {
            return $module;
        }

        throw new RuntimeException("module $moduleId must be instance of Module");
    }
}
