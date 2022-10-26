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

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ReflectionClass;
use RuntimeException;

/**
 * Module is the base class for classes containing module logic.
 *
 * @author Sylvain PHILIP <contact@sphilip.com>
 */
abstract class Module extends Component implements RequestHandlerInterface
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
     * @var array<string>
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
     * Sub modules configuration
     *
     * @var array<mixed>
     */
    public $modules = [];

    /**
     * The layout directory of the module.
     *
     * @var string
     */
    public $layoutPath;

    /**
     * The name of the module's layout file or false
     * to deactivate the layout rendering
     *
     * @var string|false
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
     * Get a sub module of this module
     *
     * @param string $moduleId The module identifier
     * @throws RuntimeException If module not found
     * @return Module
     */
    public function getModule($moduleId): Module
    {
        if (!isset($this->modules[$moduleId])) {
            throw new RuntimeException("Configuration not found for sub module {$moduleId}.");
        }

        $module = Piko::createObject($this->modules[$moduleId]);

        if ($module instanceof Module) {
            return $module;
        }

        throw new RuntimeException("module $moduleId must be instance of Module");
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
            $this->basePath = dirname($class->getFileName()); // @phpstan-ignore-line
        }

        return $this->basePath;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->id = $request->getAttribute('module', '');
        $controllerId = $request->getAttribute('controller', 'default');
        $controller = $this->createController($controllerId);

        // TODO: wrap controller response into a layout

        return  $controller->handle($request);
    }

    protected function createController(string $controllerId): Controller
    {
        $controllerName = str_replace(' ', '', ucwords(str_replace('-', ' ', $controllerId))) . 'Controller';
        $controllerClass = $this->controllerMap[$controllerId] ?? $this->controllerNamespace . '\\' . $controllerName;

        $controller = new $controllerClass();
        $controller->module = $this;
        $controller->id = $controllerId;

        return $controller;
    }
}
