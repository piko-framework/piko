<?php
namespace tests\modules\test\sub\controllers;


class TestController extends \piko\Controller
{
    public $layout = false;

    public function indexAction()
    {
        return 'TestModule::SubModule::TestController::indexAction';
    }
}