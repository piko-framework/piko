<?php
namespace tests\modules\test\sub\til\controllers;


class TestController extends \piko\Controller
{
    public $layout = false;

    public function indexAction()
    {
        return 'TestModule::SubModule::SubtilModule::TestController::indexAction';
    }
}