<?php
namespace tests\modules\test\controllers;

class DefaultController extends \Piko\Controller
{
    public $layout = false;

    public function indexAction()
    {
        return 'TestModule::DefaultController::indexAction';
    }
}