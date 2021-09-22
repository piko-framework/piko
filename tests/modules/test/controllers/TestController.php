<?php
namespace tests\modules\test\controllers;

use piko\Piko;

class TestController extends \piko\Controller
{
    public $layout = false;

    public function indexAction()
    {
        return 'index Action';
    }

    public function errorAction()
    {
        $exception = Piko::get('exception');

        if ($exception instanceof \Throwable) {
            return $exception->getMessage();
        }
    }

    public function index2Action()
    {
        $this->layout = 'main';

        return 'index2 Action';
    }

    public function index3Action()
    {
        return $_GET['id'];
    }
}