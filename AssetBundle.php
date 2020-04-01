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
 * AssetBundle represents a collection of CSS files and JS files to publish inside the public path.
 *
 * @author Sylvain PHILIP <contact@sphilip.com>
 */
class AssetBundle extends Component
{
    /**
     * @var string The bundle name. (eg. jquery, bootstrap, etc.)
     */
    public $name = '';

    /**
     * @var string the directory that contains the source asset files for this asset bundle.
     * You can use either a directory or an alias of the directory.
     */
    public $sourcePath = '';

    /**
     * @var array list of JavaScript files that this bundle contains.
     */
    public $js = [];

    /**
     * @var array list of CSS files that this bundle contains.
     */
    public $css = [];

    /**
     * @var integer Position of the js file in the generated view.
     * @see View
     */
    public $jsPosition = View::POS_END;

    /**
    * @var string the root directory storing the published asset files.
    */
    public $publishedBasePath = '@webroot/assets';

    /**
     * @var string the base URL through which the published asset files can be accessed.
     */
    public $publishedBaseUrl = '@web/assets';

    /**
     * @var array Bundle dependencies.
     */
    public $dependencies = [];

    /**
     * @var AssetBundle[] list of the registered asset bundles. The keys are the bundle names, and the values
     * are the registered [[AssetBundle]] objects.
     * @see register()
     */
    protected static $assetBundles = [];


    /**
     * Registers this asset bundle with a view.
     * @param View $view the view to be registered with
     *
     * @return static the registered asset bundle instance
     */
    public static function register($view)
    {
        $className = get_called_class();
        $bundle = new $className();

        if (isset(static::$assetBundles[$className])) {
            return static::$assetBundles[$className];
        }

        $bundle->trigger('register', [$className, $bundle]);

        static::$assetBundles[$className] = $bundle;

        foreach ($bundle->dependencies as $class) {
            call_user_func($class . '::register', $view);
        }

        $beforeHead = function () use ($bundle) {
            /* @var $this View */
            $bundle->publish();

            foreach ($bundle->css as $cssFile) {
                $this->registerCSSFile(
                    Piko::getAlias($bundle->publishedBaseUrl) . '/' . $bundle->name . '/' . $cssFile
                );
            }

            if ($bundle->jsPosition == View::POS_HEAD) {
                foreach ($bundle->js as $jsFile) {
                    $this->registerJsFile(
                        Piko::getAlias($bundle->publishedBaseUrl) . '/' . $bundle->name . '/' . $jsFile,
                        View::POS_HEAD
                    );
                }
            }
        };

        $view->on('beforeHead', $beforeHead->bindTo($view));

        if ($bundle->jsPosition == View::POS_END) {

            $beforeEndBody = function () use ($bundle) {
                /* @var $this View */
                $bundle->publish();

                foreach ($bundle->js as $jsFile) {
                    $this->registerJsFile(
                        Piko::getAlias($bundle->publishedBaseUrl) . '/' . $bundle->name . '/' . $jsFile,
                        View::POS_END
                    );
                }
            };

            $view->on('beforeEndBody', $beforeEndBody->bindTo($view));
        }

        return $bundle;
    }

    /**
     * Publish assets into public path
     */
    public function publish()
    {
        if (!file_exists(Piko::getAlias($this->publishedBasePath) . '/' . $this->name)) {
            $this->copy(
                Piko::getAlias($this->sourcePath),
                Piko::getAlias($this->publishedBasePath) . '/' . $this->name
            );
        }
    }

    /**
     * Copy recursively a folder into another one.
     *
     * @param string $src The source directory to copy
     * @param string $dest The destination directory to copy
     */
    protected function copy($src, $dest)
    {
        if (!file_exists($src)) {
            throw new \RuntimeException("Src: $src does not exists.");
        }

        $dir = opendir($src);
        mkdir($dest, 0755, true);

        while (false !== ($file = readdir($dir))) {

            if (( $file != '.' ) && ( $file != '..' )) {
                if (is_dir($src . '/' . $file)) {
                    $this->copy($src . '/' . $file, $dest . '/' . $file);
                } else {
                    copy($src . '/' . $file, $dest . '/' . $file);
                }
            }
        }

        closedir($dir);
    }
}
