<?php
/**
 * This file is part of Piko - Web micro framework
 *
 * @copyright 2019 Sylvain PHILIP.
 * @license LGPL-3.0; see LICENSE.txt
 * @link https://github.com/ilhooq/piko
 */
declare(strict_types=1);

namespace piko;

use RuntimeException;

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
     * List of modules that should be run during the application bootstrapping process.
     *
     * Each module may be specified with a module ID as specified via [[modules]].
     *
     * During the bootstrapping process, each module will be instantiated. If the module class
     * implements the bootstrap() method, this method will be also be called.
     *
     * @var array
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
     * @var array
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
     * Constructor
     *
     * @param array $config The application configuration.
     * @return void
     */
    public function __construct(array $config)
    {
        parent::__construct($config);

        if (!isset($config['components']['view'])) {
            $config['components']['view'] = 'piko\View';
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

        $baseUrl = isset($config['baseUrl'])? $config['baseUrl'] : rtrim(dirname($_SERVER['SCRIPT_NAME']), '\\/');

        Piko::setAlias('@web', $baseUrl);
        Piko::setAlias('@webroot', dirname($_SERVER['SCRIPT_FILENAME']));
        Piko::setAlias('@app', $this->basePath);

        $this->config = $config;

        Piko::$app = $this;

        $this->trigger('init');
    }

    /**
     * Run the application.
     *
     * @return void
     */
    public function run()
    {
        foreach ($this->bootstrap as $name) {
            $module = Piko::createObject($this->config['modules'][$name]);

            if ($module instanceof Module && method_exists($module, 'bootstrap')) {
                $module->bootstrap();
            }
        }

        $this->trigger('beforeRoute');

        $router = $this->getRouter();

        $route = $router->resolve();

        $this->trigger('afterRoute', [&$route]);

        try {

            if (empty($route)) {
                throw new HttpException('Not found', 404);
            }

            echo $this->dispatch($route);

        } catch (\Exception $e) {

            if (empty($this->errorRoute)) {
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
     * '{moduleId}/{controllerId}/{actionId}'
     * '{moduleId}/{controllerId}'
     * '{moduleId}'
     * ```
     * @throws RuntimeException
     * @return string The output result.
     */
    public function dispatch($route)
    {
        $parts = explode('/', trim($route, '/'));

        if (!isset($parts[0])) {
            throw new RuntimeException("Module not found in the route $route.");
        }

        $moduleId = $parts[0];
        $controllerId = isset($parts[1])? $parts[1] : null;
        $actionId = isset($parts[2])? $parts[2] : null;
        $module = $this->getModule($moduleId);
        $module->id = $moduleId;
        $this->trigger('beforeRender', [&$module, $controllerId, $actionId]);
        $output = $module->run($controllerId, $actionId);

        if ($module->layout !== false) {
            $layout = $module->layout == null ? $this->defaultLayout : $module->layout;
            $view = $this->getView();
            $path = empty($module->layoutPath)? $this->defaultLayoutPath : $module->layoutPath;
            $view->paths[] = $path;
            $output = $view->render($layout, ['content' => $output]);
        }

        $this->trigger('afterRender', [&$module, &$output]);

        return $output;
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
     * Get the application user instance
     *
     * @return User instance
     */
    public function getUser(): ?User
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
        if (!isset($this->config['modules'][$moduleId])) {
            throw new RuntimeException("Configuration not found for module {$moduleId}.");
        }

        return Piko::createObject($this->config['modules'][$moduleId]);
    }

    /**
     * Redirect the application to another url.
     *
     * @param string $url
     * @return void
     */
    public function redirect($url)
    {
        header('Location: ' . $url);
        exit();
    }
}
