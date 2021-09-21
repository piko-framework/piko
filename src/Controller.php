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
 * Controller is the base class for classes containing controller logic.
 *
 * @author Sylvain PHILIP <contact@sphilip.com>
 */
abstract class Controller extends Component
{
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
     * Runs an action within this controller with the specified action ID.

     * @param string $id the ID of the action to be executed.
     * @return mixed the result of the action.
     * @throws RuntimeException if the requested action ID cannot be resolved into an action successfully.
     */
    public function runAction(string $id)
    {
        $this->trigger('beforeAction', [$this, $id]);

        $methodName = $id . 'Action';

        if (!method_exists($this, $methodName)) {
            throw new RuntimeException("Method \"$methodName\" not found in " . get_called_class());
        }

        $output = $this->$methodName();
        $this->trigger('afterAction', [$this, $id, $output]);

        return $output;
    }

    /**
     * Render a view.
     *
     * @param string $viewName The view file name.
     * @param array $data An array of data (name-value pairs) to transmit to the view.
     * @return string|null The view output.
     */
    protected function render(string $viewName, array $data = []): ?string
    {
        $view = Piko::$app->getView();
        $view->paths[] = $this->getViewPath();

        return $view->render($viewName, $data);
    }

    /**
     * Set a response redirection
     *
     * @param string $url The url to redirect
     */
    protected function redirect(string $url): void
    {
        Piko::$app->setHeader('Location', $url);
    }

    /**
     * Proxy to Application::dispatch
     *
     * @param string $route The route to forward
     */
    protected function forward(string $route): string
    {
        return Piko::$app->dispatch($route);
    }

    /**
     * Convenient method to convert a route to an url
     *
     * @param string $route The route to convert
     * @param array $params The route params
     * @param boolean $absolute Optional to have an absolute url.
     * @throws RuntimeException if router is not instance of piko\Router
     * @return string
     * @see Router::getUrl
     */
    protected function getUrl(string $route, array $params = [], bool $absolute = false): string
    {
        $router = Piko::get('router');

        if ($router instanceof Router) {
            return $router->getUrl($route, $params);
        }

        throw new RuntimeException('Router must be instance of piko\Router');
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
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            return true;
        }

        return false;
    }

    /**
     * Get the request method
     *
     * @return string
     */
    protected function getMethod(): string
    {
        return $_SERVER['REQUEST_METHOD'] ?? '';
    }

    /**
     * Check if the request method is GET
     *
     * @return boolean
     */
    protected function isGet(): bool
    {
        return $this->getMethod() === 'GET';
    }

    /**
     * Check if the request method is POST
     *
     * @return boolean
     */
    protected function isPost(): bool
    {
        return $this->getMethod() === 'POST';
    }

    /**
     * Check if the request method is PUT
     *
     * @return boolean
     */
    protected function isPut(): bool
    {
        return $this->getMethod() === 'PUT';
    }

    /**
     * Check if the request method is DELETE
     *
     * @return boolean
     */
    protected function isDelete(): bool
    {
        return $this->getMethod() === 'DELETE';
    }

    /**
     * Get the raw input data of the request
     *
     * @param int $size The size in bytes of the raw input
     * @return string
     */
    protected function rawInput($size = 1024)
    {
        $handle = fopen('php://input', 'r');
        $data = fread($handle, $size);
        fclose($handle);

        return $data;
    }

    /**
     * Convenient method to return a JSON response
     *
     * @param mixed $data
     * @return string
     */
    protected function jsonResponse($data)
    {
        $this->layout = false;
        Piko::$app->setHeader('Content-Type', 'application/json');

        return json_encode($data);
    }
}
