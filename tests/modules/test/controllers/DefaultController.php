<?php
namespace tests\modules\test\controllers;

use piko\Piko;

class DefaultController extends \piko\Controller
{
    public $layout = false;

    public function indexAction()
    {
        return 'TestModule::DefaultController::indexAction';
    }
}