<?php
namespace Piko\Tests\lib;

use Piko\View\ViewInterface;

class CustomView implements ViewInterface
{
    public $viewPath = '';

    public function render(string $file, array $model = []): string
    {
        $output = '';

        if (file_exists($this->viewPath . '/' . $file . '.php')) {

            extract($model, EXTR_OVERWRITE);

            ob_start();
            require $this->viewPath . '/' . $file . '.php';
            $output = (string) ob_get_contents();
            ob_end_clean();
        }

        return $output;
    }
}