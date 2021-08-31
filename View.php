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
use Exception;

/**
 * Base application view.
 * @author Sylvain PHILIP <contact@sphilip.com>
 */
class View extends Component
{
    /**
     * Head position.
     *
     * @var integer
     */
    const POS_HEAD = 1;

    /**
     * End of body position.
     *
     * @var integer
     */
    const POS_END = 2;

    /**
     * View filename extension
     *
     * @var string
     */
    public $extension = 'php';

    /**
     * View parameters.
     *
     * @var array
     */
    public $params = [];

    /**
     * The page title
     *
     * @var string
     */
    public $title;

    /**
     * The registered CSS code blocks.
     *
     * @var array
     * @see View::registerCss()
     */
    public $css = [];

    /**
     * The registered CSS files.
     *
     * @var array
     * @see View::registerCssFile()
     */
    public $cssFiles = [];

    /**
     * The registered JS code blocks
     *
     * @var array
     * @see View::registerJs()
     */
    public $js = [];

    /**
     * The registered JS files.
     *
     * @var array
     * @see View::registerJsFile()
     */
    public $jsFiles = [];

    /**
     * Directories where to find view files.
     *
     * @var array
     */
    public $paths = [];

    /**
     * Parts of the head.
     *
     * @var array
     */
    public $head = [];

    /**
     * Parts of the end of the body.
     *
     * @var array
     */
    public $endBody = [];

    /**
     * Theme map configuration.
     *
     * A key paired array where each key represents
     * a path to override and the value, the mapped path. The value could be either a string
     * or an array of path and in this case, it may be possibe to use child themes.
     * Configuration example :
     * ```
     * ...
     * 'view' => [
     *     'class' => 'piko\View',
     *     'themeMap' => [
     *         '@app/modules/site/views' => [
     *             '@app/themes/child-theme',
     *             '@app/themes/parent-theme',
     *         ],
     *         '@app/modules/admin/views' => '@app/themes/piko/admin',
     *     ],
     * ],
     * ```
     *
     * @var array
     */
    public $themeMap = [];

    /**
     * Assemble html in the head position.
     *
     * @return string The head html.
     */
    protected function head(): string
    {
        $this->trigger('beforeHead');

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
     *
     * @return string The end of the body html.
     */
    protected function endBody(): string
    {
        $this->trigger('beforeEndBody');

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
     *
     * @param string $url The script url.
     * @param int $position The view position where to insert the script (default at the end of the body).
     * @param string $key An optional identifier
     * @return void
     */
    public function registerJsFile(string $url, int $position = self::POS_END, string $key = null): void
    {
        $key = $key ?: md5($url);
        $this->jsFiles[$position][$key] = $url;
    }

    /**
     * Register a stylesheet url.
     *
     * @param string $url The stylesheet url.
     * @return void
     */
    public function registerCSSFile(string $url): void
    {
        $this->cssFiles[] = $url;
    }

    /**
     * Register a script.
     *
     * @param string $js The script code.
     * @param int $position The view position where to insert the script (default at the end of the body).
     * @param string $key An optional identifier
     * @return void
     */
    public function registerJs(string $js, int $position = self::POS_END, string $key = null): void
    {
        $key = $key ?: md5($js);
        $this->js[$position][$key] = $js;
    }

    /**
     * Register css code.
     *
     * @param string $css The css code.
     * @param string $key An optional identifier
     * @return void
     */
    public function registerCSS(string $css, string $key = null): void
    {
        $key = $key ?: md5($css);
        $this->css[$key] = $css;
    }

    /**
     * Escape HTML special characters.
     *
     * @param string $string Dirty html.
     * @return string Clean html.
     */
    public function escape(?string $string): string
    {
        return htmlentities($string, ENT_COMPAT | ENT_HTML5, Piko::$app->charset);
    }

    /**
     * Convenient method to convert a route to an url
     *
     * @param string $route The route to convert
     * @param array $params The route params
     * @throws RuntimeException if router is not instance of piko\Router
     * @return string
     * @see Router::getUrl
     */
    protected function getUrl(string $route, array $params = []): string
    {
        $router = Piko::get('router');

        if ($router instanceof Router) {
            return $router->getUrl($route, $params);
        }

        throw new RuntimeException('Router must be instance of piko\Router');
    }

    /**
     * Retrieve a view file.
     *
     * @param string $viewName The view name (without extension).
     * @throws RuntimeException if view file not found.
     * @return string The path of the view file.
     */
    protected function findFile(string $viewName): string
    {
        foreach ($this->paths as $path) {
            if (file_exists(Piko::getAlias($path) . '/' . $viewName . '.' . $this->extension)) {
                return Piko::getAlias($path) . '/' . $viewName . '.' . $this->extension;
            }
        }

        throw new RuntimeException("Cannot find the view file for the viewname: $viewName");
    }

    /**
     * Try to find an override of the file in a theme.
     *
     * @param string $path The file path
     * @return string The overriden or not file path
     */
    protected function applyTheme(string $path): string
    {
        if (!empty($this->themeMap)) {

            foreach ($this->themeMap as $from => $tos) {
                $from = Piko::getAlias($from);

                if (strpos($path, $from) === 0) {
                    $n = strlen($from);

                    foreach ((array) $tos as $to) {
                        $to = Piko::getAlias($to);
                        $file = $to . substr($path, $n);
                        if (is_file($file)) {
                            return $file;
                        }
                    }
                }
            }
        }

        return $path;
    }

    /**
     * Render the view.
     *
     * @param string $file The view file name.
     * @param array $data An array of data (name-value pairs) to transmit to the view file.
     * @return string The view output.
     */
    public function render(string $file, array $model = []): string
    {
        if (!file_exists($file)) {
            $file = $this->findFile($file);
        }

        $file = $this->applyTheme($file);

        $this->trigger('beforeRender', [
            $file,
            $model
        ]);

        extract($model, EXTR_OVERWRITE);

        ob_start();

        try {
            require $file;
            $output = ob_get_contents();
        } catch (Exception $e) {
            throw $e;
        } finally {
            ob_end_clean();
        }

        $this->trigger('afterRender', [
            $output,
            $file,
            $model
        ]);

        return $output;
    }
}
