<?php
namespace tests\modules\test\controllers;


class TestController extends \piko\Controller
{
    public $layout = false;

    public function indexAction()
    {
        return 'index Action';
    }

    public function index2Action()
    {
        return 'index2 Action';
    }

    public function index3Action()
    {
        return $_GET['id'];
    }
}