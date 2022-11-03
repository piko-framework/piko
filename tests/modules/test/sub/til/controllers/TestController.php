<?php
namespace Piko\Tests\modules\test\sub\til\controllers;

class TestController extends \Piko\Controller
{
    public $layout = false;

    public function indexAction()
    {
        return 'TestModule::SubModule::SubtilModule::TestController::indexAction';
    }
}