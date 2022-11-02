<?php

/**
 * This file is part of Piko - Web micro framework
 *
 * @copyright 2019-2022 Sylvain PHILIP
 * @license LGPL-3.0; see LICENSE.txt
 * @link https://github.com/piko-framework/piko
 */

declare(strict_types=1);

namespace Piko;

use Piko\ModularApplication\ErrorHandler;
use Piko\ModularApplication\BootstrapMiddleware;
use Piko\ModularApplication\RoutingMiddleware;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;

/**
 * This class implements a modular application
 *
 * @author Sylvain PHILIP <contact@sphilip.com>
 */
class ModularApplication extends Application
{
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
     * @var array<string, string|array<string,mixed>|callable|Module>
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
     * Constructor
     *
     * @param array<string, mixed> $config The application configuration.
     * @return void
     */
    public function __construct(array $config = [])
    {
        if (isset($config['modules']) && is_array($config['modules'])) {
            foreach ($config['modules'] as $id => $definition) {
                if (is_string($definition) || is_array($definition)) {
                    $this->modules[$id] = fn() => \Piko::createObject($definition);
                }
            }

            unset($config['modules']);
        }

        $this->errorHandler = new ErrorHandler($this);

        parent::__construct($config);
    }

    /**
     * {@inheritDoc}
     * @see \Piko\Application::run()
     */
    public function run(ServerRequestInterface $request = null, bool $emitHeaders = true)
    {
        $this->pipe(new BootstrapMiddleware($this));
        $this->pipe(new RoutingMiddleware($this));

        parent::run($request, $emitHeaders);
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
     * Get a module instance
     *
     * @param string $moduleId The module identifier
     * @throws RuntimeException
     *
     * @return Module instance
     */
    public function getModule($moduleId)
    {
        $parts = [];

        if (strpos($moduleId, '/') !== false) {
            $parts = explode('/', trim($moduleId, '/'));
            $moduleId = array_shift($parts);
        }

        if (!isset($this->modules[$moduleId])) {
            throw new RuntimeException("Configuration not found for module {$moduleId}.");
        }

        $module = is_callable($this->modules[$moduleId]) ? $this->modules[$moduleId]() : $this->modules[$moduleId];

        if ($module instanceof Module) {

            // In case of sub module
            while ($parts) {
                $moduleId = array_shift($parts);
                $module = $module->getModule($moduleId);
            }

            $module->setApplication($this);

            return $module;
        }

        throw new RuntimeException("Module $moduleId must be instance of \Piko\Module");
    }
}
