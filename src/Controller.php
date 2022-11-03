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

use HttpSoft\Message\Response;
use HttpSoft\Message\StreamFactory;
use Piko\Controller\Event\AfterActionEvent;
use Piko\Controller\Event\BeforeActionEvent;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use RuntimeException;

/**
 * Controller is the base class for classes containing controller logic.
 *
 * @method string getUrl(string $route, array<mixed> $params, boolean $absolute) Convenient method to convert
 * a route to an url (see Router::getUrl()). This method is implemented as a behavior and can be overriden.
 *
 * @author Sylvain PHILIP <contact@sphilip.com>
 */
abstract class Controller implements RequestHandlerInterface
{
    use BehaviorTrait;
    use EventHandlerTrait;

    /**
     * The controller identifier.
     *
     * @var string
     */
    public $id = '';

    /**
     * The name of the layout to be applied to this controller's views.
     *
     * This property mainly affects the behavior of render().
     * Defaults to null, meaning the actual layout value should inherit that from module's layout value.
     * If false, no layout will be applied.
     *
     * @var null|string|false
     */
    public $layout;

    /**
     * The root directory that contains view files for this controller.
     *
     * @var string
     */
    public $viewPath;

    /**
     * The module that this controller belongs to.
     *
     * @var Module
     */
    protected $module;

    /**
     * @var View
     */
    protected $view;

    /**
     *
     * @var ServerRequestInterface
     */
    protected $request;

    /**
     *
     * @var ResponseInterface
     */
    protected $response;

    public function __construct(Module $module)
    {
        $this->module = $module;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->request = $request;
        $this->response = new Response();
        $params = $this->request->getAttribute('route_params', []);
        $actionId = $this->request->getAttribute('action', 'index');

        return $this->runAction($actionId, $params); // @phpstan-ignore-line
    }

    /**
     * Runs an action within this controller with the specified action ID.

     * @param string $id the ID of the action to be executed.
     * @param mixed[] $params An array of request parameters.
     * @return ResponseInterface the result of the action.
     * @throws RuntimeException if the requested action ID cannot be resolved into an action successfully.
     */
    private function runAction(string $id, array $params = []): ResponseInterface
    {
        $beforeEvent = new BeforeActionEvent($this, $params);
        $this->trigger($beforeEvent);
        $methodName = \lcfirst(str_replace(' ', '', ucwords(str_replace('-', ' ', $id)))) . 'Action';

        if (!method_exists($this, $methodName)) {
            throw new RuntimeException("Method \"$methodName\" not found in " . get_called_class());
        }

        $actionParams = $this->getMethodArguments($methodName, $beforeEvent->params);

        if (count($actionParams)) {
            // @phpstan-ignore-next-line
            $response = call_user_func_array([$this, $methodName], $actionParams);
        } else {
            $response = $this->$methodName();
        }

        if (!$response instanceof ResponseInterface) {
            $response = $this->response->withBody((new StreamFactory())->createStream((string) $response));
        }

        $afterEvent = new AfterActionEvent($this, $response);
        $this->trigger($afterEvent);

        return $afterEvent->response;
    }

    /**
     * @param string $methodName The method to analyse
     * @param array<mixed> $data A key-value paired array to bind into the method arguments.
     * @return array<mixed>
     */
    private function getMethodArguments(string $methodName, array $data = []): array
    {
        $method = new \ReflectionMethod(get_called_class(), $methodName);
        $actionParams = [];

        foreach ($method->getParameters() as $param) {
            /* @var $param \ReflectionParameter */
            $name = (string) $param->getName();

            if (isset($data[$name])) {

                switch ((string) $param->getType()) {
                    case 'int':
                        $data[$name] = filter_var($data[$name], FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);
                        break;
                    case 'float':
                        $data[$name] = filter_var($data[$name], FILTER_VALIDATE_FLOAT, FILTER_NULL_ON_FAILURE);
                        break;
                    case 'bool':
                        $data[$name] = filter_var($data[$name], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                        break;
                }

                $actionParams[$name] = $data[$name];
            }
        }

        return $actionParams;
    }

    /**
     * Render a view.
     *
     * @param string $viewName The view file name.
     * @param array<mixed> $data An array of data (name-value pairs) to transmit to the view.
     * @return ResponseInterface
     */
    protected function render(string $viewName, array $data = []): ResponseInterface
    {
        $app = $this->module->getApplication();
        $view = $app->getComponent(View::class);
        $output = '';

        if ($view instanceof View) {
            $view->attachBehavior('getUrl', [$app, 'getUrl']); // @phpstan-ignore-line
            $view->paths[] = $this->getViewPath();
            $output = $view->render($viewName, $data);

            if ($this->layout !== false) {
                $layout = $this->layout === null ? $app->defaultLayout : $this->layout;
                $path = $this->module->layoutPath ?? $app->defaultLayoutPath ;
                $view->paths[] = $path;
                $output = $view->render($layout, ['content' => $output]);
            }
        }

        $body = (new StreamFactory())->createStream($output);

        return $this->response->withBody($body);
    }

    /**
     * Set a response redirection
     *
     * @param string $url The url to redirect
     */
    protected function redirect(string $url): void
    {
        $this->response = $this->response->withHeader('Location', $url);
    }

    /**
     * Forward the given route to another module
     *
     * @param string $route The route to forward
     * @param array<string> $params An array of params (name-value pairs) associated to the route.
     */
    protected function forward(string $route, array $params = []): string
    {
        list($moduleId, $controllerId, $actionId) = ModularApplication::parseRoute($route);

        if ($moduleId) {
            $request = $this->request->withAttribute('module', $moduleId);
            $module = $this->module->getApplication()->getModule($moduleId);

            if ($controllerId) {
                $request = $request->withAttribute('controller', $controllerId);
            }

            if ($actionId) {
                $request = $request->withAttribute('action', $actionId);
            }

            $response = $module->handle($request->withAttribute('route_params', $params));

            return (string) $response->getBody();
        }

        return '';
    }

    /**
     * Returns the directory containing view files for this controller.
     *
     * The default implementation returns the directory named as controller id under the module's
     * viewPath directory.
     *
     * @return string the directory containing the view files for this controller.
     */
    protected function getViewPath()
    {
        if (empty($this->viewPath)) {
            $this->viewPath = $this->module->getBasePath()
                            . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . $this->id;
        }

        return $this->viewPath;
    }

    /**
     * Check if the request is AJAX
     * @return boolean
     */
    protected function isAjax(): bool
    {
        $server = $this->request->getServerParams();

        if (
            isset($server['HTTP_X_REQUESTED_WITH'])
            && strtolower($server['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'
        ) {
            return true;
        }

        return false;
    }

    /**
     * Convenient method to return a JSON response
     *
     * @param mixed $data
     * @return ResponseInterface
     */
    protected function jsonResponse($data)
    {
        $this->layout = false;

        $json = json_encode($data);

        if ($json === false) {
            throw new RuntimeException('JSON encoding error'); // @codeCoverageIgnore
        }

        $body = (new StreamFactory())->createStream($json);

        return $this->response->withHeader('Content-Type', 'application/json')
                              ->withBody($body);
    }
}
