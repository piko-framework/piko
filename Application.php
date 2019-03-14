<?php
/**
 * This file is part of Piko - Web micro framework
 *
 * @copyright 2019 Sylvain PHILIP.
 * @license LGPL-3.0; see LICENSE.txt
 * @link https://github.com/ilhooq/piko
 */
namespace piko;

/**
 * The Web application class
 *
 * @author Sylvain PHILIP <contact@sphilip.com>
 */
class Application extends Component
{
    /**
     * @var string The absolute base path of the application.
     */
    public $basePath = '';

    /**
     * @var array list of modules that should be run during the application bootstrapping process.
     *
     * Each module may be specified with a module ID as specified via [[modules]].
     *
     * During the bootstrapping process, each module will be instantiated. If the module class
     * implements the bootstrap() method, this method will be also be called.
     */
    public $bootstrap = [];

    /**
     * @var string The charset encoding used in the application.
     */
    public $charset = 'UTF-8';

    /**
     * @var array The configuration loaded on application instantiation.
     */
    public $config = [];

    /**
     * @var string The default layout name without file extension.
     */
    public $defaultLayout = 'main';

    /**
     * @var string The default layout path. An alias could be used.
     */
    public $defaultLayoutPath = '@app/layouts';

    /**
     * @var string The Error route to display exceptions in a friendly way.
     *
     * If not set, Exceptions catched will be thrown and stop the script execution.
     */
    public $errorRoute = '';

    /**
     * @var string The language that is meant to be used for end users.
     */
    public $language = 'en';

    /**
     * Constructor
     *
     * @param array $config The application configuration.
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
    }

    /**
     * Run the application.
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

        $router = Piko::get('router');

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
     * '{moduleId}/{controllerId}/{actionId}'
     * '{moduleId}/{controllerId}'
     * '{moduleId}'
     * @throws \RuntimeException
     * @return string The output result.
     */
    public function dispatch($route)
    {
        $parts = explode('/', trim($route, '/'));

        if (!isset($parts[0])) {
            throw new \RuntimeException("Module not found in the route $route.");
        }

        $moduleId = $parts[0];

        if (!isset($this->config['modules'][$moduleId])) {
            throw new \RuntimeException("Configuration not found for module {$moduleId}.");
        }

        $controllerId = isset($parts[1])? $parts[1] : null;
        $actionId = isset($parts[2])? $parts[2] : null;

        $module = Piko::createObject($this->config['modules'][$moduleId]);
        $module->id = $moduleId;

        $this->trigger('beforeRender', [&$module, $controllerId, $actionId]);
        $output = $module->run($controllerId, $actionId);

        if ($module->layout !== false) {
            $layout = $module->layout == null ? $this->defaultLayout : $module->layout;
            $view = Piko::get('view');
            $path = empty($module->layoutPath)? $this->defaultLayoutPath : $module->layoutPath;
            $view->paths[] = $path;
            $output = $view->render($layout, ['content' => $output]);
        }

        $this->trigger('afterRender', [&$module, &$output]);

        return $output;
    }

    /**
     * Redirect the application to another url.
     *
     * @param string $url
     */
    public function redirect($url)
    {
        header('Location: ' . $url);
        exit();
    }
}
