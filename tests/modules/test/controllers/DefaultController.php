<?php
namespace Piko\Tests\modules\test\controllers;

class DefaultController extends \Piko\Controller
{
    public $layout = false;

    public function indexAction()
    {
        return 'TestModule::DefaultController::indexAction';
    }

    public function errorAction($exception)
    {
        return $exception->getMessage();
    }
}