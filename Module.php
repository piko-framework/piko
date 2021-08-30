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

use ReflectionClass;

/**
 * Module is the base class for classes containing module logic.
 *
 * @author Sylvain PHILIP <contact@sphilip.com>
 */
abstract class Module extends Component
{
    /**
     * The module identifier.
     *
     * @var string
     */
    public $id = '';

    /**
     * Mapping from controller ID to controller class.
     *
     * @var array
     */
    public $controllerMap = [];

    /**
     * Base name space of module's controllers.
     * Default to \{baseModuleNameSpace}\\controllers
     *
     * @var string
     */
    public $controllerNamespace;

    /**
     * The layout directory of the module.
     *
     * @var string
     */
    public $layoutPath;

    /**
     * The name of the module's layout file.
     *
     * @var string
     */
    public $layout;

    /**
     * The root directory of the module.
     *
     * @var string
     */
    private $basePath;

    /**
     * {@inheritDoc}
     * @see \piko\Component::init()
     */
    protected function init(): void
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
     *
     * @return string the root directory of the module.
     */
    public function getBasePath()
    {
        if ($this->basePath === null) {
            $class = new ReflectionClass($this);
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
    public function run(string $controllerId, string $actionId)
    {
        $controllerName = str_replace(' ', '', ucwords(str_replace('-', ' ', $controllerId))) . 'Controller';
        $actionId = str_replace(' ', '', ucwords(str_replace('-', ' ', $actionId)));

        $controllerClass = isset($this->controllerMap[$controllerId])?
                           $this->controllerMap[$controllerId] :
                           $this->controllerNamespace . '\\' . $controllerName;

        $controller = new $controllerClass;
        $controller->module = $this;
        $controller->id = $controllerId;

        $output = $controller->runAction(lcfirst($actionId));

        if ($controller->layout) {
            $this->layout = $controller->layout;
        } elseif ($controller->layout === false) {
            $this->layout = false;
        }

        return $output;
    }
}
