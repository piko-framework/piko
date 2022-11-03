<?php
namespace Piko\Tests\modules\test\sub\controllers;


class TestController extends \Piko\Controller
{
    public $layout = false;

    public function indexAction()
    {
        return 'TestModule::SubModule::TestController::indexAction';
    }
}