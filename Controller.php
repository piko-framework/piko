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
 * Controller is the base class for classes containing controller logic.
 *
 * @author Sylvain PHILIP <contact@sphilip.com>
 */
class Controller extends Component
{
    /**
     * @var string The controller identifier.
     */
    public $id = '';

    /**
     * @var null|string|false The name of the layout to be applied to this controller's views.
     * This property mainly affects the behavior of render().
     * Defaults to null, meaning the actual layout value should inherit that from module's layout value.
     * If false, no layout will be applied.
     */
    public $layout;

    /**
     * @var string the root directory that contains view files for this controller.
     */
    public $viewPath;

    /**
     * @var Module the module that this controller belongs to.
     */
    public $module;

    /**
     * Runs an action within this controller with the specified action ID.

     * @param string $id the ID of the action to be executed.
     * @return mixed the result of the action.
     * @throws \RuntimeException if the requested action ID cannot be resolved into an action successfully.
     */
    public function runAction($id)
    {
        $this->trigger('beforeAction', [$this, $id]);

        $methodName = $id . 'Action';

        if (!method_exists($this, $methodName)) {
            throw new \RuntimeException("Method \"$methodName\" not found in " . get_class($this));
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
     * @return string The view output.
     */
    public function render($viewName, $data = [])
    {
        /* @var $view View */
        $view = Piko::get('view');
        $view->paths[] = $this->getViewPath();

        return $view->render($viewName, $data);
    }

    /**
     * Returns the directory containing view files for this controller.
     * The default implementation returns the directory named as controller id under the module's
     * viewPath directory.
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
}
