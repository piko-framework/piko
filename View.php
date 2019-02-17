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
 * Base application view.
 * @author Sylvain PHILIP <contact@sphilip.com>
 */
class View extends Component
{
    /**
     * @var integer Head position.
     */
    const POS_HEAD = 1;

    /**
     * @var integer End of body position.
     */
    const POS_END = 2;

    /**
     * @var array View parameters.
     */
    public $params = [];

    /**
     * @var string the page title
     */
    public $title;

    /**
     * @var array the registered CSS code blocks.
     * @see registerCss()
     */
    public $css = [];

    /**
     * @var array the registered CSS files.
     * @see registerCssFile()
     */
    public $cssFiles = [];

    /**
     * @var array the registered JS code blocks
     * @see registerJs()
     */
    public $js = [];

    /**
     * @var array the registered JS files.
     * @see registerJsFile()
     */
    public $jsFiles = [];

    /**
     * @var array Directories where to find view files.
     */
    public $paths = [];

    /**
     * @var array parts of the head.
     */
    public $head = [];

    /**
     * @var array parts of the end of the body.
     */
    public $endBody = [];

    /**
     * Assemble html in the head position.
     * @return string The head html.
     */
    protected function head()
    {
        if (!empty($this->cssFiles)) {
            foreach ($this->cssFiles as $url) {
                $this->head[] = '<link href="' . $url . '" rel="stylesheet">';
            }
        }

        if (!empty($this->css)) {
            $this->head[] = '<style>';

            foreach ($this->css as $css) {
                $this->head[] = $css;
            }

            $this->head[] = '</style>';
        }

        if (!empty($this->js[self::POS_HEAD])) {
            $this->head[] = '<script>';

            foreach ($this->js[self::POS_HEAD] as $js) {
                $this->head[] = $js;
            }

            $this->head[] = '</script>';
        }

        return implode("\n", $this->head);
    }

    /**
     * Assemble html in the end of the body position.
     * @return string The end of the body html.
     */
    protected function endBody()
    {
        if (!empty($this->jsFiles[self::POS_END])) {
            foreach ($this->jsFiles[self::POS_END] as $url) {
                $this->endBody[] = '<script src="' . $url . '"></script>';
            }
        }

        if (!empty($this->js[self::POS_END])) {
            $this->endBody[] = '<script>' . PHP_EOL;

            foreach ($this->js[self::POS_END] as $js) {
                $this->endBody[] = $js;
            }

            $this->endBody[] = '</script>' . PHP_EOL;
        }

        return implode("\n", $this->endBody);
    }

    /**
     * Register a script url.
     * @param string $url The script url.
     * @param int $position The view position where to insert the script (default at the end of the body).
     * @param string $key An optional identifier
     */
    public function registerJsFile($url, $position = self::POS_END, $key = null)
    {
        $key = $key ?: md5($url);
        $this->jsFiles[$position][$key] = $url;
    }

    /**
     * Register a stylesheet url.
     * @param string $url The stylesheet url.
     */
    public function registerCSSFile($url)
    {
        $this->cssFiles[] = $url;
    }

    /**
     * Register a script.
     * @param string $js The script code.
     * @param int $position The view position where to insert the script (default at the end of the body).
     * @param string $key An optional identifier
     */
    public function registerJs($js, $position = self::POS_END, $key = null)
    {
        $key = $key ?: md5($js);
        $this->js[$position][$key] = $js;
    }

    /**
     * Register css code.
     * @param string $css The css code.
     * @param string $key An optional identifier
     */
    public function registerCSS($css, $key = null)
    {
        $key = $key ?: md5($css);
        $this->css[$key] = $css;
    }

    /**
     * Escape HTML special characters.
     * @param string $string Dirty html.
     * @return string Clean html.
     */
    public function escape($string)
    {
        return htmlentities($string, ENT_COMPAT | ENT_HTML5, Piko::$app->charset);
    }

    /**
     * Retrieve a view file.
     * @param string $viewName The view name (without .php extension).
     * @throws \RuntimeException if view file not found.
     * @return string The path of the view file.
     */
    protected function findFile($viewName)
    {
        foreach ($this->paths as $path) {
            if (file_exists(Piko::getAlias($path) . '/' . $viewName . '.php')) {
                return Piko::getAlias($path) . '/' . $viewName . '.php';
            }
        }

        throw new \RuntimeException("Cannot find the view file for the viewname: $viewName");
    }

    /**
     * Render the view.
     *
     * @param string $file The view file name.
     * @param array $data An array of data (name-value pairs) to transmit to the view file.
     * @return string The view output.
     */
    public function render($file, $model = [])
    {
        if (! file_exists($file)) {
            $file = $this->findFile($file);
        }

        $this->trigger('beforeRender', [
            $file,
            $model
        ]);

        extract($model, EXTR_OVERWRITE);

        ob_start();
        require $file;
        $output = ob_get_contents();
        ob_end_clean();

        $this->trigger('afterRender', [
            $output,
            $file,
            $model
        ]);

        return $output;
    }
}
