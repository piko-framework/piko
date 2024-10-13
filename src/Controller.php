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
use Piko\View\ViewInterface;
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
    public $module;

    /**
     * @var ViewInterface
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

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->request = $request;
        $this->response = new Response();
        $params = array_merge(
            (array) $this->request->getAttribute('route_params', []),
            $this->request->getQueryParams()
        );
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
        $beforeEvent = new BeforeActionEvent($this, $id, $params);
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

            if (!is_string($response)) {
                $response = (string) $response;
            }

            $view = $this->getView();

            if ($view instanceof View) {
                $app = $this->module->getApplication();

                if ($this->layout !== false) {
                    $layout = $this->layout === null ? $app->defaultLayout : $this->layout;
                    $path = $this->module->layoutPath ?? $app->defaultLayoutPath ;
                    $view->paths[] = $path;
                    $response = $view->render($layout, ['content' => $response]);
                }
            }

            $response = $this->response->withBody((new StreamFactory())->createStream($response));

        }

        $afterEvent = new AfterActionEvent($this, $id, $response);
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
     * @return string
     */
    protected function render(string $viewName, array $data = []): string
    {
        $view = $this->getView();

        if (!$view instanceof ViewInterface) {
            throw new RuntimeException('View must be instance of Piko\View\ViewInterface');
        }

        if ($view instanceof View) {
            $view->paths[] = $this->getViewPath();
        }

        return $view->render($viewName, $data);
    }

    /**
     * Converts a given modular route into its corresponding URL.
     *
     * This method is useful for generating URLs dynamically, based on the specified
     * route and optional parameters. The route should follow the format:
     * {moduleId}/{ControllerId}/{ActionId}.
     *
     * @param string $route The modular route in the format {moduleId}/{ControllerId}/{ActionId}.
     *                      This string determines which module, controller, and action to target.
     * @param array<string> $params An optional associative array of query parameters to append
     *                              to the generated URL. Each key-value pair represents a parameter
     *                              name and its corresponding value.
     * @param bool $absolute An optional boolean flag indicating whether the generated URL should be
     *                       absolute (including protocol and host) or relative. Defaults to false,
     *                       meaning a relative URL will be returned.
     * @return string The resulting URL that corresponds to the provided route and parameters.
     */
    protected function getUrl(string $route, array $params = [], $absolute = false): string
    {
        $router = $this->module->getApplication()->getComponent(Router::class);

        if (!$router instanceof Router) {
            throw new RuntimeException(
                'getUrl method needs that Piko\Router is registered as application component'
            );

        }

        return $router->getUrl($route, $params, $absolute);
    }

    /**
     * Returns the application View component
     *
     * @return ViewInterface|null
     */
    protected function getView(): ?ViewInterface
    {
        if ($this->view === null) {
            try {
                $view = $this->module->getApplication()->getComponent(View::class);
                assert($view instanceof View);
                $view->attachBehavior('getUrl', [$this, 'getUrl']);
            } catch (RuntimeException $e) {
                try {
                    $view = $this->module->getApplication()->getComponent(ViewInterface::class);
                } catch (RuntimeException $e) {
                    return null;
                }
            }

            assert($view instanceof ViewInterface);
            $this->view = $view;
        }

        return $this->view;
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
