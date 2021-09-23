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

    public function sayHelloAction()
    {
        return $this->render('hello', [
            'name' => $_GET['name']
        ]);
    }

    public function goHomeAction()
    {
        $this->redirect($this->getUrl('test/test/index'));
    }

    public function homeTestAction()
    {
        return $this->forward('test/test/index');
    }

    public function testJsonAction()
    {
        return $this->jsonResponse(['status' => 'ok']);
    }

    public function testGetAction()
    {
        if ($this->isGet()) {
            return 'is get';
        }
    }

    public function testPostAction()
    {
        if ($this->isPost()) {
            return 'is post';
        }
    }

    public function testPutAction()
    {
        if ($this->isPut()) {
            return 'is put';
        }
    }

    public function testDeleteAction()
    {
        if ($this->isDelete()) {
            return 'is delete';
        }
    }

    public function testAjaxAction()
    {
        if ($this->isAjax()) {
            return 'is ajax';
        }

        return 'is not ajax';
    }

    public function rawInputAction()
    {
        return $this->rawInput();
    }

}