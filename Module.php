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
 * Module is the base class for classes containing module logic.
 *
 * @author Sylvain PHILIP <contact@sphilip.com>
 */
class Module extends Component
{
    /**
     * @var string The module identifier.
     */
    public $id = '';

    /**
     * @var array Mapping from controller ID to controller class.
     */
    public $controllerMap = [];

    /**
     * @var string Base name space of module's controllers.
     * Default to \{baseModuleNameSpace}\\controllers
     */
    public $controllerNamespace;

    /**
     * @var string The layout directory of the module.
     */
    public $layoutPath;

    /**
     * @var string The name of the module's layout file.
     */
    public $layout;

    /**
     * @var string the root directory of the module.
     */
    private $basePath;

    /**
     * {@inheritDoc}
     * @see \piko\Component::init()
     */
    protected function init()
    {
        if ($this->controllerNamespace === null) {
            $class = get_class($this);
            if (($pos = strrpos($class, '\\')) !== false) {
                $this->controllerNamespace = substr($class, 0, $pos) . '\\controllers';
            }
        }
    }

    /**
     * Returns the root directory of the module.
     * @return string the root directory of the module.
     */
    public function getBasePath()
    {
        if ($this->basePath === null) {
            $class = new \ReflectionClass($this);
            $this->basePath = dirname($class->getFileName());
        }

        return $this->basePath;
    }

    /**
     * Run module controller action.
     *
     * @param string $controllerId The controller identifier.
     * @param string $actionId The controller action identifier.
     * @return mixed The module output.
     */
    public function run($controllerId, $actionId)
    {
        $controllerName = str_replace(' ', '', ucwords(str_replace('-', ' ', $controllerId))) . 'Controller';
        $actionId = str_replace(' ', '', ucwords(str_replace('-', ' ', $actionId)));

        $controllerClass = isset($this->controllerMap[$controllerId])?
                           $this->controllerMap[$controllerId] :
                           $this->controllerNamespace . '\\' . $controllerName;

        $controller = new $controllerClass;
        $controller->module = $this;
        $controller->id = $controllerId;

        $output = $controller->runAction($actionId);

        if ($controller->layout) {
            $this->layout = $controller->layout;
        } elseif ($controller->layout === false) {
            $this->layout = false;
        }

        return $output;
    }
}
