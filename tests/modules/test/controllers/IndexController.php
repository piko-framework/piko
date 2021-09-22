<?php
namespace tests\modules\test\controllers;

use piko\Piko;

class IndexController extends \piko\Controller
{
    public $layout = false;

    public function indexAction()
    {
        return 'TestModule::IndexController::indexAction';
    }
}